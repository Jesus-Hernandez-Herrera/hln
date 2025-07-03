<?php
require_once "headeradm.php";
require_once 'bdd.php';
date_default_timezone_set("America/Mexico_City");
setlocale(LC_TIME, 'spanish');

if (isset($_SESSION["msgeditado"]) && $_SESSION["msgeditado"] == "1") {
    $_SESSION["msgeditado"] = 0;
    ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Los datos personales se modificaron con éxito!',
            showConfirmButton: false,
            timer: 2000
        })
    </script>
    <?php
}
if (isset($_SESSION["msgFotoSubida"]) && $_SESSION["msgFotoSubida"] == "1") {
    $_SESSION["msgFotoSubida"] = 0;
    ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡La foto de perfil se cargo con éxito!',
            showConfirmButton: false,
            timer: 2000
        })
    </script>
    <?php
}



if (isset($_POST['editDatosUSU'])) {
    $idEditUSU = $_POST['idEditUSU'];
    $direccon = $_POST['direccon'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $pass = $_POST['pass'];



    $sqlupper = "UPDATE  usuario  SET direccion ='" . $direccon . "', telefono ='" . $telefono . "'
    , correo_personal ='" . $correo . "', password ='" . $pass . "'
                WHERE id= " . $idEditUSU;


    $resultupper = mysqli_query($conexion, $sqlupper);

    $_SESSION["msgeditado"] = 1;
    echo "<script>window.location = 'datosPersonales.php'</script> ";
}
// editar vvvvvvvvvvvvvvvvvvvvvv


if (isset($_POST['EditDatos'])) {
    $idEditDatos = $_POST['idEditDatos'];
    $qry = 'SELECT * from usuario
                 where usuario.id=' . $idEditDatos;

    $resultdasp = mysqli_query($conexion, $qry);
    while ($rowdasp = mysqli_fetch_assoc($resultdasp)) {

        ?>




        <!-- MODAL PARA REALIZAR LA MODIFICACIÓN -->

        <div class="modal fade modalito" id="modalFormEditDatos" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header ">
                        <h5 class="modal-title" id="exampleModalLabel">Editar datos personales:
                            <?php echo $rowdasp['nombre']; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12">
                            <form class="form-contact " action="" method="post" accept-charset="utf-8">
                                <div class="row">
                                    <!--<div class="col-sm-6">
                                        <div class="form-group">
                                            Curp:<input class="form-control placeholder hide-on-focus" name="curp" type="text" required placeholder="Ingresa tu nueva direccion. " value="<?php echo $rowdasp['curp_dni']; ?>" title="Ingresa las mensualidades">
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            Fecha de nacimiento:<input class="form-control placeholder hide-on-focus" name="fechaNac" type="date" required placeholder="Ingresa tu nueva direccion. " value="<?php echo $rowdasp['fechaNac']; ?>" title="Ingresa las mensualidades">
                                        </div>
                                    </div>-->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            Direccion:<input class="form-control placeholder hide-on-focus" name="direccon"
                                                type="text" required placeholder="Ingresa tu nueva direccion. "
                                                value="<?php echo $rowdasp['direccion']; ?>" title="Ingresa las mensualidades">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            Telefono:<input class="form-control placeholder hide-on-focus"
                                                value="<?php echo $rowdasp['telefono']; ?>" name="telefono" type="text" required
                                                placeholder="Ingresa la abreviación">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            Correo:<input class="form-control placeholder hide-on-focus"
                                                value="<?php echo $rowdasp['correo_personal']; ?>" name="correo" type="text"
                                                required placeholder="Ingresa la abreviación">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            Contraseña:<input class="form-control placeholder hide-on-focus"
                                                value="<?php echo $rowdasp['password']; ?>" name="pass" type="password"
                                                pattern="(?=.*[A-Za-z])(?=.*[0-9])[A-Za-z0-9]+"
                                                title="Al menos una Mayúscula una minúscula y un numero"
                                                placeholder="Ingresa la abreviación">
                                        </div>
                                    </div>


                                </div>


                                <form method="POST">
                                    <br>
                                    <div class="form-group mt-3" align="center">
                                        <input type="hidden" name="idEditUSU" value="<?php echo $rowdasp['id']; ?>">
                                        <input type="submit" name="editDatosUSU" class="btn btn-primary " value="Modificar"
                                            style="background-color: #4CAF50; border: 1px solid black; color: white; padding: 16px 32px; text-decoration: none; margin: 4px 2px; border-radius: 5px; cursor: pointer;">
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
        $(document).ready(function () {
            $("#modalFormEditDatos").modal('show');
        });
    </script>
    <?php
}
if (isset($_POST['editSubirFoto'])) {

    $idSubirFoto = $_POST['idSubirFoto'];


    $dir_subida = 'perfil/';
    $nombrearchivo = basename($_FILES['foto']['name']);
    $ext = pathinfo($nombrearchivo, PATHINFO_EXTENSION);
    $nombrearchivo = "ID-" . $idSubirFoto . '.' . $ext;
    $fichero_subido = $dir_subida . $nombrearchivo;
    $queryDele = "SELECT fotoPerfil as foPe from usuario where id =" . $idSubirFoto;
    $resultDele = mysqli_query($conexion, $queryDele);
    while ($rowDelete = mysqli_fetch_assoc($resultDele)) {
        $fotoPerfil = $rowDelete['foPe'];
    }

    if ($fotoPerfil == $nombrearchivo) {
        unlink($fichero_subido);
        move_uploaded_file($_FILES['foto']['tmp_name'], $fichero_subido);

        $qryUpdate = "UPDATE usuario SET fotoPerfil='" . $nombrearchivo . "' WHERE id =" . $idSubirFoto;

        $result = mysqli_query($conexion, $qryUpdate);

        $_SESSION["msgFotoSubida"] = 1;
        echo "<script>window.location = 'datosPersonales.php'</script> ";
    } else {
        move_uploaded_file($_FILES['foto']['tmp_name'], $fichero_subido);

        $qryUpdate = "UPDATE usuario SET fotoPerfil='" . $nombrearchivo . "' WHERE id =" . $idSubirFoto;

        $result = mysqli_query($conexion, $qryUpdate);

        $_SESSION["msgFotoSubida"] = 1;
        echo "<script>window.location = 'datosPersonales.php'</script> ";
    }
}

if (isset($_POST['enviarFoto'])) {

    $idFoto = $_POST['idFoto'];

    $qry = 'SELECT * from usuario
                 where usuario.id=' . $idFoto;

    $resultdasp = mysqli_query($conexion, $qry);
    while ($rowdasp = mysqli_fetch_assoc($resultdasp)) {

        ?>
        <!-- MODAL PARA REALIZAR LA SUBIDA DE LA FOTO-->

        <div class="modal fade modalito" id="modalFormSubirFoto" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-m">
                <div class="modal-content">
                    <div class="modal-header ">
                        <h5 class="modal-title" id="exampleModalLabel">Actualizar foto de perfil:
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12">
                            <form method="post" accept-charset="utf-8" enctype="multipart/form-data">
                                <div class="row">

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            Selecciona tu foto:
                                            <br><input class="form-control placeholder hide-on-focus" name="foto"
                                                accept="image/png,image/jpeg,image/gif" type="file" required
                                                placeholder="Ingresa la abreviacion" id="fotoUsu" onchange="validarArchivo()">
                                        </div>
                                    </div>

                                </div>


                                <br>
                                <div class="form-group mt-3" align="center">
                                    <input type="hidden" name="idSubirFoto" value="<?php echo $idFoto ?>">
                                    <input type="submit" name="editSubirFoto" class="btn btn-primary " value="Subir foto"
                                        style="background-color: #4CAF50; border: 1px solid black; color: white; padding: 16px 32px; text-decoration: none; margin: 4px 2px; border-radius: 5px; cursor: pointer;">
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
        $(document).ready(function () {
            $("#modalFormSubirFoto").modal('show');
        });

        function validarArchivo() {
            var archivoInput = document.getElementById('fotoUsu');

            var maxFileSize = 2 * 1024 * 1024; // 2 MB en bytes

            if (archivoInput.files.length > 0) {
                var tamañoArchivo = archivoInput.files[0].size;
                if (tamañoArchivo > maxFileSize) {
                    alert("El archivo seleccionado es demasiado grande. El tamaño máximo permitido es 2 MB.");
                    archivoInput.value = ''; // Limpiar la selección del archivo
                }
            }
        }
    </script>
    <?php
}





include 'datatable/datateibol.php';

?>



<section class="contact-section area-padding">
    <div class="container">

    </div>
</section>




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


                        <div class="blog_details" style="border-radius: 30px; padding: 10px 30px 10px 35px;box-shadow: 0px 10px 20px 0px #0000003b;font-size: 18px;background-color: #77a6d9;
    color:white;">
                            <center>
                                <h1><b>PERFIL</b></h1>
                            </center>





                            <?php

                            $sqldasp = "SELECT *,date_format(fechaNac,'%d-%m-%Y') as fechaNac,date_format(fregistro_usuario,'%d-%m-%Y') as fregistro,
                     pais.nombre as nomPais, estado.nombre as nomEstado, usuario.id as idUsuMod, usuario.nombre as nomUsu from usuario
                    join pais on pais.id = usuario.id_pais
                    join estado on estado.id = usuario.id_estado
                    where usuario.id=" . $_SESSION["idusuario"];





                            $resultdasp = mysqli_query($conexion, $sqldasp);
                            if ($resultdasp) {
                                while ($rowdasp = mysqli_fetch_assoc($resultdasp)) {
                                    $idUsuMod = $rowdasp['idUsuMod'];
                                    $nomUsu = $rowdasp['nomUsu'];
                                    $appaterno = $rowdasp['appaterno'];
                                    $apmaterno = $rowdasp['apmaterno'];
                                    $nomCom = $nomUsu . ' ' . $appaterno . ' ' . $apmaterno;
                                    $sexo = $rowdasp['sexo'];
                                    $fechaNac = $rowdasp['fechaNac'];
                                    $curp = $rowdasp['curp_dni'];
                                    $direccion = $rowdasp['direccion'];
                                    $nomPais = $rowdasp['nomPais'];
                                    $nomEstado = $rowdasp['nomEstado'];
                                    $telefono = $rowdasp['telefono'];
                                    $correo = $rowdasp['correo_personal'];
                                    $correoinst = $rowdasp['correo_trabajo'];
                                    $fregistro = $rowdasp['fregistro_usuario'];
                                    $password = $rowdasp['password'];
                                    $status = $rowdasp['status_usuario'];
                                }
                            }
                            ?>

                            <div class="datosPer">

                                <div class="encabezado">

                                    <table class="table encabezado" style="width: 100%;">
                                        <tr class="table">
                                            <td style="background-color: #77a6d9; color: white; width: 33%;"><img
                                                    style="max-width: 100%;" src="imagenes/<?php echo $logot; ?>"
                                                    alt="">
                                            </td>
                                            <td style="background-color: #adadad; color: white; width: 33%;"><b>Datos
                                                    personales</b></td>
                                            <td style="background-color: #77a6d9; color: white; width: 33%;"><img
                                                    style="max-width: 100%;" src="imagenes/<?php echo $logot; ?>"
                                                    alt="">
                                            </td>
                                        </tr>
                                    </table>



                                </div>
                                <?php
                                $qryShow = "SELECT usuario.id as idUsuFoto, usuario.rol as nombre_rol, usuario.fotoPerfil as fotoPerUsu
                                 from usuario
                                where usuario.id=" . $_SESSION["idusuario"];
                                // echo $qryShow;
                                $result = mysqli_query($conexion, $qryShow);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $idUsuFoto = $row['idUsuFoto'];
                                    $fotoPerUsu = $row['fotoPerUsu'];
                                    $nomrol = $row['nombre_rol'];
                                }
                                if ($fotoPerUsu == '') {
                                    ?>
                                    <img class="imageFodo" src="imagenes/logocolorok.png" alt="" />
                                    <?php
                                }
                                if ($fotoPerUsu != '') {
                                    ?>

                                    <img class="image" src="<?php echo 'perfil/' . $fotoPerUsu; ?>" alt="" />
                                    <?php
                                }

                                ?>
                                <form method="POST">
                                    <input type="hidden" name="idFoto" value="<?php echo $idUsuMod; ?>">
                                    <input class="replace" type="submit" name="enviarFoto" value="">
                                </form>




                                <table class="table table-bordered">
                                    <tr class="table">
                                        <th style="background-color: #00aab3; color: white;">Nombre:</th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $nomCom; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #00aab3; color: white;">Sexo:</th>
                                        <td style=" background-color: #77a6d9; color: white; "><?php echo $sexo; ?></td>

                                    </tr>
                                    <tr>
                                        <th style=" background-color: #00aab3; color: white;">Fecha de nacimiento: </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $fechaNac; ?>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th style="background-color: #00aab3; color: white;">Curp: </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $curp; ?></td>

                                    </tr>
                                    <tr>
                                        <th style="background-color: #00aab3; color: white;">Direccion: </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $direccion; ?>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th style="background-color: #00aab3; color: white;">Estado: </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $nomEstado; ?>
                                        </td>

                                    </tr>


                                    <tr>
                                        <th style="background-color: #00aab3; color: white;">Pais: </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $nomPais; ?>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th style="background-color: #00aab3; color: white;" " >Telefono: </th>
                                                    <td  style=" background-color: #77a6d9; color: white; " ><?php echo $telefono; ?></td>

                                                    </tr>
                                                    <tr>
                                                    <th style=" background-color: #00aab3; color: white;">Correo: </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $correo; ?>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th style=" background-color: #00aab3; color: white;">Correo Institucional:
                                        </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $correoinst; ?>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th style=" background-color: #00aab3; color: white;">Cargo: </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $nomrol; ?>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th style=" background-color: #00aab3; color: white;">Fecha de registro: </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $fregistro; ?>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th style=" background-color: #00aab3; color: white;">Estatus: </th>
                                        <td style="background-color: #77a6d9; color: white; "><?php echo $status; ?>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th style=" background-color: #00aab3; color: white;">Acciones: </th>
                                        <td style="background-color: #77a6d9; color: white; ">

                                            <form method="POST">
                                                <input type="hidden" name="idEditDatos"
                                                    value="<?php echo $idUsuMod; ?>">
                                                <input
                                                    style="background-color:#00aab3; color: white; padding: 10px; border-radius: 5px;"
                                                    type="submit" name="EditDatos" value="Modificar datos personales">
                                            </form>
                                        </td>

                                    </tr>

                                    </tbody>
                                </table>


                            </div>



                        </div><br>


                    </body>

                    </html>
                </div>
            </div>
        </div>
    </div>

</section>


</div>






<style>
    body {
        background-color: white;
    }

    .container2 {
        margin: 0 auto;
        display: table;
        width: 100%;
        height: 100%;
    }

    .image-upload>input {
        display: none;
    }

    .image-upload img {

        cursor: pointer;
    }

    .left {
        float: left;
        width: 100%;
        height: 100%;

    }

    .right {
        float: none;
        display: table;
        width: 60%;
    }

    .fa {
        display: inline-block;
    }

    .image {
        background-color: white;

        margin: 0 auto;
        width: auto;
        height: 200px;
        border-radius: 50%;
        border: 1px solid gray;
        display: inline-block;
        padding: 3px;
        border: 3px solid #77a6d9;



    }

    .imageFodo {
        background-color: white;
        margin: 0 auto;
        width: auto;
        height: 200px;
        border-radius: 50%;
        border: 1px solid gray;
        display: inline-block;
        padding: 3px;
        border: 3px solid #77a6d9;



    }

    .replace {
        position: absolute;
        margin-top: -80px;
        margin-left: 50px;

        background-image: url("imagenes/replace.png");
        background-size: 50px 50px;
        background-repeat: no-repeat;
        background-position-x: center;
        background-position-y: center;
        width: 60px;
        height: 60px;
        border-radius: 20%;
        display: inline-flex;
        padding: 3px;
        border: 3px solid #77a6d9;
    }

    .has-feedback .form-control {
        padding-right: 42.5px;
    }

    select[multiple],
    select[size] {
        height: auto;
    }

    .form-control {
        border-radius: 0;
        box-shadow: none;
        border-color: #00aab3;
    }

    .form-control {
        display: block;
        width: 100%;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ccc;
    }

    label {
        cursor: default;
    }

    label {
        display: inline-block;
        max-width: 100%;
        margin-bottom: 5px;
        font-weight: 00;
    }

    .usuername {
        font-size: 21px;

    }

    .btn {
        margin: 0 auto;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        cursor: pointer;
        touch-action: manipulation;
        margin-top: 100px;
    }

    a {
        color: #3c8dbc;
    }

    a {
        color: #337ab7;
        text-decoration: none;
    }

    .btn-primary {
        color: #fff;
        background-color: #00aab3;
        display: block;
    }

    .datosPer {
        color: #77a6d9;
        background-color: #adadad;
        text-align: center;
    }


    .datosDesc {
        color: white;



    }

    h3 {
        font-family: ' Source Sans Pro', sans-serif;
    }

    .control-label {
        float: left;
    }

    /* .form-horizontal .form-group
                                                                    { margin-right: -15px; margin-left: -15px; } */
    .form-group {
        margin-bottom: 15px;
    }

    .btn-submit {
        background-color: #dd4b39;
        border-color: red;
    }

    .btn {
        border-radius: 3px;
        box-shadow: none;
        border: 1px solid transparent;
    }

    .has-feedback {
        position: relative;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .left.box-primary {
        border-top-color: #00aab3;
    }

    /* .left { position: relative;
                                                                    border-radius: 3px; background: #ffffff; border-top: 3px solid
                                                                    #d2d6de; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,0.1);
                                                                    border-top-left-radius: 0; border-top-right-radius: 0;
                                                                    border-bottom-right-radius: 3px; border-bottom-left-radius: 3px;
                                                                    padding: 10px; } */
    /* button, meter, progress {
                                                                    -webkit-writing-mode: horizontal-tb; } */
    .encabezado {
        background-color: #77a6d9;
        /* position: relative;*/
        text-align:
            center;
        padding: 0px 0px 0px 0px;
        box-shadow: 0px 10px 20px 0px #0000003b;
        font-size: 28px;
        color: #00aab3;
    }
</style>


<script>
    //funcionpassword
    function mostrarPassword() {
        var cambio = document.getElementById("txtPassword");
        if (cambio.type == "password") {
            cambio.type = "text";
            $('.icon').removeClass('fa fa-eye-slash').addClass('fa fa-eye');
        } else {
            cambio.type = "password";
            $('.icon').removeClass('fa fa-eye').addClass('fa fa-eye-slash');
        }
    }

    $(document).ready(function () {
        //CheckBox mostrar contraseña
        $('#ShowPassword').click(function () {
            $('#Password').attr('type', $(this).is(':checked') ? 'text' : 'password');
        });
    });
    //funcionpassword
</script>
<?php
require_once "footer.php";
?>