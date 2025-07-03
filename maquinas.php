<?php
include "headeradm.php";
include 'datatable/datateibol.php';

// Detectar si se recibe un POST vía AJAX para insertar
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['llenodatosmaquina'])) {
    // Obtener datos del POST
    $nombre_maquina = $_POST['nombre_insert'];
    $modelo = $_POST['modelo_insert'];
    $serial = $_POST['serial_insert'];
    $tipodeproduccion = $_POST['tipodeproduccion_insert'];
    $capacidad = $_POST['capacidad_insert'];
    $unidadmedidacap = $_POST['unidadmedidacap_insert'];
    $descripcion = $_POST['descripcion_insert'];
    $horas_operacion = $_POST['horasoperacion_insert'];
    $frecuenciamantdias_insert = $_POST['frecuenciamantdias_insert'];
    $frecuenciamanthorasuso_insert = $_POST['frecuenciamanthorasuso_insert'];
    $fechaultimomantenimiento_insert = $_POST['fechaultimomantenimiento_insert'];
    $horasusoultimomantenimiento_insert = $_POST['horasusoultimomantenimiento_insert'];
    // Inserción en base de datos
    $stmt = $conexion->prepare("INSERT INTO maquina 
    (nombre_maquina, modelo, serial, tipodeproduccion, capacidad, unidadmedidacap, descripcion, horas_operacion, frecuenciamant_dias, frecuenciamant_horasuso, fecha_ultimomantenimiento, horasuso_ultimomantenimiento) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssssssssss",
        $nombre_maquina,
        $modelo,
        $serial,
        $tipodeproduccion,
        $capacidad,
        $unidadmedidacap,
        $descripcion,
        $horas_operacion,
        $frecuenciamantdias_insert,
        $frecuenciamanthorasuso_insert,
        $fechaultimomantenimiento_insert,
        $horasusoultimomantenimiento_insert
    );
    $stmt->execute();
    $stmt->close();
    // Mensaje de éxito y redirección
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Máquina registrada con éxito.',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'maquinas.php';
            }
        });
    </script>";
}

// Obtener listado de máquinas
$sql = "SELECT * FROM maquina";
$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Listado de Máquinas</title>
</head>

<body>
    <center>
        <h2
            style="font-size: 2rem; background-color: #0088ff; border-radius: 5px; width: fit-content; padding: 10px 35px 10px 35px; color: white;">
            Listado de maquinaria</h2>
    </center>
    <div style="text-align: right">
        <button type="button" class="btn btn-success " data-bs-toggle="modal" data-bs-target="#modalForm">
            Registrar nueva maquina
        </button>
    </div>

    <!-- Tabla de listado -->
    <table id="exampleXXX">
        <thead>
            <tr>
                <th>ID</th>
                <th>Estatus</th>
                <th>Nombre</th>
                <th>Modelo</th>
                <th>Serial</th>
                <th>Tipo de Producción</th>
                <th>Capacidad</th>
                <th>Unidad Medida</th>
                <th>Descripción</th>
                <th>Horas Operación</th>
                <th>Frec. Mantenimiento (días)</th>
                <th>Frec. Mantenimiento (horas)</th>
                <th>Fecha Último Mantenimiento</th>
                <th>Horas Último Mantenimiento</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id_maquinaria']; ?></td>
                        <td><?php echo $row['estado_maquina']; ?></td>
                        <td><?php echo $row['nombre_maquina']; ?></td>
                        <td><?php echo $row['modelo']; ?></td>
                        <td><?php echo $row['serial']; ?></td>
                        <td><?php echo $row['tipodeproduccion']; ?></td>
                        <td><?php echo $row['capacidad']; ?></td>
                        <td><?php echo $row['unidadmedidacap']; ?></td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><?php echo $row['horas_operacion']; ?></td>
                        <td><?php echo $row['frecuenciamant_dias']; ?></td>
                        <td><?php echo $row['frecuenciamant_horasuso']; ?></td>
                        <td><?php echo $row['fecha_ultimomantenimiento']; ?></td>
                        <td><?php echo $row['horasuso_ultimomantenimiento']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="13">No hay máquinas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!--//Modal para registrar una nueva maquina-->

    <div class="modal fade modalito" id="modalForm" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Registro de maquinaria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-12">
                        <form class="form-contact contact_form"
                            action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                            accept-charset="utf-8">
                            <div class="row">
                                <!-- >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> -->
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Nombre:<input class="form-control  hide-on-focus" name="nombre_insert"
                                            type="text" placeholder="* Ingresa el Nombre " title="* Ingresa el Nombre "
                                            required="required">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Modelo:<input class="form-control  hide-on-focus" name="modelo_insert"
                                            type="text" placeholder="* Ingresa el Modelo " title="* Ingresa el Modelo "
                                            required="required">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Serial:<input class="form-control  hide-on-focus" name="serial_insert"
                                            type="text" placeholder="Ingresa el Serial " title="Ingresa el Serial "
                                            required="required">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Tipo de produccion:
                                        <select class="form-control" name="tipodeproduccion_insert">
                                            <option value="Cubos">Cubos</option>
                                            <option value="Barras">Barras</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Capacidad:<input class="form-control  hide-on-focus" name="capacidad_insert"
                                            type="number" placeholder="Ingresa la Capacidad "
                                            title="Ingresa la Capacidad " required="required">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Unidad medida de capacidad:
                                        <select class="form-control" name="unidadmedidacap_insert">
                                            <option value="Toneladas">Toneladas</option>
                                            <option value="Kilogramos">Kilogramos</option>
                                            <option value="Libras">Libras</option>
                                            <option value="Litros">Litros</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        Descripcion:<input class="form-control  hide-on-focus" name="descripcion_insert"
                                            type="text" placeholder="Ingresa la Descripcion "
                                            title="Ingresa la Descripcion " required="required">
                                    </div>
                                </div>
                                <div class="col-sm-12 divider" style="text-align: center;background-color: #cfe3ff;">
                                    Información para sus
                                    mantenimientos</div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Horas de operación actuales:<input class="form-control  hide-on-focus"
                                            name="horasoperacion_insert" type="number"
                                            placeholder="* Ingresa la horas de operación"
                                            title="* Ingresa las horas de operación" required="required">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Frecuencia de mantenimiento en dias:<input class="form-control  hide-on-focus"
                                            name="frecuenciamantdias_insert" type="number"
                                            placeholder="* Frecuencia de mantenimiento en dias"
                                            title="* Frecuencia de mantenimiento en dias" required="required">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Frecuencia mantenimiento en horas de uso:<input
                                            class="form-control  hide-on-focus" name="frecuenciamanthorasuso_insert"
                                            type="number" placeholder="* Frecuencia mantenimiento en horas de uso"
                                            title="* Frecuencia mantenimiento en horas de uso">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Fecha de ultimo mantenimiento:<input class="form-control  hide-on-focus"
                                            name="fechaultimomantenimiento_insert" type="date"
                                            placeholder="* Fecha de ultimo mantenimiento"
                                            title="* Fecha de ultimo mantenimiento">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        Horas de uso de ultimo mantenimiento:<input class="form-control  hide-on-focus"
                                            name="horasusoultimomantenimiento_insert" type="number"
                                            placeholder="* Horas de uso de ultimo mantenimiento"
                                            title="* Horas de uso de ultimo mantenimiento">
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="form-group mt-3" align="center">
                        <button type="submit" name="llenodatosmaquina"
                            style="background-color: #4CAF50; border: 1px solid black; color: white; padding: 16px 32px; text-decoration: none; margin: 4px 2px; border-radius: 5px; cursor: pointer;">Registrar
                            maquina</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<?php
include_once "footer.php";
?>