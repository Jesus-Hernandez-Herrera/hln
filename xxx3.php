<?php
include "conexion.php";
date_default_timezone_set("America/Mexico_City");
setlocale(LC_TIME, 'spanish');
setlocale(LC_ALL, 'es_ES');
$date = new DateTime();
$palqry = date_format($date, 'Y-m-d H:i:s');

// Adjusted query
$sqladv = "SELECT aspirante.idpersona aspid 
FROM persona 
JOIN aspirante ON aspirante.idpersona = persona.id 
WHERE (
        (aspirante.asignadoa = 8 AND (aspirante.status = 'Nuevo' OR aspirante.status = 'Volvió a comunicarse')) 
        OR (DATE_ADD(aspirante.fhregistro, INTERVAL 10 Minute) < '$palqry' AND aspirante.id > 20)
    )
AND (aspirante.id > 20 
    AND (
        aspirante.status = 'Nuevo' 
        OR (aspirante.status = 'Nuevo' AND aspirante.asignadoa = 8) 
        OR (aspirante.status = 'Volvió a comunicarse' AND aspirante.asignadoa = 11)
    )
) 
GROUP BY aspid;";

$resultadv = mysqli_query($conexion, $sqladv);
if (!$resultadv) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

$countadv = mysqli_num_rows($resultadv);
echo $countadv;

?>