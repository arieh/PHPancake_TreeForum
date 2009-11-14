<?php
//***************************************************************************************************
// ShusterDb v5.0
//
// Purpose: this object handles actions agains mySql db
//
// Description: Will handle conecting to db, closing con' to db, cleaning input fields from
//              Malicius content, execute queries/scalaras (update/insert).
//              And will support debug mode and real mode with file error log.
//              
//              PROPER USE OF THIS OBJECT REQUIERS A log DIRECTORY AND TO DEFINE IT'S FULL PATH
//              IN THIS FILE.
//
// Methods:     - constructor: Initiate the object with the conection parameters. 
//                            
//
//             	 - connect:     this method will simply connect to the db. All connection parameters
//                             must be declared inside the class (see constructor)
//
//              	- cleanStr:    this method will return the input string with escaped contentd.
//                             Thuse - preventing sql injection
//
//	            - cleanNum:    This function is intended to avoid sql injection with numeric input fields.
//				               The method is to clean all non numeric characters from the input field.
//
//              	- select:  Performs select queries
//
//              	- getRow:    Reads next record from recordset - only way to read.
//							   user is responsible to check if next arrived to end 
//							   of recordset.
//
//		- fetchField:	 		 Fetches a field from the last result set. (index is numeric or asociative).
//							   Retruns entire record if called without input param.
//
//		- fetchParams:		 	extracts a single param from a parameter field (pname=pvalue;pname=pvalue);
//
//		-getIterator:			Returns an Iterator Object that can be used to travers the record set. Currently, only forward iteration is possible.
//
//
//              	- scalar:      Perform a scalar operation (insert, update, delete)
//
//             	 - error:       Writes error messages to log file and to screen
//
//***************************************************************************************************
//30-4-2009
// At last, moving to mysqli
//***************************************************************************************************
//30-5-2009
// Adding error shutdown on request only
//***************************************************************************************************
class lib_dbutils_ShusterDb
{
	/**
	 * Mysqli instance
	 *
	 * @var mysqli
	 */
	private	$MySqli=null;

	/**
	 * Last SQL ran in the system for THIS instance
	 *
	 * @var string
	 */
	private $lastSql='';
	
	/**
	 * Last result set fetched
	 *
	 * @var mysqli_result
	 */
	private $ResultSet=null;
	
	/**
	 * The last record fetched
	 *
	 * @var mixed, either a StdObj or array.
	 */
	private $record;
	
	/**
	 * Number of rows fetched/ affected by the last SQL performed
	 *
	 * @var integer
	 */	
	private	$numRows;
	
	private $lastInsertId;
	private	$num_fields;//number of fields in the query
	private	$errorMessag;
	
	/**
	 * Last query error code
	 *
	 * @var integer
	 */
	public	$errorCode=0;
	
	/**
	 * If this flag is set to true, On error I will not die, but return myself and set the error code to some value.
	 *
	 * @var boolean
	 */
	protected $getErrorCode=false;

	static private $Instances=array(); //Shuster instances
	
	/**
	 * My Own db class
	 *
	 * @param mixed $instance place holder if I will need several instances in the same APP
	 * @return lib_dbutils_ShusterDb
	 */
	static public function getInstance($instance=1)
	{	
		if(array_key_exists($instance,self::$Instances) && is_object(self::$Instances[$instance])) {
			return self::$Instances[$instance];
		}
		$out_message='The database is unavailable for maintenance. We appreciate your patience.';

		$Registry = Zend_Registry::getInstance();
		$MySqli=mysqli_init();
		switch($instance) {
			case(1):
				//can be different for each case
				$Config=$Registry->get('config')->get('db')->get('params');//TODO make use of the priority flag.
				$MySqli->real_connect($Config->get('host'),
									  $Config->get('username'),
									  $Config->get('password'),
									  $Config->get('dbname')
									);
				break;
/*			case(2):
				$link= @mysql_connect ($GLOBALS['dbIP_rept'], $GLOBALS['db_rept_user'], $GLOBALS['db_rept_pass'], $force_connect);
				break;
			case(3):
				$link= @mysql_connect ($GLOBALS['dbIPR'], $GLOBALS['db_select_user'], $GLOBALS['db_select_pass'], $force_connect);
				break;
*/
			default:
				die('You have asked for un existing DB instance, check class.shuster_db.php under db utilities to see the possible instances');
				break;
		}
		if($MySqli->connect_errno>0) {
			self::error();
		}
		self::$Instances[$instance]=new self($MySqli);
		return self::$Instances[$instance]; 
	}
	
	
	//*****************************************************************************
	// Constructor
	//
	// Initiate the object with the conection parameters. 
	//  
	//*****************************************************************************
	private function __construct(mysqli $MySqli) {
		$this->MySqli=$MySqli;
		$this->MySqli->set_charset('utf8'); 
		//$this->select('SET NAMES utf8');
	}//----------------------------------END OF CONSTRUCTOR

	/**
	 * Setter for getErrorCode
	 *
	 * @param boolean $flag
	 */
	public function setErrorCodeFlag($flag=true){
				$this->getErrorCode=$flag;
	}
	
	//*****************************************************************************
	// clean_str
	//
	// this method will return the input string with escaped contentd.
	// Those preventing sql injection.tests_list.csv.clear.delete
	//****************************************************************************
	public function cleanStr ($i_string) {
		return ($this->MySqli->real_escape_string($i_string));
	}//----------------------------------END OF FUNCTION clean_str
	
	
	//******************************************************************
	//
	//  function: cleanNum
	//
	//  description: This function is intended to avoid sql injection with numeric input fields.
	//               The method is to clean all non numeric characters from the inputtests_list.csv.clear.delete field. 
	//
	//  input: input numeric field (variable)
	//
	// returned value: clean numeric value - without strings
	//**********************************************************************
	public function cleanNum($i_numericField) {
		$o_cleanField=floatval($i_numericField);
		return($o_cleanField);
	}//----------------------------------END OF FUNCTION clean_num
	
	//*****************************************************************************
	/**
	 * Performs SELECTS
	 *
	 * @param string $sql
	 * @return lib_dbutils_ShusterDb
	 */
	public function select($sql,$buffer_type=MYSQLI_STORE_RESULT) {
		$this->errorCode=0;
		$this->lastSql=$sql;
		if(!($this->ResultSet=$this->MySqli->query($sql,$buffer_type))){
			$this->error();
		}
		$this->numRows=$this->ResultSet->num_rows;
		return $this; //FOR CHAINING (and Ponies!)
	}//EOF select

	//*****************************************************************************
	/**
	 * Performs un buffered SELECTS
	 *
	 * @param string $sql
	 * @return lib_dbutils_ShusterDb
	 */
	public function unBufferedSelect($sql) {
		$this->errorCode=0;
		return $this->select($sql,MYSQLI_USE_RESULT); //FOR CHAINING (and Ponies!)
	}//EOF unBufferedSelect
	
	//*****************************************************************************
	/**
	 * used to read next record from the recordset - user is responsible to check
	 * if next arrived to end of recordset
	 *
	 * @param int $type of result to return. defaults to MYSQLI_ASSOC. Can be: MYSQLI_ASSOC, MYSQLI_NUM, MYSQLI_BOTH
	 * @return array
	 */
	public function getRow($type=MYSQLI_ASSOC) {
		$this->record=$this->ResultSet->fetch_array($type);
		return($this->record);		
	}//EOF getRow
	
	//*****************************************************************************
	/**
	 * returns a row as object
	 * 
	 * @return stdClass
	 */
	public function getObj(){
		$this->record=$this->ResultSet->fetch_object();
		return($this->record);		
	}//EOF getObj
	
	//*****************************************************************************
	// fetchField
	//
	// Fetches a field from the last result set. (index is numeric or asociative)
	//*****************************************************************************
	public function fetchField($index=-1) {
		if(isset($this->recoraffected_rowsd[$index])){
			return $this->record[$index];
		}
		elseif($index==-1)	{
			return $this->record;
		}
		else{
			return(null);
		}
	}//EOF fetchField

	
	//*****************************************************************************
	// fetchParams
	//
	// Fetches a param from field params "param1=value1;param2=value2;"
	//*****************************************************************************
	public function fetchParams($param_key) {
		$field=$this->record['params'];
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

	//*****************************************************************************
	/**
	 * Return result as two dim array
	 * @return array
	 */
	public function getDataSet() {
		// return $this->ResultSet->fetch_all(); TODO Only from PHP 5.3 REQUIRES MYSQLND
 		$data=array();
		while($this->getRow())
			$data[]=$this->record;
		return $data;
	}//----------------------------------END OF FUNCTION getDataSet
	
	/**
	 * @return integer Number of rows affected/fetched in the last SQL
	 */
	public function numRows() {
		return $this->numRows;
	}//----------------------------------END OF FUNCTION numRows

	
	//*****************************************************************************
	// numFields
	//
	// Fetches number of fields in the last select
	//*****************************************************************************
	public function numFields() {
		return $this->ResultSet->field_count;
	}//----------------------------------END OF FUNCTION numFields

	
	//*****************************************************************************
	// scalarQuery
	/**
	 * Perform a scalar operation (insert, update, delete)
	 *
	 * @param string $sql
	 * @return lib_dbutils_ShusterDb
	 */
	//TODO KILL
	public function scalarQuery($sql) {
		$this->lastSql=$sql;
		$this->errorCode=0;
		if(!$this->MySqli->real_query($sql)) {
			$this->error('scalar');
		}		
		$this->numRows=$this->MySqli->affected_rows;
		return $this; 
	}//----------------------------------END OF FUNCTION scalarQuery
	
	public function insert($sql){
		$this->errorCode=0;
		$this->lastSql=$sql;
		if(!$this->MySqli->real_query($sql)){
			$this->error('insert');
		}
		$this->numRows=$this->MySqli->affected_rows;
		return $this;//For chaining and PONIES!!!
	}//EOF insert
	
	public function getLastInsertId(){
		return $this->MySqli->insert_id;
	}

	public function close () {
		mysql_close($this->linkId);
	}
	
	//*****************************************************************************
	// getIterator
	//
	// Returns an Iterator Object that can be used to travers the record set. Currently, 
	// only forward iteration is possible.
	//*****************************************************************************
	/**
	 * getIterator
	 *
	 * Returns an Iterator Object that can be used to travers the record set. Currently, 
	 * only forward iteration is possible..
	 *
	 * @return lib_dbutils_RecordsetIterator
	 */
	public function getIterator($as_obj=false) {
		return new lib_dbutils_RecordsetIterator($this,$as_obj);
	}
//------------------------------PRIVATE METHODS-----------------------------------------------------

	//*****************************************************************************
	// error
	//
	// Writes error messages to log file and to screen
	//
	// $i_function - the function in wich the error occured
	// $i_error    - error text
	//*****************************************************************************
	protected function error () {
		if(!$this->getErrorCode){
			die($this->lastSql."\n--------------\n".$this->MySqli->error);
		}
		$this->errorCode=$this->MySqli->errno;
		return $this;
	}//END OF FUNCTION error
}//--------------------------------------END OF CLASS------------------------------------------------
