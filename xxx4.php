<?php
session_start();
require_once 'conexion.php';
date_default_timezone_set("America/Mexico_City");
setlocale(LC_TIME, 'spanish');
if ($_GET['idconvinculotomar'] != "") {
    $_SESSION["adpersona"] = $_GET['idconvinculotomar'];   
}
$now = date_create()->format('Y-m-d H:i:s');
$sqlupper    = "UPDATE aspirante SET asignadoa ='" . $_SESSION["idasesor"] . "', aspirante.fhregistro='" . $now . "' WHERE aspirante.idpersona= '" . $_SESSION["adpersona"] . "'";
$resultupper = mysqli_query($conexion, $sqlupper);
 echo ('<script>location.href="seguimiento.php?idconvinculo=' . $_SESSION["adpersona"] . '"</script>');