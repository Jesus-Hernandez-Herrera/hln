<?php
include 'headeradm.php';
include 'bdd.php';

// Función para validar datos
function validarDatos($datos) {
    $errores = [];
    
    if (empty($datos['placa'])) {
        $errores[] = "La placa es obligatoria";
    }
    
    if (empty($datos['modelo'])) {
        $errores[] = "El modelo es obligatorio";
    }
    
    if (!is_numeric($datos['kilometraje']) || $datos['kilometraje'] < 0) {
        $errores[] = "El kilometraje debe ser un número positivo";
    }
    
    if (!is_numeric($datos['km_mantenimiento']) || $datos['km_mantenimiento'] < 0) {
        $errores[] = "Los kilómetros de mantenimiento deben ser un número positivo";
    }
    
    if (!is_numeric($datos['cant_combustible']) || $datos['cant_combustible'] < 0 || $datos['cant_combustible'] > 100) {
        $errores[] = "El combustible debe estar entre 0 y 100%";
    }
    
    return $errores;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Actualizar sólo kilometraje y fecha último mantenimiento
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar_km') {
        $id = intval($_POST['id_vehiculo']);
        $nuevoKm = intval($_POST['kilometraje']);
        
        $stmt = $conexion->prepare("UPDATE vehiculo SET kilometraje = ?, fechaultimimantenimiento = NOW() WHERE id_vehiculo = ?");
        $stmt->bind_param("ii", $nuevoKm, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Kilometraje actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar kilometraje']);
        }
        exit;
    }

    // Registrar nuevo vehículo
    if (isset($_POST['accion']) && $_POST['accion'] === 'nuevo') {
        $errores = validarDatos($_POST);
        
        if (!empty($errores)) {
            echo json_encode(['success' => false, 'message' => implode(', ', $errores)]);
            exit;
        }
        
        $stmt = $conexion->prepare("INSERT INTO vehiculo (
            modelo, placa_serial, descripcion, kilometraje, kilometros_mantenimiento,
            cant_combustible, tipo_combustible, status, tipo_vehiculo, capacidad_kg_carga,
            frecuencia_dias_mantenimiento, fechaultimimantenimiento, alturacaja_vehiculo, 
            longitudcaja_vehiculo, anchocaja_vehiculo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssiiisssissddd",
            $_POST['modelo'], $_POST['placa'], $_POST['descripcion'], 
            intval($_POST['kilometraje']), intval($_POST['km_mantenimiento']), 
            intval($_POST['cant_combustible']), $_POST['tipo_combustible'],
            $_POST['status'], $_POST['tipo'], floatval($_POST['capacidad']), 
            intval($_POST['frecuencia']), $_POST['fecha_mant'],
            floatval($_POST['altura'] ?? 0), floatval($_POST['longitud'] ?? 0), 
            floatval($_POST['ancho'] ?? 0)
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vehículo registrado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar vehículo']);
        }
        exit;
    }

    // Actualizar vehículo completo
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
        $errores = validarDatos($_POST);
        
        if (!empty($errores)) {
            echo json_encode(['success' => false, 'message' => implode(', ', $errores)]);
            exit;
        }
        
        $stmt = $conexion->prepare("UPDATE vehiculo SET
            placa_serial=?, modelo=?, descripcion=?, kilometraje=?, kilometros_mantenimiento=?,
            cant_combustible=?, tipo_combustible=?, status=?, tipo_vehiculo=?, capacidad_kg_carga=?,
            frecuencia_dias_mantenimiento=?, fechaultimimantenimiento=?, alturacaja_vehiculo=?, 
            longitudcaja_vehiculo=?, anchocaja_vehiculo=?
            WHERE id_vehiculo=?");

        $stmt->bind_param("sssiiisssissddd",
            $_POST['placa'], $_POST['modelo'], $_POST['descripcion'], 
            intval($_POST['kilometraje']), intval($_POST['km_mantenimiento']), 
            intval($_POST['cant_combustible']), $_POST['tipo_combustible'],
            $_POST['status'], $_POST['tipo'], floatval($_POST['capacidad']), 
            intval($_POST['frecuencia']), $_POST['fecha_mant'],
            floatval($_POST['altura']), floatval($_POST['longitud']), 
            floatval($_POST['ancho']), intval($_POST['id'])
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vehículo actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar vehículo']);
        }
        exit;
    }
}

// Función para obtener el estado del mantenimiento
function obtenerEstadoMantenimiento($kilometraje, $km_mantenimiento, $fecha_ultimo, $frecuencia_dias) {
    $dias_transcurridos = 0;
    if ($fecha_ultimo) {
        $fecha_actual = new DateTime();
        $fecha_mant = new DateTime($fecha_ultimo);
        $dias_transcurridos = $fecha_actual->diff($fecha_mant)->days;
    }
    
    $necesita_por_km = $kilometraje >= $km_mantenimiento;
    $necesita_por_tiempo = $dias_transcurridos >= $frecuencia_dias;
    
    if ($necesita_por_km || $necesita_por_tiempo) {
        return 'urgente';
    } elseif ($kilometraje >= ($km_mantenimiento * 0.8) || $dias_transcurridos >= ($frecuencia_dias * 0.8)) {
        return 'proximo';
    }
    return 'ok';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .header-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .card-custom {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
        }
        
        .btn-custom {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .table-custom {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-en-ruta { background: #d4edda; color: #155724; }
        .status-mantenimiento { background: #fff3cd; color: #856404; }
        .status-descompuesto { background: #f8d7da; color: #721c24; }
        .status-corralon { background: #d1ecf1; color: #0c5460; }
        .status-estacionado { background: #e2e3e5; color: #383d41; }
        
        .mantenimiento-urgente { background-color: #ffebee !important; }
        .mantenimiento-proximo { background-color: #fff3e0 !important; }
        .mantenimiento-ok { background-color: #e8f5e8 !important; }
        
        .combustible-bar {
            height: 6px;
            border-radius: 3px;
            background: #e9ecef;
            overflow: hidden;
        }
        
        .combustible-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .combustible-alto { background: #28a745; }
        .combustible-medio { background: #ffc107; }
        .combustible-bajo { background: #dc3545; }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }
        
        .form-floating-custom {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .form-floating-custom input,
        .form-floating-custom select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-floating-custom input:focus,
        .form-floating-custom select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .modal-custom .modal-content {
            border-radius: 20px;
            border: none;
        }
        
        .modal-custom .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
        }
        
        .quick-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .quick-action-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .quick-action-btn:hover {
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            .header-bg {
                padding: 1rem 0;
            }
            
            .stats-card {
                text-align: center;
            }
            
            .quick-actions {
                bottom: 10px;
                right: 10px;
            }
        }
    </style>
</head>
<body class="bg-light">
     

    <div class="container-fluid">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <?php
            $sql_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'En ruta' THEN 1 ELSE 0 END) as en_ruta,
                SUM(CASE WHEN status = 'Mantenimiento' THEN 1 ELSE 0 END) as mantenimiento,
                SUM(CASE WHEN status = 'Descompuesto' THEN 1 ELSE 0 END) as descompuesto,
                SUM(CASE WHEN kilometraje >= kilometros_mantenimiento THEN 1 ELSE 0 END) as necesitan_mantenimiento
                FROM vehiculo";
            $result_stats = $conexion->query($sql_stats);
            $stats = $result_stats->fetch_assoc();
            ?>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number"><?php echo $stats['total']; ?></div>
                            <div class="text-muted">Total Vehículos</div>
                        </div>
                        <i class="bi bi-truck text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number text-success"><?php echo $stats['en_ruta']; ?></div>
                            <div class="text-muted">En Ruta</div>
                        </div>
                        <i class="bi bi-arrow-right-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number text-warning"><?php echo $stats['mantenimiento']; ?></div>
                            <div class="text-muted">Mantenimiento</div>
                        </div>
                        <i class="bi bi-tools text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number text-danger"><?php echo $stats['necesitan_mantenimiento']; ?></div>
                            <div class="text-muted">Necesitan Mantenimiento</div>
                        </div>
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card card-custom mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select2" id="filtroStatus">
                            <option value="">Todos los estados</option>
                            <option value="En ruta">En ruta</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Descompuesto">Descompuesto</option>
                            <option value="Corralon">Corralón</option>
                            <option value="Estacionado">Estacionado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select2" id="filtroTipo">
                            <option value="">Todos los tipos</option>
                            <option value="Camioneta">Camioneta</option>
                            <option value="Camion">Camión</option>
                            <option value="Autobus">Autobús</option>
                            <option value="Camion de Carga">Camión de Carga</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select2" id="filtroMantenimiento">
                            <option value="">Estado mantenimiento</option>
                            <option value="urgente">Urgente</option>
                            <option value="proximo">Próximo</option>
                            <option value="ok">Al día</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success btn-custom w-100" id="btnNuevoVehiculo">
                            <i class="bi bi-plus-circle me-2"></i>Nuevo Vehículo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de vehículos -->
        <div class="card card-custom">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaVehiculos" class="table table-hover table-custom">
                        <thead class="table-dark">
                            <tr>
                                <th>Placa</th>
                                <th>Modelo</th>
                                <th>Descripción</th>
                                <th>Kilometraje</th>
                                <th>Combustible</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th>Capacidad</th>
                                <th>Mantenimiento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sql = "SELECT * FROM vehiculo ORDER BY placa_serial";
                        $resultado = $conexion->query($sql);
                        
                        while ($fila = $resultado->fetch_assoc()) {
                            $estado_mant = obtenerEstadoMantenimiento(
                                $fila['kilometraje'], 
                                $fila['kilometros_mantenimiento'], 
                                $fila['fechaultimimantenimiento'], 
                                $fila['frecuencia_dias_mantenimiento']
                            );
                            
                            $clase_fila = '';
                            switch($estado_mant) {
                                case 'urgente': $clase_fila = 'mantenimiento-urgente'; break;
                                case 'proximo': $clase_fila = 'mantenimiento-proximo'; break;
                                case 'ok': $clase_fila = 'mantenimiento-ok'; break;
                            }
                            
                            echo "<tr class='$clase_fila' data-status='{$fila['status']}' data-tipo='{$fila['tipo_vehiculo']}' data-mantenimiento='$estado_mant'>";
                            echo "<td><strong>{$fila['placa_serial']}</strong></td>";
                            echo "<td>{$fila['modelo']}</td>";
                            echo "<td>{$fila['descripcion']}</td>";
                            echo "<td>
                                <div class='d-flex align-items-center'>
                                    <span class='me-2'>{$fila['kilometraje']} km</span>
                                    <button class='btn btn-sm btn-outline-primary actualizar-km' 
                                            data-id='{$fila['id_vehiculo']}' 
                                            data-km='{$fila['kilometraje']}' 
                                            title='Actualizar kilometraje'>
                                        <i class='bi bi-arrow-repeat'></i>
                                    </button>
                                </div>
                            </td>";
                            
                            // Barra de combustible
                            $combustible = $fila['cant_combustible'];
                            $clase_combustible = $combustible > 60 ? 'combustible-alto' : ($combustible > 30 ? 'combustible-medio' : 'combustible-bajo');
                            echo "<td>
                                <div class='combustible-bar'>
                                    <div class='combustible-fill $clase_combustible' style='width: {$combustible}%'></div>
                                </div>
                                <small>{$combustible}%</small>
                            </td>";
                            
                            // Estado con badge
                            $status_class = 'status-' . strtolower(str_replace(' ', '-', $fila['status']));
                            echo "<td><span class='status-badge $status_class'>{$fila['status']}</span></td>";
                            
                            echo "<td>{$fila['tipo_vehiculo']}</td>";
                            echo "<td>{$fila['capacidad_kg_carga']} kg</td>";
                            
                            // Información de mantenimiento
                            $icono_mant = $estado_mant == 'urgente' ? 'exclamation-triangle text-danger' : 
                                         ($estado_mant == 'proximo' ? 'clock text-warning' : 'check-circle text-success');
                            echo "<td>
                                <div class='d-flex align-items-center'>
                                    <i class='bi bi-$icono_mant me-2'></i>
                                    <div>
                                        <small>Próximo: {$fila['kilometros_mantenimiento']} km</small><br>
                                        <small>Último: {$fila['fechaultimimantenimiento']}</small>
                                    </div>
                                </div>
                            </td>";
                            
                            echo "<td>
                                <div class='btn-group' role='group'>
                                    <button class='btn btn-sm btn-outline-primary editar' 
                                            data-id='{$fila['id_vehiculo']}' 
                                            title='Editar vehículo'>
                                        <i class='bi bi-pencil'></i>
                                    </button>
                                    <button class='btn btn-sm btn-outline-info ver-detalles' 
                                            data-id='{$fila['id_vehiculo']}' 
                                            title='Ver detalles'>
                                        <i class='bi bi-eye'></i>
                                    </button>
                                </div>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción rápida -->
    <div class="quick-actions">
        <button class="btn btn-primary quick-action-btn" id="btnMantenimientoUrgente" title="Vehículos que necesitan mantenimiento urgente">
            <i class="bi bi-exclamation-triangle"></i>
        </button>
        <button class="btn btn-success quick-action-btn" id="btnVehiculosDisponibles" title="Vehículos disponibles">
            <i class="bi bi-check-circle"></i>
        </button>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function () {
        // Inicializar DataTable
        const tabla = $('#tablaVehiculos').DataTable({
            scrollX: true,
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            columnDefs: [
                { targets: [4, 5, 8, 9], orderable: false }
            ]
        });

        // Filtros
        $('#filtroStatus, #filtroTipo, #filtroMantenimiento').on('change', function() {
            filtrarTabla();
        });

        function filtrarTabla() {
            const status = $('#filtroStatus').val();
            const tipo = $('#filtroTipo').val();
            const mantenimiento = $('#filtroMantenimiento').val();

            tabla.rows().every(function() {
                const row = this.node();
                const $row = $(row);
                
                let mostrar = true;
                
                if (status && $row.data('status') !== status) mostrar = false;
                if (tipo && $row.data('tipo') !== tipo) mostrar = false;
                if (mantenimiento && $row.data('mantenimiento') !== mantenimiento) mostrar = false;
                
                if (mostrar) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        }

        // Modelos más comunes para vehículos de distribución
        const modelosComunes = {
            'Camioneta': ['Ford Ranger', 'Chevrolet Colorado', 'Nissan Frontier', 'Toyota Hilux', 'Isuzu D-Max'],
            'Camion': ['Freightliner Cascadia', 'Volvo VNL', 'International LT', 'Peterbilt 579', 'Kenworth T680'],
            'Autobus': ['Mercedes-Benz Sprinter', 'Ford Transit', 'Chevrolet Express', 'Nissan NV200', 'Iveco Daily'],
            'Camion de Carga': ['Isuzu NPR', 'Hino 155', 'Freightliner M2', 'International CV', 'Volvo VHD']
        };

        function generarOpcionesModelo(tipo) {
            const modelos = modelosComunes[tipo] || [];
            let opciones = '<option value="">Seleccionar modelo</option>';
            modelos.forEach(modelo => {
                opciones += `<option value="${modelo}">${modelo}</option>`;
            });
            opciones += '<option value="otro">Otro (especificar)</option>';
            return opciones;
        }

        function mostrarCamposCaja(tipo) {
            const esCamion = tipo.toLowerCase().includes('camion');
            const campos = ['#divAltura', '#divLongitud', '#divAncho'];
            
            campos.forEach(campo => {
                if (esCamion) {
                    $(campo).show();
                } else {
                    $(campo).hide();
                    $(campo).find('input').val('');
                }
            });
        }

        function generarFormularioVehiculo(datos = {}) {
            const esEdicion = Object.keys(datos).length > 0;
            const titulo = esEdicion ? 'Editar Vehículo' : 'Registrar Nuevo Vehículo';
            
            return `
                <div class="row">
                    ${esEdicion ? `<input type="hidden" id="idVehiculo" value="${datos.id || ''}" />` : ''}
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="text" id="placa" class="form-control" placeholder="Placa" value="${datos.placa || ''}">
                            <label for="placa">Placa *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <select id="tipoVehiculo" class="form-select2">
                                <option value="">Seleccionar tipo</option>
                                <option value="Camioneta" ${datos.tipo === 'Camioneta' ? 'selected' : ''}>Camioneta</option>
                                <option value="Camion" ${datos.tipo === 'Camion' ? 'selected' : ''}>Camión</option>
                                <option value="Autobus" ${datos.tipo === 'Autobus' ? 'selected' : ''}>Autobús</option>
                                <option value="Camion de Carga" ${datos.tipo === 'Camion de Carga' ? 'selected' : ''}>Camión de Carga</option>
                            </select>
                            <label for="tipoVehiculo">Tipo de Vehículo *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <select id="modelo" class="form-select2">
                                <option value="">Primero seleccione tipo</option>
                            </select>
                            <label for="modelo">Modelo *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="text" id="descripcion" class="form-control" placeholder="Descripción" value="${datos.descripcion || ''}">
                            <label for="descripcion">Descripción</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating-custom">
                            <input type="number" id="kilometraje" class="form-control" placeholder="Kilometraje" value="${datos.kilometraje || ''}">
                            <label for="kilometraje">Kilometraje *</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating-custom">
                            <input type="number" id="kmMantenimiento" class="form-control" placeholder="Km Mantenimiento" value="${datos.km_mantenimiento || ''}">
                            <label for="kmMantenimiento">Km Mantenimiento *</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating-custom">
                            <input type="range" id="combustible" class="form-range" min="0" max="100" step="1" value="${datos.combustible || '50'}">
                            <label for="combustible">Combustible: <span id="combustibleValor">${datos.combustible || '50'}</span>%</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <select id="tipoCombustible" class="form-select2">
                                <option value="Gasolina" ${datos.tipo_combustible === 'Gasolina' ? 'selected' : ''}>Gasolina</option>
                                <option value="Diesel" ${datos.tipo_combustible === 'Diesel' ? 'selected' : ''}>Diesel</option>
                                <option value="Gas" ${datos.tipo_combustible === 'Gas' ? 'selected' : ''}>Gas</option>
                                <option value="Electrico" ${datos.tipo_combustible === 'Electrico' ? 'selected' : ''}>Eléctrico</option>
                            </select>
                            <label for="tipoCombustible">Tipo de Combustible</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <select id="status" class="form-select2">
                                <option value="En ruta" ${datos.status === 'En ruta' ? 'selected' : ''}>En ruta</option>
                                <option value="Mantenimiento" ${datos.status === 'Mantenimiento' ? 'selected' : ''}>Mantenimiento</option>
                                <option value="Descompuesto" ${datos.status === 'Descompuesto' ? 'selected' : ''}>Descompuesto</option>
                                <option value="Corralon" ${datos.status === 'Corralon' ? 'selected' : ''}>Corralón</option>
                                <option value="Estacionado" ${datos.status === 'Estacionado' ? 'selected' : ''}>Estacionado</option>
                            </select>
                            <label for="status">Estado</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="number" id="capacidad" class="form-control" placeholder="Capacidad" value="${datos.capacidad || ''}">
                            <label for="capacidad">Capacidad (kg)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="number" id="frecuencia" class="form-control" placeholder="Frecuencia días" value="${datos.frecuencia || '30'}">
                            <label for="frecuencia">Frecuencia Mantenimiento (días)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="date" id="fechaMantenimiento" class="form-control" value="${datos.fecha_mantenimiento || ''}">
                            <label for="fechaMantenimiento">Fecha Último Mantenimiento</label>
                        </div>
                    </div>
                    <div class="col-md-6"></div>
                    
                    <!-- Campos específicos para camiones -->
                    <div class="col-md-4" id="divAltura" style="display: none;">
                        <div class="form-floating-custom">
                            <input type="number" id="altura" class="form-control" step="0.1" placeholder="Altura" value="${datos.altura || ''}">
                            <label for="altura">Altura Caja (m)</label>
                        </div>
                    </div>
                    <div class="col-md-4" id="divLongitud" style="display: none;">
                        <div class="form-floating-custom">
                            <input type="number" id="longitud" class="form-control" step="0.1" placeholder="Longitud" value="${datos.longitud || ''}">
                            <label for="longitud">Longitud Caja (m)</label>
                        </div>
                    </div>
                    <div class="col-md-4" id="divAncho" style="display: none;">
                        <div class="form-floating-custom">
                            <input type="number" id="ancho" class="form-control" step="0.1" placeholder="Ancho" value="${datos.ancho || ''}">
                            <label for="ancho">Ancho Caja (m)</label>
                        </div>
                    </div>
                </div>
            `;
        }

        // Evento para nuevo vehículo
        $('#btnNuevoVehiculo').on('click', function() {
            Swal.fire({
                title: 'Registrar Nuevo Vehículo',
                html: generarFormularioVehiculo(),
                width: '80%',
                showCancelButton: true,
                confirmButtonText: 'Registrar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'modal-custom'
                },
                didOpen: () => {
                    // Configurar eventos del modal
                    $('#tipoVehiculo').on('change', function() {
                        const tipo = $(this).val();
                        $('#modelo').html(generarOpcionesModelo(tipo));
                        mostrarCamposCaja(tipo);
                    });
                    
                    $('#modelo').on('change', function() {
                        if ($(this).val() === 'otro') {
                            Swal.fire({
                                title: 'Especificar Modelo',
                                input: 'text',
                                inputPlaceholder: 'Ingrese el modelo del vehículo',
                                showCancelButton: true,
                                confirmButtonText: 'Aceptar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.isConfirmed && result.value) {
                                    $('#modelo').append(`<option value="${result.value}" selected>${result.value}</option>`);
                                } else {
                                    $('#modelo').val('');
                                }
                            });
                        }
                    });
                    
                    $('#combustible').on('input', function() {
                        $('#combustibleValor').text($(this).val());
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarFormulario('nuevo');
                }
            });
        });

        // Evento para editar vehículo
        $(document).on('click', '.editar', function() {
            const id = $(this).data('id');
            const fila = $(this).closest('tr');
            
            // Extraer datos de la fila
            const datos = {
                id: id,
                placa: fila.find('td:nth-child(1)').text(),
                modelo: fila.find('td:nth-child(2)').text(),
                descripcion: fila.find('td:nth-child(3)').text(),
                kilometraje: fila.find('td:nth-child(4)').text().replace(' km', ''),
                combustible: fila.find('td:nth-child(5) small').text().replace('%', ''),
                status: fila.find('td:nth-child(6) span').text(),
                tipo: fila.find('td:nth-child(7)').text(),
                capacidad: fila.find('td:nth-child(8)').text().replace(' kg', '')
            };
            
            Swal.fire({
                title: 'Editar Vehículo',
                html: generarFormularioVehiculo(datos),
                width: '80%',
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'modal-custom'
                },
                didOpen: () => {
                    // Configurar eventos del modal
                    $('#tipoVehiculo').on('change', function() {
                        const tipo = $(this).val();
                        $('#modelo').html(generarOpcionesModelo(tipo));
                        mostrarCamposCaja(tipo);
                    });
                    
                    $('#modelo').on('change', function() {
                        if ($(this).val() === 'otro') {
                            Swal.fire({
                                title: 'Especificar Modelo',
                                input: 'text',
                                inputPlaceholder: 'Ingrese el modelo del vehículo',
                                showCancelButton: true,
                                confirmButtonText: 'Aceptar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.isConfirmed && result.value) {
                                    $('#modelo').append(`<option value="${result.value}" selected>${result.value}</option>`);
                                } else {
                                    $('#modelo').val('');
                                }
                            });
                        }
                    });
                    
                    $('#combustible').on('input', function() {
                        $('#combustibleValor').text($(this).val());
                    });
                    
                    // Mostrar campos apropiados según el tipo
                    mostrarCamposCaja(datos.tipo);
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarFormulario('actualizar');
                }
            });
        });

        // Evento para actualizar kilometraje
        $(document).on('click', '.actualizar-km', function() {
            const id = $(this).data('id');
            const kmActual = $(this).data('km');
            
            Swal.fire({
                title: 'Actualizar Kilometraje',
                input: 'number',
                inputLabel: 'Nuevo kilometraje',
                inputValue: kmActual,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value || value < 0) {
                        return 'Ingrese un kilometraje válido';
                    }
                    if (value < kmActual) {
                        return 'El nuevo kilometraje no puede ser menor al actual';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '',
                        type: 'POST',
                        data: {
                            accion: 'actualizar_km',
                            id_vehiculo: id,
                            kilometraje: result.value
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Éxito', response.message, 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error de conexión', 'error');
                        }
                    });
                }
            });
        });

        // Función para procesar formulario
        function procesarFormulario(accion) {
            const datos = {
                accion: accion,
                placa: $('#placa').val(),
                modelo: $('#modelo').val(),
                descripcion: $('#descripcion').val(),
                kilometraje: $('#kilometraje').val(),
                km_mantenimiento: $('#kmMantenimiento').val(),
                cant_combustible: $('#combustible').val(),
                tipo_combustible: $('#tipoCombustible').val(),
                status: $('#status').val(),
                tipo: $('#tipoVehiculo').val(),
                capacidad: $('#capacidad').val(),
                frecuencia: $('#frecuencia').val(),
                fecha_mant: $('#fechaMantenimiento').val(),
                altura: $('#altura').val(),
                longitud: $('#longitud').val(),
                ancho: $('#ancho').val()
            };
            
            if (accion === 'actualizar') {
                datos.id = $('#idVehiculo').val();
            }
            
            $.ajax({
                url: '',
                type: 'POST',
                data: datos,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error de conexión', 'error');
                }
            });
        }

        // Botones de acción rápida
        $('#btnMantenimientoUrgente').on('click', function() {
            $('#filtroMantenimiento').val('urgente').trigger('change');
            Swal.fire({
                title: 'Vehículos con Mantenimiento Urgente',
                text: 'Se han filtrado los vehículos que requieren mantenimiento urgente',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
        });

        $('#btnVehiculosDisponibles').on('click', function() {
            $('#filtroStatus').val('En ruta').trigger('change');
            Swal.fire({
                title: 'Vehículos Disponibles',
                text: 'Se han filtrado los vehículos disponibles en ruta',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
        });

        // Evento para ver detalles
        $(document).on('click', '.ver-detalles', function() {
            const id = $(this).data('id');
            const fila = $(this).closest('tr');
            
            const placa = fila.find('td:nth-child(1)').text();
            const modelo = fila.find('td:nth-child(2)').text();
            const descripcion = fila.find('td:nth-child(3)').text();
            const kilometraje = fila.find('td:nth-child(4)').text();
            const combustible = fila.find('td:nth-child(5) small').text();
            const status = fila.find('td:nth-child(6) span').text();
            const tipo = fila.find('td:nth-child(7)').text();
            const capacidad = fila.find('td:nth-child(8)').text();
            
            Swal.fire({
                title: `Detalles del Vehículo - ${placa}`,
                html: `
                    <div class="row text-start">
                        <div class="col-md-6">
                            <p><strong>Placa:</strong> ${placa}</p>
                            <p><strong>Modelo:</strong> ${modelo}</p>
                            <p><strong>Descripción:</strong> ${descripcion}</p>
                            <p><strong>Tipo:</strong> ${tipo}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Kilometraje:</strong> ${kilometraje}</p>
                            <p><strong>Combustible:</strong> ${combustible}</p>
                            <p><strong>Estado:</strong> ${status}</p>
                            <p><strong>Capacidad:</strong> ${capacidad}</p>
                        </div>
                    </div>
                `,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        });

        // Actualizar indicador de combustible en tiempo real
        $(document).on('input', '#combustible', function() {
            $('#combustibleValor').text($(this).val());
        });
    });
    </script>
</body>
</html>