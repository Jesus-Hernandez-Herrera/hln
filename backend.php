<?php
// =============================================
// BACKEND PHP CORREGIDO
// =============================================

// Configuración de la base de datos
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'hln';

// Conexión a la base de datos
$mysqli = new mysqli($host, $username, $password, $database);

// Verificar conexión
if ($mysqli->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión: ' . $mysqli->connect_error]));
}

$mysqli->set_charset("utf8");

// Obtener la acción a realizar
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'cargar_responsables':
        cargarResponsables($mysqli);
        break;

    case 'cargar_maquinas':
        cargarMaquinas($mysqli);
        break;

    case 'cargar_operadores':
        cargarOperadores($mysqli);
        break;

    case 'cargar_productos':
        cargarProductos($mysqli);
        break;

    case 'cargar_operadores_especificos':
        cargarOperadoresEspecificos($mysqli);
        break;

    case 'registrar_tirada':
        registrarTirada($mysqli);
        break;

    case 'listar_tiradas':
        listarTiradas($mysqli);
        break;

    case 'listar_tiradas_finalizadas':
        listarTiradasFinalizadas($mysqli);
        break;

    case 'obtener_tirada':
        obtenerTirada($mysqli);
        break;

    case 'finalizar_tirada':
        finalizarTirada($mysqli);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

function cargarResponsables($mysqli)
{
    $query = "SELECT id, nombre, appaterno, apmaterno FROM usuario WHERE rol IN ('responsable', 'administrador') AND status_usuario = 'activo'";
    $result = $mysqli->query($query);

    $responsables = [];
    while ($row = $result->fetch_assoc()) {
        $responsables[] = $row;
    }

    echo json_encode($responsables);
}

function cargarMaquinas($mysqli)
{
    $query = "SELECT id_maquina, nombre_maquina, modelo FROM maquina WHERE estado_maquina = 'Sin operar'";
    $result = $mysqli->query($query);

    $maquinas = [];
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }

    echo json_encode($maquinas);
}

function cargarOperadores($mysqli)
{
    // Primero verificar si la tabla tirada_operadores existe y tiene datos
    $query_check = "SELECT COUNT(*) as count FROM tirada_operadores";
    $result_check = $mysqli->query($query_check);
    $has_data = $result_check->fetch_assoc()['count'] > 0;

    if ($has_data) {
        // Excluir operadores que ya están en tiradas en curso
        $query = "SELECT u.id, u.nombre, u.appaterno, u.apmaterno 
                  FROM usuario u 
                  WHERE u.rol = 'operador' 
                  AND u.status_usuario = 'activo'
                  AND u.id NOT IN (
                      SELECT DISTINCT to_op.id_operador 
                      FROM tirada_operadores to_op 
                      INNER JOIN tirada t ON to_op.id_tirada = t.id_tirada 
                      WHERE t.estatus_tirada = 'En curso'
                  )";
    } else {
        // Si no hay datos en tirada_operadores, mostrar todos los operadores activos
        $query = "SELECT u.id, u.nombre, u.appaterno, u.apmaterno 
                  FROM usuario u 
                  WHERE u.rol = 'operador' 
                  AND u.status_usuario = 'activo'";
    }

    $result = $mysqli->query($query);

    $operadores = [];
    while ($row = $result->fetch_assoc()) {
        $operadores[] = $row;
    }

    echo json_encode($operadores);
}

function cargarProductos($mysqli)
{
    $query = "SELECT id_producto, nombre_producto, tipo_producto FROM producto WHERE estatus = 1";
    $result = $mysqli->query($query);

    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }

    echo json_encode($productos);
}

function cargarOperadoresEspecificos($mysqli)
{
    $ids = json_decode($_POST['ids'], true);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';

    $query = "SELECT id, nombre, appaterno, apmaterno FROM usuario WHERE id IN ($placeholders)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $operadores = [];
    while ($row = $result->fetch_assoc()) {
        $operadores[] = $row;
    }

    echo json_encode($operadores);
}

function registrarTirada($mysqli)
{
    $mysqli->begin_transaction();

    try {
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_final = $_POST['fecha_final'];
        $id_responsable = $_POST['responsable'];
        $id_maquina = $_POST['maquina'];
        $operadores = json_decode($_POST['operadores'], true);

        // Validar que la máquina esté disponible
        $query_maquina = "SELECT estado_maquina FROM maquina WHERE id_maquina = ?";
        $stmt = $mysqli->prepare($query_maquina);
        $stmt->bind_param('i', $id_maquina);
        $stmt->execute();
        $result = $stmt->get_result();
        $maquina = $result->fetch_assoc();

        if (!$maquina || $maquina['estado_maquina'] !== 'Sin operar') {
            throw new Exception('La máquina seleccionada no está disponible.');
        }

        // Validar que los operadores estén disponibles (solo si hay tiradas existentes)
        if (count($operadores) > 0) {
            $placeholders = str_repeat('?,', count($operadores) - 1) . '?';
            $query_operadores = "SELECT COUNT(*) as ocupados FROM tirada_operadores to_op 
                                INNER JOIN tirada t ON to_op.id_tirada = t.id_tirada 
                                WHERE to_op.id_operador IN ($placeholders) 
                                AND t.estatus_tirada = 'En curso'";
            $stmt = $mysqli->prepare($query_operadores);
            $stmt->bind_param(str_repeat('i', count($operadores)), ...$operadores);
            $stmt->execute();
            $result = $stmt->get_result();
            $ocupados = $result->fetch_assoc()['ocupados'];

            if ($ocupados > 0) {
                throw new Exception('Algunos operadores ya están asignados a otras tiradas en curso.');
            }
        }

        // Insertar la tirada
        $query_tirada = "INSERT INTO tirada (fecha_inicio, fecha_final, estatus_tirada, id_responsable, id_maquina) VALUES (?, ?, 'En curso', ?, ?)";
        $stmt = $mysqli->prepare($query_tirada);
        $stmt->bind_param('ssii', $fecha_inicio, $fecha_final, $id_responsable, $id_maquina);
        $stmt->execute();

        $id_tirada = $mysqli->insert_id;

        // Insertar operadores de la tirada
        if (count($operadores) > 0) {
            $query_operadores = "INSERT INTO tirada_operadores (id_tirada, id_operador) VALUES (?, ?)";
            $stmt = $mysqli->prepare($query_operadores);

            foreach ($operadores as $id_operador) {
                $stmt->bind_param('ii', $id_tirada, $id_operador);
                $stmt->execute();
            }
        }

        // Actualizar estado de la máquina
        $query_update_maquina = "UPDATE maquina SET estado_maquina = 'En curso' WHERE id_maquina = ?";
        $stmt = $mysqli->prepare($query_update_maquina);
        $stmt->bind_param('i', $id_maquina);
        $stmt->execute();

        $mysqli->commit();
        echo json_encode(['success' => true, 'message' => 'Tirada registrada exitosamente.']);

    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => 'Error al registrar la tirada: ' . $e->getMessage()]);
    }
}

function listarTiradas($mysqli)
{
    // Consulta simplificada y más robusta
    $query = "SELECT 
                t.id_tirada,
                DATE_FORMAT(t.fecha_inicio, '%d/%m/%Y %H:%i') as fecha_inicio,
                DATE_FORMAT(t.fecha_final, '%d/%m/%Y %H:%i') as fecha_final,
                t.estatus_tirada,
                CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.appaterno, ''), ' ', COALESCE(u.apmaterno, '')) as responsable,
                CONCAT(COALESCE(m.nombre_maquina, ''), ' - ', COALESCE(m.modelo, '')) as maquina,
                COALESCE(op_list.operadores, 'Sin operadores') as operadores,
                COALESCE(op_list.operadores_ids, '') as operadores_ids
              FROM tirada t
              LEFT JOIN usuario u ON t.id_responsable = u.id
              LEFT JOIN maquina m ON t.id_maquina = m.id_maquina
              LEFT JOIN (
                  SELECT 
                      to_op.id_tirada,
                      GROUP_CONCAT(CONCAT(op.nombre, ' ', COALESCE(op.appaterno, '')) SEPARATOR ', ') as operadores,
                      GROUP_CONCAT(op.id SEPARATOR ',') as operadores_ids
                  FROM tirada_operadores to_op 
                  LEFT JOIN usuario op ON to_op.id_operador = op.id
                  GROUP BY to_op.id_tirada
              ) op_list ON t.id_tirada = op_list.id_tirada
              WHERE t.estatus_tirada = 'En curso'
              ORDER BY t.fecha_inicio DESC";

    $result = $mysqli->query($query);

    if (!$result) {
        echo json_encode(['data' => [], 'error' => 'Error en la consulta: ' . $mysqli->error]);
        return;
    }

    $tiradas = [];
    while ($row = $result->fetch_assoc()) {
        $tiradas[] = $row;
    }

    echo json_encode(['data' => $tiradas]);
}

function listarTiradasFinalizadas($mysqli)
{
    $query = "SELECT 
                t.id_tirada,
                DATE_FORMAT(t.fecha_inicio, '%d/%m/%Y %H:%i') as fecha_inicio,
                DATE_FORMAT(t.fecha_final, '%d/%m/%Y %H:%i') as fecha_final,
                t.estatus_tirada,
                CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.appaterno, ''), ' ', COALESCE(u.apmaterno, '')) as responsable,
                CONCAT(COALESCE(m.nombre_maquina, ''), ' - ', COALESCE(m.modelo, '')) as maquina,
                COALESCE(op_list.operadores, 'Sin operadores') as operadores,
                COALESCE(prod_list.productos_producidos, 'Sin producción registrada') as productos_producidos,
                COALESCE(prod_list.total_producido, 0) as total_producido
              FROM tirada t
              LEFT JOIN usuario u ON t.id_responsable = u.id
              LEFT JOIN maquina m ON t.id_maquina = m.id_maquina
              LEFT JOIN (
                  SELECT 
                      to_op.id_tirada,
                      GROUP_CONCAT(CONCAT(op.nombre, ' ', COALESCE(op.appaterno, '')) SEPARATOR ', ') as operadores
                  FROM tirada_operadores to_op 
                  LEFT JOIN usuario op ON to_op.id_operador = op.id
                  GROUP BY to_op.id_tirada
              ) op_list ON t.id_tirada = op_list.id_tirada
              LEFT JOIN (
                  SELECT 
                      td.id_tirada,
                      GROUP_CONCAT(CONCAT(p.nombre_producto, ': ', td.cantidad_tirada_detalle, ' unidades') SEPARATOR ', ') as productos_producidos,
                      SUM(td.cantidad_tirada_detalle) as total_producido
                  FROM tirada_detalle td 
                  LEFT JOIN producto p ON td.id_producto = p.id_producto
                  GROUP BY td.id_tirada
              ) prod_list ON t.id_tirada = prod_list.id_tirada
              WHERE t.estatus_tirada = 'Finalizada'
              ORDER BY t.fecha_inicio DESC";

    $result = $mysqli->query($query);

    if (!$result) {
        echo json_encode(['data' => [], 'error' => 'Error en la consulta: ' . $mysqli->error]);
        return;
    }

    $tiradas = [];
    while ($row = $result->fetch_assoc()) {
        $tiradas[] = $row;
    }

    echo json_encode(['data' => $tiradas]);
}

function obtenerTirada($mysqli)
{
    $id_tirada = $_POST['id_tirada'];

    $query = "SELECT 
                t.id_tirada,
                t.fecha_inicio,
                t.fecha_final,
                t.estatus_tirada,
                CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.appaterno, ''), ' ', COALESCE(u.apmaterno, '')) as responsable,
                CONCAT(COALESCE(m.nombre_maquina, ''), ' - ', COALESCE(m.modelo, '')) as maquina,
                COALESCE(op_list.operadores, 'Sin operadores') as operadores,
                COALESCE(op_list.operadores_ids, '') as operadores_ids
              FROM tirada t
              LEFT JOIN usuario u ON t.id_responsable = u.id
              LEFT JOIN maquina m ON t.id_maquina = m.id_maquina
              LEFT JOIN (
                  SELECT 
                      to_op.id_tirada,
                      GROUP_CONCAT(CONCAT(op.nombre, ' ', COALESCE(op.appaterno, '')) SEPARATOR ', ') as operadores,
                      GROUP_CONCAT(op.id SEPARATOR ',') as operadores_ids
                  FROM tirada_operadores to_op 
                  LEFT JOIN usuario op ON to_op.id_operador = op.id
                  GROUP BY to_op.id_tirada
              ) op_list ON t.id_tirada = op_list.id_tirada
              WHERE t.id_tirada = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id_tirada);
    $stmt->execute();
    $result = $stmt->get_result();

    $tirada = $result->fetch_assoc();
    echo json_encode($tirada);
}

function finalizarTirada($mysqli)
{
    $id_tirada = $_POST['id_tirada'];
    $producciones = json_decode($_POST['producciones'], true);

    $mysqli->begin_transaction();

    try {
        // Obtener información de la tirada
        $query_tirada = "SELECT id_maquina FROM tirada WHERE id_tirada = ?";
        $stmt = $mysqli->prepare($query_tirada);
        $stmt->bind_param('i', $id_tirada);
        $stmt->execute();
        $result = $stmt->get_result();
        $tirada_info = $result->fetch_assoc();

        if (!$tirada_info) {
            throw new Exception('Tirada no encontrada.');
        }

        // Procesar las producciones
        $productos_totales = [];

        foreach ($producciones as $produccion) {
            if ($produccion['tipo'] === 'general') {
                // Producción general
                $id_producto = $produccion['producto'];
                $cantidad = $produccion['cantidad'];

                if (!isset($productos_totales[$id_producto])) {
                    $productos_totales[$id_producto] = 0;
                }
                $productos_totales[$id_producto] += $cantidad;

            } else {
                // Producción individual
                $id_operador = $produccion['operador'];
                $id_producto = $produccion['producto'];
                $cantidad = $produccion['cantidad'];

                // Verificar si la tabla tirada_individual existe
                $query_check_table = "SHOW TABLES LIKE 'tirada_individual'";
                $result_check = $mysqli->query($query_check_table);

                if ($result_check->num_rows > 0) {
                    // Insertar en tirada_individual
                    $query_individual = "INSERT INTO tirada_individual (id_tirada, id_producto, cantidad_tirada_individual, id_operador) VALUES (?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($query_individual);
                    $stmt->bind_param('iiii', $id_tirada, $id_producto, $cantidad, $id_operador);
                    $stmt->execute();
                }

                // Acumular para tirada_detalle
                if (!isset($productos_totales[$id_producto])) {
                    $productos_totales[$id_producto] = 0;
                }
                $productos_totales[$id_producto] += $cantidad;
            }
        }

        // Insertar en tirada_detalle los totales por producto
        $query_detalle = "INSERT INTO tirada_detalle (id_tirada, id_producto, cantidad_tirada_detalle) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($query_detalle);

        foreach ($productos_totales as $id_producto => $cantidad_total) {
            $stmt->bind_param('iii', $id_tirada, $id_producto, $cantidad_total);
            $stmt->execute();
        }

        // Actualizar estado de la tirada
        $query_update_tirada = "UPDATE tirada SET estatus_tirada = 'Finalizada' WHERE id_tirada = ?";
        $stmt = $mysqli->prepare($query_update_tirada);
        $stmt->bind_param('i', $id_tirada);
        $stmt->execute();

        // Actualizar estado de la máquina
        $query_update_maquina = "UPDATE maquina SET estado_maquina = 'Sin operar' WHERE id_maquina = ?";
        $stmt = $mysqli->prepare($query_update_maquina);
        $stmt->bind_param('i', $tirada_info['id_maquina']);
        $stmt->execute();

        // Actualizar stock si la tabla existe
        $query_check_stock = "SHOW TABLES LIKE 'stock_almacen'";
        $result_check_stock = $mysqli->query($query_check_stock);

        if ($result_check_stock->num_rows > 0) {
            foreach ($productos_totales as $id_producto => $cantidad_total) {
                $query_stock = "INSERT INTO stock_almacen (id_producto, cantidad, fecha_ultima_actualizacion) 
                               VALUES (?, ?, CURRENT_TIMESTAMP)
                               ON DUPLICATE KEY UPDATE 
                               cantidad = cantidad + ?,
                               fecha_ultima_actualizacion = CURRENT_TIMESTAMP";
                $stmt = $mysqli->prepare($query_stock);
                $stmt->bind_param('iii', $id_producto, $cantidad_total, $cantidad_total);
                $stmt->execute();
            }
        }

        $mysqli->commit();
        echo json_encode(['success' => true, 'message' => 'Tirada finalizada exitosamente.']);

    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => 'Error al finalizar la tirada: ' . $e->getMessage()]);
    }
}

$mysqli->close();
?>