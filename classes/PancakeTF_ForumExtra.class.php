<?php
require_once dirname(__FILE__) . "/PancakeTF_ForumPermissionHandler.class.php";
require_once dirname(__FILE__) . "/PancakeTF_ShusterDB.class.php";
require_once dirname(__FILE__) . "/PancakeTF_Forum.class.php";

class PancakeTF_ForumExtra extends PancakeTF_Forum{
	protected $message_extras = 'pancaketf_message_extras';
	protected $user_table = 'users';
	
	public function __construct($id,$options=array()){
		if (false === is_null($options['dba']) || false === ($options['dba'] instanceof PancakeTF_DBAccessI)){
			$dba = new PancakeTF_ShusterDB(lib_dbutils_ShusterDb::getInstance());
		}else $dba = $options['dba'];
		
		if (false===isset($options['handler']) || false === ($options['handler'] instanceof PancakeTF_PermissionHandlerI)){
			$ph = new PancakeTF_ForumPermissionHandler($dba);
		}else $ph = $options['handler'];
		
		parent::__construct($dba,$ph,$id,$options);
	}
	
    protected function retrieveSubMessages($id){
		$get_messages_sql = 
			"SELECT
				{$this->message_table}.id,
				{$this->message_table}.dna,
				DATE_FORMAT({$this->message_table}.`date`,'{$this->date_format}') as `date`,
				{$this->message_contents_table}.title,
				{$this->message_contents_table}.content,
				{$this->message_extras}.votes,
				{$this->user_table}.id as `user_id`,
				{$this->user_table}.name as `user_name`,
				{$this->user_table}.email as `user_email`
			FROM
				{$this->message_table}
			Inner Join {$this->message_contents_table} ON {$this->message_contents_table}.message_id = {$this->message_table}.id
			Inner Join {$this->message_extras} ON {$this->message_extras}.message_id = {$this->message_table}.id
			Inner Join {$this->user_table} ON {$this->user_table}.id = {$this->message_extras}.user
			WHERE `{$this->message_table}`.`base_id` =?";
			
		$sub_messages = $this->dba->queryIterator($get_messages_sql,array($id));
		
		return $sub_messages;
	}
}
