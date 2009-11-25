<?php
class MysqliTest extends PHPUnit_Framework_Testcase{
	public function testa(){
		$my = new mysqli('localhost','root','','pancake_tests');
		$sql = "SELECT * FROM `pancaketf_messages`";
		$st = $my->prepare($sql);
		$arr = array();
		$st->execute();
		$st->bind_result($arr);
		$st->fetch();
		print_r($arr);
	}
}
