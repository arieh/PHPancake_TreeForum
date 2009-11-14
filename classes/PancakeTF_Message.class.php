<?php
require_once dirname(__FILE__) . "/interfaces/PancakeTF_MessageI.class.php";
require_once dirname(__FILE__) . "/PancakeTF_NoPermissionException.class.php";

class PancakeTF_Message implements PancakeTF_MessageI{
	const DEFAULT_ID = 0;
	const DEFAULT_FORUM_ID = 0;
	const MIN_TITLE_LENGTH = 3;
	const MIN_CONTENT_LENGTH = 0;
	
	/**
	 * @var PancakeTF_DBAccessI a database accessor
	 * @access protected
	 */
	protected $_dba = null;
	
	/**
	 * @var PancakeTF_PermissionHandler a permission handler
	 * @access protected
	 */
	protected $_permission_handler = null;
	
	/**
	 * @var string the messages table in the db
	 * @access protected
	 */
	protected $id_table = 'pancaketf_messages';
	
	/**
	 * @var string the message contents table in the db
	 * @access protected
	 */
	protected $content_table = 'pancaketf_message_contents';
	
	/**
	 * @var string the message forums table in the db
	 * @access protected
	 */
	protected $forums_table = 'pancaketf_forums';
	
	/**
	 * @var bool whether the state of the message is modified and unsaved
	 * @access protected
	 */
	protected $modified = false;
	
	/**
	 * @var string the message`s title
	 * @access protected
	 */
	protected $title = '';
	
	/**
	 * @var int the message's id
	 * @access protected
	 */
	protected $_id = PancakeTF_Message::DEFAULT_ID;
	
	/**
	 * @var string the message`s content
	 * @access protected
	 */
	protected $content = '';
	
	/**
	 * @var string a way to format the date with
	 * @access protected
	 */
	protected $date_format ='%d/%m/%Y';
	
	/**
	 * @var int the message`s forum id
	 * @access protected
	 */
	protected $_forum_id = PancakeTF_Message::DEFAULT_FORUM_ID;
	
	/**
	 * @var PancakTF_MessageI a parent message
	 * @access protected
	 */
	protected $parent = null;
	
	/**
	 * @var string the message`s dna
	 * @access protected
	 */
	protected $_dna = '';
	
	/**
	 * @var bool whether or not the dna was modified in any way
	 * @access protected
	 */
	protected $_dna_modified = false;

	/**
	 * @var int the message`s base id
	 * @access protected
	 */
	protected $_base = PancakeTF_Message::DEFAULT_ID;
	
	/**
	 * @var string last time message was updated
	 * @access protected
	 */
	 protected $_date = '';
	
	/**
	 * @var array a list of permissions and their name in the current system
	 * @access protected
	 */
	protected $_permissions = array(
		'open' => 'open', //open a message
		'update'=>'update', //update the database
		'create' => 'create', //create a message
		'move' => 'move', //move a message
		'delete' => 'delete' //delete a messages
	);

	
	public function __construct(PancakeTF_DBAccessI $dba, PancakeTF_PermissionHandlerI $permission_handler, $id=false, $options = array()){
		$this->_dba = $dba;
		$this->_permission_handler = $permission_handler;
		
		if ($id){
			$this->setId($id);
		}

		$this->setOptions($options);
	}

    /** 
     * returns the message's id
	 * @access public
	 * @return int
	 */
	public function getId(){return $this->_id;}
	
	/**
	 * returns the Message`s forum-id
	 * @access public
	 * @return int
	 * 
	 * @throws InvalidArgumentException if forum id is invalid
	 */
	public function getForumId(){return $this->_forum_id;}
	
	/**
	 * returns the Message`s Title
	 * @access public
	 * @return string
	 */
	public function getTitle(){
		return $this->title;
	}
	
	/**
	 * returns the Message's content
	 * @access public
	 * @return string
	 */
	public function getContent(){return $this->content;}
	
	/**
	 * returns the Message`s base-id
	 * @access public
	 * @return int a message id
	 * 
	 * @throws LogicException
	 */
	public function getBaseId(){
		if ($this->_base === self::DEFAULT_ID) throw new LogicException("Message is not yet initialized");
		return (int)($this->_base);
	}
	
	/**
	 * return the Message`s DNA
	 * @access public
	 * @return string
	 */
	public function getDna(){
		if ($this->_dna === '') throw new LogicException("Message is not yet initialized");
		return $this->_dna;
	}
	
	/**
	 * returns weather or not the message is a base message
	 * @access public
	 * @return bool 
	 * 
	 * @throws LogicException
	 */
	public function isBase(){
		if ($this->_base === self::DEFAULT_ID) throw new LogicException("Message is not yet initialized");
		return (bool)($this->_base === $this->_id);
	}
	
	/**
	 * returns the last time the message was updated
	 * @access public
	 * @return string
	 * 
	 * @throws LogicException if message was not yet initialized
	 */
	public function getDate(){
		if ($this->getId() === self::DEFAULT_ID) throw new LogicException('message has not yet been initialized');
		return $this->_date;
	}
	
	/**
	 * sets the message as a base message
	 * @access public
	 * @return this
	 */
	public function setBase(){
		if ($this->_id === self::DEFAULT_ID){
			$this->parent = null;
			$this->_base = self::DEFAULT_ID;
			$this->_dna = '';
		}else {
			if ($this->_permission_handler->doesHavePermission($this->_permissions['move'],$this->getForumId(),$this->getId())===false){
				throw new PancakeTF_NoPermissionException('move');
			}
			$this->parent = null;
			$this->_base = $this->_id;
			$this->_dna = $this->_id;

		}
		$this->modified = true;
		$this->_dna_modified = true;
		return $this;
	}
	/**
	 * set's/changes the Message`s title
	 * 	@param string $value the value of the new title
	 * @access public
	 * @return PancakeTF_MessageI the current message
	 * 
	 * @throws InvalidArgumentException if forum id is invalid
	 */
	public function setTitle($value){
		if (false === is_string($value) || strlen($value)<self::MIN_TITLE_LENGTH) throw new InvalidArgumentException('Title must be a string');
		$this->title = $value;
		$this->modified = true;
		return $this;
	}
	
	/**
	 * sets/changes the Message`s content
	 * 	@param string $value the new content
	 * @access public
	 * @return PancakeTF_MessageI the current message
	 * 
	 * @throws InvalidArgumentException if content is invalid
	 */
	public function setContent($value){
		if (false==is_string($value) || strlen($value)<self::MIN_CONTENT_LENGTH){
			throw new InvalidArgumentException('Content is too short');
		}
		$this->content = $value;
		$this->modified = true;
		return $this;
	}
	
	/**
	 * sets the message`s id (can be used to open a Message). 
	 * 	@param int $id a valid message id
	 * @access public 
	 * @return PancakeTF_MessageI the current message
	 */
	public function setId($id){
		if (false === is_numeric($id) || $this->_dba->count($this->id_table,array('id'=>$id))==0)
			throw new InvalidArgumentException('Invalid ID supplied('.$id.')');
		
		if ($this->_permission_handler->doesHavePermission($this->_permissions['open'],$this->getForumId(),$id)===false){
			throw new PancakeTF_NoPermissionException('open');
		}
		
		$sql = "
			SELECT
				{$this->id_table}.id,
				{$this->id_table}.forum_id,
				{$this->id_table}.dna,
				{$this->id_table}.base_id,
				DATE_FORMAT({$this->id_table}.date,'{$this->date_format}') as `date`,
				{$this->content_table}.title,
				{$this->content_table}.content
			FROM
				pancaketf_messages
			Inner Join {$this->content_table} ON {$this->content_table}.message_id ={$this->id_table}.id
			WHERE `id` = ?";	
			
		$this->_id = $id;
		$row = $this->_dba->queryRow($sql,array($id));
		$this->title =$row['title'];
		$this->content = $row['content'];
		$this->_dna = $row['dna'];
		$this->_base = $row['base_id'];
		$this->_forum_id = $row['forum_id'];
		$this->_date = $row['date'];
		$this->modified = false;
	}
	
	/**
	 * sets/changes the message`s forum id
	 * 	@param int $id the new forum-id. must be a valid forum id
	 * @access public 
	 * @return PancakeTF_MessageI
	 * 
	 * @throws InvalidArgumentException if forum id is invalid
	 */
	public function setForumId($id){
		if (0===$this->_dba->count($this->id_table,array('id'=>$id))){
			throw new InvalidArgumentException('No Forum By Specified ID('.$id.') exists');
		}
		
		$this->_forum_id = $id;
		$this->modified = true;
		
		return $this;
	}

	/**
	 * sets/changes the message`s parent id. must be a valid message id
	 * 
	 * this will actualy change the message`s dna and all it's siblings` dna. The message must be of the same forum.
	 * if the id is set to itself the message will be considered and marked as base (default).
	 * 
	 * 	@param PancakeTF_MessageI $message a new parent message. message must be of the same forum and connot be a descendant of the current message 
	 * @access public 
	 * @return PancakeTF_MessageI 
	 * 
	 * @throws InvalidArgumentException if the parent isn't from the right forum or is a sibling of the current message
	 * @throws PancakeTF_NoPermissionException if the user doesn't have permission to move the message
	 */
	public function setParent(PancakeTF_MessageI $message){
		if ($this->_dba->count($this->forums_table,array('id'=>$message->getForumId()))===0)
				throw new InvalidArgumentException('Parent ForumId is invalid');
		if ($this->_dba->count($this->id_table,array('id'=>$message->getId()))===0)
			throw new InvalidArgumentException('Message id is invalid');	
		
		if ($this->getId() !=self::DEFAULT_ID){
			if ($this->_permission_handler->doesHavePermission($this->_permissions['move'],$this->getForumId(),$this->getId())===false){
				throw new PancakeTF_NoPermissionException('move');
			}
			$dna = $message->getDna();
			$dna = explode('.',$dna);
			if (in_array($this->getId(),$dna)){
				throw new InvalidArgumentException('Parent is a child of current message');
			}
		}
		
		if ($this->_forum_id != self::DEFAULT_FORUM_ID){	
			if ($message->getForumId() != $this->_forum_id)
				throw new InvalidArgumentException('Parent ID must be of the same forum');
		}
		$this->parent = $message;
		$this->setForumId($message->getForumId());
		
		return $this;
	}
	
	/**
	 * an accessor to the variouse setters via an array
	 * 	@param array $options an associative array of options and their values
	 * @access public
	 * 
	 * @throws InvalidArgumentException if option is not allowed to be manualy changed
	 */
	public function setOptions($options=array()){
		foreach ($options as $key=>$option){
			if (isset($this->$key) && substr($key,0,1)==='_'){
				throw new InvalidArgumentException('You are not allowed to manualy set this method:'.$key);
			}else{
				switch($key){
					case 'permissions':
						foreach ($option as $perm=>$value) if (isset($this->permissions[$perm])) $this->permissions[$perm] = $value;
					break;
					case 'parent':
						$this->setParent($option);
					break;
					case 'title':
						$this->setTitle($option);
					break;
					case 'forum_id':
						$this->setForumId($option);
					break;
					case 'content':
						$this->setContent($option);
					break;
					case 'date_format':
						$this->date_format = $option;
					break;
				}
			}
		}
	}
	
	/**
	 * saves the changes made (if any) to the Message to the DB
	 * @access public 
	 */
	public function save(){		
		if ( $this->_id != self::DEFAULT_ID && false === $this->modified ) return;
		
		if (strlen($this->title)<self::MIN_TITLE_LENGTH)
			throw new LengthException('title length is too short');
			
		if (strlen($this->title)<self::MIN_CONTENT_LENGTH){
			throw new LengthException('content is too short');
		}
		
		if ($this->_forum_id === self::DEFAULT_FORUM_ID){
			throw new BadMethodCallException('forum id was not specified');
		}
		
		if ( $this->_id === self::DEFAULT_ID){
			
			if ($this->_permission_handler->doesHavePermission($this->_permissions['create'],$this->getForumId())===false){
				throw new PancakeTF_NoPermissionException('create');
			}
			$this->createMessage();
		}elseif ($this->modified){
			if ($this->_permission_handler->doesHavePermission($this->_permissions['update'],$this->getForumId(),$this->getId())===false){
				throw new PancakeTF_NoPermissionException('update');
			} 
			$this->updateMessage();
		}
		$this->date = date(str_replace('%','',$this->date_format));
		$this->modified = false;
	}
	
	/**
	 * completely deletes a message and its siblings
	 * @access public
	 * @throws PancakeTF_NoPermissionException
	 */
	public function delete(){
		if ($this->getId() === self::DEFAULT_ID) throw new LogicError('message not yet initialized');
		
		if ($this->_permission_handler->doesHavePermission($this->_permissions['delete'])===false){
			throw new PancakeTF_NoPermissionException('delete');
		}
	
		$delete_ids_sql = "DELETE FROM `{$this->id_table}` WHERE `id`=?";
		$delete_contents_sql = "DELETE FROM `{$this->content_table}` WHERE `message_id`=?";
		
		$this->_dba->update($delete_ids_sql,array($this->getId()));
		$this->_dba->update($delete_contents_sql,array($this->getId()));
		$this->id = self::DEFAULT_ID;
		$this->_dna = '';
		$this->deleteSiblings();
	}
	
	protected function createMessage(){
		$insert_sql = "INSERT INTO `". $this->id_table ."`(`forum_id`,`date`) VALUES (?,NOW())";
		$insert_extra_sql = "UPDATE `". $this->id_table ."`SET `dna`=?,`base_id`=? WHERE `id`=?";
		$insert_content_sql = "INSERT INTO `".$this->content_table."`(`message_id`,`title`,`content`) VALUES (?,?,?)";

		$this->_dba->update($insert_sql,array($this->getForumId()));
		$id = $this->_id = $this->_dba->getLastId();
			
		if ($this->parent instanceof PancakeTF_MessageI){
			$this->_dna  = $dna  = $this->parent->getDna(). '.'. $id;
			$this->_base = $base = $this->parent->getBaseId();
		}else{
			$this->_dna  = $dna  = $id;
			$this->_base = $base = $id;
	
		}
		
		$this->_dba->update($insert_extra_sql,array($dna,$base,$id));

		$this->_dba->update($insert_content_sql,array($id,$this->getTitle(),$this->getContent()));
		
		return $this;
	}
	
	protected function updateMessage(){
		$update_content_sql = "UPDATE `{$this->content_table}` SET `title`=?,`content`=? WHERE (`message_id`=?)  ";
		$update_ids_sql = "UPDATE `{$this->id_table}` SET `dna`=?,`base_id`=?,`date`=NOW() WHERE (`id`=?)";
		$update_base_date = "UPDATE `{$this->id_table}` SET `date` = NOW() WHERE `id`=?";

		$this->_dba->update($update_content_sql,array($this->getTitle(),$this->getContent(),$this->getId()));
		if ($this->parent instanceof PancakeTF_MessageI){
			
			$dna = $this->_dna = $this->parent->getDna(). '.' .$this->getId();
			$base = $this->_base = $this->parent->getBaseId();
			 
			$this->updateSiblings();
		}elseif ($this->_dna_modified){
			$dna = $this->getDna();
			$base = $this->getBaseId();
			$this->updateSiblings();
			$this->_dna_modified = false;
		}else return;
		
		$this->_dba->update($update_ids_sql,array($dna,$base,$this->getId()));
		$this->_dba->update($update_base_date,array($base));
	}
	
	protected function updateSiblings(){
		$recive_siblings_sql = "SELECT `id`,`dna` FROM `{$this->id_table}` WHERE `dna` LIKE ? OR `dna` LIKE ?";
		$update_sibling_sql = "UPDATE `{$this->id_table}` SET `dna`=?,`base_id`=? WHERE `id`=?";
		
		$id = $this->getId();
		$my_dna = $this->getDna();
		$base_id = $this->getBaseId();
		$sibs = $this->_dba->queryIterator($recive_siblings_sql,array($id.'.%','%.'.$id.'.%'));
		
		foreach ($sibs as $sib){
			if (substr($sib['dna'],0,strlen($id))==$id){
				$ndna = explode($id.'.',$sib['dna']);
			}else $ndna = explode($id.'.',$sib['dna']);
			
			$sib['dna'] = $my_dna.'.'.$ndna[1];
			$sib['base_id'] = $base_id;
			$this->_dba->update($update_sibling_sql,array($sib['dna'],$sib['base_id'],$sib['id']));
		}
	}
	
	public function deleteSiblings(){
		$delete_ids_sql = "DELETE FROM `{$this->id_table}` WHERE `id`=?";
		$delete_contents_sql = "DELETE FROM `{$this->content_table}` WHERE `message_id`=?";
		$recive_siblings_sql = "SELECT `id` FROM `{$this->id_table}` WHERE `dna` LIKE ? OR `dna` LIKE ?";
		$siblings = $this->_dba->queryIterator($recive_siblings_sql,array($this->getId().'.%','%.'.$this->getId().'.%')) ;
		foreach ($siblings as $sibling){
			$this->_dba->update($delete_ids_sql,array($sibling['id']));
			$this->_dba->update($delete_contents_sql,array($sibling['id']));
		}
	}
}
