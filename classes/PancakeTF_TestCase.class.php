<?php
require_once dirname(__FILE__) . "/../classes/PancakeTF_PDOAccess.class.php";

abstract class PancakeTF_TestCase extends MyTestCase{
	protected function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = false, $callOriginalClone = TRUE, $callAutoload = TRUE){
    	return parent::getMock($originalClassName,$methods,$arguments,$mockClassName,$callOriginalConstructor,$callOriginalClone,$callAutoload);
    }	
    protected $db = null;
    public function setUpDB(){
    	PancakeTF_PDOAccess::connect('mysql','localhost','pancake_tests','root','');
    	$this->db = new PancakeTF_PDOAccess();
    	$sql = file_get_contents(dirname(__FILE__).'/../db/test_sql.sql');
    	$sql = explode (';',$sql);
    	foreach ($sql as $stmt){
    		try{
    			$this->db->update($stmt);		
    		}catch (Exception $e){}
    	}
    } 
}