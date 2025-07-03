<?php
// =============================================
// BACKEND PHP (Guardar como backend.php)
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
    $query = "SELECT id, nombre, appaterno, apmaterno FROM usuario WHERE rol = 'operador' AND status_usuario = 'activo'";
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
    // Verificar que no se esté enviando el formulario por segunda vez
    session_start();
    $form_token = $_POST['form_token'] ?? uniqid();

    if (isset($_SESSION['last_form_token']) && $_SESSION['last_form_token'] === $form_token) {
        echo json_encode(['success' => false, 'message' => 'Esta tirada ya ha sido registrada.']);
        return;
    }

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

        // Insertar la tirada
        $query_tirada = "INSERT INTO tirada (fecha_inicio, fecha_final, estatus_tirada, id_responsable, id_maquina) VALUES (?, ?, 'En curso', ?, ?)";
        $stmt = $mysqli->prepare($query_tirada);
        $stmt->bind_param('ssii', $fecha_inicio, $fecha_final, $id_responsable, $id_maquina);
        $stmt->execute();

        $id_tirada = $mysqli->insert_id;

        // Insertar operadores de la tirada
        $query_operadores = "INSERT INTO tirada_operadores (id_tirada, id_operador) VALUES (?, ?)";
        $stmt = $mysqli->prepare($query_operadores);

        foreach ($operadores as $id_operador) {
            $stmt->bind_param('ii', $id_tirada, $id_operador);
            $stmt->execute();
        }

        // Actualizar estado de la máquina
        $query_update_maquina = "UPDATE maquina SET estado_maquina = 'En curso' WHERE id_maquina = ?";
        $stmt = $mysqli->prepare($query_update_maquina);
        $stmt->bind_param('i', $id_maquina);
        $stmt->execute();

        $mysqli->commit();

        // Guardar token para evitar duplicados
        $_SESSION['last_form_token'] = $form_token;

        echo json_encode(['success' => true, 'message' => 'Tirada registrada exitosamente.']);

    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => 'Error al registrar la tirada: ' . $e->getMessage()]);
    }
}

function listarTiradas($mysqli)
{
    $query = "SELECT 
                t.id_tirada,
                DATE_FORMAT(t.fecha_inicio, '%d/%m/%Y %H:%i') as fecha_inicio,
                DATE_FORMAT(t.fecha_final, '%d/%m/%Y %H:%i') as fecha_final,
                t.estatus_tirada,
                CONCAT(u.nombre, ' ', u.appaterno, ' ', u.apmaterno) as responsable,
                CONCAT(m.nombre_maquina, ' - ', m.modelo) as maquina,
                GROUP_CONCAT(CONCAT(op.nombre, ' ', op.appaterno) SEPARATOR ', ') as operadores,
                GROUP_CONCAT(op.id SEPARATOR ',') as operadores_ids
              FROM tirada t
              LEFT JOIN usuario u ON t.id_responsable = u.id
              LEFT JOIN maquina m ON t.id_maquina = m.id_maquina
              LEFT JOIN tirada_operadores to_op ON t.id_tirada = to_op.id_tirada
              LEFT JOIN usuario op ON to_op.id_operador = op.id
              WHERE t.estatus_tirada = 'En curso'
              GROUP BY t.id_tirada
              ORDER BY t.fecha_inicio DESC";

    $result = $mysqli->query($query);

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
                CONCAT(u.nombre, ' ', u.appaterno, ' ', u.apmaterno) as responsable,
                CONCAT(m.nombre_maquina, ' - ', m.modelo) as maquina,
                GROUP_CONCAT(CONCAT(op.nombre, ' ', op.appaterno) SEPARATOR ', ') as operadores,
                GROUP_CONCAT(op.id SEPARATOR ',') as operadores_ids
              FROM tirada t
              LEFT JOIN usuario u ON t.id_responsable = u.id
              LEFT JOIN maquina m ON t.id_maquina = m.id_maquina
              LEFT JOIN tirada_operadores to_op ON t.id_tirada = to_op.id_tirada
              LEFT JOIN usuario op ON to_op.id_operador = op.id
              WHERE t.id_tirada = ?
              GROUP BY t.id_tirada";

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

                // Insertar en tirada_individual
                $query_individual = "INSERT INTO tirada_individual (id_tirada, id_producto, cantidad_tirada_individual, id_operador) VALUES (?, ?, ?, ?)";
                $stmt = $mysqli->prepare($query_individual);
                $stmt->bind_param('iiii', $id_tirada, $id_producto, $cantidad, $id_operador);
                $stmt->execute();

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

        // Actualizar stock (opcional - puedes personalizar esta lógica)
        foreach ($productos_totales as $id_producto => $cantidad_total) {
            $query_stock = "UPDATE stock_almacen SET 
                           cantidad = cantidad + ?,
                           fecha_ultima_actualizacion = CURRENT_TIMESTAMP
                           WHERE id_producto = ?";
            $stmt = $mysqli->prepare($query_stock);
            $stmt->bind_param('ii', $cantidad_total, $id_producto);
            $stmt->execute();
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