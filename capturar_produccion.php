<?php
include "conexion.php";
$data = json_decode(file_get_contents("php://input"), true);

$id_tirada = $data['id_tirada'];
$cantidad = $data['cantidad'];
$id_producto = $data['id_producto'];
$tipo = $data['tipo'];
$id_operador = $data['id_operador'] ?? null;

if ($tipo == 'individual') {
    $stmt1 = $conexion->prepare("INSERT INTO tirada_individual (id_tirada, id_producto, cantidad_tirada_individual, id_operador)
                             VALUES (?, ?, ?, ?)");
    $stmt1->bind_param("iiii", $id_tirada, $id_producto, $cantidad, $id_operador);
    $stmt1->execute();
}

// Registrar (o actualizar) en tirada_detalle
$result = $conexion->query("SELECT * FROM tirada_detalle WHERE id_tirada=$id_tirada AND id_producto=$id_producto");
if ($result->num_rows > 0) {
    $conexion->query("UPDATE tirada_detalle SET cantidad_tirada_detalle = cantidad_tirada_detalle + $cantidad 
                  WHERE id_tirada = $id_tirada AND id_producto = $id_producto");
} else {
    $conexion->query("INSERT INTO tirada_detalle (id_tirada, id_producto, cantidad_tirada_detalle)
                  VALUES ($id_tirada, $id_producto, $cantidad)");
}

// Finalizar la tirada y liberar máquina
$conexion->query("UPDATE tirada SET estatus_tirada = 'Finalizada', fecha_final = NOW() WHERE id_tirada = $id_tirada");
$conexion->query("UPDATE maquina SET estado_maquina = 'Sin operar' 
              WHERE id_maquina = (SELECT id_maquina FROM tirada WHERE id_tirada = $id_tirada)");

echo json_encode(['success' => true]);