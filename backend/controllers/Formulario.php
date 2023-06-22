<?php
    include ('../models/Formulario_model.php'); // Se incluye la clase Model.


    /**
	 * @author Jose Sepulveda
	 * @since Version 1.0.0 
	 * Funcion que obtiene la variable "funcion" y orquesta el llamdo de funciones. 
	*/
    
    // METODOS GET
    if(isset($_GET['funcion']) && !empty($_GET['funcion'])) {
        $funcion = $_GET['funcion'];
        switch($funcion) {
            case 'obtenerRegiones': 
                obtenerRegiones();
            break;

            case 'obtenerComunas': 
                obtenerComunas();
            break;

            case 'obtenerCandidatos': 
                obtenerCandidatos();
            break;
        }
    }

    // METODOS POST
    if(isset($_POST['funcion']) && !empty($_POST['funcion'])) {
        $funcion = $_POST['funcion'];
        switch($funcion) {
            case 'realizarVotacion': 
                realizarVotacion();
            break;
        }
    }

     /**
	 * @author Jose Sepulveda
	 * @since Version 1.0.0 
	 * Funcion que va en busca de las regiones a la clase Model 
	*/
    function obtenerRegiones()
    {  
        $regiones = obtenerRegionesModel(); 
        if($regiones)
        {
            $data = array(
                "respuesta" => true,
                "data" => $regiones
            );
            
            echo json_encode($data);
        }
        else
        {
            $data = array(
                "respuesta" => false
            );

            echo json_encode($data);
        }

        

    }

    /**
	 * @author Jose Sepulveda
	 * @since Version 1.0.0 
	 * Funcion que va en busca de las Comunas a la clase Model 
	*/
    function obtenerComunas()
    {
        if(isset($_GET['region']) && !empty($_GET['region'])) 
        {
            $region = $_GET['region'];
            $comunas = obtenerComunasModel($region);

            $data = array(
                "respuesta" => true,
                "data" => $comunas
            );

            echo json_encode($data);
        }
        else
        {
            $data = array(
                "respuesta" => false,
            );

            echo json_encode($data);
        }
    }

    /**
	 * @author Jose Sepulveda
	 * @since Version 1.0.0 
	 * Funcion que va en busca de los candidatos a la clase Model 
	*/
    function obtenerCandidatos(){
        $candidatos = obtenerCandidatosModel(); 
        if($candidatos)
        {
            $data = array(
                "respuesta" => true,
                "data" => $candidatos
            );

            echo json_encode($data);
        }
        else
        {
            $data = array(
                "respuesta" => false
            );

            echo json_encode($data);
        }
    }


    /**
	 * @author Jose Sepulveda
	 * @since Version 1.0.0 
	 * Funcion que recopila los datos recibidos por POST para el ingreso del voto
	*/
    function realizarVotacion(){

        $txtNombreApellido = (!empty($_POST['txtNombreApellido']) ? $_POST['txtNombreApellido'] : '');
        $txtAlias = (!empty($_POST['txtAlias']) ? $_POST['txtAlias'] : '');
        $txtRut = (!empty($_POST['txtRut']) ? $_POST['txtRut'] : '');
        $txtEmail = (!empty($_POST['txtEmail']) ? $_POST['txtEmail'] : '');
        $slcComuna = (!empty($_POST['slcComuna']) ? $_POST['slcComuna'] : NULL);
        $slcCandidato = (!empty($_POST['slcCandidato']) ? $_POST['slcCandidato'] : '');
        $checkboxes = (!empty($_POST['checkBoxes']) ? $_POST['checkBoxes'] : array());

        // Se crea Array con la data obtenida
        $arrayDatos = array(
            "nombre_votante" =>  $txtNombreApellido,
            "alias_votante" => $txtAlias,
            "rut_votante" => $txtRut,
            "email_votante" => $txtEmail,
            "checkbox" => $checkboxes,
            "id_candidato" => $slcCandidato,
            "id_comuna" => $slcComuna
        );
        
        // Se hacen las validaciones correspondientes, si el metodo retorna 0 validaciones pendientes se procede a llamar al Model
        // para hacer el guardado de la informacion.
        $validaciones = validaciones($arrayDatos); 
        if(count($validaciones) == 0)
        {
            $guardarRegistros = realizarVotacionModel($arrayDatos);
            if($guardarRegistros)
            {
                $data = array(
                    "respuesta" => true
                );
            }
            else
            {
                $data = array(
                    "respuesta" => false
                );
            }

            echo json_encode($data);
        }
        else
        {
            $data = array(
                "respuesta" => false,
                "validate" => true,
                "dataValidate" => $validaciones
            );

            echo json_encode($data);
        }
    }

     /**
	 * @author Jose Sepulveda
	 * @since Version 1.0.0 
	 * Funcion que realiza las validaciones correspondientes a los valores ingresados por el usuario
	*/
    function validaciones($arrayDatos){
        $arrayValidaciones = array(); // Se crea variable como Array Vacio
        

        // Si el valor ingresado por el usuario no cumple con las validaciones correspondientes, se agrega al $arrayValidaciones
        // los valores son ID = corresponde a ID del input del html, required = el valor del texto a mostrar en caso de validacion. 

        if(empty($arrayDatos["nombre_votante"]) || $arrayDatos["nombre_votante"] == "")
        {
            $dataAux = array("ID" => 'txtNombreApellido', "required" => "Debe ingresar un Nombre y Apellido");
            array_push($arrayValidaciones, $dataAux);
        }

        if(strlen($arrayDatos["alias_votante"]) < 5)
        {
            $dataAux = array("ID" => 'txtAlias', "required" => "El campo debe tener almenos 5 caracteres, ademas solo letras y números");
            array_push($arrayValidaciones, $dataAux);
        }

        if($arrayDatos["rut_votante"] !== "")
        {
            $validaRut = validaRutChileno($arrayDatos["rut_votante"]);

            if($validaRut["error"])
            {
                $dataAux = array("ID" => 'txtRut', "required" => $validaRut["msj"]);
                array_push($arrayValidaciones, $dataAux);
            }
    
            $validaRutRepetido = validaRutModel($arrayDatos["rut_votante"]);
            if($validaRutRepetido > 0 )
            {
                $dataAux = array("ID" => 'txtRut', "required" => "El rut ingresado ya tiene registrado un voto!");
                array_push($arrayValidaciones, $dataAux);
            }
        }
        else
        {
            $dataAux = array("ID" => 'txtRut', "required" => "Debe ingresar un rut!");
                array_push($arrayValidaciones, $dataAux);
        }
    
        if($arrayDatos["email_votante"] == "" || empty($arrayDatos["email_votante"]))
        {
            $dataAux = array("ID" => 'txtEmail', "required" => "Debe ingresar un email");
            array_push($arrayValidaciones, $dataAux);
        }
        else
        {
            if(!filter_var($arrayDatos["email_votante"], FILTER_VALIDATE_EMAIL))
            {
                $dataAux = array("ID" => 'txtEmail', "required" => "Debe ingresar un email valido");
                array_push($arrayValidaciones, $dataAux);
            }
        }

        if($arrayDatos["id_candidato"] == "" || empty($arrayDatos["id_candidato"]))
        {
            $dataAux = array("ID" => 'slcCandidato', "required" => "Debe seleccionar un candidato");
            array_push($arrayValidaciones, $dataAux);
        }

        if(count($arrayDatos["checkbox"]) < 2)
        {
            $dataAux = array("ID" => 'dv_checkboxes', "required" => "Debe seleccionar almenos 2 opciones");
            array_push($arrayValidaciones, $dataAux);
        }

        return $arrayValidaciones;
    }


     /**
	 * @author Jose Sepulveda
	 * @since Version 1.0.0 
	 * Funcion valida los rut chilenos
     * Formato rut 11111111-1
	*/
    function validaRutChileno($rut)
    {
        $rut = str_replace('.','',$rut);
        // Verifica que no esté vacio y que el string sea de tamaño mayor a 3 carácteres(1-9)        
        if ((empty($rut)) || strlen($rut) < 3) {
            return array('error' => true, 'msj' => 'RUT vacío o con menos de 3 caracteres.');
        }

        // Quitar los últimos 2 valores (el guión y el dígito verificador) y luego verificar que sólo sea
        // numérico
        $parteNumerica = str_replace(substr($rut, -2, 2), '', $rut);

        if (!preg_match("/^[0-9]*$/", $parteNumerica)) {
            return array('error' => true, 'msj' => 'La parte numérica del RUT sólo debe contener números.');
        }

        $guionYVerificador = substr($rut, -2, 2);
        // Verifica que el guion y dígito verificador tengan un largo de 2.
        if (strlen($guionYVerificador) != 2) {
            return array('error' => true, 'msj' => 'Error en el largo del dígito verificador.');
        }

        // obliga a que el dígito verificador tenga la forma -[0-9] o -[kK]
        if (!preg_match('/(^[-]{1}+[0-9kK]).{0}$/', $guionYVerificador)) {
            return array('error' => true, 'msj' => 'El dígito verificador no cuenta con el patrón requerido');
        }

        // Valida que sólo sean números, excepto el último dígito que pueda ser k
        if (!preg_match("/^[0-9.]+[-]?+[0-9kK]{1}/", $rut)) {
            return array('error' => true, 'msj' => 'Error al digitar el RUT');
        }

        $rutV = preg_replace('/[\.\-]/i', '', $rut);
        $dv = substr($rutV, -1);
        $numero = substr($rutV, 0, strlen($rutV) - 1);
        $i = 2;
        $suma = 0;
        foreach (array_reverse(str_split($numero)) as $v) {
            if ($i == 8) {
                $i = 2;
            }
            $suma += $v * $i;
            ++$i;
        }
        $dvr = 11 - ($suma % 11);
        if ($dvr == 11) {
            $dvr = 0;
        }
        if ($dvr == 10) {
            $dvr = 'K';
        }
        if ($dvr == strtoupper($dv)) {
            return array('error' => false, 'msj' => 'RUT ingresado correctamente.');
        } else {
            return array('error' => true, 'msj' => 'El RUT ingresado no es válido.');
        }
    }
?>