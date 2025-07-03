<?php
require_once "headeradm.php";
require_once 'bdd.php';
date_default_timezone_set("America/Mexico_City");
setlocale(LC_TIME, 'spanish');
include 'datatable/datateibol.php';
$fecha = new DateTime(); // Date object using current date and time
$fechaReg = $fecha->format('Y-m-d\TH:i:s');
if (isset($_POST['enviarRolUsuCom'])) {
    $_SESSION['idRolUsuCom'] = $_POST['idRolUsuCom'];
    echo "<script>window.location = 'verRoles.php'</script> ";
}
if (isset($_POST['enviarRolUsuComExt'])) {
    $_SESSION['idRolUsuComExt'] = $_POST['idRolUsuComExt'];
    echo "<script>window.location = 'listarRolesxUsuario.php'</script> ";
}

if (isset($_POST["regisRolUsu"])) {
    $ruta_url = $_POST["ruta_url"];
    $rol_usu_paruta = $_POST["rol_usu_paruta"];
    $status = $_POST["status"];

    $sql = "INSERT INTO rol_permisos (rol, ruta, status) VALUES ('$rol_usu_paruta', '$ruta_url', '$status')";

    if ($conexion->query($sql) === true) {
        echo "<script>swal('El permiso se registro con exito.');</script> ";
    } else {
        echo "<script>swal('Error al registrar el permiso.');</script> ";
        
    }
}








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

                        <div style="text-align: right">


                            <button type="button" class="btn btn-success " data-bs-toggle="modal"
                                data-bs-target="#modalFormRegRolUsu">
                                Asignar rol a usuario
                            </button>


                        </div>

                        <div class="blog_details"
                            style="padding: 10px 30px 10px 35px;box-shadow: 0px 10px 20px 0px #0000003b;font-size: 18px">
                            <center>
                                <h1> Listado de roles de usuario
                                </h1>
                            </center>
                        </div><br>
                    </body>

                    </html>
                </div>
            </div>
        </div>
    </div>
</section>

<table id="example" class="cell-border" style="width:100%">
    <thead>
        <tr
            style="background:<?php echo $Color_Encabezado_tablas; ?>; color: <?php echo $Color_Encabezado_tablas_letra; ?>; font-size: 15px; text-align: center;">
            <th style="padding: 0px;text-align: center;">Rol</th>
            <th style="padding: 0px;text-align: center;">Número de rutas</th>
            <th style="padding: 0px;text-align: center;">Número de usuarios</th>
            <th style="padding: 0px;text-align: center;">Acciones</th>



        </tr>
    </thead>
    <tbody>
        <?php 
        $sqldasp = "SELECT
    usuario.rol,
    COUNT(rol_permisos.rol) as contadorrutas,
    /* conteo de usuarios con ese rol */ 
    COUNT(DISTINCT usuario.id) as contadorUsuarios
FROM
    usuario
LEFT JOIN rol_permisos ON usuario.rol = rol_permisos.rol
GROUP BY
    rol_permisos.rol, usuario.rol";

        $resultdasp = mysqli_query($conexion, $sqldasp);
        if ($resultdasp) {
            while ($rowdasp = mysqli_fetch_assoc($resultdasp)) {
                echo '<tr>
                        <td  style="padding: 0px;text-align: center;">' . $rowdasp['rol'] . '</td>
                        <td  style="padding: 0px;text-align: center;">' . $rowdasp['contadorrutas'] . '</td>
                        <td  style="padding: 0px;text-align: center;">' . $rowdasp['contadorUsuarios'] . '</td> 
                        <td  style="padding: 0px;text-align: center;">
                            <form method="POST" action="rolesrutas.php" style="margin-block-end: 0;">
                                <input type="submit" style="border-radius: 5px; background: blue; color: #fff; padding: 8px 10px;margin: 5px; font-weight: bold; border: #a91111; cursor: pointer;"
                                name="enviarRolUsuCom" value="Ver rutas " >
                                <input type="hidden" name="roldeusuario" value="' . $rowdasp['rol'] . '">
                            </form>
                        </td>
                ';
            }
        }
        ?>

    </tbody>
    <tfoot>
        <tr
            style="background:<?php echo $Color_Encabezado_tablas; ?>; color: <?php echo $Color_Encabezado_tablas_letra; ?>; font-size: 15px; text-align: center;">
            <th style="padding: 0px;text-align: center;">Rol</th>
            <th style="padding: 0px;text-align: center;">Número de rutas</th>
            <th style="padding: 0px;text-align: center;">Número de usuarios</th>
            <th style="padding: 0px;text-align: center;">Acciones</th>

        </tr>
    </tfoot>
</table>
</div>






<!-- MODAL PARA EL REGISTRO DE ROLES AL USUARIO -->
<div class="modal fade modalito" id="modalFormRegRolUsu" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-m">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Asignar ruta al rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <!--input text-->
                                    <label for="cve_rol">Ruta:</label>
                                    <input type="text" class="form-control placeholder hide-on-focus" name="ruta_url"
                                        id="ruta_url" required="required">

                                </div>
                            </div>



                            <div class="col-sm-6">
                                <div class="form-group">
                                    Usuario:<select id="rol_usu_paruta" style="height: 40px;" class="form-control"
                                        name="rol_usu_paruta" required="required">
                                        <option value="1">-- Selecciona un rol --</option>

                                        <?php
                            $query = $conexion->query('select rol from usuario group by rol');
                            while ($r = $query->fetch_assoc()) {
                                echo '<option value="' . $r['rol'] . '">' . $r['rol'] . '</option>';
                            }
                             
                             
                            ?>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <input type="hidden" class="form-control placeholder hide-on-focus" name="status"
                            value="Activo">
                </div>
                <div class="form-group mt-3">
                    <button type="submit" name="regisRolUsu"
                        style="background-color: #4CAF50; border: 1px solid black; color: white; padding: 16px 32px; text-decoration: none; margin: 4px 2px; border-radius: 5px; cursor: pointer;">Registrar
                        ruta al rol</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>


<?php
require_once "footer.php";
?>