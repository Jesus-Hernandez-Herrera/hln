<?php
include "headeradm.php";
include 'datatable/datateibol.php';

// Iniciar la lógica para el ciclo de maquinaria
if (isset($_POST['regciclo_maquinaria'])) {
    $idmaquinaria_inputReg = $_POST['idmaquinaria_inputReg'];
    $nombre_maquina_inputReg = $_POST['nombre_maquina_inputReg'];
    $tipodeproduccion_inputReg = $_POST['tipodeproduccion_inputReg'];
    
    // Obtener responsables activos que no estén asignados a ciclos activos
    $responsables_qry = "SELECT u.id, u.nombre, u.appaterno, u.apmaterno 
                         FROM usuario u 
                         WHERE u.rol='Responsable producción' 
                         AND u.status_usuario='Activo' 
                         AND u.id NOT IN (
                             SELECT cp.id_responsable 
                             FROM cicloproduccion cp 
                             WHERE cp.horafin_cp = '' OR cp.horafin_cp IS NULL
                         )";
    $responsables_result = mysqli_query($conexion, $responsables_qry);
    
    // Obtener operadores activos que no estén asignados a ciclos activos
    $operadores_qry = "SELECT u.id, u.nombre, u.appaterno, u.apmaterno 
                       FROM usuario u 
                       WHERE u.rol='Cubero' 
                       AND u.status_usuario='Activo' 
                       AND u.id NOT IN (
                           SELECT cpo.id_operador 
                           FROM cicloproduccion_operador cpo
                           JOIN cicloproduccion cp ON cp.id_cp = cpo.id_cicloproduccion
                           WHERE cp.horafin_cp = '' OR cp.horafin_cp IS NULL
                       )";
    $operadores_result = mysqli_query($conexion, $operadores_qry);
?>

<!-- Modal para asignar responsables y operadores -->
<div class="modal fade modalito" id="modalFormRegCiclo" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Designar responsable del ciclo de producción y operadores
                    de la máquina: <?php echo $nombre_maquina_inputReg . ' - ' . $tipodeproduccion_inputReg; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="form-contact contact_form" action="" method="post" id="asignacionForm">
                    <input type="hidden" name="idmaquinaria_inputReg" value="<?php echo $idmaquinaria_inputReg; ?>">
                    <input type="hidden" name="nombre_maquina_inputReg" value="<?php echo $nombre_maquina_inputReg; ?>">
                    <input type="hidden" name="tipodeproduccion_inputReg"
                        value="<?php echo $tipodeproduccion_inputReg; ?>">

                    <!-- Lista responsables -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <h5>Responsables disponibles</h5>
                            <div class="alert alert-info">
                                Arrastre un responsable al área de asignados para designarlo como responsable del ciclo.
                                Solo puede asignar un responsable.
                            </div>
                            <ul id="responsablesDisponibles" class="list-group dropzone"
                                style="min-height:150px; border:1px solid #ccc; padding:10px; overflow-y:auto;">
                                <?php
                                if (mysqli_num_rows($responsables_result) > 0) {
                                    while($rowResp = mysqli_fetch_assoc($responsables_result)){
                                        echo "<li class='list-group-item draggable' draggable='true' data-id='{$rowResp['id']}' data-type='responsable'>
                                                {$rowResp['nombre']} {$rowResp['appaterno']} {$rowResp['apmaterno']}
                                            </li>";
                                    }
                                } else {
                                    echo "<li class='list-group-item text-danger'>No hay responsables disponibles</li>";
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="col-6">
                            <h5>Responsable asignado</h5>
                            <div class="alert alert-warning">
                                Solo se puede asignar un responsable por ciclo de producción.
                            </div>
                            <ul id="responsablesAsignados" class="list-group dropzone"
                                style="min-height:150px; border:1px solid #ccc; padding:10px; overflow-y:auto;">
                                <!-- Aquí se cargará el responsable asignado -->
                            </ul>
                        </div>
                    </div>

                    <!-- Lista operadores -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <h5>Operadores disponibles</h5>
                            <div class="alert alert-info">
                                Arrastre los operadores al área de asignados para incluirlos en el ciclo de producción.
                            </div>
                            <ul id="operadoresDisponibles" class="list-group dropzone"
                                style="min-height:150px; border:1px solid #ccc; padding:10px; overflow-y:auto;">
                                <?php
                                if (mysqli_num_rows($operadores_result) > 0) {
                                    while($rowOp = mysqli_fetch_assoc($operadores_result)){
                                        echo "<li class='list-group-item draggable' draggable='true' data-id='{$rowOp['id']}' data-type='operador'>
                                                {$rowOp['nombre']} {$rowOp['appaterno']} {$rowOp['apmaterno']}
                                            </li>";
                                    }
                                } else {
                                    echo "<li class='list-group-item text-danger'>No hay operadores disponibles</li>";
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="col-6">
                            <h5>Operadores asignados</h5>
                            <div class="alert alert-warning">
                                Puede asignar múltiples operadores al ciclo de producción.
                            </div>
                            <ul id="operadoresAsignados" class="list-group dropzone"
                                style="min-height:150px; border:1px solid #ccc; padding:10px; overflow-y:auto;">
                                <!-- Aquí se cargarán los operadores asignados -->
                            </ul>
                        </div>
                    </div>

                    <!-- Botón de submit -->
                    <div class="text-center">
                        <button type="submit" name="regciclo_maquinaria_insert" class="btn btn-primary"
                            id="btnGuardarCiclo">
                            Guardar asignaciones
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script para drag-and-drop -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar el modal automáticamente
    var myModal = new bootstrap.Modal(document.getElementById('modalFormRegCiclo'));
    myModal.show();

    // Elementos draggables
    const draggables = document.querySelectorAll('.draggable');
    const dropzones = document.querySelectorAll('.dropzone');

    // Añadir listeners a los elementos arrastrables
    draggables.forEach(draggable => {
        draggable.addEventListener('dragstart', dragStart);
        draggable.addEventListener('dragend', dragEnd);
    });

    // Añadir listeners a las zonas de destino
    dropzones.forEach(dropzone => {
        dropzone.addEventListener('dragover', dragOver);
        dropzone.addEventListener('dragleave', dragLeave);
        dropzone.addEventListener('drop', drop);
    });

    // Funciones para el drag and drop
    function dragStart(e) {
        e.dataTransfer.setData('text/plain', e.target.dataset.id);
        e.dataTransfer.setData('sourceId', e.target.parentElement.id);
        e.dataTransfer.setData('itemType', e.target.dataset.type);

        setTimeout(() => {
            e.target.classList.add('dragging');
        }, 0);
    }

    function dragEnd(e) {
        e.target.classList.remove('dragging');
    }

    function dragOver(e) {
        e.preventDefault();
        e.currentTarget.classList.add('drag-over');
    }

    function dragLeave(e) {
        e.currentTarget.classList.remove('drag-over');
    }

    function drop(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('drag-over');

        const id = e.dataTransfer.getData('text/plain');
        const sourceId = e.dataTransfer.getData('sourceId');
        const itemType = e.dataTransfer.getData('itemType');
        const sourceElement = document.getElementById(sourceId);
        const item = sourceElement.querySelector(`[data-id="${id}"]`);

        // Si no existe el elemento, salir
        if (!item) return;

        // Manejar restricciones específicas
        if (itemType === 'responsable' && e.currentTarget.id === 'responsablesAsignados') {
            // Solo permitir un responsable
            const existingResponsables = e.currentTarget.querySelectorAll('[data-type="responsable"]');
            if (existingResponsables.length > 0) {
                // Devolver el responsable existente a la lista de disponibles
                document.getElementById('responsablesDisponibles').appendChild(existingResponsables[0]);
            }
            e.currentTarget.appendChild(item);
        } else if (itemType === 'operador' && e.currentTarget.id === 'operadoresAsignados') {
            // Permitir múltiples operadores
            e.currentTarget.appendChild(item);
        } else if (e.currentTarget.id === 'responsablesDisponibles' && itemType === 'responsable') {
            // Devolver responsable a disponibles
            e.currentTarget.appendChild(item);
        } else if (e.currentTarget.id === 'operadoresDisponibles' && itemType === 'operador') {
            // Devolver operador a disponibles
            e.currentTarget.appendChild(item);
        }

        // Actualizar estado del botón de guardar
        checkFormState();
    }

    // Verificar el estado del formulario para habilitar/deshabilitar el botón
    function checkFormState() {
        const btnGuardar = document.getElementById('btnGuardarCiclo');
        const hasResponsable = document.getElementById('responsablesAsignados').children.length > 0;

        if (hasResponsable) {
            btnGuardar.disabled = false;
        } else {
            btnGuardar.disabled = true;
        }
    }

    // Configurar estado inicial del botón
    checkFormState();

    // Antes de enviar, recopilar IDs y crear inputs ocultos
    document.getElementById('asignacionForm').addEventListener('submit', function(e) {
        // Validar que haya al menos un responsable
        const responsablesAsignados = document.getElementById('responsablesAsignados').children;
        if (responsablesAsignados.length === 0) {
            e.preventDefault();
            alert('Debe asignar al menos un responsable al ciclo de producción.');
            return false;
        }

        // Limpiar cualquier input oculto existente
        document.querySelectorAll('input[name="responsable_id"], input[name="operadores[]"]').forEach(
            input => input.remove());

        // Añadir el ID del responsable
        const responsableId = responsablesAsignados[0].dataset.id;
        const inputResponsable = document.createElement('input');
        inputResponsable.type = 'hidden';
        inputResponsable.name = 'responsable_id';
        inputResponsable.value = responsableId;
        this.appendChild(inputResponsable);

        // Recoger operadores
        const operadoresAsignados = document.getElementById('operadoresAsignados').children;
        Array.from(operadoresAsignados).forEach(operador => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'operadores[]';
            input.value = operador.dataset.id;
            this.appendChild(input);
        });
    });
});
</script>
<?php
}
// Fin de la lógica para registrar ciclo de maquinaria

// Procesar el formulario de registro de ciclo
if (isset($_POST["regciclo_maquinaria_insert"])) {
    $idmaquinaria = $_POST['idmaquinaria_inputReg'];
    $responsable_id = $_POST['responsable_id'];
    $operadores = isset($_POST['operadores']) ? $_POST['operadores'] : [];
    
    // Insertar el ciclo de producción
    $horainicio = date('Y-m-d H:i:s');
    $horafin = null;
    $cantidad_producida = 0;
    
    $insertCiclo = "INSERT INTO cicloproduccion (horainicio_cp, horafin_cp, cantidadproducida_cp, id_responsable, id_maquina)
                    VALUES ('$horainicio', NULL, $cantidad_producida, $responsable_id, $idmaquinaria)";
    
    if (mysqli_query($conexion, $insertCiclo)) {
        $idCiclo = mysqli_insert_id($conexion);
        
        // Asignar operadores al ciclo
        foreach ($operadores as $idOp) {
            mysqli_query($conexion, "INSERT INTO cicloproduccion_operador (cantidadproducida_cpo, id_operador, id_cicloproduccion) 
                                    VALUES (0, $idOp, $idCiclo)");
        }
        
        // Actualizar el estado de la máquina a "En operación"
        mysqli_query($conexion, "UPDATE maquina SET estado_maquina = 'En operación' WHERE id_maquinaria = $idmaquinaria");
        
        echo "<div class='alert alert-success'>Ciclo de producción registrado con éxito.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error al registrar el ciclo de producción: " . mysqli_error($conexion) . "</div>";
    }
}

// Mostrar tabla de máquinas
echo "<h2>Máquinas disponibles</h2>";
echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<h3>Máquinas en operación</h3>";
echo "<div class='list-group'>";

// Obtener las máquinas
$qry_maquinas = "SELECT id_maquinaria, nombre_maquina, tipodeproduccion, estado_maquina FROM maquina ORDER BY estado_maquina DESC, nombre_maquina ASC";
$select_maquinas = mysqli_query($conexion, $qry_maquinas) or die(mysqli_error($conexion));

$maquinas_operacion = false;
$maquinas_sin_operar = false;

while ($row = mysqli_fetch_array($select_maquinas)) {
    $id_maquinaria = $row['id_maquinaria'];
    $nombre_maquina = $row['nombre_maquina'];
    $tipodeproduccion = $row['tipodeproduccion'];
    $estado_maquina = $row['estado_maquina']; 
    
    if ($estado_maquina == 'En operación') {
        $maquinas_operacion = true;
        echo "<div class='list-group-item list-group-item-success'>
                <strong>$nombre_maquina</strong> - $tipodeproduccion
                <span class='badge bg-success float-end'>$estado_maquina</span>
              </div>";
    } 
}

if (!$maquinas_operacion) {
    echo "<div class='list-group-item list-group-item-light'>No hay máquinas en operación actualmente.</div>";
}

echo "</div>"; // Fin lista máquinas en operación
echo "</div>"; // Fin columna izquierda

echo "<div class='col-md-6'>";
echo "<h3>Máquinas disponibles</h3>";
echo "<div class='list-group'>";

// Resetear el puntero de resultados
mysqli_data_seek($select_maquinas, 0);

while ($row = mysqli_fetch_array($select_maquinas)) {
    $id_maquinaria = $row['id_maquinaria'];
    $nombre_maquina = $row['nombre_maquina'];
    $tipodeproduccion = $row['tipodeproduccion'];
    $estado_maquina = $row['estado_maquina']; 
    
    if ($estado_maquina != 'En operación') {
        $maquinas_sin_operar = true;
        $color_clase = '';
        switch ($estado_maquina) {
            case 'Sin operar':
                $color_clase = 'list-group-item-light';
                break;
            case 'Mantenimiento':
                $color_clase = 'list-group-item-warning';
                break;
            case 'Fuera de servicio':
                $color_clase = 'list-group-item-danger';
                break;
            default:
                $color_clase = 'list-group-item-secondary';
        }
        
        echo "<div class='list-group-item $color_clase'>
                <strong>$nombre_maquina</strong> - $tipodeproduccion
                <span class='badge bg-secondary float-end'>$estado_maquina</span>";
        
        if ($estado_maquina == 'Sin operar') {
            echo "<form action='ciclodeproduccion.php' method='post' class='mt-2'>
                    <input type='hidden' name='idmaquinaria_inputReg' value='$id_maquinaria'>
                    <input type='hidden' name='tipodeproduccion_inputReg' value='$tipodeproduccion'>
                    <input type='hidden' name='nombre_maquina_inputReg' value='$nombre_maquina'>
                    <button type='submit' name='regciclo_maquinaria' class='btn btn-sm btn-primary'>Crear ciclo de producción</button>
                  </form>";
        }
        
        echo "</div>";
    }
}

if (!$maquinas_sin_operar) {
    echo "<div class='list-group-item list-group-item-light'>Todas las máquinas están en operación.</div>";
}

echo "</div>"; // Fin lista máquinas disponibles
echo "</div>"; // Fin columna derecha
echo "</div>"; // Fin row

// Mostrar ciclos de producción
echo "<h2 class='mt-4'>Ciclos de producción</h2>";
echo "<div class='table-responsive'>";

// Consulta SQL para los ciclos de producción
$queryverciclos = mysqli_query($conexion, "
SELECT
    cp.id_cp,
    cp.horainicio_cp,
    cp.horafin_cp,
    cp.cantidadproducida_cp,
    resp.nombre AS responsable_nombre,
    resp.appaterno AS responsable_appaterno,
    m.nombre_maquina,
    m.id_maquinaria,
    m.tipodeproduccion,
    GROUP_CONCAT(DISTINCT CONCAT(op.nombre, ' ', op.appaterno) SEPARATOR ', ') AS operadores,
    GROUP_CONCAT(CONCAT(COALESCE(op.nombre, ''), ' ', COALESCE(op.appaterno, ''), ': ', cpo.cantidadproducida_cpo) SEPARATOR ', ') AS producciones_individuales
FROM
    cicloproduccion cp
JOIN
    usuario resp ON resp.id = cp.id_responsable
JOIN
    maquina m ON m.id_maquinaria = cp.id_maquina
LEFT JOIN
    cicloproduccion_operador cpo ON cpo.id_cicloproduccion = cp.id_cp
LEFT JOIN
    usuario op ON op.id = cpo.id_operador
GROUP BY
    cp.id_cp, cp.horainicio_cp, cp.horafin_cp, cp.cantidadproducida_cp,
    resp.nombre, resp.appaterno,
    m.nombre_maquina, m.id_maquinaria, m.tipodeproduccion
ORDER BY
    cp.id_cp DESC;
");

echo '<table id="example" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Cantidad Producida</th>
                <th>Tipo de Producción</th>
                <th>Responsable</th>
                <th>Operadores y Producción</th>
                <th>Máquina</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>';

while ($rowverciclos = mysqli_fetch_assoc($queryverciclos)) {
    $estado = empty($rowverciclos['horafin_cp']) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Finalizado</span>';
    
    echo "<tr>
            <td>" . $rowverciclos['id_cp'] . "</td>
            <td>" . $rowverciclos['horainicio_cp'] . "</td>
            <td>" . ($rowverciclos['horafin_cp'] ?: 'En proceso') . "</td>
            <td>" . $rowverciclos['cantidadproducida_cp'] . "</td>
            <td>" . $rowverciclos['tipodeproduccion'] . "</td>
            <td>" . $rowverciclos['responsable_nombre'] . " " . $rowverciclos['responsable_appaterno'] . "</td>
            <td>" . $rowverciclos['producciones_individuales'] . "</td>
            <td>" . $rowverciclos['nombre_maquina'] . "</td>
            <td>" . $estado . "</td>
        </tr>";
}
echo '</tbody></table>';
echo '</div>'; // Fin table-responsive

// Estilos adicionales para el drag and drop
echo '<style>
    .draggable {
        cursor: grab;
    }
    
    .dragging {
        opacity: 0.5;
    }
    
    .drag-over {
        background-color: #e9ecef;
        border: 2px dashed #6c757d;
    }
    
    .dropzone {
        transition: all 0.2s ease;
    }
</style>';

include "footer.php";
?>