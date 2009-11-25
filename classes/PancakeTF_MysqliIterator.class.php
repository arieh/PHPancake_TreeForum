<?php
class PancakeTF_MysqliIterator implements Iterator,Countable {
	/**
	 * @var lib_dbutils_RecordsetWrapper
	 */
	private $result;
	private $recordIndex =0;
	private $currentRow;
	private $allRowsCount;
	private $stack = array();
	private $allowRewind = false;
	private $fromStack = false;
	
	public function __construct(MySQLi_Result $res) {
		$this-> result = $res;
		$this->currentRow= $this->result->fetch_assoc();
		if ($this->currentRow) $this->recordIndex = 1;
		$this->allRowsCount=$this->result->num_rows;
	}
	
	public function enableRewind(){
		if ($this->recordIndex>1) throw new LogicException('Cannot allow rewind. results alreadt interated');
		$this->allowRewind = true;
		$this->stack[]=$this->current();
		return $this;
	}
		
	public function current() {
		if ($this->fromStack){
			return ($this->stack[$this->recordIndex-1]);
		}
		return $this->currentRow;
	}
	
	public function next() {
		$this->recordIndex++;
		if ($this->fromStack) return;
		
		$this->recordIndex++;
		$this->currentRow=$this->result->fetch_assoc();
		if ($this->allowRewind && $this->fromStack === false){
			$this->stack[]=$this->currentRow;
			if (false === $this->valid()) $this->fromStack = true;
		}
	}
	
	public function key() {
		return $this->recordIndex;
	}
	
	public function rewind (){
		if (false === $this->allowRewind) return;
		if ($this->valid() && $this->recordIndex>1){
			while ($this->valid()){
				$this->next();
			}
		}
		$this->recordIndex = 1;
	}
	
	public function valid()	{
		if ($this->fromStack) return ($this->recordIndex<count($this->stack));
		return (bool)$this->currentRow;
	}
	
	public function count()	{
		return $this->allRowsCount;
	}
}
