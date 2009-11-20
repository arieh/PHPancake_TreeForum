<?php
require_once dirname(__FILE__) . "/PancakeTF_Message.class.php";
require_once dirname(__FILE__) . "/PancakeTF_MessagePermissionHandler.class.php";
require_once dirname(__FILE__) . "/PancakeTF_ShusterDB.class.php";

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
	
	protected $delete_flag = false;
	
	protected $_permissions = array(
		'open' => 'open', //open a message
		'update'=>'Edit My Post', //update the database
		'create' => 'Post questions', //create a message
		'move' => 'move', //move a message
		'delete' => 'Delete Post', //delete a messages
		'mark_delete'=>'Flag Post Delete'
	);
	
	public function __construct($id=false, $options = array()){
		if (false === isset($options['dba']) || false === ($options['dba'] instanceof PancakeTF_DBAccessI)){
			$dba = new PancakeTF_ShusterDB(lib_dbutils_ShusterDb::getInstance());
		}else $dba = $options['dba'];
		
		if (false === isset($options['handler']) ||  false === ($options['handler'] instanceof PancakeTF_PermissionHandlerI)){
			$permission_handler = new PancakeTF_MessagePermissionHandler($dba);
		}else $permission_handler = $options['handler'];
		
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
	 * 	@param int $id a user id. if not set will attempt to fetch it independantly
	 * @access public
	 * @return $this
	 */
	public function setUserId($id=false){
		if ($this->getUserId() != self::DEFAULT_USER_ID) throw new LogicException('cannot change user');
		if (false === $id){
			$id = bl_User::getInstance()->getUserId();
		}
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
	
	/**
	 * sets the delete flag for the message
	 * 	@param bool $state what to set the flag to
	 * @access public
	 * @return $this;
	 */
	public function setDeleteFlag($state = false){
		if ($this->_permission_handler->doesHavePermission($this->_permissions['mark_delete'])===false){
			throw new PancakeTF_NoPermissionException('mark_delete');
		}
		$this->delete_flag = (bool)$state;
		return $this;
	}	
	
	/**
	 * returns the status of the delete flag
	 * @access public
	 * @return bool
	 */
	public function getDeleteFlag(){
		return (bool)$this->delete_flag;
	}
	
	/**
	 * accessor for the various setters
	 * 	@param array $options an assosiative array of options to be set and their values
	 * @access public
	 */
	public function setOptions(array $options = array()){
		parent::setOptions($options);
		if (isset($options['user'])) $this->setUserId($options['user']);
		if (isset($options['dba'])) $this->setDBAccessor($options['dba']);
		if (isset($options['handler'])) $this->setPermissionHandler($options['handler']);
		return $this;
	}
	
	/**
	 * sets a DBAccessor
	 * 	@param PancakeTF_DBAccessI $dba a database accessor
	 * @access public
	 * @return $this
	 */
	public function setDBAccessor(PancakeTF_DBAccessI $dba){
		$this->_dba = $dba;
		return $this;
	}
	
	/**
	 * sets a permission handler
	 * 	@param PancakeTF_PermissionHandlerI $ph
	 * @access public
	 * @return $this
	 */
	public function setPermissionHandler(PancakeTF_PermissionHandlerI $ph){
		$this->_permission_handler = $ph;
		return $this;
	}
	
	/**
	 * an accessor for the permission handler
	 * 	@access string $type permssion type to check
	 * @access public
	 * @return true
	 */
	public function doesHavePermission($type){
		return $this->_permission_handler->doesHavePermission($type,$this->getForumId(),$this->getId());
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
				`{$this->user_table}`.email,
				`{$this->extras_table}`.delete_flag
			FROM
				`{$this->extras_table}`
			Inner Join users ON `{$this->user_table}`.id = `{$this->extras_table}`.`user`
			WHERE `message_id` = ?";
		
		$row = $this->_dba->queryRow($extra_sql,array($this->getId()));
		
		$this->user['id'] = $row['user'];
		$this->user['name'] = $row['username'];
		$this->user['email'] = $row['email'];
		$this->votes = $row['votes'];
		$this->delete_flag = (bool)$row['delete_flag'];
	}
	
	protected function updateExtra(){
		$update_sql = "UPDATE `{$this->extras_table}` SET `votes`=?,`delete_flag`=? WHERE `message_id`=?";
		$del_flag = ($this->delete_flag)? 1 : 0;
		$this->_dba->update($update_sql,array($this->getVotes(),$del_flag,$this->getId()));
	}
	
	protected function insertExtra(){
		$insert_sql = "INSERT INTO `{$this->extras_table}`(`message_id`,`votes`,`user`) VALUES (?,?,?)";
		$this->_dba->update($insert_sql,array($this->getId(),$this->getVotes(),$this->getUserId()));
	}
}