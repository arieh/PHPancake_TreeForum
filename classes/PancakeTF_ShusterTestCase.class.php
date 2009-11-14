<?php
require_once dirname(__FILE__) . "/PancakeTF_TestCase.class.php";
require_once dirname(__FILE__) . "/../classes/PancakeTF_ShusterDB.class.php";

class  Zend_Registry{
	private static $instance = null;
	
	static public function getInstance(){
		if (is_null(self::$instance)){
			self::$instance = new Zend_Registry();
		}	
		return self::$instance;
	}
	
	public function get($param){
		switch ($param){
			default: return $this;
			case 'host': return 'localhost'; break;
			case 'dbname': return 'pancake_tests'; break;
			case 'username': return 'root'; break;
			case 'password': return ''; break;
		}
	}
}

abstract class PancakeTF_ShusterTestCase extends PancakeTF_TestCase{
	public function setUpDB(){
    	lib_dbutils_ShusterDb::getInstance()->setErrorCodeFlag(true);
    	$this->db = new PancakeTF_ShusterDB(lib_dbutils_ShusterDb::getInstance());
    	$sql = file_get_contents(dirname(__FILE__).'/../db/test_sql.sql');
    	$sql = explode (';',$sql);
    	foreach ($sql as $stmt){
    		try{
    			$this->db->update($stmt);		
    		}catch (Exception $e){}    	
    	}
    } 
}