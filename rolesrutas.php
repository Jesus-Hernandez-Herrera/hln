<?php
require_once "headeradm.php";
require_once 'bdd.php';
date_default_timezone_set("America/Mexico_City");
setlocale(LC_TIME, 'spanish');
include 'datatable/datateibol.php';


if (isset($_POST['eliminar_rutarol'])) {
    $ruta = $_POST['ruta'];
    $roldeusuario = $_POST['roldeusuario'];
    $sql_eliminar = "DELETE FROM rol_permisos WHERE rol = '$roldeusuario' AND ruta = '$ruta'";
    $resultado_eliminar = mysqli_query($conexion, $sql_eliminar);
    if ($resultado_eliminar) {
        echo "<script>swal.fire('El permiso se elimino con exito.');</script> ";
    } else {
        echo "<script>swal.fire('Error al eliminar el permiso.');</script> ";
    }
}
echo "<button class='btn btn-primary' style='margin: 10px; font-weight: bold; border: #a91111; cursor: pointer; background-color: #4CAF50;' onclick='window.location = \"roles.php\"'>🢀 Regresar a los roles de usuario </button>";//boton regresar con una flecha


if (isset($_POST['roldeusuario'])) {
    $roldeusuario = $_POST['roldeusuario'];
}

if($roldeusuario != '0' || $roldeusuario != '' || $roldeusuario == !null){
$sql_rolestutas = "SELECT u.rol, rp.ruta
FROM usuario u
JOIN rol_permisos rp ON u.rol = rp.rol
WHERE u.rol = '" . $roldeusuario . "'";
echo '<table id="example" class="cell-border" style="width:100%">
        <thead>
            <tr
                style="background:<?php echo $Color_Encabezado_tablas; ?>; color:
<?php echo $Color_Encabezado_tablas_letra; ?>; font-size: 15px; text-align: center;">
<th style="padding: 0px;text-align: center;">Rutas</th>
<th style="padding: 0px;text-align: center;">Rol</th>
<th style="padding: 0px;text-align: center;">Acciones</th>
</tr>
</thead>
<tbody>';

    $sql_rolestutas = mysqli_query($conexion, $sql_rolestutas);
    if (!$sql_rolestutas) {
    echo "Error en la consulta: " . mysqli_error($conexion);
    exit;
    }

    while ( $row = mysqli_fetch_array($sql_rolestutas)) {
    $ruta = $row['ruta'];
    $rol = $row['rol'];
    echo '<tr>
        <td style="padding: 0px;text-align: center;">' . $ruta . '</td>
        <td style="padding: 0px;text-align: center;">' . $rol . '</td>
        <td style="padding: 0px;text-align: center;">
            <form method="POST" action="rolesrutas.php" style="margin-block-end: 0;">
                <input type="submit"
                    style="border-radius: 5px; background: blue; color: #fff; padding: 8px 10px;margin: 5px; font-weight: bold; border: #a91111; cursor: pointer;"
                    name="eliminar_rutarol" value="Eliminar">
                <input type="hidden" name="roldeusuario" value="' . $rol . '">
                <input type="hidden" name="ruta" value="' . $ruta . '">
            </form>
        </td>
    </tr>';
    }
    echo '</tbody>
<tfoot>
    <tr style="background:<?php echo $Color_Encabezado_tablas; ?>; color:
<?php echo $Color_Encabezado_tablas_letra; ?>; font-size: 15px; text-align: center;">
        <th style="padding: 0px;text-align: center;">Rutas</th>
        <th style="padding: 0px;text-align: center;">Rol</th>
        <th style="padding: 0px;text-align: center;">Acciones</th>
    </tr>
</tfoot>
</table>';
}
require_once "footer.php";
?>