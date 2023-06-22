// [FUNCIONES HELPERS]

const checkBox = ["checkWeb","checkTV","checkRRSS","checkAmigo"];

/**
 * @author Jose Sepulveda
 * @version 1.0
 * @description Funcion que inicia las funciones requeridas al inicio de pagina.
*/
function inicioPagina() 
{
    obtenerRegiones();
    obtenerCandidatos();
    limpiarFormulario();
}

/**
 * @author Jose Sepulveda
 * @version 1.0
 * @description Funcion que obtienes las regiones para el despliegue del select Regiones
*/
function obtenerRegiones(){

    $.ajax({
        url: 'http://localhost/AreaDeTrabajo/SistemaDeVotacion/backend/controllers/Formulario.php',  // Reemplaza 'ruta_al_archivo.php' con la ruta correcta a tu archivo PHP
        method: 'GET',
        dataType: 'json',
        data: {funcion : 'obtenerRegiones'},
        success: function(response) {
            if(response.respuesta)
            {
                $("#slcRegion").empty();
                $("#slcRegion").append('<option class="opcion" value="">--Seleccione--</option>');
                $.each(response.data, function(index, value)
                {
                    $("#slcRegion").append('<option class="opcion" value="'+value.id_region+'">'+value.nombre_region+'</option>');
                });
            }

        },
        error: function(xhr, status, error) {
            $("#slcRegion").empty();
            console.log('Error:', error);
        }
    });
}


/**
 * @author Jose Sepulveda
 * @version 1.0
 * @description Funcion que se gatilla cuando el usuario seleciona una region y trae las comunas de esa region.
*/
function getComunas()
{
    let region = $("#slcRegion").val();
    $.ajax({
        url: 'http://localhost/AreaDeTrabajo/SistemaDeVotacion/backend/controllers/Formulario.php',  // Reemplaza 'ruta_al_archivo.php' con la ruta correcta a tu archivo PHP
        method: 'GET',
        dataType: 'json',
        data: {funcion : 'obtenerComunas', region},
        success: function(response) {

            if(response.respuesta)
            {
                $("#slcComuna").empty();
                $("#slcComuna").append('<option class="opcion" value="">--Seleccione--</option>');
                $.each(response.data, function(index, value)
                {
                    $("#slcComuna").append('<option class="opcion" value="'+value.id_comuna+'">'+value.nombre_comuna+'</option>');
                });
            }
            else
            {
                $("#slcComuna").empty();
                $("#slcComuna").append('<option class="opcion" value="">--Seleccione--</option>');
            }
        },
        error: function(xhr, status, error) {
            console.log('Error:', error);
        }
    });
}

/**
 * @author Jose Sepulveda
 * @version 1.0
 * @description Funcion que obtienes los candidatos para el despliegue del select Candidatos
*/
function obtenerCandidatos(){

    $.ajax({
        url: 'http://localhost/AreaDeTrabajo/SistemaDeVotacion/backend/controllers/Formulario.php',  // Reemplaza 'ruta_al_archivo.php' con la ruta correcta a tu archivo PHP
        method: 'GET',
        dataType: 'json',
        data: {funcion : 'obtenerCandidatos'},
        success: function(response) {
            if(response.respuesta)
            {
                $("#slcCandidato").empty();
                $("#slcCandidato").append('<option class="opcion" value="">--Seleccione--</option>');
                $.each(response.data, function(index, value)
                {
                    $("#slcCandidato").append('<option class="opcion" value="'+value.id_candidato+'">'+value.nombre_candidato+'</option>');
                });
            }
            else
            {
                $("#slcCandidato").empty();
                $("#slcCandidato").append('<option class="opcion" value="">--Seleccione--</option>');
            }
        },
        error: function(xhr, status, error) {
            console.log('Error:', error);
        }
    });
}



/**
 * @author Jose Sepulveda
 * @version 1.0
 * @description Funcion que recopila la informacion ingresada por el usuario para guardar el voto.
*/
function enviarFormulario()
{  
    unsetInvalid();
    let checkBoxes = [];
    checkBox.forEach(function(value){
        if($("#"+value).prop('checked'))
        {
            checkBoxes.push({'valor' : $("#"+value).val()})
        }
    });

    let dataSend = {
        funcion             : 'realizarVotacion',
        txtNombreApellido   : $("#txtNombreApellido").val(),
        txtAlias            : $("#txtAlias").val(),
        txtRut              : $("#txtRut").val(),
        txtEmail            : $("#txtEmail").val(),
        slcRegion           : $("#slcRegion").val(),
        slcComuna           : $("#slcComuna").val(),
        slcCandidato        : $("#slcCandidato").val(),
        checkBoxes
    };

    $.ajax({
        url: 'http://localhost/AreaDeTrabajo/SistemaDeVotacion/backend/controllers/Formulario.php',  // Reemplaza 'ruta_al_archivo.php' con la ruta correcta a tu archivo PHP
        method: 'POST',
        dataType: 'json',
        data: dataSend,
        success: function(response) {
            if(response.respuesta)
            {
                unsetInvalid();  
                limpiarFormulario();
            }
            else
            {
                if(response.validate)
                {
                    unsetInvalid();
                    validateShow(response.dataValidate);
                }
                else
                {
                    alert("Voto echo NO OK");
                }
            }

            
        },
        error: function(xhr, status, error) {
            console.log('Error:', error);
        }
    });





}


/**
 * @author Jose Sepulveda
 * @version 1.0
 * @description Funcion que recibe la data con los inputs con ingreso incorrecto, 
 * y les agrega la clase CSS de validacion y se inserta despues de el mensaje de validacion
*/
function validateShow(data){

    data.forEach(input => {

        $("#"+input["ID"]).addClass("is-invalid");
        $("<div id='dv_"+input["ID"]+"' class='invalid-feedback'>" + input["required"] + "</div>").insertAfter($("#"+input["ID"]));    
    });
}

/**
 * @author Jose Sepulveda
 * @version 1.0
 * @description Funcion que limpia las validaciones de inputs
*/
function unsetInvalid() {

    $('input').removeClass('is-invalid');
    $('select').removeClass('is-invalid');
    $('textarea').removeClass('is-invalid');
    $(".invalid-feedback").remove();

}


/**
 * @author Jose Sepulveda
 * @version 1.0
 * @description Funcion que limpia el formulario
*/
function limpiarFormulario(){
    $("#txtNombreApellido").val("");
    $("#txtAlias").val("");
    $("#txtRut").val("");
    $("#txtEmail").val("");
    $("#slcRegion").val("");
    $("#slcRegion").val("");
    $("#slcComuna").val("");
    $("#slcCandidato").val("");

    checkBox.forEach(function(value){
        $("#"+value).prop('checked', false);
    });

}

/**
 * @author Jose Sepulveda
 * @version 1.0
 * @description Funcion que valia el input rut en el ingreso de informacion (KEYUP)
*/
function validaRut()
{
    $("#txtRut").Rut({
        format_on: 'keyup'
    })    
}
















// [LLAMADO DE FUNCIONES]

inicioPagina();