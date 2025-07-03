<?php
session_start();
include "conexion.php";

// Contar pendientes
$sqlcuentapendi = "SELECT COUNT(persona.id) AS cuantos 
                    FROM persona 
                    JOIN aspirante ON aspirante.idpersona = persona.id 
                    WHERE (aspirante.status = 'Nuevo' OR aspirante.status = 'Volvió a comunicarse' 
                            OR aspirante.status = 'Volvió a comunicarse FV' 
                            OR aspirante.status = 'Nuevo FV') 
                    AND (aspirante.asignadoa = '" . $_SESSION["idasesor"] . "' OR " . $_SESSION["idasesor"] . " IN (8, 9, 11));";
    
$result1 = mysqli_query($conexion, $sqlcuentapendi);
$row1 = mysqli_fetch_assoc($result1);
$asignacion = $row1['cuantos']; // Total de asignaciones

// Contar alertas de aspirantes sin atención en 10 mins
$sql = "SELECT COUNT(DISTINCT aspirante.idpersona) AS alertas
        FROM aspirante
        WHERE DATE_ADD(aspirante.fhregistro, INTERVAL 10 MINUTE) < NOW() 
          AND aspirante.id > 20 
          AND aspirante.status NOT IN ('Nuevo FV', 'Volvió a comunicarse FV')
          AND aspirante.status IN ('Nuevo', 'Volvió a comunicarse');";
    
$result = mysqli_query($conexion, $sql);
$row2 = mysqli_fetch_assoc($result);
$alerta = $row2['alertas']; // Total de alertas

// Lógica para las alertas sonoras
if ($alerta > 0 || $asignacion > 0) { // Solo entra si hay alertas o asignaciones
    if ($alerta > 0) {
        // Si hay alertas, se reproduce el sonido de alerta
        echo '<audio autoplay preload="auto"><source src="sonidos/alerta2.mp3" type="audio/mp3"></audio>';
    } elseif ($asignacion > 0) {
        // Si no hay alertas pero hay asignaciones, se reproduce el sonido de asignación
        echo '<audio autoplay preload="auto"><source src="sonidos/ding.mp3" type="audio/mp3"></audio>';
    }
}
?>