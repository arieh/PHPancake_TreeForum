<?php
class Tester{
	public function __call($name,$params){
		return 'a';
	}
}

class TesterTester extends Tester{
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

class TesterTest extends PHPUnit_Framework_Testcase{
	public function setUp(){
		$this->tested = new TesterTester();
	}
	
	public function testCall(){
		$this->assertEquals('a',$this->tested->getA());
	}
}
