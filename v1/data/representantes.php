<?php



class representantes
{


	const NOMBRE_TABLA 	= "REPRESENTANTES";
	const ID_REPRESENTANTE 	= "id";
	const AP_PATERNO 	= "ap_paterno";
	const AP_MATERNO 	= "ap_materno";
	const NOMBRE 		= "nombre";
	const REP_GENERAL 	= "rep_general";
	const CONTRASENA 	= "contrasena";
	const API_KEY 		= "api_key";
	
	const ESTADO_CREACION_EXITOSA = 1;
	const ESTADO_CREACION_FALLIDA = 2;
	const ESTADO_FALLA_DESCONOCIDA = 3;
	const ESTADO_PARAMETROS_INCORRECTOS = 4;
	const ESTADO_CLAVE_NO_AUTORIZADA = 5;
 	const ESTADO_AUSENCIA_CLAVE_API = 6;

	public static function post($peticion)
	{
			if($peticion[0] == 'insertar') {
				//var_dump($peticion);
				return self::insertar();
			} else if ($peticion[0] == 'login') {
				return self::login();
			} else {
				throw new ExceptionAPI(self::ESTADO_URL_INCORRECTA, "Url mal formado",400);
			}

	}

		// ESTRUCTURA DEL CUERPO JSON PARA REGISTRAR NUEVO REPRESENTANTE (PROVISIONAL)
		//{
		//	"ap_paterno":" "
		//	"ap_materno":" "
		//	"nombre":" "
		//	"rep_general":" "
		//	"contrasena":" "
		//}


	private static function insertar()
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
	}
	
	private static function crear($datosRepresentante)
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

	private static function login()
	{
		$respuesta = array();
		$body = file_get_contents('php://input');
		$usuario = json_decode($body);

		$nombre = $usuario->nombre;
		$contrasena = $usuario->contrasena;

		if(self::autenticar($nombre,$contrasena)) {

			$usuarioBD = self::obtenerUsuarioPorNombre($nombre);
			if($usuarioBD != NULL)
			{
				http_response_code(200);
				$respuesta["ap_paterno"] = $usuarioBD["ap_paterno"];
				$respuesta["ap_materno"] = $usuarioBD["ap_materno"];
				$respuesta["nombre"] = $usuarioBD["nombre"];
				$respuesta["rep_general"] = $usuarioBD["rep_general"];
				$respuesta["api_key"] = $usuarioBD["api_key"];

				return ["estado" => 1, "usuario" => $respuesta];
			} else { 
				throw new ExceptionAPI(self::ESTADO_FALLA_DESCONOCIDA, "Ha ocurrido un error");
			}
		} else {
			throw new ExceptionAPI(self::ESTADO_PARAMETROS_INCORRECTOS,
				utf8_encode("Correo o contraseña inválidos"));
		}

	}

	private static function generarClaveAPI()
	{
		return md5(microtime().rand());
	}

	private static function encriptarContrasena($contrasenaPlana)
	{
		if($contrasenaPlana)
			return password_hash($contrasenaPlana, PASSWORD_DEFAULT);
		else
			return null;
	}

	private static function autenticar($nombre, $contrasena)
	{
		$comando = "SELECT contrasena FROM " . self::NOMBRE_TABLA .
			" WHERE " . self::NOMBRE . " = ?";

		try
		{
			$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
			$sentencia->bindParam(1,$nombre);
			$sentencia->execute();

			if($sentencia)
			{
				$resultado = $sentencia->fetch();
				//var_dump($resultado);
				if(self::validarContrasena($contrasena,$resultado['contrasena']))
					return true;
				else
					return false;
			}
			else
				return false;

		} catch (PDOException $e) {
			throw new ExceptionAPI(self::ESTADO_ERROR_BD, $e->getMessage());
		}

	}

	private static function validarContrasena($contrasenaPlana, $contrasenaHash)
	{
		return password_verify($contrasenaPlana, $contrasenaHash);
	}

	private static function obtenerUsuarioPorNombre($nombre)
	{
		$comando = "SELECT " .
			self::AP_PATERNO . "," .
			self::AP_MATERNO . "," .
			self::NOMBRE . "," .
			self::REP_GENERAL . "," .
			SELF::API_KEY .
			" FROM " . self::NOMBRE_TABLA .
			" WHERE " . self::NOMBRE . "=?";

		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
		$sentencia->bindParam(1,$nombre);

		if($sentencia->execute())
			return $sentencia->fetch(PDO::FETCH_ASSOC);
		else
			return null;
	}


	//Funciones de Autenticación por Clave API.

	public static function autorizar()
	{
		$cabeceras = apache_request_headers();

		//var_dump($cabeceras);

		if(isset($cabeceras["authorization"])) {

			$claveAPI = $cabeceras["authorization"];

			if(representantes::validarClaveApi($claveAPI)) {
				return representantes::obtenerIdUsuario($claveAPI);
			} else {
				throw new ExceptionAPI(self::ESTADO_CLAVE_NO_AUTORIZADA, "Clave API no autorizada",401);
			}
		} else {

			throw new ExceptionAPI (self::ESTADO_AUSENCIA_CLAVE_API,utf8_encode("Se requiere Clave API para autenticación"));

		}
	}

	private function validarClaveApi($claveAPI)
	{
		$comando = "SELECT COUNT(" . self::ID_REPRESENTANTE . ")" .
			" FROM " . self::NOMBRE_TABLA .
			" WHERE " . self::API_KEY . "=?";

		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
		$sentencia->bindParam(1,$claveAPI);
		$sentencia->execute();

		return $sentencia->fetchColumn(0) > 0;
	}

	private function obtenerIdUsuario($claveAPI)
	{
		$comando = "SELECT " . self::ID_REPRESENTANTE .
			" FROM " . self::NOMBRE_TABLA .
			" WHERE " . self::API_KEY . "=?";

		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
		$sentencia->bindParam(1,$claveAPI);

		if($sentencia->execute()) {
			$resultado = $sentencia->fetch();
			return $resultado['id'];
		} else
			return null;
	}

}



?>
