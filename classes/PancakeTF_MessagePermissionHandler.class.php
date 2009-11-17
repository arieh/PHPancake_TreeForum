<?php

class PancakeTF_MessagePermissionHandler implements PancakeTF_PermissionHandlerI{
	/**
	 * @var string the message extras table at the DB
	 * @access protected
	 */
	protected $message_extras = "pancaketf_message_extras";
	
	/**
	 * constructor
	 * 	@param PancakeTF_DBAccessI $dba a DB accessor
	 * @access public
	 */
	public function __construct(PancakeTF_DBAccessI $dba){
		$this->dba = $dba;
	}
	
	/**
	 * checks if the action is allowed for this session
	 * 	@param string $type permission type to check for
	 * 	@param int $forum a forum id
	 * 	@param int $message a message id
	 * @access public
	 * @return bool
	 */
	public function doesHavePermission($type, $forum = false, $message = false){
		switch ($type){
			case 'open': return true; break;
			case 'Edit My Post':
				if (false === is_numeric($message)) return false;
				$user_id = bl_User::getInstance()->getUserId();
				return ($this->isMessageOwner($user_id,$message) && bl_ActionsLevels::isAllowed($type));
			break;
		}
		
		return bl_ActionsLevels::isAllowed($type);
	}
	
	/**
	 * checks if a user is a message owner
	 * 	@param int user id
	 * 	@param int message id
	 * @access protected
	 * @return bool 
	 */
	protected function isMessageOwner($user,$message){
		return ($this->dba->count($this->message_extras,array('message_id'=>$message,'user'=>$user))>0);
	}
}