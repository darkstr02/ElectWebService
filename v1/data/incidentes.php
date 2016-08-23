<?php

require '/var/www/html/api.eleccionesdemo.com/v1/utilities/ExceptionAPI.php';
require '/var/www/html/api.eleccionesdemo.com/v1/index.php';

class incidentes
{

	const NOMBRE_TABLA = "INCIDENTES";
	const ID_INCIDENTE = "id";
	const ID_CASILLA = "id_casilla";
	const ID_REPRESENTANTE = "id_representante";
	const COMENTARIOS = "comentarios";
	const FOTOGRAFIA = "fotografia";
	const UBICACION = "ubicacion";
	const UPDATED_LOG = "updated_log";

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


	/*

		Estructura de la Peticion JSON:
		{
			"id_casilla":" "
			"id_representante":" "
			"comentarios":" "
			"fotografia":" "
			"ubicacion":" "
		}


	*/

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
					"mensaje" => utf8_encode("¡Incidente Registrado con Éxito!")
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

	private function crear($datosIncidente)
	{

		var_dump($datosIncidente);
		//$id_incidente = $datosIncidente->id;
		$id_casilla = $datosIncidente->id_casilla;
		$id_representante = $datosIncidente->id_representante;
		$comentarios = $datosIncidente->comentarios;
		$fotografia = $datosIncidente->fotografia;
		$ubicacion = $datosIncidente->ubicacion;

	//	$claveAPI = self::generarClaveAPI();

		try {
			$pdo = ConexionBD::obtenerInstancia()->obtenerBD();

			//INSERT
			$comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
			self::ID_CASILLA . "," .
			self::ID_REPRESENTANTE . "," .
			self::COMENTARIOS . "," .
			self::FOTOGRAFIA . "," .
			self::UBICACION . ")" .
			" VALUES(?,?,?,?,GeomFromText(?))";

			$sentencia = $pdo->prepare($comando);

			$sentencia->bindParam(1,$id_casilla);
			$sentencia->bindParam(2,$id_representante);
			$sentencia->bindParam(3,$comentarios);
			$sentencia->bindParam(4,$fotografia);
			$sentencia->bindParam(5,$ubicacion);

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

	//-----------USALA PARA LOS REPRESENTANTES WEY ------------------------------//
	//private function generarClaveAPI()
	//{
	//	return md5(microtime().rand());
	//}


}


?>
