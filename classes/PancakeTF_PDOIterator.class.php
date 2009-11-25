<?php

class PancakeTF_PDOIterator implements Iterator,Countable{
	private $current_row = null;
	private $position = 0;
	private $stack = array();
	private $stms = null;
	private $from_stack = false;
	private $row_count = false;
    
    public function __construct(PDOStatement $stmt){
    	$this->current_row = $stmt->fetch(PDO::FETCH_ASSOC);
    	$this->stmt = $stmt;
    }
    
   public function count(){
		if (false === $this->row_count){
			$this->stack[]=$this->current();
			$this->stack = array_merge($this->stack,$this->stmt->fetchAll(PDO::FETCH_ASSOC));
			$this->from_stack = true;
			$this->row_count = count($this->stack);	
		}
		return $this->row_count;
   }
   
   public function key(){return $this->position;}
   public function current(){return $this->current_row;}
   public function next(){
   		if ($this->from_stack){
   			$this->position++;
   			if ($this->valid())
   				$this->current_row = $this->stack[$this->position];
   			return;
   		}
		$this->stack[]=$this->current_row;
   		$this->position++; 
   		$this->current_row = $this->stmt->fetch(PDO::FETCH_ASSOC);
   		
   		if (false === $this->valid()){
   			$this->from_stack = true;
   			$this->row_count = count($this->stack);
   			return;
   		}
   }
   
   public function valid(){
		if ($this->from_stack) return ($this->position<$this->count());
		return (bool)$this->current_row;
   }
   
   public function rewind(){
   		if ($this->position===0) return;
   		if ($this->valid()){
   			$this->stack = $stmt->fetchAll(PDO::FETCH_ASSOC);
   			$this->row_count = count($this->stack);
   			$this->from_stack = true;
   		}
   		$this->position = 0;
   		if ($this->from_stack){
   			$this->current_row = $this->stack[0];
   		}
   		
   }
}