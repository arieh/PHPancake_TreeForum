<?php
require_once dirname(__FILE__) . "/interfaces/PancakeTF_ForumI.class.php";

class PancakeTF_Forum implements PancakeTF_ForumI, Iterator{
	const DEFAULT_LIMIT = 10;
	
	protected $table = 'pancaketf_forums';
	protected $message_table = 'pancaketf_messages';
	protected $message_contents_table = 'pancaketf_message_contents';
	
	protected $limit = PancakeTF_Forum::DEFAULT_LIMIT;
	
	protected $date_format = '%d/%m/%Y';
	
	protected $start = 0;
	
	protected $id;
	
	protected $messages = array();
	
	protected $current;
	
	protected $position = 0;
	
	protected $keys = array();
	
	public function __construct(PancakeTF_DBAccessI $dba, PancakeTF_PermissionHandlerI $ph, $forum_id,$options = array()){
		$this->dba = $dba;
		$this->p_handler = $ph;
		if ($this->dba->count($this->table,array('id'=>$forum_id))===0){
			throw new InvalidArgumentException('Invalid Forum ID');
		}
		if (false === $this->p_handler->doesHavePermission('open',$forum_id)){
			throw new PancakeTF_NoPermissionException('open');
		}
		$this->id = $forum_id;
		if (count($options)>0){
			foreach ($options as $op=>$value){
				switch($op){
					case 'limit':
						$this->limit=$value;
					break;
					case 'start':
						$this->start = $value;
					break;
					case 'date_format':
						$this->date_format = $value;
					break;
				}
			}
		}
		$this->retrieveMessages($forum_id);
	}
	
	protected function retrieveMessages(){
		$messages = array();
		foreach ($this->retrieveBaseMessages() as $message){
			$msgs = array();
			$sub_meessages = $this->retrieveSubMessages($message['id']);
			foreach ($sub_meessages as $sub) $msgs[$sub['dna']] = $sub;
			$messages = array_merge($messages,$this->orderMessages($msgs));
		}
		foreach ($messages as $msg) $this->messages[$msg['id']] = $msg;
		$this->keys = array_keys($this->messages);
	}
	
	protected function retrieveBaseMessages(){
		$get_base_messages_sql = 
			"SELECT
				{$this->message_table}.id
			FROM
				{$this->message_table}
			WHERE `{$this->message_table}`.`forum_id` = ? AND `id`=`base_id` ORDER BY `date`";
		
		if ($this->limit>0){
			$get_base_messages_sql .= " LIMIT {$this->start},{$this->limit}";
		}
		return $this->dba->queryIterator($get_base_messages_sql,array($this->getId()));
	}
	
	protected function retrieveSubMessages($id){
		$get_messages_sql = 
			"SELECT
				{$this->message_table}.id,
				{$this->message_table}.dna,
				DATE_FORMAT({$this->message_table}.`date`,'{$this->date_format}') as `date`,
				{$this->message_contents_table}.title,
				{$this->message_contents_table}.content
			FROM
				{$this->message_table}
			Inner Join {$this->message_contents_table} ON {$this->message_contents_table}.message_id = {$this->message_table}.id
			WHERE `{$this->message_table}`.`base_id` =?";
			
		$sub_messages = $this->dba->queryIterator($get_messages_sql,array($id));
		
		return $sub_messages;
	}
	
	public function getId(){return $this->id;}
	
	protected function orderMessages($arr){
		if (count($arr)==0) return array();
    	$keys = array_keys($arr);
    	natsort($keys);
    	$messages = $arr;
    	$arr = array();
    	foreach ($keys as $key){
    		$messages[$key]['depth'] = count(explode('.',$messages[$key]['dna']));
    		$arr[$messages[$key]['id']] = $messages[$key];
    	}
    	return $arr;
	}	
	
	public function rewind(){
		$this->position = 0;
		$this->key = null;
	}
	
	public function next(){
		if (!$this->valid()) return false;
		$key = $this->keys[$this->position++];
		return $this->messages[$key];
	}
	
	public function current(){
		if ($this->valid()) return $this->messages[$this->keys[$this->position]];
	}
	
	public function key(){
		return $this->keys[$this->position];
	}
	
	public function count(){return count($this->messages);}
	
	public function valid(){return ($this->position<count($this->messages));}
	
	public function getMessages(){return $this->messages;}
	
	public function getMessage() {return $this->next();}
}