<?php
require_once "headeradm.php";
require_once 'bdd.php';
date_default_timezone_set("America/Mexico_City");
setlocale(LC_TIME, 'spanish');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
$date = new DateTime(); // Date object using current date and time
$dt = $date->format('Y-m-d\TH:i:s');

$date = new DateTime(); // Date object using current date and time
$fechaBajaSist = $date->format('Y-m-d');


if ( isset($_SESSION["msgeliminado"]) && $_SESSION["msgeliminado"] == "1") {
    $_SESSION["msgeliminado"] = 0;
?>
<script>
Swal.fire({
    icon: 'success',
    title: '¡El personal se elimino con éxito!',
    showConfirmButton: false,
    timer: 2000
})
</script>
<?php
}
if (isset($_SESSION["msgRegistroCom"]) && $_SESSION["msgRegistroCom"] == "1") {
    $_SESSION["msgRegistroCom"] = 0;

 ?>
<script type="text/javascript">
<?php


        echo "var rol ='$rol';";
        echo "var nombre ='$nombre';";
        echo "var appat ='$appat';";
        echo "var apmat ='$apmat';";

        ?>
Swal.fire({
    icon: "success",
    title: "El usuario se registro con exito",
    color: '#716add',
    showConfirmButton: true,
    confirmButtonText: 'Continuar.',
    confirmButtonColor: '#2ECC71'



})
</script>
<?php
}
if (isset($_SESSION["msgeditado"]) && $_SESSION["msgeditado"] == "1") {
    $_SESSION["msgeditado"] = 0;
?>
<script>
Swal.fire({
    icon: 'success',
    title: '¡El personal  se actualizo con éxito!',
    showConfirmButton: false,
    timer: 2000
})
</script>
<?php
}
if (isset($_SESSION["msgBajaAdmin"]) && $_SESSION["msgBajaAdmin"] == "1") {
    $_SESSION["msgBajaAdmin"] = 0;
?>
<script>
Swal.fire({
    icon: "success",
    title: "El personal se dio de baja con éxito!",
    color: '#716add',
    showConfirmButton: true,
    confirmButtonText: 'Continuar.',
    confirmButtonColor: '#2ECC71'
})
</script>
<?php
}
if (isset($_SESSION["msgBajaAdmin"]) && $_SESSION["msgBajaAdmin"] == "2") {
    $_SESSION["msgBajaAdmin"] = 0;
?>
<script>
Swal.fire({
    icon: "warning",
    title: "El personal ya esta dado de baja!",
    color: '#716add',
    showConfirmButton: true,
    confirmButtonText: 'Continuar.',
    confirmButtonColor: '#2ECC71'
})
</script>
<?php
}

if (isset($_POST['bajaAdmin'])) {
        $idbajaU = $_POST['idUsu'];
        $motivobaja = $_POST['motivobaja'];
        $fechaBajaIns = $_POST['fechaBajaIns'];
        $cve_RespBaja = $_SESSION["idusuario"];
        $updateUsuBaja = "UPDATE usuario SET usuario.status_usuario = 'Inactivo', usuario.observacion_usuario='".$motivobaja."' where usuario.id =" .$idbajaU;
        $resultupdateconsultaUsuario = mysqli_query($conexion, $updateUsuBaja);
        $_SESSION['msgBajaAdmin'] = 1;
        echo "<script>window.location = 'lista_personal.php'</script> ";
    
}
if (isset($_POST['bajaUsu'])) {
    $id = base64_decode($_POST['idbUsu']);

    $buscaUsu = "SELECT
                    usuario.id idUsu,
                    usuario.nombre nomUsu,
                    usuario.appaterno apUsu,
                    usuario.apmaterno amUsu,
                    usuario.rol,
                    usuario.observacion_usuario
                FROM
                    usuario
                WHERE
                    usuario.id =" .$id;

    $resultdasp =  mysqli_query($conexion, $buscaUsu);

    while ($rowdasp = mysqli_fetch_assoc($resultdasp)) {
    ?>
<!-- MODAL PARA DAR DE BAJA A UN PERSONAL >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> -->

<div class="modal fade modalito" id="modalFormEdit" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title" id="exampleModalLabel">Dar de baja al personal:
                    <?php echo $rowdasp['nomUsu'].' ' .$rowdasp['apUsu']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="cerrar"></button>
            </div>
            <script>
            const redireccionarBtn = document.getElementById('cerrar');
            redireccionarBtn.addEventListener('click', () => {
                window.location = 'lista_personal.php'
            });
            </script>
            <div class="modal-body">
                <div class="col-12">

                    <!-- para insertar un nuevo programa al mismo estudiante >>>>>>>>>>>>>>>>>>>>>>>>>>>>> -->
                    <form class="form-contact contact_form" action="" method="post" accept-charset="utf-8">
                        <div id="contentpro">

                            <div class="row">
                                <div class="col-sm-5">
                                    <div>
                                        <h3><?php echo $rowdasp['nomUsu'].' ' .$rowdasp['apUsu'].' ' .$rowdasp['amUsu']; ?>
                                        </h3>
                                        <p>Cargo: <br>
                                            <?php echo $rowdasp['rol']; ?></p>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Fecha de baja del trabajo:<input class="form-control  hide-on-focus" type="date"
                                            name="fechaBajaIns" required>

                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Fecha de baja del sistema: <?php echo $fechaBajaSist; ?>

                                    </div>
                                </div>
                                <br><br>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        Motivo de la baja:<textarea class="form-control" id="motivobaja"
                                            name="motivobaja" placeholder="Ingresa la observación"
                                            title="Ingresa la observación"><?php echo $rowdasp['observacion_usuario']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group mt-3" align="center">
                                    <input type="hidden" name="idUsu" value="<?php echo $rowdasp['idUsu']; ?>">
                                    <button type="submit" name="bajaAdmin" style="background-color: #4CAF50; border: 1px solid black; color: white; padding: 16px 32px;
                        text-decoration: none; margin: 4px 2px; border-radius: 5px; cursor: pointer;">Baja</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- para insertar un nuevo programa al mismo estudiante <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL PARA REALIZAR LA MODIFICACION <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->
<?php
    }
    ?>
<script type="text/javascript">
$(document).ready(function() {
    $("#modalFormEdit").modal('show');
});
</script>
<?php
}
if (isset($_GET['idaelimUsu']) && $_GET['idaelimUsu'] == "") {

} elseif (isset($_GET['idaelimUsu'])) {
    $id = base64_decode(base64_decode($_GET['idaelimUsu']));

    $sql = "DELETE FROM usuario WHERE id = "."'$id'";
    mysqli_query($conexion, $sql);

    $_SESSION["msgeliminado"] = 1;
    echo "<script>window.location = 'lista_personal.php'</script> ";
}

if (isset($_POST['editDatosUsu'])) {
    $idUsu = $_POST['idUsu'];
    $grado    = $_POST['grado'];
    $password    = $_POST['password'];
    $telefono    = $_POST['telefono'];
    $telefono2    = $_POST['telefono2'];
    $correo_personal      = $_POST['correo_personal'];
    $correo_trabajo      = $_POST['correo_trabajo'];
    $direccion   = $_POST['direccion'];
    $curp_dni   = $_POST['curp_dni'];
    $fechaNac   = $_POST['fechaNac'];
    $sqlupper    = "UPDATE  usuario  SET grado ='".$grado."',  telefono ='".$telefono."',telefono2 ='".$telefono2."', correo_personal ='".$correo_personal."',correo_trabajo ='".$correo_trabajo."', curp_dni ='".$curp_dni."'
                , direccion ='".$direccion."' , fechaNac ='".$fechaNac."' , password ='".$password."'
                WHERE id= " .$idUsu;
    $resultupper = mysqli_query($conexion, $sqlupper);
    $_SESSION["msgeditado"] = 1;
    echo "<script>window.location = 'lista_personal.php'</script> ";
}


//editar vvvvvvvvvvvvvvvvvvvvvv
if (isset($_POST['editarUsu'])) {
    $idEsp = $_POST['idEditUsu'];

    $qry = 'SELECT * from usuario where usuario.id=' .$idEsp;
    $resultdasp =  mysqli_query($conexion, $qry);
    while ($rowdasp = mysqli_fetch_assoc($resultdasp)) {

    ?>
<!-- MODAL PARA REALIZAR LA MODIFICACION -->

<div class="modal fade modalito" id="modalFormEdit" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header ">

                <h5 class="modal-title" id="exampleModalLabel">Editar personal:
                    <?php echo $rowdasp['nombre']." " .$rowdasp['appaterno']." " .$rowdasp['apmaterno']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12">
                    <form class="form-contact contact_form" action="" method="post" accept-charset="utf-8">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    Grado:<input class="form-control placeholder hide-on-focus" name="grado" type="text"
                                        required placeholder="Ingresa el grado academico. "
                                        value="<?php echo $rowdasp['grado']; ?>" title="Ingresa el grado academico">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    Contraseña:<input id="password_field" class="form-control placeholder hide-on-focus"
                                        name="password" type="password" required placeholder="Ingresa la contraseña. "
                                        value="<?php echo $rowdasp['password']; ?>" title="Ingresa la contraseña"> Ver
                                    contraseña <input type="checkbox" onclick="mostrarContrasena()"
                                        title="Ver contraseña">
                                    <script type="text/javascript">
                                    function mostrarContrasena() {
                                        var x = document.getElementById("password_field");
                                        if (x.type === "password") {
                                            x.type = "text";
                                        } else {
                                            x.type = "password";
                                        }
                                    }
                                    </script>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    Curp/DNI:<input class="form-control placeholder hide-on-focus" name="curp_dni"
                                        type="text" required placeholder="Ingresa la curp o dni. "
                                        value="<?php echo $rowdasp['curp_dni']; ?>" title="Ingresa la curp o dni">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    Fecha de nacimiento:<input class="form-control placeholder hide-on-focus"
                                        name="fechaNac" type="date" required
                                        placeholder="Ingresa la fecha de nacimiento. "
                                        value="<?php echo $rowdasp['fechaNac']; ?>"
                                        title="Ingresa la fecha de nacimiento">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    Teléfono:<input class="form-control placeholder hide-on-focus" name="telefono"
                                        type="text" required placeholder="Ingresa el teléfono. "
                                        value="<?php echo $rowdasp['telefono']; ?>" title="Ingresa el teléfono">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    Teléfono 2:<input class="form-control placeholder hide-on-focus" name="telefono2"
                                        type="text" required placeholder="Ingresa el telefono 2. "
                                        value="<?php echo $rowdasp['telefono2']; ?>" title="Ingresa el telefono 2">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    Correo Personal:<input class="form-control placeholder hide-on-focus"
                                        value="<?php echo $rowdasp['correo_personal']; ?>" name="correo_personal"
                                        type="text" required placeholder="Ingresa el correo personal">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    Correo trabajo:<input class="form-control placeholder hide-on-focus"
                                        value="<?php echo $rowdasp['correo_trabajo']; ?>" name="correo_trabajo"
                                        type="text" required placeholder="Ingresa el correo de trabajo">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    Direccion:<input class="form-control placeholder hide-on-focus"
                                        value="<?php echo $rowdasp['direccion']; ?>" name="direccion" type="text"
                                        required placeholder="Ingresa la abreviacion">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <center>
                                <div class="form-group">
                                    <input type="hidden" name="idUsu" value="<?php echo $rowdasp['id']; ?>">
                                    <input type="submit" name="editDatosUsu" class="btn btn-primary "
                                        data-bs-toggle="modal" data-bs-target="#modalFormEdit" value="Modificar"
                                        style="background-color: #4CAF50; border: 1px solid black; color: white; padding: 16px 32px; text-decoration: none; margin: 4px 2px; border-radius: 5px; cursor: pointer;">
                                </div>
                            </center>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
    }
    ?>
<script>
$(document).ready(function() {
    $("#modalFormEdit").modal('show');
});
</script>
<?php
}
//editar MMMMMMMMMMMMMMMMMMMM
if (isset($_POST['submiteliminarUsu'])) {
    $encriptado = base64_encode($_POST['idaeliminarUsu']);
?>
<script>
var idaeliminarUsu = '<?php echo $encriptado; ?>';
</script>

<script>
swal.fire({
    title: '¿Estas seguro que deseas eliminar este personal?',
    text: "¡No podrás revertir esto!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Eliminar',
    cancelButtonText: 'Cancelar'
}).then((result) => {
    if (result.isConfirmed) {
        window.location = 'lista_personal.php?idaelimUsu=' + idaeliminarUsu;
    } else if (result.isDenied) {
        window.location = 'lista_personal.php'
    }
})
</script>

<?php

}



if (isset($_POST['llenodatos'])) { // 55555555555555 lleno los datos
    $grado          = htmlentities($_POST['grado']);
    $grado = mysqli_real_escape_string($conexion, $grado);
    $grado = utf8_encode($grado);
    $grado = html_entity_decode($grado, ENT_QUOTES | ENT_HTML401, "UTF-8");

    $nombrens          = htmlentities($_POST['nombre']);
    $nombrecod = mysqli_real_escape_string($conexion, $nombrens);
    $nombre = utf8_encode($nombrecod);
    $nombre = html_entity_decode($nombre, ENT_QUOTES | ENT_HTML401, "UTF-8");
    #echo $nombre;

    $appatns           = htmlentities($_POST['appat']);
    $appatcod = mysqli_real_escape_string($conexion, $appatns);
    $appat = utf8_decode($appatcod);
    $appat = html_entity_decode($appat, ENT_QUOTES | ENT_HTML401, "UTF-8");
    #echo $appat;

    $apmatns           = htmlentities($_POST['apmat']);
    $apmatcod = mysqli_real_escape_string($conexion, $apmatns);
    $apmats = utf8_decode($apmatcod);
    $apmat = utf8_encode($apmats);
    $apmat = html_entity_decode($apmat, ENT_QUOTES | ENT_HTML401, "UTF-8");
    #echo $apmat;

    $curp_dnins           = htmlentities($_POST['curp_dni']);
    $curp_dnicod = mysqli_real_escape_string($conexion, $curp_dnins);
    $curp_dnis = utf8_decode($curp_dnicod);
    $curp_dni = utf8_encode($curp_dnis);
    $curp_dni = html_entity_decode($curp_dni, ENT_QUOTES | ENT_HTML401, "UTF-8");
    #echo $curp_dni;

    $direccionns       = htmlentities($_POST['direccion']);
    $direccioncod = mysqli_real_escape_string($conexion, $direccionns);
    $direccion = utf8_decode($direccioncod);
    $direccion = html_entity_decode($direccion, ENT_QUOTES | ENT_HTML401, "UTF-8");
    #echo $direccion;

    $telefonons        = htmlentities($_POST['telefono']);
    $telefonoseguro = mysqli_real_escape_string($conexion, $telefonons);

    $telefono2ns        = htmlentities($_POST['telefono2']);
    $telefono2 = mysqli_real_escape_string($conexion, $telefono2ns);

    $fechanacns        = htmlentities($_POST['fechanac']);
    $fechanac = mysqli_real_escape_string($conexion, $fechanacns);

    $sexons        = htmlentities($_POST['sexo']);
    $sexo = mysqli_real_escape_string($conexion, $sexons);

    $correo_personalns          = htmlentities($_POST['correo_personal']);
    $correo_personal = mysqli_real_escape_string($conexion, $correo_personalns);

    $correo_trabajons          = htmlentities($_POST['correo_trabajo']);
    $correo_trabajo = mysqli_real_escape_string($conexion, $correo_trabajons);


    $paisns            = htmlentities($_POST['pais']);
    $pais = mysqli_real_escape_string($conexion, $paisns);

    $estadons          = htmlentities($_POST['estado']);
    $estado = mysqli_real_escape_string($conexion, $estadons);

    $rolns            = htmlentities($_POST['roles_input']);
    $rolns2 = mysqli_real_escape_string($conexion, $rolns);
    $rol = html_entity_decode($rolns2, ENT_QUOTES | ENT_HTML401, "UTF-8");


    //status mysql defecto Activo si no para que lo esta registrando

    //matricula ver para hacerla autoincrement pero tomando el ultimo registro si es estudiante

    $telefono = preg_replace('([^0-9])', '', $telefonoseguro);

    $now = date("Y-m-d H:i:s");


    //ffffffffffffffffffff
    if ($sexo == "Mujer") {
        $fotoperfil = "mujer.png";
    }
    if ($sexo == "Hombre") {
        $fotoperfil = "hombre.png";
    }
    if ($sexo == "Otro") {
        $fotoperfil = "otro.png";
    }

    $sql = "INSERT INTO usuario(
    id, /*ok 1*/
    grado, /*ok 2*/
    nombre,  /*ok 3*/
    appaterno,  /*ok 4*/
    apmaterno,  /*ok 5*/
    direccion,  /*ok 6*/
    telefono,   /*ok 7*/
    telefono2,  /*ok 8*/
    sexo,   /*ok 9*/
    correo_personal,    /*ok 10*/
    correo_trabajo,     /*ok 11*/
    curp_dni,   /*ok 12*/
    password,   /*ok 13*/
    status_usuario,     /*ok 14*/
    fregistro_usuario,  /*ok 15*/
    observacion_usuario,    /*ok 16*/
    fechaNac,   /*ok 17*/
    fotoPerfil,     /*ok 18*/
    rol,    /*ok 19*/
    id_pais,    /*ok 20*/
    id_estado)  /*ok 21*/
    VALUES (
        NULL, /*ok 1*/
        '" . $grado . "', /*ok 2*/
        '" . $nombre . "',  /*ok 3*/
        '" . $appat . "',   /*ok 4*/
        '" . $apmat . "',   /*ok 5*/
        '" . $direccion . "',   /*ok 6*/
        '" . $telefono . "',    /*ok 7*/
        '" . $telefono2 . "',    /*ok 8*/
        '" . $sexo . "',    /*ok 9*/
        '" . $correo_personal . "',    /*ok 10*/
        '" . $correo_trabajo . "',   /*ok 11*/
        '" . $curp_dni . "',    /*ok 12*/
        'pass',     /*ok 13*/
        'Activo',   /*ok 14*/
        '" . $now . "',   /*ok 15*/
        '',     /*ok 16*/
        '" . $fechanac . "',  /*ok 17*/
        '" . $fotoperfil . "',  /*ok 18*/
        '" . $rol . "',  /*ok 19*/
        '" . $pais . "',    /*ok 20*/
        '" . $estado . "'    /*ok 21*/
    )";
    $result = mysqli_query($conexion, $sql);
    $LAST_INSERT_ID_usuarioRolAdmin = mysqli_insert_id($conexion); //LAST_INSERT_ID
    $_SESSION["msgRegistroCom"] = 1;
    echo "<script>swal.fire({
        icon: 'success',
        title: '¡El nuevo personal se registró con éxito!',
        showConfirmButton: false,
        timer: 2000
        
        }).then(function () {
            window.location = 'lista_personal.php'
        })</script>";
        
} // 55555555555555 lleno los datos





include 'datatable/datateibol.php';
?>


<section class="contact-section area-padding">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <html>

                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF8">
                    </head>

                    <body>

                        <div style="text-align: right">
                            <button type="button" class="btn btn-success " data-bs-toggle="modal"
                                data-bs-target="#modalForm">
                                Registrar personal
                            </button>
                        </div>


                        <div class="blog_details"
                            style="padding: 10px 30px 10px 35px;box-shadow: 0px 10px 20px 0px #0000003b;font-size: 18px">
                            <center>
                                <h1>Listado de personal</h1>
                            </center>
                        </div>
                    </body>

                    </html>
                </div>
            </div>
        </div>
    </div>
</section>

<table id="example" class="table table-striped table-hover" style="width:100%">
    <thead style="font-size:12px;">
        <tr
            style="background:<?php echo $Color_Encabezado_tablas; ?>; color: <?php echo $Color_Encabezado_tablas_letra; ?>; font-size: 15px;">
            <th>Grado</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Correo personal</th>
            <th>Correo trabajo</th>
            <th>Foto</th>
            <th>Rol</th>
            <th>Eliminar</th>
        </tr>
    </thead>
    <tbody>
        <?php


        $sqldasp = "SELECT
        usuario.id AS idUsu,
        usuario.grado,
        usuario.nombre,
        usuario.appaterno,
        usuario.apmaterno,
        usuario.telefono,
        usuario.telefono2,
        usuario.correo_personal,
        usuario.correo_trabajo,
        usuario.fotoPerfil,
        usuario.rol
    FROM
        usuario
    WHERE usuario.status_usuario = 'Activo'";

        $resultdasp = mysqli_query($conexion, $sqldasp);

        if ($resultdasp) {
            while ($rowdasp = mysqli_fetch_assoc($resultdasp)) {
                if (isset($_POST['rol']) && $rowdasp['rol'] == "Administrador") {
                    echo '<tr>
                    <td>' .$rowdasp['grado'].'</td>
            <td>' .$rowdasp['nombre'].' ' .$rowdasp['appaterno'].' ' .$rowdasp['apmaterno'].'</td>
            <td>' .$rowdasp['telefono'].' - ' .$rowdasp['telefono2'].'</td>
            <td>' .$rowdasp['correo_personal'].'</td>
            <td>' .$rowdasp['correo_trabajo'].'</td>
            <td>' .$rowdasp['rol'].'</td>
            <td><input type="submit" value="N/A" style="border-radius: 5px; background: #a91111; color: #fff; padding: 8px 10px;margin: 5px;
            font-weight: bold; border: #a91111; cursor: pointer;">
            </td>
          </tr>';
                } else {
                    echo '<tr>
                    <td>' .$rowdasp['grado'].'</td>
              <td>' .$rowdasp['nombre'].' ' .$rowdasp['appaterno'].' ' .$rowdasp['apmaterno'].'</td>
              <td>' .$rowdasp['telefono'].' - ' .$rowdasp['telefono2'].'</td>
              <td>' .$rowdasp['correo_personal'].'</td>
              <td>' .$rowdasp['correo_trabajo'].'</td>
                <td>
                    <a href="perfil/' .$rowdasp['fotoPerfil'].'" target="_blank">
                        <img src="perfil/' .$rowdasp['fotoPerfil'].'" alt="Foto de perfil" style="width: 50px; height: 50px;">
                    </a>
                </td>
              <td>' .$rowdasp['rol'].'</td>
              <td>
                <form  method="POST" style="margin-block-end: 0;">
                    <input type="hidden"  name="idEditUsu" value="' .$rowdasp["idUsu"].'">
                    <input type="submit" name="editarUsu"  class="btn btn-primary " data-bs-toggle="modal" data-bs-target="#modalFormEdit" value="Modificar" >
                </form>
                <form  method="POST" style="margin-block-end: 0;">
                  <input type="hidden" name="idbUsu" value="'.base64_encode($rowdasp["idUsu"]).'">
                  <input type="submit" name="bajaUsu" value="Baja" style="border-radius: 5px; background: #a91111; color: #fff; padding: 8px 10px; font-weight: bold; border: #a91111; cursor: pointer;">
                </form>
              </td>
            </tr>';
                }
            }
        }
        ?>

    </tbody>
    <tfoot>
        <tr
            style="background:<?php echo $Color_Encabezado_tablas; ?>; color: <?php echo $Color_Encabezado_tablas_letra; ?>; font-size: 15px;">
            <th>Grado</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Correo personal</th>
            <th>Correo trabajo</th>
            <th>Foto</th>
            <th>Rol</th>
            <th>Eliminar</th>
        </tr>
    </tfoot>
</table>
</div>

<!-- MODAL PARA REALIZAR EL REGISTRO PERSONAL -->
<div class="modal fade modalito" id="modalForm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Registro de personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12">
                    <form class="form-contact contact_form"
                        action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                        accept-charset="utf-8">
                        <div class="row">
                            <!-- >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> -->
                            <div class="col-sm-4">
                                <div class="form-group">
                                    Grado:<input class="form-control  hide-on-focus" name="grado" type="text"
                                        placeholder="* Ingresa el grado academico; Dr,Dra,Mtr,Mtra. "
                                        title="* Ingresa el grado academico; Dr,Dra,Mtr,Mtra. " required="required">

                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    Nombre:<input class="form-control  hide-on-focus" name="nombre" type="text"
                                        placeholder="* Ingresa el Nombre " title="* Ingresa el Nombre "
                                        required="required">

                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    Apellido paterno:<input class="form-control  hide-on-focus" name="appat" type="text"
                                        placeholder="Ingresa el Apellido paterno " title="Ingresa el Apellido paterno "
                                        required="required">

                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    Apellido materno:<input class="form-control  hide-on-focus" name="apmat" type="text"
                                        placeholder="Ingresa el Apellido materno " title="Ingresa el Apellido materno "
                                        required="required">

                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    Fecha Nacimiento:<input class="form-control  hide-on-focus" name="fechanac"
                                        type="date" placeholder="Ingresa el Apellido materno "
                                        title="Ingresa el Apellido materno " required="required">

                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    Teléfono:<input class="form-control  hide-on-focus" name="telefono" type="tel"
                                        placeholder="Ingresa el Teléfono " title="Ingresa el Teléfono "
                                        required="required">

                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    Teléfono 2:<input class="form-control  hide-on-focus" name="telefono2" type="tel"
                                        placeholder="Ingresa el Teléfono " title="Ingresa el Teléfono "
                                        required="required">

                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    Correo personal:<input class="form-control  hide-on-focus" name="correo_personal"
                                        type="email" placeholder="* Ingresa el Correo" title="* Ingresa el Correo "
                                        required="required">

                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    Correo de trabajo:<input class="form-control  hide-on-focus" name="correo_trabajo"
                                        type="email" placeholder="* Ingresa el Correo"
                                        title="* Ingresa el correo de trabajo" required="required">

                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    Curp:<input class="form-control  hide-on-focus" name="curp_dni" type="text"
                                        placeholder="* Ingresa la curp o dni" title="*  Si no tiene CURP ponga DNI"
                                        required="required">

                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    DNI:<input class="form-control  hide-on-focus" name="dni" type="text"
                                        placeholder="* Ingresa el DNI" title="* Si no tiene DNI ponga SIN DNI">

                                </div>
                            </div>
                            <!-- >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> -->
                            <?php
                            $query  = $conexion->query("select * from pais order by nombre asc");
                            $paises = array();
                            while ($r = $query->fetch_object()) {
                                $paises[] = $r;
                            }
                            ?>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    País:<select id="pais_id" style="height: 40px;" class="form-control" name="pais"
                                        required="required">
                                        <option value="1">-- País --</option>
                                        <?php foreach ($paises as $c) : ?>
                                        <option value="<?php echo $c->id; ?>"><?php echo $c->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    Estado:<select id="estado_id" style="height: 40px;" class="form-control"
                                        name="estado" required="required">
                                        <option value="1">-- Estado --</option>
                                    </select>
                                </div>
                            </div>
                            <!-- <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->
                            <div class="col-4">
                                <div class="form-group">
                                    Dirección:<input class="form-control  hide-on-focus" name="direccion" type="text"
                                        placeholder="Ingresa el Domicilio " title="Ingresa el Domicilio "
                                        required="required">

                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    Sexo:<select name="sexo" style="height: 40px;" class="form-control" required>
                                        <option value="Hombre">Hombre</option>
                                        <option value="Mujer">Mujer</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <?php
                            $query_roles= "select * from usuario group by rol order by rol asc" ;
                            $resultquery_roles = mysqli_query($conexion, $query_roles);
                            if (!$resultquery_roles) {
                                die('Consulta fallida: ' . mysqli_error($conexion));
                            }
                            echo '<div class="col-sm-4">
                                <div class="form-group">
                                    Rol de usuario:<select id="roles_input" style="height: 40px;" class="form-control" name="roles_input" required="required">
                                        <option value="">-- Elige un rol de usuario --</option>';
                            while ($rowquery_roles = mysqli_fetch_assoc($resultquery_roles)) {
                                if( $rowquery_roles['rol'] != "" || $rowquery_roles['rol'] != null){
                                    echo '<option value="' . $rowquery_roles['rol'] . '">' . $rowquery_roles['rol'] . '</option>';
                                }
                            }
                            echo '</select>
                                </div>
                            </div>';
                            ?>
                        </div>
                </div>
                <div class="form-group mt-3" align="center">
                    <button type="submit" name="llenodatos"
                        style="background-color: #4CAF50; border: 1px solid black; color: white; padding: 16px 32px; text-decoration: none; margin: 4px 2px; border-radius: 5px; cursor: pointer;">Registrar</button>

                </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>


<script type="text/javascript">
$(document).ready(function() {
    $("#pais_id").change(function() {
        $.get("get_estados.php", "pais_id=" + $("#pais_id").val(), function(data) {
            $("#estado_id").html(data);
            console.log(data);
        });
    });
});
</script>
<!-- / listado <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->
<?php
require_once "footer.php";
?>