<?php
require_once dirname(__FILE__) . "/../classes/PancakeTF_Message.class.php";

class PancakeTF_MessageExtra extends PancakeTF_Message{
	const DEFAULT_USER_ID=0;
	
	/**
	 * @var string the domain user`s table on the db
	 * @access protected
	 */
	protected $user_table = 'users';
	/**
	 * @var string the domain's message extra data table on the db
	 * @access protected
	 */
	protected $extras_table = 'pancaketf_message_extras';
	
	/**
	 * @var array the user data
	 * @access protected
	 */
	protected $user = array(
		'id'=>PancakeTF_MessageExtra::DEFAULT_USER_ID,
		'name'=>'',
		'email'=>''
	);
	
	/**
	 * @var int number of votes the message holds
	 * @access protected
	 */
	protected $votes = 0;
	
	public function __construct(PancakeTF_DBAccessI $dba, PancakeTF_PermissionHandlerI $permission_handler, $id=false, $options = array()){
		parent::__construct($dba,$permission_handler,$id,$options);
	}
	
	/**
	 * sets the message`s id (can be used to open a Message). 
	 * 	@param int $id a valid message id
	 * @access public 
	 * @return PancakeTF_MessageI the current message
	 */
	public function setId($id){
		parent::setId($id);
		$this->retrieveExtraData();
		return $this;
	}
	
	/**
	 * saves the changes made (if any) to the Message to the DB
	 * @access public 
	 */
	public function save(){
		if ($this->getId() === self::DEFAULT_ID){
			if ($this->getUserId() === self::DEFAULT_USER_ID)
				throw new LogicException('user id was not yet set');
			parent::save();
			$this->insertExtra();
		}else{
			parent::save();
			$this->updateExtra();	
		}		
		return $this;
	}
	
	/**
	 * sets the message user id
	 * 	@param int $id a user id
	 * @access public
	 * @return $this
	 */
	public function setUserId($id){
		if ($this->getUserId() != self::DEFAULT_USER_ID) throw new LogicException('cannot change user');
		if ($this->_dba->count($this->user_table,array('id'=>$id))===0){
			throw new InvalidArgumentException('user_id');
		}
		$this->user['id'] = $id;
		return $this;
	}
	
	/**
	 * increases the vote count for a message
	 * @access public
	 */
	public function increaseVote(){ $this->votes++;}
	
	/**
	 * decreases the vote count for a message
	 * @access public
	 */
	public function decreaseVote(){ $this->votes--;}
	
	/**
	 * returns the message`s user id
	 * @access public
	 */
	public function getUserId(){return $this->user['id'];}
	
	/**
	 * returns the user`s data
	 * @access public
	 */
	public function getUserData(){return $this->user;}
	
	/**
	 * returns the number of votes the message have
	 * @access public
	 */
	public function getVotes(){return (int)$this->votes;}
	
	
	public function setOptions(array $options = array()){
		parent::setOptions($options);
		if (isset($options['user'])) $this->setUserId($options['user']);
	}
	
	/**
	 * an accessor to the variouse setters via an array
	 * 	@param array $options an associative array of options and their values
	 * @access public
	 * 
	 * @throws InvalidArgumentException if option is not allowed to be manualy changed
	 */
	protected function retrieveExtraData(){
		$extra_sql = "
			SELECT
				`{$this->extras_table}`.`user`,
				`{$this->extras_table}`.votes,
				`{$this->user_table}`.name as `username`,
				`{$this->user_table}`.email
			FROM
				`{$this->extras_table}`
			Inner Join users ON `{$this->user_table}`.id = `{$this->extras_table}`.`user`
			WHERE `message_id` = ?";
		
		$row = $this->_dba->queryRow($extra_sql,array($this->getId()));
		
		$this->user['id'] = $row['user'];
		$this->user['name'] = $row['username'];
		$this->user['email'] = $row['email'];
		$this->votes = $row['votes'];
	}
	
	protected function updateExtra(){
		$update_sql = "UPDATE `{$this->extras_table}` SET `votes`=? WHERE `id`=?";
		$this->_dba->update($update_sql,array($this->getVotes(),$this->getId()));
	}
	
	protected function insertExtra(){
		$insert_sql = "INSERT INTO `{$this->extras_table}`(`message_id`,`votes`,`user`) VALUES (?,?,?)";
		$this->_dba->update($insert_sql,array($this->getId(),$this->getVotes(),$this->getUserId()));
	}
}