<?php
/**
 * A Wrapper for common activities on a MySqli result set
 * 
 * @author Itay Moav <itay.malimovka@gmail.com> 
 * @version 1
 */
class lib_dbutils_ResultsetWrapper{
	
	/**
	 * A mysqli recordset
	 *
	 * @var mysqli_result
	 */
	private $ResultSet;
	private $currentRecord=null;
	
	public function __construct(mysqli_result $ResultSet) {
		$this->ResultSet=$ResultSet;
	}
	
	public function getNumRows(){
		return $this->ResultSet->num_rows;
	}
	
	/**
	 * Fetches number of fields in the last select
	 *
	 * @return Integer
	 */
	public function getFieldsNum() {
		return $this->ResultSet->field_count;
	}//----------------------------------END OF FUNCTION numFields
	
	/**
	 * used to read next record from the recordset - user is responsible to check
	 * if next arrived to end of recordset
	 *
	 * @param int $type of result to return. defaults to MYSQLI_ASSOC. Can be: MYSQLI_ASSOC, MYSQLI_NUM, MYSQLI_BOTH
	 * @return array
	 */
	public function getRow($type=MYSQLI_ASSOC) {
		$this->currentRecord=$this->ResultSet->fetch_array($type);
		return($this->currentRecord);		
	}//EOF getRow

	/**
	 * returns a row as object
	 * 
	 * @return stdClass
	 */
	public function getObj(){
		$this->currentRecord=$this->ResultSet->fetch_object();
		return($this->currentRecord);		
	}//EOF getObj

	/**
	 * Fetches a field from the last record. (index is numeric or asociative)
	 *
	 * @param mixed $index
	 * @return unknown
	 */
	public function fetchField($index=-1) {
		if(isset($this->currentRecord[$index])){
			return $this->currentRecord[$index];
		}
		elseif($index==-1)	{
			return $this->currentRecord;
		}
		else{
			return(null);
		}
	}//EOF fetchField

	/**
	 * Fetches a value from field params "param1=value1;param2=value2;"
	 *
	 * @param mixed $param_key
	 * @return mixed
	 */
	public function fetchParams($param_key) {
		$field=$this->currentRecord['params'];
		$cells=explode(';',$field);
		foreach($cells as $bob)
		{
			$param=explode('=',$bob);
			if($param[0]==$param_key)
			{
				return $param[1];
			}
		}
		//FIN
		return null;
	}//----------------------------------END OF FUNCTION fetchParams

	public function getDataSet() {
		// return $this->ResultSet->fetch_all(); TODO Only from PHP 5.3 REQUIRES MYSQLND
 		$data=array();
		while($this->getRow())
			$data[]=$this->currentRecord;
		return $data;
	}//----------------------------------END OF FUNCTION getDataSet
	
}
