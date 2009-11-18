<?php
require_once dirname(__FILE__) . "/interfaces/PancakeTF_PermissionHandlerI.class.php";
class PancakeTF_ForumPermissionHandler implements PancakeTF_PermissionHandlerI{
	private $dba = null;
	public function __construct(PancakeTF_DBAccessI $dba){
		$this->dba = $dba;
	}
	
	public function doesHavePermission($type,$forum_id=false,$message_id=false){
		return true;
	}
}