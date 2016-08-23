<?php

class representantes
{


	const NOMBRE_TABLA = "REPRESENTANTES";
	const ID_REPRESENTANTE = "id";
	const AP_PATERNO = "ap_paterno";
	const AP_MATERNO = "ap_materno";
	const NOMBRE = "nombre";
	const REP_GENERAL = "rep_general";
	const CONTRASENA = "contrasena";
	const API_KEY = "api_key";
	
	const ESTADO_CREACION_EXITOSA = 1;
	const ESTADO_CREACION_FALLIDA = 2;
	const ESTADO_FALLA_DESCONOCIDA = 3;


	public static function post($peticion)
	{
			if($peticion[0] = 'insertar') {
			return self::insertar();
			} else {
				throw new ExceptionAPI(self::ESTADO_URL_INCORRECTA, "Url mal formado",400);
			}

	}
		//{
		//	"ap_paterno":" "
		//	"ap_materno":" "
		//	"nombre":" "
		//	"rep_general":" "
		//	"contrasena":" "
		//}

	private function insertar()
	{
		$cuerpo = file_get_contents('php://input');
		$usuario = json_decode($cuerpo);
		
		$resultado = self::crear($usuario);

		switch($resultado)
		{
			case self::ESTADO_CREACION_EXITOSA:
				http_response_code(200);
				return
				[
					"estado" => self::ESTADO_CREACION_EXITOSA,
					"mensaje" => utf8_encode("¡Representante Registrado con Éxito!")
				];
			break;

			case self::ESTADO_CREACION_FALLIDA:
				throw new ExceptionAPI(self::ESTADO_CREACION_FALLIDA,"Ha ocurrido un error");
				break;
			default:
				throw new ExceptionAPI(self::ESTADO_FALLA_DESCONOCIDA,"Falla desconocida",400);
		}
		//Continuara
	}
	
	private function crear($datosRepresentante)
	{

		//var_dump($datosRepresentante);

		$ap_paterno = $datosRepresentante->ap_paterno;
		$ap_materno = $datosRepresentante->ap_materno;
		$nombre = $datosRepresentante->nombre;
		$rep_general = $datosRepresentante->rep_general;
		$contrasena = self::encriptarContrasena($datosRepresentante->contrasena);
		$api_key = self::generarClaveAPI();
		
		try {
			$pdo = ConexionBD::obtenerInstancia()->obtenerBD();

			//INSERT
			$comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
			self::AP_MATERNO . "," .
			self::AP_PATERNO . "," .
			self::NOMBRE . "," .
			self::REP_GENERAL . "," .
			self::CONTRASENA . "," .
			self::API_KEY . ")" .
			" VALUES(?,?,?,?,?,?)";

			$sentencia = $pdo->prepare($comando);

			$sentencia->bindParam(1,$ap_paterno);
			$sentencia->bindParam(2,$ap_materno);
			$sentencia->bindParam(3,$nombre);
			$sentencia->bindParam(4,$rep_general);
			$sentencia->bindParam(5,$contrasena);
			$sentencia->bindParam(6,$api_key);

			$resultado = $sentencia->execute();

			if($resultado) {
				return self::ESTADO_CREACION_EXITOSA;
			} else {
				return self::ESTADO_CREACION_FALLIDA;
			}

		} catch(PDOException $e) {
			throw new ExceptionAPI(1, $e->getMessage());
		}

	}
	
	private function generarClaveAPI()
	{
		return md5(microtime().rand());
	}

	private function encriptarContrasena($contrasenaPlana)
	{
		if($contrasenaPlana)
			return password_hash($contrasenaPlana, PASSWORD_DEFAULT);
		else
			return null;
	}

}



?>
