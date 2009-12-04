<?php
require_once dirname(__FILE__) . "/../classes/PancakeTF_ShusterTestCase.class.php";
require_once dirname(__FILE__) . "/../classes/PancakeTF_Forum.class.php";

class ForumTester extends PancakeTF_Forum{
	public function __call($name,$params){
		if (substr($name,0,7)==='public_'){		
			$name = substr($name,7);
			if (method_exists($this,$name)){
				return call_user_func_array(array($this,$name),$params);
			}
		}
		$parents = class_parents($this);
		if (is_array($parents)){
			foreach ($parents as $classname){
				if (method_exists ($classname,'__call')) return parent::__call($name,$params);
			}
		}
		throw new Exception("Method $name does not exist for this class");
	}
}

class PancakeTF_ForumTest extends PancakeTF_ShusterTestCase{
	public function setUp(){
		$this->setUpDB();
		$p_h = $this->getMock('PancakeTF_PermissionHandlerI',array('doesHavePermission'));
		$p_h->expects($this->any())->method('doesHavePermission')->will($this->returnValue(true));
		$this->forum = new ForumTester($this->db,$p_h,1);
	}
		
	public function testGetMessages(){
		$this->assertEquals(count($this->forum->getMessages()),8);
	}
	
	public function testGetMessage(){
		$wmsg1 = array('id'=>1,'dna'=>'1','title'=>'a message','content'=>'content','depth'=>1);
		$wmsg2 = array('id'=>2,'dna'=>'1.2','title'=>'another message','content'=>'content','depth'=>2);
		
		$msg1 = $this->forum->getMessage();
		$msg2 = $this->forum->getMessage();
		foreach($wmsg1 as $key=>$value) $this->assertEquals($msg1[$key],$value);
		foreach($wmsg2 as $key=>$value) $this->assertEquals($msg2[$key],$value);
	} 
	
	public function testMessageOrder(){
		$this->assertEquals(array(1,2,4,3,7,5,8,9),array_keys($this->forum->getMessages()));	
	}
	
	public function testLimit(){
		$p_h = $this->getMock('PancakeTF_PermissionHandlerI',array('doesHavePermission'));
		$p_h->expects($this->any())->method('doesHavePermission')->will($this->returnValue(true));
		$forum = new PancakeTF_Forum($this->db,$p_h,1,array('limit'=>1));
		
		$this->assertEquals(count($forum->getMessages()),5);
	}
	
	public function testLimitWithStart(){
		$p_h = $this->getMock('PancakeTF_PermissionHandlerI',array('doesHavePermission'));
		$p_h->expects($this->any())->method('doesHavePermission')->will($this->returnValue(true));
		$forum = new PancakeTF_Forum($this->db,$p_h,1,array('limit'=>1,'start'=>1));
		
		$this->assertEquals(count($forum->getMessages()),2);
	}
	
	public function testIteration(){
		$keys = array('id','dna','date','title','content','depth');
		$all_messages = true;
		$count = 0;
		foreach ($this->forum as $msg){
			$this->assertEquals(array_keys($msg),$keys);
			$count++;
		}
		$this->assertEquals($count,8);
	}
	
	
}