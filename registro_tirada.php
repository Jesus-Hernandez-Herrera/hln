<?php
include "conexion.php";

// Evitar reenvío del formulario
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['tirada_registrada'])) {
    $fecha_inicio = date('Y-m-d H:i:s');
    $id_responsable = $_POST['responsable'];
    $id_maquina = $_POST['maquina'];
    $operadores = $_POST['operadores']; // Array de IDs

    // Crear tirada
    $stmt = $conexion->prepare("INSERT INTO tirada (fecha_inicio, fecha_final, estatus_tirada, id_responsable, id_maquina) VALUES (?, ?, 'En curso', ?, ?)");
    $fecha_final = date('Y-m-d H:i:s', strtotime('+1 day'));
    $stmt->bind_param("ssii", $fecha_inicio, $fecha_final, $id_responsable, $id_maquina);
    $stmt->execute();
    $id_tirada = $stmt->insert_id;

    // Asignar operadores
    $stmt2 = $conexion->prepare("INSERT INTO tirada_operadores (id_tirada, id_operador) VALUES (?, ?)");
    foreach ($operadores as $id_operador) {
        $stmt2->bind_param("ii", $id_tirada, $id_operador);
        $stmt2->execute();
    }

    // Cambiar estatus de la máquina
    $conexion->query("UPDATE maquina SET estado_maquina='En curso' WHERE id_maquina=$id_maquina");

    $_SESSION['tirada_registrada'] = true;
    echo "<script>alert('Tirada registrada correctamente'); location.href='registro_tirada.php';</script>";
    exit;
}
unset($_SESSION['tirada_registrada']);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Registro de Tirada</title>
    <style>
    .box {
        border: 1px solid #ccc;
        padding: 10px;
        margin: 10px;
        min-height: 100px;
    }

    .item {
        padding: 5px;
        border: 1px solid #000;
        margin-bottom: 5px;
        cursor: move;
    }
    </style>
</head>

<body>

    <h2>Registro de Tirada</h2>
    <form method="POST">
        Responsable:
        <select name="responsable" required>
            <?php
            $res = $conexion->query("SELECT id, nombre FROM usuario WHERE rol = 'responsable'");
            while ($r = $res->fetch_assoc())
                echo "<option value='{$r['id']}'>{$r['nombre']}</option>";
            ?>
        </select><br><br>

        Máquina:
        <select name="maquina" required>
            <?php
            $res = $conexion->query("SELECT id_maquina, nombre_maquina FROM maquina WHERE estado_maquina='Sin operar'");
            while ($r = $res->fetch_assoc())
                echo "<option value='{$r['id_maquina']}'>{$r['nombre_maquina']}</option>";
            ?>
        </select><br><br>

        <div style="display: flex;">
            <div class="box" ondrop="drop(event)" ondragover="allowDrop(event)" id="dropzone">
                <strong>Arrastra operadores aquí</strong>
            </div>
            <div class="box">
                <strong>Operadores disponibles</strong>
                <?php
                $query = "SELECT u.id, u.nombre FROM usuario u
                      WHERE rol = 'operador' AND u.id NOT IN (
                          SELECT id_operador FROM tirada_operadores 
                          INNER JOIN tirada t ON t.id_tirada = tirada_operadores.id_tirada
                          WHERE t.estatus_tirada = 'En curso'
                      )";
                $res = $conexion->query($query);
                while ($op = $res->fetch_assoc()) {
                    echo "<div class='item' draggable='true' ondragstart='drag(event)' id='op{$op['id']}'>{$op['nombre']}</div>";
                }
                ?>
            </div>
        </div>

        <input type="hidden" name="operadores[]" id="operadoresData">
        <button type="submit" onclick="setOperadores()">Registrar Tirada</button>
    </form>

    <script>
    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }

    function drop(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        let elem = document.getElementById(data);
        if (!document.getElementById('dropzone').contains(elem)) {
            ev.target.appendChild(elem);
        }
    }

    function setOperadores() {
        let seleccionados = document.querySelectorAll('#dropzone .item');
        let array = [];
        seleccionados.forEach(op => {
            array.push(op.id.replace('op', ''));
        });
        document.getElementById('operadoresData').value = array;
    }
    </script>

</body>

</html>