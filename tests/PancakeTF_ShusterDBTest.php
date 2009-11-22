<?php
require_once dirname(__FILE__) . "/../classes/PancakeTF_ShusterTestCase.class.php";

class PancakeTF_ShusterDBTest extends PancakeTF_ShusterTestCase{	
	public function testConnection(){
		$this->setUpDB();
    	
    	$this->assertTrue($this->db instanceof PancakeTF_DBAccessI);
	}
	
	public function testMultipleIteration(){
		$this->setUpDB();
		$sql = "SELECT * FROM `pancaketf_messages`";
		$result = $this->db->queryIterator($sql)->enableRewind();
		$arr1 = array();
		$arr2 = array();
		foreach ($result as $res){
			$arr1[]=$res;
		} 
		foreach ($result as $res){
			$arr2[]=$res;
		} 

		$this->assertEquals($arr1,$arr2);
	}

	public function testQueryRow(){
		$this->setUpDB();
		$sql = "SELECT * FROM `pancaketf_messages` WHERE `id`=?";
		$row = $this->db->queryRow($sql,array('1'));
		$this->assertTrue(is_array($row));
		$this->assertGreaterThan(0,count($row));
		$this->assertEquals(count(array_keys($row)),5);
	}
	
	public function testQueryArray(){
		$this->setUpDB();
		$sql = "SELECT * FROM `pancaketf_messages` WHERE `id`>?";
		$rows = $this->db->queryArray($sql,array('1'));
		$this->assertTrue(is_array($rows));
		$this->assertEquals(count($rows),7);
		$array = array(2,3,4,5,7,8,9);
	}
	
	public function testUpdate(){
		$this->setUpDB();
		$sql = "INSERT INTO `pancaketf_messages`(`forum_id`,`dna`,`base_id`) values (?,?,?)";
		$this->assertEquals(1,$this->db->update($sql,array(1,10,10)));
	}
	
	public function testCount(){
		$this->setUpDB();
		$this->assertEquals($this->db->count('pancaketf_messages',array()),8);
	}
	

	
	public function testSimpleSQLInjection(){
		$this->setUpDB();
		$sql = "SELECT * FROM `pancaketf_messages` WHERE `id`=?";
		$rows = $this->db->queryArray($sql,array("200' OR 1=1"));
		$this->assertEquals(count($rows),0);
	}
	
	public function testLastId(){
		$this->setUpDB();
		$sql = "INSERT INTO `pancaketf_messages`(`forum_id`,`dna`,`base_id`,`date`) values (?,?,?,NOW())";
		$this->db->update($sql,array(1,10,10));
		$this->assertEquals(10,$this->db->getLastId());
	}
	
	public function testGetIteratorInterfaces(){
		$this->setUpDB();
		$sql = "SELECT * FROM `pancaketf_messages`";
		$result = $this->db->queryIterator($sql);
		$this->assertTrue($result instanceof Iterator);
		$this->assertTrue($result instanceof Countable);
	}
	
	public function testGetIteratoForeach(){
		$this->setUpDB();
		$sql = "SELECT * FROM `pancaketf_messages`";
		$result = $this->db->queryIterator($sql);
		$count = 0;
		foreach ($result as $res) $count++;
		$this->assertEquals(8,$count);
	}
	
	public function testGetIteratoCount(){
		$this->setUpDB();
		$sql = "SELECT * FROM `pancaketf_messages`";
		$result = $this->db->queryIterator($sql);
		$this->assertTrue($result->count()==8);
	}
	
	public function testGenerateInList(){
		$this->setUpDB();
		$wanted = "1,'a',2,'a4'";
		$this->assertEquals($wanted,$this->db->generateInList(array(1,'a',2,'a4')));
	}
} 