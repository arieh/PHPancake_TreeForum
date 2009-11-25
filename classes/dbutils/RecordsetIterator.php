<?php
/**
 * Abstracts iteration over a DB recordset
 * 
 * @author Itay Moav <itay.malimovka@gmail.com> 
 * @version 2
 */
class lib_dbutils_RecordsetIterator implements Iterator,Countable {
	/**
	 * @var lib_dbutils_RecordsetWrapper
	 */
	private $ResultSetWrapper;
	private $recordIndex =0;
	private $currentRow;
	private $allRowsCount;
	private $resType='getRow';
	private $stack = array();
	private $allowRewind = false;
	private $fromStack = false;
	private $position = 0;
	
	public function __construct(lib_dbutils_ResultsetWrapper $ResultSet,$as_obj=false) {
		$this->ResultSetWrapper=$ResultSet;
		$this->asObj=$as_obj;
		if($as_obj) $this->resType='getObj';
		$this->currentRow=$this->ResultSetWrapper->{$this->resType}();
		if ($this->currentRow) $this->recordIndex = 1;
		$this->allRowsCount=$this->ResultSetWrapper->getNumRows();
	}
	
	public function enableRewind(){
		if ($this->recordIndex>1) throw new LogicException('Cannot allow rewind. results already interated');
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
		
		$this->currentRow=$this->ResultSetWrapper->{$this->resType}();
		if ($this->allowRewind && false === $this->fromStack ){
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
