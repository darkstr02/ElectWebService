<?php

class casillas
{
	//Datos de la tabla "casillas"

	const NOMBRE_TABLA 	= 	"CASILLAS";
	const ID_CASILLA 	= 	"id";
	const ID_SECTOR 	= 	"id_sector";
	const DOMICILIO 	= 	"domicilio";
	const DOMICILIO_AUX 	= 	"domicilio_aux";
	const ID_TIPOCASILLA 	= 	"id_tipocasilla";
	const UBICACION 	= 	"ubicacion";
	const HORA_APERTURA 	= 	"hora_apertura";
	const HORA_CIERRE 	= 	"hora_cierre";

	const ESTADO_ERROR_BD = 1;
	const ESTADO_URL_INCORRECTA = 2;

	public static function get($peticion)
	{
		if($peticion[0] == 'puntos')	{
			return self::puntos();
		} else {
			throw new ExceptionAPI(self::ESTADO_URL_INCORRECTA, "Url mal formado");
		}


	}

	public static function post($peticion)
	{
		//Procesar POST
	}



	private static function puntos()
	{
		$respuesta = array();
		$cuerpo = file_get_contents('php://input');
		$payload = json_decode($cuerpo);

		$resultado = self::recolectar();
		if(!is_null($resultado))
		{
			//var_dump($resultado);
			http_response_code(200);

			foreach($resultado as $key => $value)
			{
				$registro[self::ID_CASILLA] = $value[self::ID_CASILLA];
				$registro[self::ID_SECTOR] = $value[self::ID_SECTOR];
				$registro[self::DOMICILIO] = $value[self::DOMICILIO];
				$registro["domicilio_aux"] = $value[3];
				//$respuesta[ID_TIPOCASILLA = $resultado[ID_TIPOCASILLA];
				$registro[self::UBICACION] = $value[self::UBICACION];
				$registro[self::HORA_APERTURA] = $value[self::HORA_APERTURA];
				$registro[self::HORA_CIERRE] = $value[self::HORA_CIERRE];

				$respuesta[$key] = $registro;

			}
			return ["estado" => 1, "casilla" => $respuesta];
		} else {
			throw new ExceptionAPI(self::ESTADO_FALLA_DESCONOCIDA,"Ha ocurrido un error");
		}

	}

	private static function recolectar()
	{
		$comando = "SELECT id,id_sector,domicilio,domicilio_aux,ST_AsText(ubicacion) as ubicacion,hora_apertura,hora_cierre FROM " 
				. self::NOMBRE_TABLA;

		try {
			$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
			$sentencia->execute();

			if($sentencia)
			{
				$resultado = $sentencia->fetchAll();
				//var_dump($resultado);
				return $resultado;
			}
			else
				return null;

		} catch (PDOException $e) {
			throw new ExceptionAPI(self::ESTADO_ERROR_BD, $e->getMessage());
		}

	}

}


?>
