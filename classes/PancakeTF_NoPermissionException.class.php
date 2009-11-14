<?php

class PancakeTF_NoPermissionException extends LogicException{
	protected $type;
	
	public function __construct($type, $code = 0, Exception $previous = null){
		$this->type = $type;
		$err = "No Permission:$type";
		if ($previous instanceof Exception)
			parent::__construct($err,$code,$previous);
		else parent::__construct($err,$code);	
	}
	
	public function getType(){return $this->type;}
}