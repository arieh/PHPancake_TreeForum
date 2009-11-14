<?php

class PancakeTF_PDOIterator implements Iterator,Countable{
	private $current = null;
	private $position = 0;
	private $array = array();
	private $row_count = 0;
    
    public function __construct(PDOStatement $stmt){
    	$this->array = $stmt->fetchAll(PDO::FETCH_ASSOC);
    	$this->row_count = count($this->array);
    }
    
   public function count(){return $this->row_count; }
   public function key(){return $this->position;}
   public function current(){return $this->array[$this->position];}
   public function next(){
   		$this->position++;
   		if ($this->valid()) return $this->current;
   		return false;
   }
   public function valid(){return $this->position<$this->row_count;}
   public function rewind(){$this->position = 0;}
}