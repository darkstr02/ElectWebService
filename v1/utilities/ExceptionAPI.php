<?php

//USO:
//		throw new ExceptionAPI(2,"Error",404); 

class ExceptionAPI extends Exception
{
	public $estado;
	
	public function __construct($estado,$mensaje,$codigo=400)
	{
		$this->estado = $estado;
		$this->message = $mensaje;
		$this->code = $codigo;
	}

	//public function getMensaje()
//	{
//		return $this->mensaje;
//	}
//
//	public function getCode()
//	{
//		return  $this->code;
//	}


}

?>
