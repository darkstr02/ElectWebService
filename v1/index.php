<?php
	include_once "data/ConexionBD.php";
	include_once "view/VistaJson.php";
	include_once "data/incidentes.php";
	include_once "data/representantes.php";
	include_once "utilities/ExceptionAPI.php";
	
	
	//print $_GET['PATH_INFO'];
	//print ConexionBD::obtenerInstancia()->obtenerBD()->errorCode();

	//MANEJO DE EXCEPCIONES
	
	$vista = new VistaJson();
	set_exception_handler(function ($exception) use ($vista) {
		$cuerpo = array (
				"estado" => $exception->estado,
				"mensaje" => $exception->getMessage()
	
			);
		if($exception->getCode()){
			$vista->estado = $exception->getCode();
	
		} else {
	
			$vista->estado=500;
		}
	$vista->imprimir($cuerpo);
	}
	);



	//Dividir URL
	$peticion = explode("/",$_GET['PATH_INFO']);
	
	
	//Obtener recurso
	$recurso = array_shift($peticion);
	
	//Recursos de Elecciones;
	$recursos_existentes = array('casillas','incidentes','representantes'); 
	
	//Comprobar si existe el recurso
	if(!in_array($recurso, $recursos_existentes)){
		//Respuesta error;
		
	}
	
	$metodo = strtolower($_SERVER['REQUEST_METHOD']);
	
	switch($metodo)
	{
		case 'get':
			//Procesar método get;
			break;
		case 'post':
			//var_dump($recurso);
			//var_dump($metodo);
			//var_dump($peticion);
			if(method_exists($recurso,$metodo))
			{	
				$respuesta = call_user_func(array($recurso,$metodo),$peticion);
				$vista->imprimir($respuesta);
				break;
			}
			
		case 'put';
			//Procesar método put;
			break;
 		case 'delete':
			//Procesar método delete;
			break;
		default:
			//Método no aceptado;
			$vista->estado = 405;
			$cuerpo = [
				"estado" => ESTADO_METODO_NO_PERMITIDO,
				"mensaje" => utf8_encode("Método no permitido")			
			];
			$vista->imprimir($cuerpo);
	}

?>
