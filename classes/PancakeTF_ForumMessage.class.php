<?php
require_once dirname(__FILE__) . "/PancakeTF_Forum.class.php";
class PancakeTF_ForumMessage extends PancakeTF_Forum{
	protected $table = 'pancaketf_messages';
	
    protected function retrieveMessages(){
    	$sql = "SELECT
				{$this->message_table}.id,
				{$this->message_table}.dna,
				DATE_FORMAT({$this->message_table}.`date`,'{$this->date_format}') as `date`,
				{$this->message_contents_table}.title,
				{$this->message_contents_table}.content
			FROM
				{$this->message_table}
			Inner Join {$this->message_contents_table} ON {$this->message_contents_table}.message_id = {$this->message_table}.id
			WHERE `{$this->message_table}`.`dna` LIKE ? OR `{$this->message_table}`.`dna` LIKE ? OR `id`=?";
			
		$unordered_msgs = $this->dba->queryArray($sql,array($this->getId().".%","%.{$this->getId()}.%",$this->getId()));
		$messages = array();
		foreach ($unordered_msgs as $msg) $messages[$msg['dna']] = $msg;
		$messages = $this->orderMessages($messages);
		foreach ($messages as $m) $this->messages[$m['id']] = $m;
		$this->keys = array_keys($this->messages);
    }
}