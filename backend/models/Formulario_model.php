<?php

// Conexion a BDD MySQL 
$conexion = mysqli_connect('localhost', 'root', '', 'registro_votaciones');
if (!$conexion) {
    die('Error de conexión: ' . mysqli_connect_error());
}


 /**
 * @author Jose Sepulveda
 * @since Version 1.0.0 
 * Funcion que ejecuta una Query que obtiene las regiones de la tabla t_region, retorna Array con valores obtenidos
*/
function obtenerRegionesModel()
{
    global $conexion;

    $query = "
        SELECT 
            t_region.id_region,
            t_region.nombre_region
        FROM
            t_region
        where
            t_region.vigente = 1       
    ";

    $result = $conexion->execute_query($query);
    if($result)
    {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    else
    {
        return NULL;
    }
}

/**
 * @author Jose Sepulveda
 * @since Version 1.0.0 
 * Funcion que ejecuta una Query que obtiene las comunas de la tabla t_comuna, retorna Array con valores obtenidos
 * @param $region = idregion
*/
function obtenerComunasModel($region)
{
    global $conexion;

    $query = "
        SELECT 
            t_comuna.id_comuna,
            t_comuna.nombre_comuna
        FROM
            t_comuna
        WHERE
            t_comuna.vigente = 1
        AND
            t_comuna.id_region = ".$region;

    $result = $conexion->execute_query($query);
    if($result)
    {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    else
    {
        return NULL;
    }
}

/**
 * @author Jose Sepulveda
 * @since Version 1.0.0 
 * Funcion que ejecuta una Query que obtiene los candidatos de la tabla t_candidato, retorna Array con valores obtenidos
*/
function obtenerCandidatosModel()
{
    global $conexion;

    $query = "
        SELECT 
            t_candidato.id_candidato,
            t_candidato.nombre_candidato
        FROM
            t_candidato
        WHERE
            t_candidato.vigente = 1   
    ";

    $result = $conexion->execute_query($query);
    if($result)
    {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    else
    {
        return NULL;
    }
}

/**
 * @author Jose Sepulveda
 * @since Version 1.0.0 
 * Funcion que valida si un rut ingresado ya tiene voto registrado, retorna total de registros encontrados
 * @param $rut = 11.111.111-1
*/
function validaRutModel($rut)
{
    global $conexion;

    $rut = explode('-',$rut);
    $rut = str_replace('.','',$rut[0]);

    $query = "
        SELECT 
            t_votacion.*
        FROM
            t_votacion
        WHERE
            t_votacion.rut_votante = '".$rut."'";

    $result = $conexion->execute_query($query);
    if($result)
    {
        return count($result->fetch_all(MYSQLI_ASSOC));
    }
    else
    {
        return NULL;
    }
}


/**
 * @author Jose Sepulveda
 * @since Version 1.0.0 
 * Funcion que ingresa el voto con los datos ingresados en el formulario por el usuario.
 * @param $datosInsert
*/
function realizarVotacionModel($arrayDatos)
{
    global $conexion;
    $rut = explode('-',$arrayDatos["rut_votante"]);

    //[INSERT VOTO] : se inserta el voto y se extrae el Id obtenido de la tabla t_votacion
    $insertVoto = "
        INSERT INTO 
        t_votacion 
            (nombre_votante, alias_votante, rut_votante, dv_votante, email_votante)
        VALUES
            (   '".$arrayDatos["nombre_votante"]."',
                '".$arrayDatos["alias_votante"]."',
                '".str_replace('.','',$rut[0])."',
                '".$rut[1]."',
                '".$arrayDatos["email_votante"]."'
            )
    ";

    $insert = $conexion->query($insertVoto);
    $idvotacion = ($insert ? $conexion->insert_id : 'NULL');

    // [IDVOTACION] : Con el id del registro ingresado, se inserta del detalle del voto.
    if($idvotacion)
    {
        $detalle = '';
        if($arrayDatos["checkbox"])
        {
            for ($i=0; $i < count($arrayDatos["checkbox"]) ; $i++) 
            {
                $detalle .=  $arrayDatos["checkbox"][$i]["valor"].', ';
            }
        }

        $insertDetalle = "
            INSERT INTO
            t_detalle
                (comuna, detalle, id_votacion, id_candidato)
            VALUES
                (
                    '".$arrayDatos["id_comuna"]."',
                    '".$detalle."',
                    ".$idvotacion.",
                    ".$arrayDatos["id_candidato"]."
                )    
        ";

        $insertDetalle = $conexion->query($insertDetalle);        
        if($insertDetalle)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}


?>