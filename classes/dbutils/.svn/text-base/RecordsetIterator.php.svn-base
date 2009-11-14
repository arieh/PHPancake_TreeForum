<?php
/**
 * Abstracts iteration over a DB recordset
 * 
 * @author Itay Moav <itay.malimovka@gmail.com> 
 * @version 2
 */
class lib_dbutils_RecordsetIterator implements Iterator,Countable {
	/**
	 * @var lib_dbutils_ShusterDB
	 */
	private $DB;
	private $recordIndex;
	private $currentRow;
	private $allRowsCount;
	private $asObj=false;
	
	public function __construct(lib_dbutils_ShusterDB $DB,$as_obj=false) {
		$this->DB=$DB;
		$this->asObj=$as_obj;
		$this->recordIndex=0;
		$this->currentRow=($this->asObj)?($this->DB->getObj()):($this->DB->getRow());
		if($this->currentRow) {
			$this->recordIndex=1;
		}
		$this->allRowsCount=$this->DB->numRows();
	}
	
	public function current() {
		return $this->currentRow;
	}
	
	public function next() {
		$this->recordIndex++;
		$this->currentRow=($this->asObj)?($this->DB->getObj()):($this->DB->getRow());
	}
	
	public function key() {
		return $this->recordIndex;
	}
	
	public function rewind ()//NOT IMPLEMENTED, AS THIS IS A FORWARD ONLY CURSOR
	{
	}
	
	public function valid()	{
		return $this->currentRow;
	}
	
	public function count()	{
		return $this->allRowsCount;
	}
}
