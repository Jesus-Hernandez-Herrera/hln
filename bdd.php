<?php
$conexion = new mysqli("localhost", "root", "", "hln");
mysqli_set_charset($conexion,"utf8");
$conexion->query("SET NAMES 'utf8'");
?>