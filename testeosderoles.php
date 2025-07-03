<?php
require_once "headeradm.php";
require_once 'bdd.php';
date_default_timezone_set("America/Mexico_City");
setlocale(LC_TIME, 'spanish');
include 'datatable/datateibol.php';


if (isset($_POST['Cambiaroles'])) {
    // Obtener el ID del usuario seleccionado desde el formulario
    $idusuario = $_POST['idusuario_sel'] ?? '';

    // Consulta SQL para obtener datos del usuario
    $sqlobtendatoslogin = "SELECT
    usuario.id,
    nombre,
    appaterno,
    apmaterno,
    correo_personal,
    fotoPerfil,
    rol rolusu,
    status_usuario,
    correo_trabajo
    FROM
        usuario
    WHERE usuario.id = $idusuario  and status_usuario = 'Activo'";

echo $sqlobtendatoslogin;
    // Ejecutar la consulta y manejar errores
    $validarexiste = mysqli_query($conexion, $sqlobtendatoslogin);

    if (!$validarexiste) {
        // die("Error en la consulta: " . mysqli_error($conexion));
        echo '<script>
       Swal.fire({
       icon: "error",
       title: "El rol no tiene un usuario registrado!",
       confirmButtonText: "Cerrar"
       })
   </script>';
    }

    // Verificar si se encontraron registros
    if (mysqli_num_rows($validarexiste) != 0) {
        $fila = mysqli_fetch_assoc($validarexiste);
        // Almacenar datos en variables de sesión
        $_SESSION["idusuario"] = $fila["id"];
        $_SESSION["nombreusuariocorto"] = $fila["nombre"];
        $_SESSION["nombrelargo"] = $fila["nombre"] . " " . $fila["appaterno"] . " " . $fila["apmaterno"];
        $_SESSION["correo_personal"] = $fila["correo_personal"];
        $_SESSION["correo_trabajo"] = $fila["correo_trabajo"];

        $_SESSION["rolusu"] = $fila["rolusu"];
        $_SESSION["fotoPerfil"] = $fila["fotoPerfil"];
        echo '<script>
        setTimeout(function() {
            /*window.location.href = "index.php";*/
        }, 1000); // Redirigir después de 2 segundos (ajusta el tiempo según tus necesidades)
    </script>';
    }
}

?>

<br>
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
                        <div class="blog_details"
                            style="padding: 10px 30px 10px 35px;box-shadow: 0px 10px 20px 0px #0000003b;font-size: 18px; background: #607C5A; border-radius: 10px;">
                            <div class="encabezadoContenedor">
                                <center>
                                    <h1 style="color: wheat;">Selecciona el rol nuevo para pruebas</h1>
                                    <!-- <p style="color: wheat;"> Recuerda abrir otra pestaña para que tu sesión de administrador no se vea afectada</p> -->
                                    <p style="color: wheat;"> Esta herramienta te permite seleccionar un rol y un
                                        usuario, así como filtrar o buscar dentro de un rol con múltiples usuarios para
                                        interactuar con su módulo de interfaces. Puedes realizar pruebas y verificar que
                                        todo funcione correctamente.
                                    </p>
                                </center>
                            </div>

                            <div class="testeoRoles">
                                <form method="POST">
                                    <select name="idusuario_sel" id="idusuario_sel" onchange="cargarOpciones()"
                                        class="inpuFechaPlan">
                                        <?php
                                        $sqlroles = "SELECT usuario.rol,usuario.id idusuario,usuario.nombre,usuario.appaterno,usuario.apmaterno FROM usuario 
                                        GROUP BY id ORDER BY rol ASC";
                                        $resultsqlroles = mysqli_query($conexion, $sqlroles);
                                        if ($resultsqlroles) {
                                            while ($rowroles = mysqli_fetch_assoc($resultsqlroles)) {
                                                echo '<option value="' . $rowroles['idusuario'] . '">' . $rowroles['rol'] . ' <- ' . $rowroles['nombre'] . ' ' . $rowroles['appaterno'] . ' ' . $rowroles['apmaterno'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <input type="submit" name="Cambiaroles" value="Ingresar con el usuario seleccionado"
                                        class="btn btn-success" style="background-color: #ff7610; color: #ffffff;">
                                </form>
                            </div>
                        </div>
                    </body>

                    </html>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
require_once "footer.php";
?>
<style>
.testeoRoles {
    text-align: center;
    margin-top: 5%;
}

.inpuFechaPlan {
    width: 495px;
    height: 45px;
    border: 2px solid transparent;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    background-color: #f8fafc;
    color: #0d0c22;
    transition: .5s ease;
}

.inpuFechaPlan::placeholder {
    color: #196F3D;
}

.inpuFechaPlan:focus,
inpuFechaPlan:hover {
    outline: none;
    border-color: rgba(25, 111, 61);
    background-color: #fff;
    box-shadow: 0 0 0 5px rgb(35 155 86 / 30%);
}

.encabezadoContenedor {
    background: #82947F;
    border-radius: 6px;
}
</style>

<script>


</script>