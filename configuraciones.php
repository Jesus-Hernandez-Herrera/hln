<?php
require_once "headeradm.php";
require_once 'bdd.php';
date_default_timezone_set("America/Mexico_City");
setlocale(LC_TIME, 'spanish');
include 'datatable/datateibol.php';


if (isset($_POST['ActualizarConfig'])) {
    $idConfEdit = $_POST['idConfEdit'];
    $valorEdit = $_POST['valorEdit'];
    $sqlupdtConfig = "update configuracion set valor='" . $valorEdit . "' where id=" . $idConfEdit;
    //echo $sqlupdtConfig;
    $resultsqlupdtConfig = mysqli_query($conexion, $sqlupdtConfig); 
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'La configuración se actualizo con éxito!',
            color: '#716add',
            showConfirmButton: true,
            confirmButtonText: 'Continuar.',
            confirmButtonColor: '#2ECC71'
        })
    </script>";
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
                        <!-- <div style="text-align: right">
                        <form method="POST">
                            <input type="submit" class="btn btn-primary " value="Crear Encuesta" name="CrearEncuesta">
                        </form>
                        </div> -->
                        <div class="blog_details" style="padding: 10px 30px 10px 35px;box-shadow: 0px 10px 20px 0px #0000003b;font-size: 18px">
                            <center>
                                <h1>Listado de configuraciones</h1>
                            </center>
                        </div>
                    </body>

                    </html>
                </div>
            </div>
        </div>
    </div>
</section>
<br><br>
<table id="example" class="display" style="width:100%">
    <thead>
        <tr style="background:<?php echo $Color_Encabezado_tablas; ?>; color: <?php echo $Color_Encabezado_tablas_letra; ?>; font-size: 15px;">
            <th>Id</th>
            <th>Descripción</th>
            <th>Valor</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sqlconfiguracion = "SELECT * from configuracion";

        $resultconf = mysqli_query($conexion, $sqlconfiguracion);
        if ($resultconf) {
            while ($rowconfiguracion = mysqli_fetch_assoc($resultconf)) {
                echo '
                <tr>
                    <td>' . $rowconfiguracion['id'] . '</td>
                    <td>' . $rowconfiguracion['descripcion'] . '</td>
                    <form method="POST">
                        <td>
                            <textarea name="valorEdit" id="" cols="30" rows="2" style="width: -webkit-fill-available;">' . $rowconfiguracion['valor'] . '</textarea>
                        </td>
                        <td style="padding: 0px;text-align: center;">
                            <input type="hidden" name="idConfEdit" value="' . $rowconfiguracion['id'] . '">
                            <input type="submit" name="ActualizarConfig" class="btn btn-primary" value="Actualizar">
                        </td>
                    </form>
                </tr>';
            }
        }
        ?>

    </tbody>
    <tfoot>
        <tr style="background:<?php echo $Color_Encabezado_tablas; ?>; color: <?php echo $Color_Encabezado_tablas_letra; ?>; font-size: 15px;">
            <th>Id</th>
            <th>Descripción</th>
            <th>Valor</th>
            <th>Acciones</th>
        </tr>
    </tfoot>
</table>

<style>

</style>
<?php
require_once "footer.php";
?>