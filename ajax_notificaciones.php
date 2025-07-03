<?php
include "bdd.php"; // Incluir conexión a la base de datos
session_start();
date_default_timezone_set("America/Mexico_City");
setlocale(LC_TIME, 'spanish');
setlocale(LC_ALL, 'es_ES');
$data = array(); // Array para almacenar los resultados
$debug = "si"; //no
$conteo_DocsRecha_CC = 0;
$conteo_PagosRechazados_CC = 0;
$conteo_PagosVencidos_CC =0;
$role = 0;
// CONTROL ESCOLAR NOTIFICACIONES inicio >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
// Verificar roles de usuario para las notificaciones de CONTROL ESCOLAR

if (isset($_SESSION["id_roles"]) &&(
    $_SESSION["id_roles"] == 8 || $_SESSION["id_roles"] == 9 || $_SESSION["id_roles"] == 1 || $_SESSION["id_roles"] == 24 ||
    $_SESSION["id_roles"] == 25 || $_SESSION["id_roles"] == 36
)) {
    // Iniciar variables necesarias
    $quienVeDocPendienteRev = "";
    // Lógica de permisos
    if ($_SESSION["id_roles"] == 8) {
        $quienVeDocPendienteRev = " and usuario.idCC = " . $_SESSION["idusuario"] . " ";
    } elseif ($_SESSION["id_roles"] == 24 || $_SESSION["id_roles"] == 25) {
        $quienVeDocPendienteRev = " and programa.nivel not in('Licenciatura')";
    } elseif ($_SESSION["id_roles"] == 36) {
        $quienVeDocPendienteRev = " and programa.nivel in('Licenciatura')";
    }
    // Consulta para obtener la lista de documentos
    // Consulta para obtener la lista de documentos con conteo total
    $sql4 = "SELECT SQL_CALC_FOUND_ROWS
usuario.id idEst,
usuario.nombre nomEst,
usuario.appaterno,
usuario.apmaterno,
docrequerida.nombre nomDoc,
docxestudiante.stat,
programa.nivel
FROM
docxestudiante
JOIN usuario ON usuario.id = docxestudiante.idestudiante
JOIN docrequerida ON docrequerida.id = docxestudiante.idreqdocum
JOIN cartera ON cartera.id_estudiante = usuario.id
JOIN atp_convocatoria ON atp_convocatoria.id = cartera.id_atpc
JOIN areaterminalprograma ON areaterminalprograma.id = atp_convocatoria.id_areaterminalprograma
JOIN programa ON programa.id = areaterminalprograma.id_programa
WHERE
(docxestudiante.statdigital = 'Provisional en revisión' || docxestudiante.statdigital = 'Pendiente de revisión')
AND usuario.status NOT IN('Baja Temporal', 'Baja Definitiva')
" . $quienVeDocPendienteRev . "
LIMIT 20"; // Limitando a 20 registros por ejemplo

    $result4 = mysqli_query($conexion, $sql4);

    // Obtener el conteo total usando FOUND_ROWS
    $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() AS totalCount");
    $countRow = mysqli_fetch_assoc($resultCount);
    $conteo_porrevision = isset($countRow['totalCount']) ? $countRow['totalCount'] : 0;



    $data['conteo_pendientes'] = "<b style='color: " . ($conteo_porrevision >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_porrevision . "</b>";
    $data['datos_estud_pendientes'] = '';
    $num = 1;
    while ($row4 = mysqli_fetch_array($result4)) {
        $data['datos_estud_pendientes'] .= '
<form class="dropdown-item" action="validardocCE.php" method="post">
<input type="submit" name="validarDocCE" class="alert alert-warning dropdown-item" style="text-wrap: wrap; font-size: 15px;" value="' . $num . '.- ' . $row4["nomEst"] . ' ' . $row4["appaterno"] . ' ' . $row4["apmaterno"] . ' - ' . $row4["nomDoc"] . ' / Estatus: ' . $row4["stat"] . '">
<input type="hidden" name="idUsuDocu" value="' . $row4["idEst"] . '">
</form>';
        $num++;
    }
    $conteo_Matrics = 0;
    //Peligro hay estudiantes que ya dieron su documentacion y no se inicio su proceso de matriculacion ante la SEP
    $sqlCENMT = "SELECT *
    FROM (
        SELECT
            d.idestudiante,
            COUNT(d.id) AS documentosnecesarios,
            COALESCE(aprobados.cantidad_aprobados, 0) AS documentosaprobados,
            TIMESTAMPDIFF(MONTH, u.fregistro, CURDATE()) AS meses_registro,
            u.nombre,
            u.appaterno,
            u.apmaterno,
            u.correo_personal,
            u.status as estatusnobajas ,
            'Peligro hay estudiantes que ya dieron su documentacion y no se inicio su proceso de matriculacion ante la SEP' as observ
        FROM
            docxestudiante d
        LEFT JOIN (
            SELECT
                idestudiante,
                COUNT(id) AS cantidad_aprobados
            FROM
                docxestudiante
            WHERE
                stat = 'Aprobado' AND stat != 'No aplica' -- Excluir documentos con status 'No aplica'
            GROUP BY
                idestudiante
        ) AS aprobados ON d.idestudiante = aprobados.idestudiante
        JOIN usuario u ON d.idestudiante = u.id
        WHERE
            d.stat != 'No aplica'
        GROUP BY
            d.idestudiante, u.fregistro
    ) AS datos
    HAVING meses_registro >= 6 AND documentosaprobados>=documentosnecesarios and estatusnobajas not in('Baja Temporal','Baja Definitiva', 'Egresado', 'Titulado')
    ";

    $resultCENMT = mysqli_query($conexion, $sqlCENMT);
    $conteo_CENMT = mysqli_num_rows($resultCENMT);

    $conteo_Matrics += $conteo_CENMT;
    $data['conteo_CE_NMT'] = "<b style='color: " . ($conteo_CENMT >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_CENMT . "</b>";


    //El estudiante no ha dado toda su documentacion, su proceso de matriculación no puede iniciar ante la SEP
    $sqlCEI = "SELECT *
    FROM (
        SELECT
            d.idestudiante,
            COUNT(d.id) AS documentosnecesarios,
            COALESCE(aprobados.cantidad_aprobados, 0) AS documentosaprobados,
            TIMESTAMPDIFF(MONTH, u.fregistro, CURDATE()) AS meses_registro,
            u.nombre,
            u.appaterno,
            u.apmaterno,
            u.correo_personal,
            u.status as estatusnobajas ,
            'El estudiante no ha dado toda su documentacion, su proceso de matriculación no puede iniciar ante la SEP' as observ
        FROM
            docxestudiante d
        LEFT JOIN (
            SELECT
                idestudiante,
                COUNT(id) AS cantidad_aprobados
            FROM
                docxestudiante
            WHERE
                stat = 'Aprobado' AND stat != 'No aplica'
            GROUP BY
                idestudiante
        ) AS aprobados ON d.idestudiante = aprobados.idestudiante
        JOIN usuario u ON d.idestudiante = u.id
        WHERE
            d.stat != 'No aplica'
        GROUP BY
            d.idestudiante, u.fregistro
    ) AS datos
    HAVING documentosaprobados<documentosnecesarios and estatusnobajas not in('Baja Temporal','Baja Definitiva', 'Egresado', 'Titulado')";

    $resultCEI = mysqli_query($conexion, $sqlCEI);
    $conteo_CEI = mysqli_num_rows($resultCEI);

    $conteo_Matrics += $conteo_CEI;
    $data['conteo_CE_EI'] = "<b style='color: " . ($conteo_CEI >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_CEI . "</b>";

    //Estos estudiantes son nuevos y estan en proceso de matriculacion ante la SEP
    $sqlCE_NM = "SELECT *
    FROM (
        SELECT
            d.idestudiante,
            COUNT(d.id) AS documentosnecesarios,
            COALESCE(aprobados.cantidad_aprobados, 0) AS documentosaprobados,
            TIMESTAMPDIFF(MONTH, u.fregistro, CURDATE()) AS meses_registro,
            u.nombre,
            u.appaterno,
            u.apmaterno,
            u.correo_personal,
            u.status as estatusnobajas ,
            'Estos estudiantes son nuevos y estan en proceso de matriculacion ante la SEP' as observ
        FROM
            docxestudiante d
        LEFT JOIN (
            SELECT
                idestudiante,
                COUNT(id) AS cantidad_aprobados
            FROM
                docxestudiante
            WHERE
                stat = 'Aprobado' AND stat != 'No aplica' -- Excluir documentos con status 'No aplica'
            GROUP BY
                idestudiante
        ) AS aprobados ON d.idestudiante = aprobados.idestudiante
        JOIN usuario u ON d.idestudiante = u.id
        WHERE
            d.stat != 'No aplica'
        GROUP BY
            d.idestudiante, u.fregistro
    ) AS datos
    HAVING documentosaprobados>=documentosnecesarios && meses_registro<6 and estatusnobajas not in('Baja Temporal','Baja Definitiva', 'Egresado', 'Titulado')
    ";
    $resultCE_NM = mysqli_query($conexion, $sqlCE_NM);
    $conteo_CE_NM = mysqli_num_rows($resultCE_NM);
    $conteo_total_CE = $conteo_CE_NM + $conteo_CEI + $conteo_CENMT + $conteo_porrevision;
    $conteo_Matrics += $conteo_CE_NM;
    $data['conteo_CE_NM'] = "<b style='color: " . ($conteo_CE_NM >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_CE_NM . "</b>";
    $data['conteo_Matrics'] = "<b style='color: " . ($conteo_Matrics >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_Matrics . "</b>";
    $data['conteo_total_CE'] = "<b style='color: " . ($conteo_total_CE >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black") . " display: inline-block;font-size: 10px;'>" . $conteo_total_CE . "</b>";
} else {
    if ($debug == "si") {
        $data['Error_ControlEscolar'] = 'Acceso denegado para control escolar'; // Manejo de acceso denegado
    }
}
// CONTROL ESCOLAR NOTIFICACIONES fin <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// CONTACT CENTER NOTIFICACIONES inicio >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
if (isset($_SESSION["id_roles"]) &&( $_SESSION["id_roles"] == 4 || $_SESSION["id_roles"] == 8 || $_SESSION["id_roles"] == 35 || $_SESSION["id_roles"] == 1 || $_SESSION["id_roles"] == 9)) {
    if ($_SESSION["id_roles"] == 4 || $_SESSION["id_roles"] == 35) { // Estudiante
        $quienVeDocRecha = "AND docxestudiante.idestudiante = " . $_SESSION["idusuario"] . " ";
    } elseif ($_SESSION["id_roles"] == 8 || $_SESSION["id_roles"] == 9) { // Contact
        $quienVeDocRecha = "AND usuario.idCC = " . $_SESSION["idusuario"] . " ";
    } elseif ($_SESSION["id_roles"] == 4 || $_SESSION["id_roles"] == 35) {
        $quienVeDocRecha = "AND usuario.id = " . $_SESSION["idusuario"] . " ";
    } else { // Rol 1
        $quienVeDocRecha = "";
    }
    // Consulta para obtener los documentos rechazados con el conteo total
    $sqlShowDocRechaza = "SELECT SQL_CALC_FOUND_ROWS
    usuario.idCC,
    usuario.id as idUsu,
    usuario.appaterno apEst,
    usuario.apmaterno amEst,
    usuario.nombre nomEst,
    docxestudiante.id docSincar,
    docrequerida.nombre as nomDoc,
    docxestudiante.statdigital,
  cartera.id_atpc,
  programa.nivel
    FROM usuario
    JOIN docxestudiante ON docxestudiante.idestudiante = usuario.id
    JOIN usuario asesor ON asesor.id = usuario.idCC
    JOIN docrequerida ON docrequerida.id = docxestudiante.idreqdocum
    JOIN cartera on cartera.id_estudiante = usuario.id
    JOIN atp_convocatoria ON atp_convocatoria.id = cartera.id_atpc
    JOIN areaterminalprograma ON areaterminalprograma.id = atp_convocatoria.id_areaterminalprograma
    JOIN programa ON programa.id = areaterminalprograma.id_programa
    WHERE docxestudiante.statdigital IN('Rechazado', 'Ilegible')
      AND cartera.status NOT IN(
        'Baja Temporal',
        'Titulado',
        'Baja Definitiva',
        'Egresado',
        'Cambio programa',
        'Cambio convocatoria'
    )
    AND asesor.status = 'Activo' " . $quienVeDocRecha . "
    LIMIT 20";
    $resultsqlShowDocRechazas = mysqli_query($conexion, $sqlShowDocRechaza);
    // Obtener el total de documentos rechazados
    $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_DocsRecha_CC = mysqli_fetch_assoc($resultCount)['total'];
    $data['conteo_DocsRecha_CC'] = "<b style='color: " . ($conteo_DocsRecha_CC >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_DocsRecha_CC . "</b>";
    $data['datos_DocsRecha_CC'] = ' <li class="dropdown-header menuencabezadosty">Documentos rechazados </li> <li class="dropdown-divider"></li>';
    while ($rowpr = mysqli_fetch_assoc($resultsqlShowDocRechazas)) {
        $data['datos_DocsRecha_CC'] .= "<li class='dropdown-item'>
                <a class='dropdown-item' href='verDocumentacionEstudiante.php?idUsuDoc=" . $rowpr['idUsu'] . "&idcaratpc=" . $rowpr['id_atpc'] . "&nivelProg=" . $rowpr['nivel'] . "'>
                <p class='alert alert-danger dropdown-item' style='text-wrap: wrap; font-size:12px'>
                <b class='dropdown-item'>Estudiante: " . $rowpr['nomEst'] . " " . $rowpr['apEst'] . " " . $rowpr['amEst'] . " ase: " . $rowpr['idCC'] . "<br>
                Documento:<br>" . $rowpr['nomDoc'] . "<br>
                Estatus: " . $rowpr['statdigital'] . "<br>
                Da click en este cuadro para revisar.</b>
                </p>
                </a>
                </li>";
    }
    if ($_SESSION["id_roles"] == 8 || $_SESSION["id_roles"] == 9 || $_SESSION["id_roles"] == 1 || $_SESSION["id_roles"] == 4 || $_SESSION["id_roles"] == 35) {
        // Definir la variable para verificación de documentos según el rol
        $quienVeDocPendienteRev = "";
        if ($_SESSION["id_roles"] == 8 || $_SESSION["id_roles"] == 9) {
            $quienVeDocPendienteRev = "AND usuario.idCC=" . $_SESSION["idusuario"] . " ";
        } elseif ($_SESSION["id_roles"] == 4 || $_SESSION["id_roles"] == 35) {
            $quienVeDocRecha = "AND usuario.id = " . $_SESSION["idusuario"] . " ";
        } else {
            $quienVeDocPendienteRev = ""; // Rol 1
        }
        $sqlTotal = "SELECT SQL_CALC_FOUND_ROWS
                                usuario.id AS idEst,
                                usuario.nombre AS nomUsu,
                                usuario.appaterno,
                                usuario.apmaterno,
                                docrequerida.nombre AS nomDoc,
                                docxestudiante.statdigital,
                                DATE_FORMAT(docxestudiante.fechaLimite, '%d-%m-%Y') AS fechaLimite,
                                cartera.id_atpc,
                                programa.nivel
                            FROM docxestudiante
                            JOIN usuario ON usuario.id = docxestudiante.idestudiante
                            JOIN docrequerida ON docrequerida.id = docxestudiante.idreqdocum
                            JOIN cartera on cartera.id_estudiante = usuario.id
                            JOIN atp_convocatoria ON atp_convocatoria.id = cartera.id_atpc
                            JOIN areaterminalprograma ON areaterminalprograma.id = atp_convocatoria.id_areaterminalprograma
                            JOIN programa ON programa.id = areaterminalprograma.id_programa
                            WHERE docxestudiante.statdigital = 'Provisional'
                            AND docxestudiante.fechaLimite <= CURDATE() " . $quienVeDocPendienteRev . " AND cartera.status NOT IN(
        'Baja Temporal',
        'Titulado',
        'Baja Definitiva',
        'Egresado',
        'Cambio programa',
        'Cambio convocatoria'
    ) LIMIT 20";

        // Ejecutar la consulta
        $resultTotal = mysqli_query($conexion, $sqlTotal);
        $numero = 1;
        $data['datos_DocProviExpi_CC'] = '<li class="dropdown-header menuencabezadosty">Documentación provisional expirada</li>';
        $data['datos_DocProviExpi_CC'] .= '<li class="dropdown-divider"></li>'; // Menu nuevo
        while ($row = mysqli_fetch_array($resultTotal)) {
            if ($row["statdigital"] == 'Provisional') {
                $data['datos_DocProviExpi_CC'] .= '<form class="dropdown-item" action="verDocumentacionEstudiante.php" method="post">
                                <input type="submit" class="dropdown-item" name="" style="text-wrap: wrap; font-size: 15px;" value="' . $numero . '.- ' . $row["nomUsu"] . ' ' . $row["appaterno"] . ' ' . $row["apmaterno"] .
                    ' / Documento: ' . $row["nomDoc"] . ' / Estatus: ' . $row["statdigital"] .
                    '. Fecha Expiración: ' . $row["fechaLimite"] . '.">
                                <input type="hidden" name="idUsuDoc" value="' . $row["idEst"] . '" >
                                <input type="hidden" name="idcaratpc" value="' . $row["id_atpc"] . '" >
                                <input type="hidden" name="nivelProg" value="' . $row["nivel"] . '" >
                            </form>';
            }
            $numero++;
        }
        $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
        $conteo_DocProviExpi_CC = mysqli_fetch_assoc($resultCount)['total'];
        $data['conteo_DocProviExpi_CC'] = "<b style='color: " . ($conteo_DocProviExpi_CC >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_DocProviExpi_CC . "</b>";
        // Definición de condiciones de acceso según roles
        $quienVeDocPendienteRev = ($_SESSION["id_roles"] == 8 || $_SESSION["id_roles"] == 9) ? "AND usuario.idCC=" . $_SESSION["idusuario"] . " " : "";
        // Consulta combinada para obtener documentos y contar total con límite de 20
        $sqlCombined = "SELECT SQL_CALC_FOUND_ROWS
                                usuario.id AS idEst,
                                usuario.nombre AS nomUsu,
                                usuario.appaterno AS appaterno,
                                usuario.apmaterno AS apmaterno,
                                docrequerida.nombre AS nomDoc,
                                docxestudiante.statdigital AS statdigital,
                                cartera.id_atpc,
                                programa.nivel
                            FROM
                                docxestudiante
                            JOIN usuario ON usuario.id = docxestudiante.idestudiante
                            JOIN docrequerida ON docrequerida.id = docxestudiante.idreqdocum
                            JOIN cartera ON cartera.id_estudiante = usuario.id
                            JOIN atp_convocatoria ON atp_convocatoria.id = cartera.id_atpc
                            JOIN areaterminalprograma ON areaterminalprograma.id = atp_convocatoria.id_areaterminalprograma
                            JOIN programa ON programa.id = areaterminalprograma.id_programa
                            WHERE
                                docxestudiante.statdigital = 'Sin cargar'
                                  AND cartera.status NOT IN(
                                        'Baja Temporal',
                                        'Titulado',
                                        'Baja Definitiva',
                                        'Egresado',
                                        'Cambio programa',
                                        'Cambio convocatoria'
                                    )
                                AND usuario.status NOT IN ('Baja', 'Baja Definitiva', 'Baja Temporal', 'Inactivo', 'Suspendido', 'Egresado')
                                " . $quienVeDocPendienteRev . "
                            LIMIT 20";

        $resultCombined = mysqli_query($conexion, $sqlCombined);
        $numero = 1;
        // Encabezado y Divisor para documentos sin cargar
        $data['datos_docSincargar_CC'] = '<li class="dropdown-header menuencabezadosty">Documentación sin cargar</li>';
        $data['datos_docSincargar_CC'] .= '<li class="dropdown-divider"></li>';

        // Mostrar los documentos
        while ($row = mysqli_fetch_array($resultCombined)) {
            $data['datos_docSincargar_CC'] .= '<form class="dropdown-item" action="verDocumentacionEstudiante.php" method="post">
                                        <input type="submit" class="dropdown-item" name="" style="text-wrap: wrap; font-size: 15px;" value="' . $numero . '.- ' . $row["nomUsu"] . ' ' . $row["appaterno"] . ' ' . $row["apmaterno"] .
                ' Documento: ' . $row["nomDoc"] . ' Estatus: ' . $row["statdigital"] . '">
                                        <input type="hidden" name="idUsuDoc" value="' . $row["idEst"] . '" >
                                        <input type="hidden" name="idcaratpc" value="' . $row["id_atpc"] . '" >
                                        <input type="hidden" name="nivelProg" value="' . $row["nivel"] . '" >
                                    </form>';
            $numero++;
        }
        $resultCount_PendienteRev = mysqli_query($conexion, "SELECT FOUND_ROWS() as totalCount");
        if ($resultCount) {
            $conteo_docSincargar_CC = mysqli_fetch_assoc($resultCount_PendienteRev)['totalCount'];
        } else {
            $conteo_docSincargar_CC = 0; // Inicializar en 0 si hay algún problema en la consulta
        }
        $data['conteo_docSincargar_CC'] = "<b style='color: " . ($conteo_docSincargar_CC >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_docSincargar_CC . "</b>";

        $quienVePagosVencidos = ($_SESSION["id_roles"] == 8 || $_SESSION["id_roles"] == 9) ? "AND usuario.idCC=" . $_SESSION["idusuario"] . " " : "";

        if ($_SESSION["id_roles"] == 4 || $_SESSION["id_roles"] == 35) {
            $quienVePagosVencidos = "AND usuario.id = " . $_SESSION["idusuario"] . " ";
        }
        $sql_PagosVencidos_CC = "
                    SELECT SQL_CALC_FOUND_ROWS
                        usuario.id as idUsuCartera,
                        usuario.nombre,
                        usuario.appaterno,
                        usuario.apmaterno,
                        DATE_FORMAT(pagos.fechalimite, '%d-%m-%y') AS fechalimite,
                        pagos.statuspago,
                        pagos.id AS idPag,
                        cartera.id_atpc as id_atpc,
                        cartera.id idCarte
                    FROM
                        pagos
                    JOIN carteraperiodo ON carteraperiodo.id = pagos.id_carteraperiodo
                    JOIN cartera ON cartera.id = carteraperiodo.id_cartera
                    JOIN usuario ON usuario.id = cartera.id_estudiante
                    WHERE
                        pagos.fechalimite <= CURDATE()
                        AND pagos.statuspago = 'Sin cargar'
                        " . $quienVePagosVencidos . "
                        AND cartera.status IN('En curso')
                    LIMIT 20
                ";
        $result_PagosVencidos_CC = mysqli_query($conexion, $sql_PagosVencidos_CC);
        $data['datos_pagosVencidos_CC'] = '
                <li class="dropdown-header menuencabezadosty" style="text-align: center;font-weight: bold;text-transform: uppercase;color: #0018ff;">Pagos vencidos</li>
                <li class="dropdown-divider"></li>';
        $numero = 1;
        while ($row_PagosVencidos_CC = mysqli_fetch_array($result_PagosVencidos_CC)) {
            $data['datos_pagosVencidos_CC'] .= '
                    <form class="dropdown-item" action="carteradepagos.php" method="post">
                        <input class="alert alert-warning dropdown-item" type="submit" name="verPagVen_menu"
                        value="' . $numero . '.- ' . $row_PagosVencidos_CC["nombre"] . ' ' . $row_PagosVencidos_CC["appaterno"] . ' ' . $row_PagosVencidos_CC["apmaterno"] .
                ' / Estatus: Pago vencido - Fecha: ' . $row_PagosVencidos_CC["fechalimite"] . '">
                        <input type="hidden" name="idPagVen_vencido_menu" value="' . $row_PagosVencidos_CC["idPag"] . '">
                        <input type="hidden" name="idUsuCartera_vencido_menu" value="' . $row_PagosVencidos_CC["idUsuCartera"] . '">
                        <input type="hidden" name="idCarte_vencido_menu" value="' . $row_PagosVencidos_CC["idCarte"] . '">
                        <input type="hidden" name="id_atpc_vencido_menu" value="' . $row_PagosVencidos_CC["id_atpc"] . '">
                    </form>';
            $numero++;
        }
        $resultCount_Vencidos = mysqli_query($conexion, "SELECT FOUND_ROWS() as totalCount");
        $countRow = mysqli_fetch_assoc($resultCount_Vencidos);
        $conteo_PagosVencidos_CC = isset($countRow['totalCount']) ? $countRow['totalCount'] : 0;
        $data['conteo_PagosVencidos_CC'] = "<b style='color: " . ($conteo_PagosVencidos_CC >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . "; display: inline-block;font-size: 10px;'>" . $conteo_PagosVencidos_CC . "</b>";
        $quienVesolicRestruc = ($_SESSION["id_roles"] == 8) ? "AND usuario.idCC = " . $_SESSION["idusuario"] . " " : "";
        $sql = "
                    SELECT SQL_CALC_FOUND_ROWS
                        historicocartera.*,
                        usuario.id AS idEst,
                        usuario.nombre AS nomUsu,
                        usuario.appaterno AS appaterno,
                        usuario.apmaterno AS apmaterno,
                        cartera.id AS idCarteraEst,
                        cartera.id_atpc AS idATPC
                    FROM historicocartera
                    JOIN cartera ON cartera.id = historicocartera.cve_cartera
                    JOIN usuario ON usuario.id = cartera.id_estudiante
                    WHERE historicocartera.estatus_hc = 'Pendiente'
                    AND cartera.status IN ('En curso', 'En espera')
                    $quienVesolicRestruc
                    LIMIT 20
                ";
        $result = mysqli_query($conexion, $sql);
        $numero = 1;
        $data['datos_RestrucPendi_CC'] = '<li class="dropdown-header menuencabezadosty" style="text-align: center;font-weight: bold;text-transform: uppercase;color: #0018ff;">
                Reestructuración de pagos</li>
                <li class="dropdown-divider"></li>';
        while ($row = mysqli_fetch_array($result)) {
            $data['datos_RestrucPendi_CC'] .= '<form class="dropdown-item" action="carteradepagos.php" method="POST">
                        <input type="submit" class="alert alert-danger dropdown-item" name="submit_reestructurar_menu" style="text-wrap: wrap; font-size: 15px;"
                        value="' . $numero . '.- ' . $row["nomUsu"] . ' ' . $row["appaterno"] . ' ' . $row["apmaterno"] .
                ' Estatus: ' . $row["estatus_hc"] . ' por reestructurar pagos">
                        <input type="hidden" name="idUsu_get_Reestruc" value="' . $row["idEst"] . '" >
                        <input type="hidden" name="idCartera_get_Reestruc" value="' . $row["idCarteraEst"] . '" >
                        <input type="hidden" name="idATPC_get_Reestruc" value="' . $row["idATPC"] . '" >
                    </form>';
            $numero++;
        }
        $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() AS totalCount");
        $countRow = mysqli_fetch_assoc($resultCount);
        $conteo_RestrucPendi_CC = isset($countRow['totalCount']) ? $countRow['totalCount'] : 0;
        $data['conteo_RestrucPendi_CC'] = "<b style='color: " . ($conteo_RestrucPendi_CC >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_RestrucPendi_CC . "</b>";
    }


    if ($_SESSION["id_roles"] == 4 || $_SESSION["id_roles"] == 35) {
        $quienVe = " AND usuario.id=" . $_SESSION["idusuario"];
    }
    if ($_SESSION["id_roles"] == 8 || $_SESSION["id_roles"] == 9) {
        $quienVe = " AND usuario.idCC=" . $_SESSION["idusuario"];
    }
    if ($_SESSION["id_roles"] == 1) {
        $quienVe = "";
    }
    $sqlPagosRechazados = "SELECT SQL_CALC_FOUND_ROWS
            pagos.statuspago,
            pagos.id AS idpago,
            usuario.id AS usuidi,
            usuario.nombre AS nomusua,
            usuario.appaterno AS appaterno,
            usuario.apmaterno AS apmaterno,
            cartera.id_atpc AS idatpccart,
            cartera.id AS idcart,
            cartera.status
        FROM pagos
        JOIN carteraperiodo ON carteraperiodo.id = pagos.id_carteraperiodo
        JOIN cartera ON cartera.id = carteraperiodo.id_cartera
        JOIN usuario ON usuario.id = cartera.id_estudiante
        WHERE (pagos.statuspago = 'Rechazado' OR pagos.statuspago = 'Ilegible')
        AND cartera.status NOT IN ('Baja Temporal', 'Baja Definitiva', 'Cambio programa', 'Cambio convocatoria')
        " . $quienVe . "
        LIMIT 20";
    $resultsqlPagosRechazados = mysqli_query($conexion, $sqlPagosRechazados);
    $data['datos_PagosRechazados_CC'] = '<li class="dropdown-header menuencabezadosty" style="text-align: center;font-weight: bold;text-transform: uppercase;color: #0018ff;">Pagos rechazados</li><li class="dropdown-divider"></li>';
    $num = 1;
    while ($rowpr = mysqli_fetch_assoc($resultsqlPagosRechazados)) {
        $data['datos_PagosRechazados_CC'] .=  "
            <form id='form_pago' action='carteradepagos.php' method='post' class='dropdown-item'>
                <input type='hidden' name='idUsua_pagorechazado_menu' value='" . $rowpr['usuidi'] . "'>
                <input type='hidden' name='idATPC_pagorechazado_menu' value='" . $rowpr['idatpccart'] . "'>
                <input type='hidden' name='idcartera_pagorechazado_menu' value='" . $rowpr['idcart'] . "'>
                <button type='submit' name='submit_pagorechazado_menu' class='btn-link dropdown-item'  style='border: none; background: none; padding: 0; font-size: 12px;'>
                    <p class='alert alert-warning dropdown-item'>
                        <b>" . $num . ".- El pago del estudiante:<br>" . $rowpr['nomusua'] . " " . $rowpr['appaterno'] . " " . $rowpr['apmaterno'] . "<br>
                        Estatus: <b style='background-color:#a4d024;padding: 2px 7px;'>" . $rowpr['statuspago'] . "</b>.<br>
                        Da click en este cuadro para revisar.
                    </b>
                    </p>
                </button>
            </form>
        ";
        $num++;
    }
    $resultCount_PagosRechazados = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_PagosRechazados_CC = mysqli_fetch_assoc($resultCount_PagosRechazados)['total'];
    $data['conteo_PagosRechazados_CC'] = "<b style='color: " . ($conteo_PagosRechazados_CC >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_PagosRechazados_CC . "</b>";

    $conteo_pagos_CC = $conteo_PagosRechazados_CC + $conteo_PagosVencidos_CC;
    $data['conteo_pagos_CC'] = "<b style='color: " . ($conteo_pagos_CC >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_pagos_CC . "</b>";
    $conteo_documentos_CC = $conteo_DocsRecha_CC + $conteo_DocProviExpi_CC + $conteo_docSincargar_CC;
    $data['conteo_documentos_CC'] = "<b style='color: " . ($conteo_documentos_CC >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_documentos_CC . "</b>";
    $conteo_total_CC = $conteo_documentos_CC + $conteo_pagos_CC;
    $data['conteo_total_CC'] = "<b style='color: " . ($conteo_total_CC >= 1 ? "red; animation: spinner-grow 2s linear infinite;" : "black;") . " display: inline-block;font-size: 10px;'>" . $conteo_total_CC . "</b>";
} else {
    if ($debug == "si") {
        $data['Error_ContactCenter'] = 'Acceso denegado para contact center'; // Manejo de acceso denegado

    }
}
// CONTACT CENTER NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// TICKETS NOTIFICACIONES inicio >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
if (isset($_SESSION["id_roles"]) &&( $_SESSION["id_roles"] != "" && isset($_SESSION["id_roles"]))) {
    $quienVe_Tickets = '';
    $quienVe_Capacitaciones = '';
    if ($_SESSION["id_roles"] != 1) {
        $quienVe_Tickets = " AND t.idusuReg = " . $_SESSION["idusuario"];
        $quienVe_Capacitaciones = " and capacitacionusuario.id_usucapaci=" . $_SESSION["idusuario"];
    }
    $sql_TicketSinvalorar = "
        SELECT SQL_CALC_FOUND_ROWS
            t.estatus,
            u.nombre,
            r.nombre_rol
        FROM ticket t
        JOIN usuario u ON u.id = t.idusuReg
        JOIN rolesusuario ru ON ru.id_usuario = u.id
        JOIN roles r ON r.id = ru.id_roles
        WHERE t.estatus = 'Completado' " . $quienVe_Tickets;
    $result_TicketSinvalorar_GENERAL = mysqli_query($conexion, $sql_TicketSinvalorar);
    $resultCount_TicketSinvalorar = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_TicketSinvalorar_GENERAL = mysqli_fetch_assoc($resultCount_TicketSinvalorar)['total'];
    $data['conteo_TicketSinvalorar_GENERAL'] = "<b style='color: " . ($conteo_TicketSinvalorar_GENERAL >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_TicketSinvalorar_GENERAL . "</b>";
    $data['datos_TicketSinvalorar_GENERAL'] = '
            <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Tickets sin valorar</li>
            <li class="dropdown-divider"></li>
        ';
    $num = 1;
    $result_TicketSinvalorar_GENERAL_LIMITED = mysqli_query($conexion, $sql_TicketSinvalorar . " LIMIT 20");
    while ($row4 = mysqli_fetch_assoc($result_TicketSinvalorar_GENERAL_LIMITED)) {
        $idEst = isset($row4["idEst"]) ? $row4["idEst"] : 'undefined';
        $data['datos_TicketSinvalorar_GENERAL'] .= "
                <form class='dropdown-item' action='listadoTicketProc.php' method='post'>
                    <input type='submit' name='validarDocCE' class='alert alert-warning dropdown-item' style='text-wrap: wrap; font-size: 15px;' value='" . $num . ".- " . $row4["nombre"] . " / Rol: " . $row4["nombre_rol"] . "'>
                    <input type='hidden' name='idUsuDocu' value='" . $idEst . "'>
                </form>
            ";
        $num++;
    }

    $sql_TicketNuevo_GENERAL = "
        SELECT SQL_CALC_FOUND_ROWS
            t.estatus,
            u.nombre,
            r.nombre_rol
        FROM ticket t
        JOIN usuario u ON u.id = t.idusuReg
        JOIN rolesusuario ru ON ru.id_usuario = u.id
        JOIN roles r ON r.id = ru.id_roles
        WHERE t.estatus = 'Nuevo' " . $quienVe_Tickets;
    $result_TicketNuevo_GENERAL = mysqli_query($conexion, $sql_TicketNuevo_GENERAL);
    $resultCount_TicketNuevo_GENERAL = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_TicketNuevo_GENERAL = mysqli_fetch_assoc($resultCount_TicketNuevo_GENERAL)['total'];
    $data['conteo_TicketNuevo_GENERAL'] = "<b style='color: " . ($conteo_TicketNuevo_GENERAL >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_TicketNuevo_GENERAL . "</b>";
    $data['datos_TicketNuevo_GENERAL'] = '
            <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Tickets nuevos</li>
            <li class="dropdown-divider"></li>
        ';
    $num = 1;
    $result_TicketNuevo_GENERAL_LIMITED = mysqli_query($conexion, $sql_TicketNuevo_GENERAL . " LIMIT 20");
    while ($row4 = mysqli_fetch_assoc($result_TicketNuevo_GENERAL_LIMITED)) {
        $idEst = isset($row4["idEst"]) ? $row4["idEst"] : 'undefined';
        $data['datos_TicketNuevo_GENERAL'] .= "
                <form class='dropdown-item' action='listadoTicketProc.php' method='post'>
                    <input type='submit' name='validarDocCE' class='alert alert-warning dropdown-item' style='text-wrap: wrap; font-size: 15px;'
                    value='" . $num . ".- " . $row4["nombre"] . " / Rol: " . $row4["nombre_rol"] . "'>
                    <input type='hidden' name='idUsuDocu' value='" . $idEst . "'>
                </form>
            ";
        $num++;
    }

    $sql_TicketRechazado_GENERAL = "
        SELECT SQL_CALC_FOUND_ROWS
            t.estatus,
            u.nombre,
            r.nombre_rol
        FROM ticket t
        JOIN usuario u ON u.id = t.idusuReg
        JOIN rolesusuario ru ON ru.id_usuario = u.id
        JOIN roles r ON r.id = ru.id_roles
        WHERE t.estatus = 'Rechazado' and t.obser!='' " . $quienVe_Tickets;
    $result_TicketRechazado_GENERAL = mysqli_query($conexion, $sql_TicketRechazado_GENERAL);
    $resultCount_TicketRechazado_GENERAL = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_TicketRechazado_GENERAL = mysqli_fetch_assoc($resultCount_TicketRechazado_GENERAL)['total'];
    $data['conteo_TicketRechazado_GENERAL'] = "<b style='color: " . ($conteo_TicketRechazado_GENERAL >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_TicketRechazado_GENERAL . "</b>";
    $data['datos_TicketRechazado_GENERAL'] = '
            <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Tickets rechazados</li>
            <li class="dropdown-divider"></li>
        ';
    $num = 1;
    $result_TicketRechazado_GENERAL_LIMITED = mysqli_query($conexion, $sql_TicketRechazado_GENERAL . " LIMIT 20");
    while ($row4 = mysqli_fetch_assoc($result_TicketRechazado_GENERAL_LIMITED)) {
        $data['datos_TicketRechazado_GENERAL'] .= "
                <form class='dropdown-item' action='listadoTicketProc.php' method='post'>
                    <input type='submit' name='validarDocCE' class='alert alert-warning dropdown-item' style='text-wrap: wrap; font-size: 15px;' value='" . $num . ".- " . $row4["nombre"] . " / Rol: " . $row4["nombre_rol"] . "'>
                    <input type='hidden' name='idUsuDocu' value='" . $row4["idEst"] . "'>
                </form>
            ";
        $num++;
    }
    $sql_capacitacionNoValorada_GENERAL = "SELECT capacitacionusuario.id, usuario.nombre nomCapacitado,roles.nombre_rol nomDepartamento, capacitacion.comentarioSis FROM capacitacionusuario
        join capacitacion on capacitacion.id= capacitacionusuario.id_capacitacion
        join usuario on usuario.id =capacitacionusuario.id_usucapaci
        join rolesusuario on rolesusuario.id_usuario= usuario.id
        join roles on roles.id= rolesusuario.id_roles
        WHERE
            capacitacionusuario.estatus = 'Listo'" . $quienVe_Capacitaciones;
    $result_capacitacionNoValorada_GENERAL = mysqli_query($conexion, $sql_capacitacionNoValorada_GENERAL);
    $resultCount_capacitacionNoValorada_GENERAL = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_capacitacionNoValorada_GENERAL = mysqli_fetch_assoc($resultCount_capacitacionNoValorada_GENERAL)['total'];
    $data['conteo_capacitacionNoValorada_GENERAL'] = "<b style='color: " . ($conteo_capacitacionNoValorada_GENERAL >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_capacitacionNoValorada_GENERAL . "</b>";
    $data['datos_capacitacionNoValorada_GENERAL'] = '
            <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Capacitaciones no valoradas</li>
            <li class="dropdown-divider"></li>
        ';
    $num = 1;
    $result_capacitacionNoValorada_GENERAL_LIMITED = mysqli_query($conexion, $sql_capacitacionNoValorada_GENERAL . " LIMIT 20");
    while ($rowcapacitacionNoValorada = mysqli_fetch_assoc($result_capacitacionNoValorada_GENERAL_LIMITED)) {
        $data['datos_capacitacionNoValorada_GENERAL'] .= "
                <form class='dropdown-item' action='calificarCapacitacion.php' method='post'>
                    <input type='submit' name='validarDocCE' class='alert alert-warning dropdown-item' style='text-wrap: wrap; font-size: 15px;' value='
                    " . $num . ".- " . $rowcapacitacionNoValorada["nomCapacitado"] . " / Rol: " . $rowcapacitacionNoValorada["nomDepartamento"] . " /Comentario: " . $rowcapacitacionNoValorada["comentarioSis"] . "'>
                    <input type='hidden' name='idUsuDocu' value='" . $rowcapacitacionNoValorada["id"] . "'>
                </form>
            ";
        $num++;
    }



//ACTIVIDADES V2 NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

if ($_SESSION['id_roles'] == '1') {//admin
    $quienVe_Actividades_nuevas = "";
} else {
    $departamento = mysqli_real_escape_string($conexion, $_SESSION['departamento']);
    $quienVe_Actividades_nuevas = " AND aa.areas_nombres = '$departamento' ";
}
    $sql_nuevas_actividades = "SELECT 
        a.id,
        a.tipo,
        a.fecha_registro,
        COALESCE(a.desglose, a.nombre) AS nombre_o_desglose,
        a.status,
        CONCAT(
            IFNULL(
                GROUP_CONCAT(DISTINCT aa.areas_nombres SEPARATOR ', '),
                'No hay departamentos asignados'
            )
        ) AS nombres_departamentos
    FROM tb_actividades a
    LEFT JOIN tb_actividad_gestor ag ON a.id = ag.actividad_id 
    LEFT JOIN tb_actividad_area aa ON aa.actividad_id = a.id 
    WHERE ag.gestor_rolusu_id IS NULL OR a.status = 'Nuevo'
    " . $quienVe_Actividades_nuevas . "
    GROUP BY a.id  
    ORDER BY a.id
    ";
    $data['conteo_nuevas_actividades'] ="";
    $data['datos_nuevas_actividades'] ="";
    $result_actividades_nuevas = mysqli_query($conexion, $sql_nuevas_actividades);
    $resultCount_actividades_nuevas = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_nuevas_actividades = mysqli_fetch_assoc($resultCount_actividades_nuevas)['total'];
    $data['conteo_nuevas_actividades'] = "<b style='color: " . ($conteo_nuevas_actividades >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_nuevas_actividades . "</b>";
    $data['datos_nuevas_actividades'] = '
            <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Actividades nuevas</li>
            <li class="dropdown-divider"></li>
        ';
    $num = 1;
    $result_actividades_nuevas_LIMITED = mysqli_query($conexion, $sql_nuevas_actividades . " LIMIT 20");
    while ($row_AN = mysqli_fetch_assoc($result_actividades_nuevas_LIMITED)) {
        $data['datos_nuevas_actividades'] .= "
        <a href='centroTickets_V2.php?Id_Act=" . $row_AN["id"] . "' class='dropdown-item' style=''>
            <div style='font-size: 14px; color:rgb(0, 0, 0);'>" . $num . ".- <b>" . $row_AN["tipo"] . "</b>: " . $row_AN["nombre_o_desglose"] . " </div>
            <div style='font-size: 12px; color:rgb(18, 83, 143);'>Departamentos: " . $row_AN["nombres_departamentos"] . " </div>
            <div style='font-size: 12px; color:rgb(36, 161, 78);'>Status: " . $row_AN["status"] . "</div>
            <div style='font-size: 12px; color:rgb(187, 105, 38);'>Fecha de registro: " . $row_AN["fecha_registro"] . "</div>
        </a>";
        $num++;
    }

    $sql_quienve_sinvalidar_actividades="";
    if ($_SESSION['id_roles'] == '1') {
        $quienVe_sinvalidar_actividades = "";
    } else {
        $departamento = mysqli_real_escape_string($conexion, $_SESSION['departamento']);
        $quienVe_sinvalidar_actividades = " AND aa.areas_nombres = '$departamento' ";
    }
    $sql_sinvalidar_actividades="SELECT 
        a.id,
        a.tipo,
        a.fecha_registro,
        aa.comentario_area,
        aa.status statarea,
        COALESCE(a.desglose, a.nombre) AS nombre_o_desglose,
        a.status,
        CONCAT(
            IFNULL(
                GROUP_CONCAT(DISTINCT aa.areas_nombres SEPARATOR ', '),
                'No hay departamentos asignados'
            )
        ) AS nombres_departamentos
    FROM tb_actividades a
    LEFT JOIN tb_actividad_gestor ag ON a.id = ag.actividad_id 
    LEFT JOIN tb_actividad_area aa ON aa.actividad_id = a.id 
    WHERE aa.status = 'Completado' AND (aa.comentario_area = '' OR aa.comentario_area IS NULL)
    " . $quienVe_Actividades_nuevas . "
    GROUP BY a.id  
    ORDER BY a.id
    ";
    $result_sinvalidar_actividades = mysqli_query($conexion, $sql_sinvalidar_actividades);
    $resultCount_sinvalidar_actividades = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_sinvalidar_actividades = mysqli_fetch_assoc($resultCount_sinvalidar_actividades)['total'];
    $data['conteo_sinvalidar_actividades'] = "<b style='color: " . ($conteo_sinvalidar_actividades >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_sinvalidar_actividades . "</b>";
    $data['datos_sinvalidar_actividades'] = '
            <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Actividades sin validar</li>
            <li class="dropdown-divider"></li>
        ';
    $num = 1;
    $result_sinvalidar_actividades_LIMITED = mysqli_query($conexion, $sql_sinvalidar_actividades . " LIMIT 20");
    while ($row_AN = mysqli_fetch_assoc($result_sinvalidar_actividades_LIMITED)) {
        $data['datos_sinvalidar_actividades'] .= "
        <a href='centroTickets_V2.php?Id_Act=" . $row_AN["id"] . "' class='dropdown-item' style=''>
            <div style='font-size: 14px; color:rgb(0, 0, 0);'>" . $num . ".- <b>" . $row_AN["tipo"] . "</b>: " . $row_AN["nombre_o_desglose"] . " </div>
            <div style='font-size: 12px; color:rgb(18, 83, 143);'>Departamentos: " . $row_AN["nombres_departamentos"] . " </div>
            <div style='font-size: 12px; color:rgb(36, 161, 78);'>Status: " . $row_AN["status"] . "</div>
            <div style='font-size: 12px; color:rgb(187, 105, 38);'>Fecha de registro: " . $row_AN["fecha_registro"] . "</div>
        </a>";
        $num++;
    }


    $quienve_rechazar_actividades="";
    if ($_SESSION['id_roles'] == '1') {
        $quienve_rechazar_actividades = "";
    } else {
        $departamento = mysqli_real_escape_string($conexion, $_SESSION['departamento']);
        $quienve_rechazar_actividades = " AND aa.areas_nombres = '$departamento' ";
    }
    $sql_rechazadas_actividades="SELECT 
        a.id,
        a.tipo,
        a.fecha_registro,
        aa.comentario_area,
        aa.status statarea,
        COALESCE(a.desglose, a.nombre) AS nombre_o_desglose,
        a.status,
        CONCAT(
            IFNULL(
                GROUP_CONCAT(DISTINCT aa.areas_nombres SEPARATOR ', '),
                'No hay departamentos asignados'
            )
        ) AS nombres_departamentos
    FROM tb_actividades a
    LEFT JOIN tb_actividad_gestor ag ON a.id = ag.actividad_id 
    LEFT JOIN tb_actividad_area aa ON aa.actividad_id = a.id 
    WHERE aa.status = 'Rechazado'
    " . $quienve_rechazar_actividades . " and aa.fecha_actualizacion >= (now() - INTERVAL 30 DAY)
    GROUP BY a.id  
    ORDER BY a.id
    ";
    //echo $sql_rechazadas_actividades;
    $result_rechazadas_actividades = mysqli_query($conexion, $sql_rechazadas_actividades);
    $resultCount_rechazadas_actividades = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_rechazadas_actividades = mysqli_fetch_assoc($resultCount_rechazadas_actividades)['total'];
    $data['conteo_rechazadas_actividades'] = "<b style='color: " . ($conteo_rechazadas_actividades >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_rechazadas_actividades . "</b>";
    $data['datos_rechazadas_actividades'] = '
            <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Actividades rechazadas</li>
            <li class="dropdown-divider"></li>
        ';
    $num = 1;
    $result_rechazadas_actividades_LIMITED = mysqli_query($conexion, $sql_rechazadas_actividades . " LIMIT 20");
    while ($row_AN = mysqli_fetch_assoc($result_rechazadas_actividades_LIMITED)) {
        $data['datos_rechazadas_actividades'] .= "
        <a href='centroTickets_V2.php?Id_Act=" . $row_AN["id"] . "' class='dropdown-item' style=''>
            <div style='font-size: 14px; color:rgb(0, 0, 0);'>" . $num . ".- <b>" . $row_AN["tipo"] . "</b>: " . $row_AN["nombre_o_desglose"] . " </div>
            <div style='font-size: 12px; color:rgb(18, 83, 143);'>Departamentos: " . $row_AN["nombres_departamentos"] . " </div>
            <div style='font-size: 12px; color:rgb(36, 161, 78);'>Status: " . $row_AN["statarea"] . "</div>
            <div style='font-size: 12px; color:rgb(187, 105, 38);'>Fecha de registro: " . $row_AN["fecha_registro"] . "</div>
        </a>";
        $num++;
    }

    $quienve_novaloradas_actividades="";
    if (isset($_SESSION["id_roles"]) &&$_SESSION['id_roles'] == '1') {
        $quienve_novaloradas_actividades = "";
    } else {
        $departamento = mysqli_real_escape_string($conexion, $_SESSION['departamento']);
        $quienve_novaloradas_actividades = " AND aa.areas_nombres = '$departamento' ";
    }
    $sql_novaloradas_actividades="SELECT 
        a.id,
        a.tipo,
        a.fecha_registro,
        COALESCE(a.desglose, a.nombre) AS nombre_o_desglose,
        a.status
    FROM tb_actividades a
    left join tb_actividad_solicitante ts on a.id=ts.id_actividad
    LEFT JOIN tb_actividad_area aa ON aa.actividad_id = a.id 
    WHERE a.status = 'Completado' AND ((ts.comentario_solic = '' OR ts.comentario_solic IS NULL) OR (ts.valoracion_solic ='' or ts.valoracion_solic IS NULL))
    " . $quienve_novaloradas_actividades . " 
    GROUP BY a.id  
    ORDER BY a.id
    ";
    //echo $sql_novaloradas_actividades;
    $result_novaloradas_actividades = mysqli_query($conexion, $sql_novaloradas_actividades);
    $resultCount_novaloradas_actividades = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
    $conteo_novaloradas_actividades = mysqli_fetch_assoc($resultCount_novaloradas_actividades)['total'];
    $data['conteo_novaloradas_actividades'] = "<b style='color: " . ($conteo_novaloradas_actividades >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_novaloradas_actividades . "</b>";
    $data['datos_novaloradas_actividades'] = '
            <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Actividades no valoradas</li>
            <li class="dropdown-divider"></li>
        ';
    $num = 1;
    $result_novaloradas_actividades_LIMITED = mysqli_query($conexion, $sql_novaloradas_actividades . " LIMIT 20");
    while ($row_AN = mysqli_fetch_assoc($result_novaloradas_actividades_LIMITED)) {
        $data['datos_novaloradas_actividades'] .= "
        <a href='centroTickets_V2.php?Id_Act=" . $row_AN["id"] . "' class='dropdown-item' style=''>
            <div style='font-size: 14px; color:rgb(0, 0, 0);'>" . $num . ".- <b>" . $row_AN["tipo"] . "</b>: " . $row_AN["nombre_o_desglose"] . " </div>
            <div style='font-size: 12px; color:rgb(187, 105, 38);'>Fecha de registro: " . $row_AN["fecha_registro"] . "</div>
        </a>";
        $num++;
    }

    $conteo_total_actividades_general=$conteo_nuevas_actividades+$conteo_sinvalidar_actividades+$conteo_rechazadas_actividades+$conteo_novaloradas_actividades;
    $data['conteo_total_actividades_general']= "<b style='color: " . ($conteo_total_actividades_general >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_actividades_general . "</b>";


// ACTIVIDADES V2 NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

    $conteo_total_GENERAL = $conteo_TicketSinvalorar_GENERAL + $conteo_TicketNuevo_GENERAL + $conteo_TicketRechazado_GENERAL + $conteo_capacitacionNoValorada_GENERAL;
    $data['conteo_total_GENERAL'] = "<b style='color: " . ($conteo_total_GENERAL >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_GENERAL . "</b>";
} else {
    $data["Error_Tickets_GENERAL"] = "Acceso denegado para tickets";
}
// TICKETS NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// actividades sistemas inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 
if ( isset($_SESSION["id_roles"]) && (in_array($_SESSION["id_roles"], [1,26,27])) ) {
    
    $sql_nuevas_actividades_SC = "SELECT 
    a.id,
    a.tipo,
    a.fecha_registro,
    COALESCE(a.desglose, a.nombre) AS nombre_o_desglose,
    a.status,
    CONCAT(
        IFNULL(
            GROUP_CONCAT(DISTINCT aa.areas_nombres SEPARATOR ', '),
            'No hay departamentos asignados'
        )
    ) AS nombres_departamentos
FROM tb_actividades a
LEFT JOIN tb_actividad_gestor ag ON a.id = ag.actividad_id 
LEFT JOIN tb_actividad_area aa ON aa.actividad_id = a.id 
WHERE (ag.gestor_rolusu_id IS NULL OR a.status = 'Nuevo')
AND aa.areas_nombres = 'Sistemas computacionales'
GROUP BY a.id  
ORDER BY a.id
";
$data['conteo_nuevas_actividades_SC'] ="";
$data['datos_nuevas_actividades_SC'] ="";
$result_actividades_nuevas_SC = mysqli_query($conexion, $sql_nuevas_actividades_SC);
$resultCount_actividades_nuevas_SC = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
$conteo_nuevas_actividades_SC = mysqli_fetch_assoc($resultCount_actividades_nuevas_SC)['total'];
$data['conteo_nuevas_actividades_SC'] = "<b style='color: " . ($conteo_nuevas_actividades_SC >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_nuevas_actividades_SC . "</b>";
$data['datos_nuevas_actividades_SC'] = '
        <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Actividades nuevas</li>
        <li class="dropdown-divider"></li>
    ';
$num_SC = 1;
$result_actividades_nuevas_SC_LIMITED = mysqli_query($conexion, $sql_nuevas_actividades_SC . " LIMIT 20");
while ($row_AN_SC = mysqli_fetch_assoc($result_actividades_nuevas_SC_LIMITED)) {
    $data['datos_nuevas_actividades_SC'] .= "
    <a href='centroTickets_V2.php?Id_Act=" . $row_AN_SC["id"] . "' class='dropdown-item' style=''>
        <div style='font-size: 14px; color:rgb(0, 0, 0);'>" . $num_SC . ".- <b>" . $row_AN_SC["tipo"] . "</b>: " . $row_AN_SC["nombre_o_desglose"] . " </div>
        <div style='font-size: 12px; color:rgb(18, 83, 143);'>Departamentos: " . $row_AN_SC["nombres_departamentos"] . " </div>
        <div style='font-size: 12px; color:rgb(36, 161, 78);'>Status: " . $row_AN_SC["status"] . "</div>
        <div style='font-size: 12px; color:rgb(187, 105, 38);'>Fecha de registro: " . $row_AN_SC["fecha_registro"] . "</div>
    </a>";
    $num_SC++;
}


$sql_sinvalidar_actividades_SC="SELECT 
    a.id,
    a.tipo,
    a.fecha_registro,
    aa.comentario_area,
    aa.status statarea,
    COALESCE(a.desglose, a.nombre) AS nombre_o_desglose,
    a.status,
    CONCAT(
        IFNULL(
            GROUP_CONCAT(DISTINCT aa.areas_nombres SEPARATOR ', '),
            'No hay departamentos asignados'
        )
    ) AS nombres_departamentos
FROM tb_actividades a
LEFT JOIN tb_actividad_gestor ag ON a.id = ag.actividad_id 
LEFT JOIN tb_actividad_area aa ON aa.actividad_id = a.id 
WHERE (aa.status = 'Completado' AND (aa.comentario_area = '' OR aa.comentario_area IS NULL))
AND aa.areas_nombres = 'Sistemas computacionales'
GROUP BY a.id  
ORDER BY a.id
";
$result_sinvalidar_actividades_SC = mysqli_query($conexion, $sql_sinvalidar_actividades_SC);
$resultCount_sinvalidar_actividades_SC = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
$conteo_sinvalidar_actividades_SC = mysqli_fetch_assoc($resultCount_sinvalidar_actividades_SC)['total'];


$data['conteo_sinvalidar_actividades_SC'] = "<b style='color: " . ($conteo_sinvalidar_actividades_SC >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_sinvalidar_actividades_SC . "</b>";
$data['datos_sinvalidar_actividades_SC'] = '
        <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Actividades sin validar</li>
        <li class="dropdown-divider"></li>
    ';
$num_SC = 1;
$result_sinvalidar_actividades_SC_LIMITED = mysqli_query($conexion, $sql_sinvalidar_actividades_SC . " LIMIT 20");
while ($row_AN_SC = mysqli_fetch_assoc($result_sinvalidar_actividades_SC_LIMITED)) {
    $data['datos_sinvalidar_actividades_SC'] .= "
    <a href='centroTickets_V2.php?Id_Act=" . $row_AN_SC["id"] . "' class='dropdown-item' style=''>
        <div style='font-size: 14px; color:rgb(0, 0, 0);'>" . $num . ".- <b>" . $row_AN_SC["tipo"] . "</b>: " . $row_AN_SC["nombre_o_desglose"] . " </div>
        <div style='font-size: 12px; color:rgb(18, 83, 143);'>Departamentos: " . $row_AN_SC["nombres_departamentos"] . " </div>
        <div style='font-size: 12px; color:rgb(36, 161, 78);'>Status: " . $row_AN_SC["status"] . "</div>
        <div style='font-size: 12px; color:rgb(187, 105, 38);'>Fecha de registro: " . $row_AN_SC["fecha_registro"] . "</div>
    </a>";
    $num_SC++;
}



$sql_rechazadas_actividades_SC="SELECT 
    a.id,
    a.tipo,
    a.fecha_registro,
    aa.comentario_area,
    aa.status statarea,
    COALESCE(a.desglose, a.nombre) AS nombre_o_desglose,
    a.status,
    CONCAT(
        IFNULL(
            GROUP_CONCAT(DISTINCT aa.areas_nombres SEPARATOR ', '),
            'No hay departamentos asignados'
        )
    ) AS nombres_departamentos
FROM tb_actividades a
LEFT JOIN tb_actividad_gestor ag ON a.id = ag.actividad_id 
LEFT JOIN tb_actividad_area aa ON aa.actividad_id = a.id 
WHERE aa.status = 'Rechazado'
AND aa.areas_nombres = 'Sistemas computacionales' and aa.fecha_actualizacion >= (now() - INTERVAL 30 DAY)
GROUP BY a.id  
ORDER BY a.id
";

$result_rechazadas_actividades_SC = mysqli_query($conexion, $sql_rechazadas_actividades_SC);
$resultCount_rechazadas_actividades_SC = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
$conteo_rechazadas_actividades_SC = mysqli_fetch_assoc($resultCount_rechazadas_actividades_SC)['total'];
$data['conteo_rechazadas_actividades_SC'] = "<b style='color: " . ($conteo_rechazadas_actividades_SC >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_rechazadas_actividades_SC . "</b>";
$data['datos_rechazadas_actividades_SC'] = '
        <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Actividades rechazadas</li>
        <li class="dropdown-divider"></li>
    ';
$num_SC = 1;
$result_rechazadas_actividades_SC_LIMITED = mysqli_query($conexion, $sql_rechazadas_actividades_SC . " LIMIT 20");
while ($row_AN_SC = mysqli_fetch_assoc($result_rechazadas_actividades_SC_LIMITED)) {
    $data['datos_rechazadas_actividades_SC'] .= "
    <a href='centroTickets_V2.php?Id_Act=" . $row_AN_SC["id"] . "' class='dropdown-item' style=''>
        <div style='font-size: 14px; color:rgb(0, 0, 0);'>" . $num_SC . ".- <b>" . $row_AN_SC["tipo"] . "</b>: " . $row_AN_SC["nombre_o_desglose"] . " </div>
        <div style='font-size: 12px; color:rgb(18, 83, 143);'>Departamentos: " . $row_AN_SC["nombres_departamentos"] . " </div>
        <div style='font-size: 12px; color:rgb(36, 161, 78);'>Status: " . $row_AN_SC["statarea"] . "</div>
        <div style='font-size: 12px; color:rgb(187, 105, 38);'>Fecha de registro: " . $row_AN_SC["fecha_registro"] . "</div>
    </a>";
    $num_SC++;
}

$sql_novaloradas_actividades_SC="SELECT 
    a.id,
    a.tipo,
    a.fecha_registro,
    COALESCE(a.desglose, a.nombre) AS nombre_o_desglose,
    a.status
FROM tb_actividades a
left join tb_actividad_solicitante ts on a.id=ts.id_actividad
LEFT JOIN tb_actividad_area aa ON aa.actividad_id = a.id 
WHERE a.status = 'Completado' AND ((ts.comentario_solic = '' OR ts.comentario_solic IS NULL) OR (ts.valoracion_solic ='' or ts.valoracion_solic IS NULL))
AND aa.areas_nombres = 'Sistemas computacionales'
GROUP BY a.id  
ORDER BY a.id
";
$result_novaloradas_actividades_SC = mysqli_query($conexion, $sql_novaloradas_actividades_SC);
$resultCount_novaloradas_actividades_SC = mysqli_query($conexion, "SELECT FOUND_ROWS() as total");
$conteo_novaloradas_actividades_SC = mysqli_fetch_assoc($resultCount_novaloradas_actividades_SC)['total'];

$data['conteo_novaloradas_actividades_SC'] = "<b style='color: " . ($conteo_novaloradas_actividades_SC >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_novaloradas_actividades_SC . "</b>";
$data['datos_novaloradas_actividades_SC'] = '
        <li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Actividades no valoradas</li>
        <li class="dropdown-divider"></li>
    ';
$num_SC = 1;
$result_novaloradas_actividades_SC_LIMITED = mysqli_query($conexion, $sql_novaloradas_actividades_SC . " LIMIT 20");
while ($row_AN_SC = mysqli_fetch_assoc($result_novaloradas_actividades_SC_LIMITED)) {
    $data['datos_novaloradas_actividades_SC'] .= "
    <a href='centroTickets_V2.php?Id_Act=" . $row_AN_SC["id"] . "' class='dropdown-item' style=''>
        <div style='font-size: 14px; color:rgb(0, 0, 0);'>" . $num_SC . ".- <b>" . $row_AN_SC["tipo"] . "</b>: " . $row_AN_SC["nombre_o_desglose"] . " </div>
        <div style='font-size: 12px; color:rgb(187, 105, 38);'>Fecha de registro: " . $row_AN_SC["fecha_registro"] . "</div>
    </a>";
    $num_SC++;
}
$conteo_total_sistemas=$conteo_nuevas_actividades_SC+$conteo_sinvalidar_actividades_SC+$conteo_rechazadas_actividades_SC+$conteo_novaloradas_actividades_SC;
$data['conteo_total_sistemas']= "<b style='color: " . ($conteo_total_sistemas >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_sistemas . "</b>";
}
// actividades sistemas termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<







// PLANIFICACION NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
if (isset($_SESSION["id_roles"]) &&($_SESSION['id_roles'] == '1' || $_SESSION['id_roles'] == '32')) {
    $sql_programaPorPlanificar_PL = "SELECT
        convocatoria.nombre AS Nomconv,
        programa.nombre AS NomProg,
        atp_convocatoria.status AS statusProgConv,
        atp_convocatoria.id AS id_atpc,
    COUNT(DISTINCT cartera.id_estudiante) AS num_estudiantes_registrados,
    COUNT(
        DISTINCT CASE WHEN cartera.id_atpc IS NULL THEN atp_convocatoria.id ELSE NULL
    END
) AS numEstNoRegistradosCartera
    FROM
        atp_convocatoria
        JOIN areaterminalprograma ON areaterminalprograma.id = atp_convocatoria.id_areaterminalprograma
        JOIN programa ON programa.id = areaterminalprograma.id_programa
        JOIN convocatoria ON convocatoria.id = atp_convocatoria.id_convocatoria
JOIN cartera ON cartera.id_atpc = atp_convocatoria.id AND cartera.status NOT IN(
        'Cambio programa',
        'Baja Temporal',
        'Baja Definitiva',
        'Cambio convocatoria'
    )
WHERE
        atp_convocatoria.status = 'Por planificar'
GROUP BY
    atp_convocatoria.id
ORDER BY
    atp_convocatoria.status
DESC";
    $result_programa = mysqli_query($conexion, $sql_programaPorPlanificar_PL);
    $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() AS total");
    $countRow = mysqli_fetch_array($resultCount);
    $conteo_programaPorPlanificar_PL = $countRow['total'];
    $data['conteo_programaPorPlanificar_PL'] = "<b style='color: " . ($conteo_programaPorPlanificar_PL >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_programaPorPlanificar_PL . "</b>";
    $data['datos_programaPorPlanificar_PL'] = '<li class="dropdown-header menuencabezadosty" style="text-align: center; font-weight: bold; text-transform: uppercase; color: #0018ff;">Programas por planificar</li>
    <li class="dropdown-divider"></li>';
    $num = 1;
    while ($row = mysqli_fetch_array($result_programa)) {
        $data['datos_programaPorPlanificar_PL'] .= "
        <form class='dropdown-item' action='planificacionPrograma.php' method='POST'>
            <input type='submit' name='verProgramasPlan' class='alert alert-warning dropdown-item' style='text-wrap: wrap; font-size: 15px;'
            value='" . $num . ".- " . $row["Nomconv"] . " / " . $row["NomProg"] . " / Estatus: " . $row["statusProgConv"] . "'>
            <input type='hidden' name='idatpcver' value='" . $row["id_atpc"] . "'>
            <input type='hidden' name='periodo' value='1' >
        </form>
    ";
        $num++;
    }


    $conteo_total_PL = $conteo_programaPorPlanificar_PL;
    $data['conteo_total_PL'] = "<b style='color: " . ($conteo_total_PL >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_PL . "</b>";
} else {
    $data["Error_PLANIFICACION"] = "Acceso denegado para planificación";
}
// PLANIFICACION NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// COORDINACION NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
if (isset($_SESSION["id_roles"]) &&($_SESSION["id_roles"] == 6 || $_SESSION["id_roles"] == 7 || $_SESSION["id_roles"] == 1)) {
    $quienve = $_SESSION["id_roles"];
    $idusuario = mysqli_real_escape_string($conexion, $_SESSION["idusuario"]);
    $concatsqlquienve = '';
    if ($quienve == 6 || $quienve == 7 || $quienve == 1) {
        $concatsqlquienve .= "gm.id_coordinador = " . $idusuario . " AND ";
    }
    $sqlConteoCoo = "SELECT SQL_CALC_FOUND_ROWS
        gm.id AS idGrupMat,
        gm.id_atpc AS idatpcAceptar,
        areaterminalprograma.id AS idatp,
        programa.id AS idProg,
        programa.nombre AS NomProg,
        convocatoria.nombre AS nomConv,
        areaterminal.nombre AS nomAt,
        areaterminalprograma.clavedelprograma AS claveProg,
        gm.numeroClases AS numclases,
        gm.statcoordinador AS estatusCoord,
        coordusu.nombre as nomcoordin
    FROM
        programa
    LEFT JOIN areaterminalprograma ON areaterminalprograma.id_programa = programa.id
    LEFT JOIN areaterminal ON areaterminal.id = areaterminalprograma.id_areaterminal
    LEFT JOIN cargaacademica ON cargaacademica.id_areaterminalprograma = areaterminalprograma.id
    LEFT JOIN grupomaestro gm ON gm.id_cargaacademica = cargaacademica.id
    LEFT JOIN usuario coordusu on coordusu.id= gm.id_coordinador
    LEFT JOIN materia ON materia.id = cargaacademica.id_materiareemplazo
    LEFT JOIN atp_convocatoria ON atp_convocatoria.id = gm.id_atpc
    LEFT JOIN convocatoria ON convocatoria.id = atp_convocatoria.id_convocatoria
    WHERE
        $concatsqlquienve gm.statcoordinador = 'En espera' AND atp_convocatoria.status NOT IN('Cancelado')
    GROUP BY
        atp_convocatoria.id
    ORDER BY
        convocatoria.fechainicio DESC
    LIMIT 20";


    $resultConteoCoo = mysqli_query($conexion, $sqlConteoCoo);
    $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() AS total");
    $conteo_GrupoSinCoord_COR = mysqli_fetch_assoc($resultCount)['total'];
    $data['conteo_GrupoSinCoord_COR'] = "<b style='color: " . ($conteo_GrupoSinCoord_COR >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_GrupoSinCoord_COR . "</b>";
    $numero2 = 1;
    $data['datos_GrupoSinCoord_COR'] = '<li class="dropdown-header menuencabezadosty">Programas por confirmar XX</li>
    <li class="dropdown-divider"></li>';
    while ($rowlistCoo = mysqli_fetch_array($resultConteoCoo)) {
        $data['datos_GrupoSinCoord_COR'] .= '
        <form class="dropdown-item" action="programasAsigCoord.php" method="post">
            <input type="submit" class="dropdown-item alert alert-warning" name="verEstatPago" value="' . $numero2 . '.- Programa: ' .
            $rowlistCoo['NomProg'] . ' - Convocatoria: ' . $rowlistCoo['nomConv'] . ' - Coordinador: ' . $rowlistCoo['nomcoordin'] . '">
            <input type="hidden" name="idUsuDocu" value="' . ($rowlistCoo["idCoo"] ?? '') . '" >
        </form>';
        $numero2++;
    }

    $conteo_total_COR = $conteo_GrupoSinCoord_COR;
    $data['conteo_total_COR'] = "<b style='color: " . ($conteo_total_COR >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_COR . "</b>";
} else {
    $data["Error_COORDINACION"] = "Acceso denegado para coordinación";
}
// COORDINACION NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// TECNOLOGIA DE LA INFORMACION  NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

if (isset($_SESSION["id_roles"]) &&( $_SESSION["id_roles"] == 1 || $_SESSION["id_roles"] == 3 || $_SESSION["id_roles"] == 4 || $_SESSION["id_roles"] == 6 || $_SESSION["id_roles"] == 7 || $_SESSION["id_roles"] == 8 || $_SESSION["id_roles"] == 9 || $_SESSION["id_roles"] == 13 || $_SESSION["id_roles"] == 22 || $_SESSION["id_roles"] == 25 || $_SESSION["id_roles"] == 32)) {

    // Consulta base
    $baseQuery = "SELECT SQL_CALC_FOUND_ROWS
        usuario.id idEst,
        usuario.nombre nomRem,
        usuario.appaterno appatRem,
        usuario.apmaterno apmatRem,
        usuario.telefono telRem,
        usuario.telefono2 telAlternoRem,
        usuario.correo_personal correoRem,
        usuario.correo_trabajo correstInstRem,
        roles.nombre_rol rolRem,
        soportetecnico.asunto asuntoRem,
        soportetecnico.contenido contenidoRem,
        soportetecnico.fecha fechaRem,
        soportetecnico.leido,
        soportetecnico.id_remitente
    FROM soportetecnico
    JOIN usuario ON usuario.id = soportetecnico.id_remitente
    JOIN rolesusuario ON rolesusuario.id_usuario = usuario.id
    JOIN roles ON roles.id = rolesusuario.id_roles
    WHERE leido = 0 AND soportetecnico.id_destinatario = ?";
    $idusuario = $_SESSION["idusuario"];
    $sql = str_replace('?', $idusuario, $baseQuery);
    $sql .= " GROUP BY soportetecnico.id_remitente LIMIT 20";
    $result = mysqli_query($conexion, $sql);
    $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() AS total");
    $conteo_SolApoyo_TI = mysqli_fetch_assoc($resultCount)['total'];
    $data['conteo_SolApoyo_TI'] = "<b style='color: " . ($conteo_SolApoyo_TI >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_SolApoyo_TI . "</b>";
    $data['datos_SolApoyo_TI'] = '<li class="dropdown-header menuencabezadosty">Solicitudes de Apoyo TI</li>
    <li class="dropdown-divider"></li>';
    if ($conteo_SolApoyo_TI > 0) {
        $numero = 1;
        while ($row = mysqli_fetch_array($result)) {
            $formClass = "dropdown-item li-option";
            $inputClass = "alert alert-warning dropdown-item";
            $data['datos_SolApoyo_TI'] .= '<form class="' . $formClass . '" action="' . ($role == 1 || $role == 13 ? '' : 'chatSeguimiento.php') . '" method="post">
                <br><input class="' . $inputClass . '" type="submit" name="verEstatPago" value="' .
                $numero . '.- ' . $row["nomRem"] . ' ' . $row["appatRem"] . ' ' . $row["apmatRem"] .
                ' / Asunto: ' . $row["asuntoRem"] . '">
                <input type="hidden" name="idUsu" value="' . $row["idEst"] . '">
            </form>';
            $numero++;
        }
    } else {
        $data['datos_SolApoyo_TI'] .= '<li class="dropdown-item">No hay solicitudes de apoyo pendientes.</li>';
    }

    $conteo_total_TI = $conteo_SolApoyo_TI;
    $data['conteo_total_TI'] = "<b style='color: " . ($conteo_total_TI >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_TI . "</b>";
} else {
    $data["Error_TI"] = "Acceso denegado para tecnologías de la información";
}
// TECNOLOGIA DE LA INFORMACION  NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// ADMINISTRACION Y FINANZAS  NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
if (isset($_SESSION["id_roles"]) &&($_SESSION["id_roles"] == 1 || $_SESSION["id_roles"] == 19 || $_SESSION["id_roles"] == 20 || $_SESSION["id_roles"] == 21 || $_SESSION["id_roles"] == 22)) {
    $sql = "SELECT SQL_CALC_FOUND_ROWS
                datosbancarios.nombreBen AS nomDatB,
                datosbancarios.estatusBan AS estDatB,
                datosbancarios.id_usuario AS idUsuBan
            FROM datosbancarios
            JOIN usuario ON usuario.id = datosbancarios.id_usuario
            WHERE datosbancarios.estatusBan IN ('Validar cuenta', 'Pendiente-Aprobacion')
            LIMIT 20";
    $result = mysqli_query($conexion, $sql);
    $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() AS total");
    $conteo_DatosBancarios_AF = mysqli_fetch_assoc($resultCount)['total'];
    $data['conteo_DatosBancarios_AF'] = "<b style='color:" . ($conteo_DatosBancarios_AF >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_DatosBancarios_AF . "</b>";

    $data['datos_DatosBancarios_AF'] = '<li class="dropdown-header menuencabezadosty">Revisar datos bancarios</li>
    <li class="dropdown-divider"></li>';

    // Construir la lista de formularios
    $numero = 1;
    while ($row = mysqli_fetch_array($result)) {
        $data['datos_DatosBancarios_AF'] .= '<form class="dropdown-item" action="gestorDatosBanCom.php" method="post">
            <input class="dropdown-item" style="text-wrap: wrap;" type="submit" name="verEstBanca" value="' . $numero . '.- ' . $row["nomDatB"] . ' / Estatus: ' . $row["estDatB"] . '">
            <input type="hidden" name="idUsuBan" value="' . $row["idUsuBan"] . '">
        </form>';
        $numero++;
    }


    // Consulta para obtener los datos y el conteo de pagos en revisión
    $sql = "
SELECT
    usuario.id AS idUsu,
    usuario.nombre AS nomUsu,
    usuario.appaterno AS ap,
    usuario.apmaterno AS am,
    pagos.statuspago AS estPag,
    pagos.id AS idPag,
    pagos.descripcion AS desPag,
    cartera.id_atpc AS id_atpc
FROM
    pagos
JOIN carteraperiodo ON carteraperiodo.id = pagos.id_carteraperiodo
JOIN cartera ON cartera.id = carteraperiodo.id_cartera
JOIN usuario ON usuario.id = cartera.id_estudiante
WHERE
    pagos.statuspago = 'En revisión' 
    AND usuario.status NOT IN('Baja Temporal', 'Baja Definitiva') 
LIMIT 20
";

    $result = mysqli_query($conexion, $sql);
    $data['datos_PagosPendientes_AF'] = '<li class="dropdown-header menuencabezadosty" style="text-align: center;
font-weight: bold;text-transform: uppercase;color: #0018ff;">Revisar pagos</li>
<li class="dropdown-divider"></li>';

    while ($rowpr = mysqli_fetch_assoc($result)) {
        $data['datos_PagosPendientes_AF'] .= '<li class="dropdown-item">
    <a href="validarpagos.php?idUsuaPag=' . $rowpr['idUsu'] . '&idcaratpc=' . $rowpr['id_atpc'] . '">
        <p class="alert alert-warning dropdown-item" style="font-size:12px">
            <b>El pago del estudiante:<br>' . $rowpr["nomUsu"] . " " . $rowpr["ap"] . " " . $rowpr["am"] . '<br>
            Estatus: <b style="background-color:#a4d024;padding: 2px 7px;">' . $rowpr['estPag'] . '</b>.<br>
            Tipo de pago: <b style="background-color:#a4d024;padding: 2px 7px;">' . $rowpr['desPag'] . '</b>.<br>
            Da click en este cuadro para revisar.
        </b>
        </p>
    </a>
</li>';
    }

    // Reemplazar el conteo de pagos pendientes con la cuenta correcta
    $conteoGeneralSQL = "
SELECT
    SUM(CASE
        WHEN p.statuspago = 'En revisión'
            AND (p.id_transaccion IS NULL OR p.id_transaccion = '')
            AND NOT EXISTS (SELECT 1 FROM renglonpagos rp WHERE rp.id_pag = p.id)
        THEN 1
        ELSE 0
    END) AS numPagosNormales,
    SUM(CASE
        WHEN p.id_transaccion IS NOT NULL AND p.id_transaccion != ''
            AND p.statuspago = 'En revisión'
            AND NOT EXISTS (SELECT 1 FROM renglonpagos rp WHERE rp.id_pag = p.id)
        THEN 1
        ELSE 0
    END) AS numPagosConTransaccion,
    SUM(CASE
        WHEN rp.estatus_renpag = 'En revisión'
        THEN 1
        ELSE 0
    END) AS numPagosDesglose
FROM usuario u
JOIN cartera c ON c.id_estudiante = u.id
JOIN carteraperiodo cp ON cp.id_cartera = c.id
JOIN pagos p ON p.id_carteraperiodo = cp.id
LEFT JOIN renglonpagos rp ON rp.id_pag = p.id
WHERE c.status NOT IN ('Cambio programa', 'Cambio convocatoria', 'Baja Definitiva', 'Baja Temporal', 'Titulado');";

    $resultConteoGeneral = mysqli_query($conexion, $conteoGeneralSQL);
    $cantidadTotales = mysqli_fetch_assoc($resultConteoGeneral);
    $conteo_PagosPendientes_AF = $cantidadTotales['numPagosNormales'] + $cantidadTotales['numPagosConTransaccion'] + $cantidadTotales['numPagosDesglose'];

    $data['conteo_PagosPendientes_AF'] = "<b style='" . ($conteo_PagosPendientes_AF >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_PagosPendientes_AF . "</b>";

    $conteo_total_AF = $conteo_DatosBancarios_AF + $conteo_PagosPendientes_AF;
    $data['conteo_total_AF'] = "<b style='color:" . ($conteo_total_AF >= 1 ? "red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_AF . "</b>";
} else {
    if ($debug == "si") {
        $data['Error_AF'] = "Acceso denegado para administración y finanzas.";
    }
}
// ADMINISTRACION Y FINANZAS  NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// RECURSOS HUMANOS  NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>



if (isset($_SESSION["id_roles"]) && in_array($_SESSION["id_roles"], [1, 2, 16])) {
    // Consulta SQL unificada con SQL_CALC_FOUND_ROWS para el conteo
    $sql = "SELECT SQL_CALC_FOUND_ROWS
        usuario.id as idUsu,
        usuario.nombre as nomUsu,
        usuario.appaterno as appaterno,
        usuario.apmaterno as apmaterno,
        docreqcontra.nombre as nomDoc,
        documentacionxadminis.statdigital
    FROM
        documentacionxadminis
    JOIN usuario ON usuario.id = documentacionxadminis.idAdministra
    JOIN rolesusuario ON rolesusuario.id_usuario = usuario.id
    JOIN roles ON roles.id = rolesusuario.id_roles
    JOIN docreqcontra ON docreqcontra.id = documentacionxadminis.iddocreqcontra
    WHERE
        documentacionxadminis.statdigital = 'Sin cargar' AND roles.id = 3 AND usuario.status = 'Activo'
    LIMIT 20";
    $result = mysqli_query($conexion, $sql);
    $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS()");
    $rowCount = mysqli_fetch_array($resultCount);
    $conteo_DocPendSubir_RH = $rowCount[0];
    $data['conteo_DocPendSubir_RH'] = "<b style='" . ($conteo_DocPendSubir_RH >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_DocPendSubir_RH . "</b>";
    $data['datos_DocPendSubir_RH'] = '<li class="dropdown-header menuencabezadosty">Documentación de contratación <br>pendiente</li>
    <li class="dropdown-divider"></li>';
    $numero = 1;
    while ($row = mysqli_fetch_array($result)) {
        $data['datos_DocPendSubir_RH'] .= '<form class="dropdown-item" action="listarDocxAdminisCom.php" method="get">
            <input type="submit" class="alert alert-danger dropdown-item" name="" style="text-wrap: wrap; font-size: 15px;"
                   value="' . $numero . '.- ' . $row["nomUsu"] . ' ' . $row["appaterno"] . ' ' . $row["apmaterno"] .
            ' / Documento: ' . $row["nomDoc"] . ' / Estatus: ' . $row["statdigital"] . '">
            <input type="hidden" name="idUsuDocAdmin" value="' . $row["idUsu"] . '">
          </form>';
        $numero++;
    }
    // Consulta para los documentos en revisión
    $sql2 = "SELECT SQL_CALC_FOUND_ROWS
usuario.id AS idUsu,
usuario.nombre,
usuario.appaterno,
usuario.apmaterno,
datosprofesionales.tipo_dat,
datosprofesionales.estatusDoc
FROM
datosprofesionales
JOIN usuario ON usuario.id = datosprofesionales.idusuario
WHERE datosprofesionales.estatusDoc = 'En revisión'
LIMIT 20";
    $result2 = mysqli_query($conexion, $sql2);
    $resultCount2 = mysqli_query($conexion, "SELECT FOUND_ROWS()");
    $countRow2 = mysqli_fetch_array($resultCount2);
    $conteo_DocsProfesionalesEnRevision_RH = $countRow2[0];
    $data['conteo_DatosProfesionalesRevision_RH'] = "<b style='" . ($conteo_DocsProfesionalesEnRevision_RH > 0 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_DocsProfesionalesEnRevision_RH . "</b>";

    $data['datos_DocsProfesionalesRevision_RH'] = '<li class="dropdown-header menuencabezadosty">Revisar documentos profesionales</li>
<li class="dropdown-divider"></li>';
    $numero2 = 1;
    while ($row2 = mysqli_fetch_array($result2)) {
        $data['datos_DocsProfesionalesRevision_RH'] .= "<form class='dropdown-item' action='docs_experienciaLaboral.php' method='post'>
       <input type='submit' class='alert alert-danger dropdown-item' name='' style='text-wrap: wrap; font-size: 15px;'
         value='" . $numero2 . '.- ' . $row2["nombre"] . ' ' . $row2["appaterno"] . ' ' . $row2["apmaterno"] .
            ' / Documento: ' . $row2["tipo_dat"] . ' / Estatus: ' . $row2["estatusDoc"] . "'>
         <input type='hidden' name='idUsuDocu' value='" . $row2["idUsu"] . "'>
         </form>";
        $numero2++;
    }
    // Consulta para los documentos en revisión
    $sql2 = "SELECT usuario.id idusu, usuario.nombre nusu,drc.nombre ndoc,dxa.statdigital statdig FROM documentacionxadminis dxa
join docreqcontra drc on drc.id= dxa.iddocreqcontra
join usuario on usuario.id=dxa.idAdministra
WHERE statdigital='En revisión'
LIMIT 20";
    $result2 = mysqli_query($conexion, $sql2);
    $resultCount2 = mysqli_query($conexion, "SELECT FOUND_ROWS()");
    $countRow2 = mysqli_fetch_array($resultCount2);
    $conteo_docsContraAdministrativosEnRevision_RH = $countRow2[0];
    $data['conteo_docsContraAdministrativosEnRevision_RH'] = "<b style='" . ($conteo_docsContraAdministrativosEnRevision_RH > 0 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_docsContraAdministrativosEnRevision_RH . "</b>";

    $data['datos_docsContraAdministrativosEnRevision_RH'] = '<li class="dropdown-header menuencabezadosty">Revisar documentos profesionales</li>
<li class="dropdown-divider"></li>';
    $numero2 = 1;
    while ($row2 = mysqli_fetch_array($result2)) {
        $data['datos_docsContraAdministrativosEnRevision_RH'] .= "<form class='dropdown-item' action='listarDocxAdminisCom.php' method='GET'>
       <input type='submit' class='alert alert-danger dropdown-item' name='' style='text-wrap: wrap; font-size: 15px;'
         value='" . $numero2 . '.- ' . $row2["nusu"] . ' / Documento: ' . $row2["ndoc"] . ' / Estatus: ' . $row2["statdig"] . "'>
         <input type='hidden' name='idUsuDoc' value='" . $row2["idusu"] . "'>
         </form>";
        $numero2++;
    }
    $sql = "SELECT SQL_CALC_FOUND_ROWS
                usuario.id idUsuDocAdmin,
                usuario.nombre,
                usuario.appaterno,
                usuario.apmaterno,
                documentacionxadminis.statdigital,
                docreqcontra.nombre nombreDocu
            FROM
                documentacionxadminis
            JOIN usuario ON usuario.id = documentacionxadminis.idAdministra
            JOIN docreqcontra ON docreqcontra.id = documentacionxadminis.iddocreqcontra
            WHERE
                documentacionxadminis.statdigital IN('Aprobado', 'Rechazo') AND documentacionxadminis.iddocreqcontra = 12
            LIMIT 20";
    $result = mysqli_query($conexion, $sql);
    $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() AS total");
    $countRow = mysqli_fetch_array($resultCount);
    $conteo_solContrato_RH = $countRow['total'];
    $data['conteo_solContrato_RH'] = "<b style='" . ($conteo_solContrato_RH >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_solContrato_RH . "</b>";
    $numero = 1;
    $data['datos_solContrato_RH'] = '<li class="dropdown-header menuencabezadosty" style="text-align: center;font-weight: bold;text-transform: uppercase;color: #0018ff;">
    Solicitudes de contratos</li>
    <li class="dropdown-divider"></li>';
    while ($rowDPV = mysqli_fetch_array($result)) {
        $rowidpagrv = isset($rowDPV["idPag"]) ? $rowDPV["idPag"] : '0';
        $rowidCartedpr = isset($rowDPV["idPag"]) ? $rowDPV["idPag"] : '0';
        $rowid_atpcdpr = isset($rowDPV["id_atpc"]) ? $rowDPV["id_atpc"] : '0';

        $statusClass = ($rowDPV["statdigital"] == 'Rechazo') ? 'alert alert-danger' : 'alert alert-warning';
        $statusMessage = ($rowDPV["statdigital"] == 'Rechazo') ?
            'Estatus: Documento rechazado - Por el Depto. Admin. y finanzas.' :
            'Estatus: Documento cargado - por revisar por el Dpto. Dirección general (Dirección académica).';
        $data['datos_solContrato_RH'] .= '<form class="dropdown-item" action="listarDocxAdminisCom.php" method="post">
                <input class="' . $statusClass . ' dropdown-item" type="submit" name="verPagVen_menu" value="' .
            $numero . '.- ' . $rowDPV["nombre"] . ' ' . $rowDPV["appaterno"] . ' ' . $rowDPV["apmaterno"] . ' / ' . $statusMessage . '">
                <input type="hidden" name="idPagVen_vencido_menu" value="' . $rowidpagrv. '">
                <input type="hidden" name="idUsuDocAdmin" value="' . $rowDPV["idUsuDocAdmin"] . '">
                <input type="hidden" name="idCarte_vencido_menu" value="' . $rowidCartedpr . '">
                <input type="hidden" name="id_atpc_vencido_menu" value="' . $rowid_atpcdpr . '">
              </form>';
        $numero++;
    }
    $conteo_total_RH = $conteo_DocPendSubir_RH + $conteo_DocsProfesionalesEnRevision_RH + $conteo_docsContraAdministrativosEnRevision_RH + $conteo_solContrato_RH;
    $data['conteo_total_RH'] = "<b style='" . ($conteo_total_RH >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_RH . "</b>";
} else {
    if ($debug == "si") {
        $data['Error_RH'] = "Acceso denegado para recursos humanos.";
    }
}
// RECURSOS HUMANOS  NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// ASESORES NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
if (isset($_SESSION["id_roles"]) && in_array($_SESSION["id_roles"], [1, 3, 6, 7, 32])) {
    $idusuario = mysqli_real_escape_string($conexion, $_SESSION["idusuario"]);
    $concatsqlquienve = ($_SESSION["id_roles"] == 3) ? " AND grupomaestro.id_maestro='$idusuario'" : "";
    $sql = "SELECT SQL_CALC_FOUND_ROWS
            programa.nombre AS nomPro,
            areaterminal.nombre AS nomArea,
            materia.nombre AS nomMat,
            grupomaestro.statmaestro AS estatMaest,
            grupomaestro.id AS idGrup,
            grupomaestro.id_maestro AS idMaest,
            usuario.nombre AS nombreMaestro,
            usuario.appaterno AS apPaternoMaestro,
            usuario.apmaterno AS apMaternoMaestro,
            grupomaestro.id_atpc,
            convocatoria.nombre AS nomConv
        FROM
            programa
        JOIN areaterminalprograma ON areaterminalprograma.id_programa = programa.id
        JOIN cargaacademica ON cargaacademica.id_areaterminalprograma = areaterminalprograma.id
        JOIN materia ON materia.id = cargaacademica.id_materiareemplazo
        JOIN areaterminal ON areaterminal.id = areaterminalprograma.id_areaterminal
        JOIN grupomaestro ON grupomaestro.id_cargaacademica = cargaacademica.id
        JOIN atp_convocatoria ON atp_convocatoria.id = grupomaestro.id_atpc
        JOIN convocatoria ON convocatoria.id = atp_convocatoria.id_convocatoria
        LEFT JOIN usuario ON usuario.id = grupomaestro.id_maestro
        WHERE
            grupomaestro.statmaestro IN ('En espera','') $concatsqlquienve
        LIMIT 20";
    $resultsqlListCoo = mysqli_query($conexion, $sql);
    $resultCount = mysqli_query($conexion, "SELECT FOUND_ROWS() AS total");
    $countRow = mysqli_fetch_array($resultCount);
    $conteo_grupoSinAceptar_ASE = $countRow['total'];
    $data['conteo_grupoSinAceptar_ASE'] = "<b style='" . ($conteo_grupoSinAceptar_ASE >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_grupoSinAceptar_ASE . "</b>";
    // Construcción de la lista de grupos
    $data['datos_grupoSinAceptar_ASE'] =  '<b class="dropdown-item" style="background-color: #9DE1A0; text-align: center;">
    <b style="color:Black; font-size: 22px;">Grupos por confirmar</b></b><br>';
    $numero = 1;
    while ($rowlistCoo = mysqli_fetch_array($resultsqlListCoo)) {
        $data['datos_grupoSinAceptar_ASE'] .= '
        <form class="dropdown-item" action="matmaestro.php" method="post" style="font:status-bar;">
            <input type="submit" class="alert alert-warning dropdown-item" name="verGrupoAsigEsp" value="' .
            $numero . ' - ' . $rowlistCoo["nomPro"] . ' | Convocatoria: ' . $rowlistCoo["nomConv"] .
            ' | Asignatura: ' . $rowlistCoo["nomMat"] .
            ' | Maestro: ' . $rowlistCoo["nombreMaestro"] . ' ' . $rowlistCoo["apPaternoMaestro"] . ' ' . $rowlistCoo["apMaternoMaestro"] . '"
            style="color:black;">
            <input type="hidden" name="verAsigxidatpc" value="' . $rowlistCoo["id_atpc"] . '" >
            <input type="hidden" name="idAsesor" value="' . $rowlistCoo["idMaest"] . '" >
            </form>';
        $numero++;
    }
    $quienve = $_SESSION["id_roles"];
    $idusuario = mysqli_real_escape_string($conexion, $_SESSION["idusuario"]);
    $concatsqlquienve = ($quienve == 3) ? " AND grupomaestro.id_maestro='$idusuario'" : "";
    $sqlConteoCoo = "SELECT
        grupomaestro.statmaestro AS alertEst
    FROM
        grupomaestro
    WHERE
        grupomaestro.statmaestro in ('') $concatsqlquienve";
    $resultConteoCoo = mysqli_query($conexion, $sqlConteoCoo);
    $conteo_grupoSinAsignar_ASE = mysqli_num_rows($resultConteoCoo);
    $data['conteo_grupoSinAsignar_ASE'] = "<b style='" . ($conteo_grupoSinAsignar_ASE >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_grupoSinAsignar_ASE . "</b>";
    // Construcción de la lista de grupos
    if ($quienve == 3) { // Solo el maestro
        $numero = 1;
        $sqlListCoo = "SELECT
            programa.nombre nomPro,
            areaterminal.nombre,
            materia.nombre nomMat,
            grupomaestro.statmaestro as estatMaest,
            grupomaestro.id idGrup,
            grupomaestro.id_maestro idMaest,
            usuario.nombre as nombreMaestro,
            usuario.appaterno as apPaternoMaestro,
            usuario.apmaterno as apMaternoMaestro
          FROM
            programa
          JOIN areaterminalprograma ON areaterminalprograma.id_programa = programa.id
          JOIN cargaacademica ON cargaacademica.id_areaterminalprograma = areaterminalprograma.id
          JOIN materia ON materia.id = cargaacademica.id_materiareemplazo
          JOIN areaterminal ON areaterminal.id = areaterminalprograma.id_areaterminal
          JOIN grupomaestro ON grupomaestro.id_cargaacademica = cargaacademica.id
          LEFT JOIN usuario ON usuario.id = grupomaestro.id_maestro
          WHERE
            grupomaestro.statmaestro IN ('En espera','') AND grupomaestro.id_maestro = '$idusuario' LIMIT 20";
    } elseif ($quienve == 6 || $quienve == 7) { // Coordinador
        $numero = 1;
        $sqlListCoo = "SELECT
            programa.nombre nomPro,
            areaterminal.nombre,
            materia.nombre nomMat,
            grupomaestro.statcoordinador as estatCoo,
            grupomaestro.id idGrup,
            grupomaestro.id_coordinador idCoo,
            usuario.nombre as nombreMaestro,
            usuario.appaterno as apPaternoMaestro,
            usuario.apmaterno as apMaternoMaestro
          FROM
            programa
          JOIN areaterminalprograma ON areaterminalprograma.id_programa = programa.id
          JOIN cargaacademica ON cargaacademica.id_areaterminalprograma = areaterminalprograma.id
          JOIN materia ON materia.id = cargaacademica.id_materiareemplazo
          JOIN areaterminal ON areaterminal.id = areaterminalprograma.id_areaterminal
          JOIN grupomaestro ON grupomaestro.id_cargaacademica = cargaacademica.id
          LEFT JOIN usuario ON usuario.id = grupomaestro.id_maestro
          WHERE
            grupomaestro.statcoordinador IN ('') LIMIT 20";
    } elseif ($quienve == 1 || $quienve == 32) { // Admin o Planificación
        $numero = 1;
        $sqlListCoo = "SELECT
            programa.nombre nomPro,
            areaterminal.nombre,
            materia.nombre nomMat,
            grupomaestro.statcoordinador as estatCoo,
            grupomaestro.id idGrup,
            grupomaestro.id_coordinador idCoo,
            usuario.nombre as nombreMaestro,
            usuario.appaterno as apPaternoMaestro,
            usuario.apmaterno as apMaternoMaestro
          FROM
            programa
          JOIN areaterminalprograma ON areaterminalprograma.id_programa = programa.id
          JOIN cargaacademica ON cargaacademica.id_areaterminalprograma = areaterminalprograma.id
          JOIN materia ON materia.id = cargaacademica.id_materiareemplazo
          JOIN areaterminal ON areaterminal.id = areaterminalprograma.id_areaterminal
          JOIN grupomaestro ON grupomaestro.id_cargaacademica = cargaacademica.id
          LEFT JOIN usuario ON usuario.id = grupomaestro.id_maestro
          WHERE
            grupomaestro.statmaestro IN ('En espera') OR grupomaestro.statcoordinador IN ('') LIMIT 20";
    }
    $data['datos_grupoSinAsignar_ASE'] = '<b class="dropdown-item" style="background-color: #9DE1A0; text-align: center;">
              <b style="color:Black; font-size: 22px;">Grupos por confirmar</b></b><br>';
    $resultsqlListCoo = mysqli_query($conexion, $sqlListCoo);
    while ($rowlistCoo = mysqli_fetch_array($resultsqlListCoo)) {
        $data['datos_grupoSinAsignar_ASE'] .= '
        <form class="dropdown-item" action="listarGrupEsp.php" method="post" style="font:status-bar;">
            <input type="submit" class="alert alert-warning dropdown-item" name="verEstatPago" value="X' . $numero . '.-' . $rowlistCoo["nomPro"] . ' | Asignatura: ' . $rowlistCoo["nomMat"] .
            ' | Maestro: Sin asignar" style="color:black;">
            <input type="hidden" name="idGrup" value="' . $rowlistCoo["idGrup"] . '" >
            <input type="hidden" name="idUsuDocu" value="' . ($rowlistCoo["idCoo"] ?? $rowlistCoo["idMaest"]) . '" >
        </form>';
        $numero++;
    }
    $conteo_total_ASE = $conteo_grupoSinAceptar_ASE + $conteo_grupoSinAsignar_ASE;
    $data['conteo_total_ASE'] = "<b style='" . ($conteo_total_ASE >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_ASE . "</b>";
} else {
    if ($debug == "si") {
        $data['Error_ASE'] = "Acceso denegado para recursos humanos.";
    }
}
// ASESORES NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// ESTUDIANTES NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>


$conteo_total_EST = $conteo_DocsRecha_CC + $conteo_PagosRechazados_CC + $conteo_PagosVencidos_CC;
$data['conteo_total_EST'] = "<b style='" . ($conteo_total_EST >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_EST . "</b>";


// ESTUDIANTE NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<


// Baja estudiantes NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
// Baja estudiantes NOTIFICACIONES inicia...
if (isset($_SESSION["id_roles"]) && in_array($_SESSION["id_roles"], [1, 6, 7, 8, 9, 24, 25, 36, 39])) {
    $quienve = $_SESSION["id_roles"];
    $idusuario = mysqli_real_escape_string($conexion, $_SESSION["idusuario"]);
    if ($quienve == 8 || $quienve == 9) {
        $concatsqlquienve = " AND usuario.idCC ='$idusuario'";
        $sql = "SELECT
        usuario.nombre nomest,
        usuario.appaterno appatest,
        usuario.apmaterno apmatest,
        baja_cartera.fecha,
        convocatoria.nombre nomconvoc,
        programa.nombre nomprogram,
        usuario.matricula matinterna,
        cartera.status statusest,
        baja_cartera.motivo,
        baja_cartera.id_usuarioBaja,
        CC.nombre AS nomCC,
        CC.appaterno AS apCC,
        CC.apmaterno AS amCC,
        usuario.idCC,
        Coord.nombre AS nomCoord
    FROM
        baja_cartera 
    JOIN cartera ON cartera.id = baja_cartera.id_cartera
    JOIN usuario ON usuario.id = cartera.id_estudiante
    JOIN usuario CC ON CC.id = baja_cartera.id_usuarioBaja
    JOIN atp_convocatoria ON atp_convocatoria.id = cartera.id_atpc
    JOIN areaterminalprograma on areaterminalprograma.id =atp_convocatoria.id_areaterminalprograma
    JOIN programa ON programa.id = areaterminalprograma.id_programa
    JOIN convocatoria ON convocatoria.id = atp_convocatoria.id_convocatoria
    JOIN rolesusuario ON rolesusuario.id_usuario = usuario.id
    JOIN grupomaestro ON grupomaestro.id_atpc = atp_convocatoria.id
    JOIN usuario Coord ON Coord.id = grupomaestro.id_coordinador
    WHERE
        rolesusuario.id_roles = 4 
        AND usuario.status IN('Baja Temporal', 'Baja Definitiva') 
        AND cartera.status IN('Baja Temporal', 'Baja Definitiva') 
        AND baja_cartera.fecha >= DATE_SUB(NOW(), INTERVAL 15 DAY) $concatsqlquienve
    GROUP BY
        usuario.id";
    } elseif ($quienve == 24 || $quienve == 25 || $quienve == 36 || $quienve == 39) {
        if ($quienve == 36) {
            $condicionLicen = "AND programa.nivel IN ('Licenciatura')";
        } else {
            $condicionLicen = "";
        }
        $sql = "SELECT
        usuario.nombre nomest,
        usuario.appaterno appatest,
        usuario.apmaterno apmatest,
        baja_cartera.fecha,
        convocatoria.nombre nomconvoc,
        programa.nombre nomprogram,
        usuario.matricula matinterna,
        cartera.status statusest,
        baja_cartera.motivo,
        baja_cartera.id_usuarioBaja,
        CC.nombre AS nomCC,
        CC.appaterno AS apCC,
        CC.apmaterno AS amCC
    FROM
        baja_cartera 
    JOIN cartera ON cartera.id = baja_cartera.id_cartera
    JOIN usuario ON usuario.id = cartera.id_estudiante
    JOIN usuario CC ON CC.id = baja_cartera.id_usuarioBaja
    JOIN atp_convocatoria ON atp_convocatoria.id = cartera.id_atpc
    JOIN areaterminalprograma on areaterminalprograma.id =atp_convocatoria.id_areaterminalprograma
    JOIN programa ON programa.id = areaterminalprograma.id_programa
    JOIN convocatoria ON convocatoria.id = atp_convocatoria.id_convocatoria
    JOIN rolesusuario ON rolesusuario.id_usuario = usuario.id
    WHERE
        rolesusuario.id_roles = 4 $condicionLicen
        AND usuario.status IN('Baja Temporal', 'Baja Definitiva') 
        AND cartera.status IN('Baja Temporal', 'Baja Definitiva') 
        AND baja_cartera.fecha >= DATE_SUB(NOW(), INTERVAL 15 DAY)
    GROUP BY
        usuario.id";
    } elseif ($quienve == 6 || $quienve == 7) {
        $concatsqlquienve = " AND grupomaestro.id_coordinador ='$idusuario'";
        $sql = "SELECT
        usuario.nombre nomest,
        usuario.appaterno appatest,
        usuario.apmaterno apmatest,
        baja_cartera.fecha,
        convocatoria.nombre nomconvoc,
        programa.nombre nomprogram,
        usuario.matricula matinterna,
        cartera.status statusest,
        baja_cartera.motivo,
        baja_cartera.id_usuarioBaja,
        CC.nombre AS nomCC,
        CC.appaterno AS apCC,
        CC.apmaterno AS amCC,
        usuario.idCC,
        Coord.nombre AS nomCoord,
        cartera.id_estudiante
    FROM
        baja_cartera 
    JOIN cartera ON cartera.id = baja_cartera.id_cartera
    JOIN usuario ON usuario.id = cartera.id_estudiante
    JOIN usuario CC ON CC.id = baja_cartera.id_usuarioBaja
    JOIN atp_convocatoria ON atp_convocatoria.id = cartera.id_atpc
    JOIN areaterminalprograma on areaterminalprograma.id =atp_convocatoria.id_areaterminalprograma
    JOIN programa ON programa.id = areaterminalprograma.id_programa
    JOIN convocatoria ON convocatoria.id = atp_convocatoria.id_convocatoria
    JOIN rolesusuario ON rolesusuario.id_usuario = usuario.id
    JOIN grupomaestro ON grupomaestro.id_atpc = atp_convocatoria.id
    JOIN usuario Coord ON Coord.id = grupomaestro.id_coordinador
    WHERE
        rolesusuario.id_roles = 4 
        AND usuario.status IN('Baja Temporal', 'Baja Definitiva') 
        AND cartera.status IN('Baja Temporal', 'Baja Definitiva') 
        AND baja_cartera.fecha >= DATE_SUB(NOW(), INTERVAL 15 DAY) $concatsqlquienve
    GROUP BY
        usuario.id";
    } elseif ($quienve == 1) {
        $sql = "SELECT
        usuario.nombre nomest,
        usuario.appaterno appatest,
        usuario.apmaterno apmatest,
        baja_cartera.fecha,
        convocatoria.nombre nomconvoc,
        programa.nombre nomprogram,
        usuario.matricula matinterna,
        cartera.status statusest,
        baja_cartera.motivo,
        baja_cartera.id_usuarioBaja,
        CC.nombre AS nomCC,
        CC.appaterno AS apCC,
        CC.apmaterno AS amCC
    FROM
        baja_cartera 
    JOIN cartera ON cartera.id = baja_cartera.id_cartera
    JOIN usuario ON usuario.id = cartera.id_estudiante
    JOIN usuario CC ON CC.id = baja_cartera.id_usuarioBaja
    JOIN atp_convocatoria ON atp_convocatoria.id = cartera.id_atpc
    JOIN areaterminalprograma on areaterminalprograma.id =atp_convocatoria.id_areaterminalprograma
    JOIN programa ON programa.id = areaterminalprograma.id_programa
    JOIN convocatoria ON convocatoria.id = atp_convocatoria.id_convocatoria
    JOIN rolesusuario ON rolesusuario.id_usuario = usuario.id
    WHERE
        rolesusuario.id_roles = 4
        AND usuario.status IN('Baja Temporal', 'Baja Definitiva') 
        AND cartera.status IN('Baja Temporal', 'Baja Definitiva') 
        AND baja_cartera.fecha >= DATE_SUB(NOW(), INTERVAL 15 DAY)
    GROUP BY
        usuario.id";
    }
    $resultsqlListBajaEst = mysqli_query($conexion, $sql);
    if (!$resultsqlListBajaEst) {
        error_log(mysqli_error($conexion));
        $data['Error'] = "Error en la consulta.";
    } else {
        $countRow = mysqli_fetch_array(mysqli_query($conexion, "SELECT FOUND_ROWS() AS total"));
        $conteo_bajaEstudiantes = $countRow['total'];

        // Preparación de los datos de respuesta
        $data['conteo_BajaEstudiantes'] = "<b style='" . ($conteo_bajaEstudiantes >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_bajaEstudiantes . "</b>";

        // Construcción de la lista de grupos
        $data['datos_BajaEstudiantes'] = '<li class="dropdown-header menuencabezadosty" style="text-align: center;font-weight: bold;text-transform: uppercase;color: #0018ff;">BAJA DE ESTUDIANTES</li>
                                           <li class="dropdown-divider"></li>';
        $numero = 1;
        while ($rowlistCoo = mysqli_fetch_array($resultsqlListBajaEst)) {
            if ($quienve == 6 || $quienve == 7) {
                $data['datos_BajaEstudiantes'] .= '<div class="dropdown-item" style="background-color: #ffdddd; border-left: 5px solid #d9534f; padding: 15px; border-radius: 8px; font-size: 10px; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); margin-bottom: 10px !important;"><strong style="color: #a94442;" class="dropdown-item">Baja de estudiante</strong><ul style="padding-left: 20px; margin: 10px 0 0;"><li><strong>Nombre:</strong> ' . htmlspecialchars($rowlistCoo["nomest"] . ' ' . $rowlistCoo["appatest"] . ' ' . $rowlistCoo["apmatest"]) . '</li><li><strong>Programa:</strong> ' . htmlspecialchars($rowlistCoo["nomprogram"]) . '</li><li><strong>Convocatoria:</strong> ' . htmlspecialchars($rowlistCoo["nomconvoc"]) . '</li><li><strong>Matrícula:</strong> ' . htmlspecialchars($rowlistCoo["matinterna"]) . '</li><li><strong>Fecha de baja:</strong> ' . htmlspecialchars($rowlistCoo["fecha"]) . '</li><li><strong>Motivo:</strong> ' . htmlspecialchars($rowlistCoo["motivo"]) . '</li><li><strong>Responsable de la baja:</strong> ' . htmlspecialchars($rowlistCoo["nomCC"] . ' ' . $rowlistCoo["apCC"] . ' ' . $rowlistCoo["amCC"]) . '
                <form action="infoEstudianteCoord.php" method="post">
                    <input type="hidden" name = "idEstcoord" value="' . htmlspecialchars($rowlistCoo["id_estudiante"]) . '">
                    <input style=" border-radius: 6px; height: 30px; background: #d9534f; color: white; width: 80px;" type="submit" value="Ver estudiante" name="VerEstudianteBaja">
                </form>
            </li></ul></div>';
            } else {
                $data['datos_BajaEstudiantes'] .= '<div class="dropdown-item" style="background-color: #ffdddd; border-left: 5px solid #d9534f; padding: 15px; border-radius: 8px; font-size: 10px; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); margin-bottom: 10px !important;"><strong style="color: #a94442;" class="dropdown-item">Baja de estudiante</strong><ul style="padding-left: 20px; margin: 10px 0 0;"><li><strong>Nombre:</strong> ' . htmlspecialchars($rowlistCoo["nomest"] . ' ' . $rowlistCoo["appatest"] . ' ' . $rowlistCoo["apmatest"]) . '</li><li><strong>Programa:</strong> ' . htmlspecialchars($rowlistCoo["nomprogram"]) . '</li><li><strong>Convocatoria:</strong> ' . htmlspecialchars($rowlistCoo["nomconvoc"]) . '</li><li><strong>Matrícula:</strong> ' . htmlspecialchars($rowlistCoo["matinterna"]) . '</li><li><strong>Fecha de baja:</strong> ' . htmlspecialchars($rowlistCoo["fecha"]) . '</li><li><strong>Motivo:</strong> ' . htmlspecialchars($rowlistCoo["motivo"]) . '</li><li><strong>Responsable de la baja:</strong> ' . htmlspecialchars($rowlistCoo["nomCC"] . ' ' . $rowlistCoo["apCC"] . ' ' . $rowlistCoo["amCC"]) . '
                <form action="listabajasEst.php" method="post">
                    <input style=" border-radius: 6px; height: 30px; background: #d9534f; color: white; width: 65px;" type="submit" value="Ver listado">
                </form>
            </li></ul></div>';
            }

            $numero++;
        }
        $data['conteo_bajas_total'] = "<b style='" . ($conteo_bajaEstudiantes >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_bajaEstudiantes . "</b>";
    }
} else {
    $data['Error_BajaEst'] = "Acceso denegado para bajas estudiantes.";
}
// Baja estudiantes NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// Videoconferencias NOTIFICACIONES inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
if (isset($_SESSION["id_roles"]) && in_array($_SESSION["id_roles"], [1, 3, 4, 6, 7])) {
    $quienve = $_SESSION["id_roles"];
    $idusuario = mysqli_real_escape_string($conexion, $_SESSION["idusuario"]);

    // Definir condición por rol sin duplicar código
    $concatsqlquienve = "";
    if ($quienve == 3) {
        $concatsqlquienve = " AND grupomaestro.id_maestro ='$idusuario'";
    } elseif ($quienve == 6 || $quienve == 7) {
        $concatsqlquienve = " AND grupomaestro.id_coordinador ='$idusuario'";
    } elseif ($quienve == 1) {
        $concatsqlquienve = "";
    }
    $sql = "SELECT
    videoconferencia.id,
    videoconferencia.nombre nomvideoCon,
    videoconferencia.fechainicio,
    videoconferencia.status,
    programa.nombre nomProg,
    convocatoria.nombre nomConv,
    materia.nombre nomAsig,
    videoconferencia.id_grupomaestro
FROM
    videoconferencia
JOIN grupomaestro ON grupomaestro.id = videoconferencia.id_grupomaestro
JOIN atp_convocatoria ON atp_convocatoria.id = grupomaestro.id_atpc
JOIN areaterminalprograma ON areaterminalprograma.id = atp_convocatoria.id_areaterminalprograma
JOIN programa ON programa.id = areaterminalprograma.id_programa
JOIN convocatoria ON convocatoria.id = atp_convocatoria.id_convocatoria
JOIN cargaacademica ON cargaacademica.id = grupomaestro.id_cargaacademica
JOIN materia ON materia.id = cargaacademica.id_materiareemplazo
WHERE
    videoconferencia.status IN(
        'Propuesta',
        'Propuesta rechazada',
        'Propuesta aceptada',
        'Propuesta reprogramación'
    ) $concatsqlquienve
    GROUP BY
        videoconferencia.id";
    $resultsqlListVC = mysqli_query($conexion, $sql);
    $countRow = mysqli_fetch_array(mysqli_query($conexion, "SELECT FOUND_ROWS() AS total"));
    $conteo_Videoconferencias = $countRow['total'];


    // Consulta para videoconferencias próximas a iniciar en la próxima hora
    $sql_proximas = "SELECT
    videoconferencia.id,
    videoconferencia.nombre AS nomvideoCon,
    videoconferencia.fechainicio,
    videoconferencia.status,
    programa.nombre AS nomProg,
    convocatoria.nombre AS nomConv,
    materia.nombre AS nomAsig,
    videoconferencia.id_grupomaestro
FROM
    videoconferencia
JOIN grupomaestro ON grupomaestro.id = videoconferencia.id_grupomaestro
JOIN atp_convocatoria ON atp_convocatoria.id = grupomaestro.id_atpc
JOIN areaterminalprograma ON areaterminalprograma.id = atp_convocatoria.id_areaterminalprograma
JOIN programa ON programa.id = areaterminalprograma.id_programa
JOIN convocatoria ON convocatoria.id = atp_convocatoria.id_convocatoria
JOIN cargaacademica ON cargaacademica.id = grupomaestro.id_cargaacademica
JOIN materia ON materia.id = cargaacademica.id_materiareemplazo
WHERE
    videoconferencia.status = 'Programada' AND videoconferencia.fechainicio BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)
    $concatsqlquienve 
    GROUP BY videoconferencia.id";

    $resultsqlListProximas = mysqli_query($conexion, $sql_proximas);

    $countRowProximas = mysqli_fetch_array(mysqli_query($conexion, "SELECT FOUND_ROWS() AS totalProx"));
    $conteo_VideoconferenciasProximas = $countRowProximas['totalProx'];

    if (!$resultsqlListVC || !$resultsqlListProximas) {
        error_log(mysqli_error($conexion));
        $data['Error'] = "Error en la consulta.";
    } else {
        // Contar videoconferencias nuevas

        // Contar videoconferencias próximas a iniciar

        // Notificación de videoconferencias nuevas
        // Preparación de los datos de respuesta
        $data['conteo_Videoconferencias'] = "<b style='" . ($conteo_Videoconferencias >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_Videoconferencias . "</b>";
        $data['conteo_VideoconferenciasProx'] = "<b style='" . ($conteo_VideoconferenciasProximas >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_VideoconferenciasProximas . "</b>";

        $conteo_total_VC = $conteo_VideoconferenciasProximas + $conteo_Videoconferencias;
        $data['conteo_total_VC'] = "<b style='" . ($conteo_total_VC >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_total_VC . "</b>";

        // Construcción de la lista de grupos
        $data['datos_Videoconferencias'] = '<li class="dropdown-header menuencabezadosty" style="text-align: center;font-weight: bold;text-transform: uppercase;color: #0018ff;">VIDEOCONFERENCIAS NUEVAS</li>
                                           <li class="dropdown-divider"></li>';

        while ($rowlistCoo = mysqli_fetch_array($resultsqlListVC)) {

            if ($quienve == 6 || $quienve == 7) {
                $data['datos_Videoconferencias'] .= '<div class="dropdown-item" style="background-color: #d9dcac; border-left: 5px solid #c3c68a; padding: 15px; border-radius: 8px; font-size: 10px; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); margin-bottom: 10px !important;"><strong style="color: #686956;" class="dropdown-item"></strong>
                    <ul style="padding-left: 20px; margin: 10px 0 0;">
                        <li><strong>Videoconferencia:</strong> ' . htmlspecialchars($rowlistCoo["nomvideoCon"]) . '</li>
                        <li><strong>Fecha y hora de inicio:</strong> ' . htmlspecialchars($rowlistCoo["fechainicio"]) . '</li>
                        <li><strong>Estatus:</strong> ' . htmlspecialchars($rowlistCoo["status"]) . '</li>
                        <li><strong>Programa:</strong> ' . htmlspecialchars($rowlistCoo["nomProg"]) . '</li>
                        <li><strong>Convocatoria:</strong> ' . htmlspecialchars($rowlistCoo["nomConv"]) . '</li>
                        <li><strong>Asignatura:</strong> ' . htmlspecialchars($rowlistCoo["nomAsig"]) . '</li>
                        <form action="listarClasesCoo.php" method="post">
                                <input type="hidden" name = "idGrupoMaestroNotVC" value="' . htmlspecialchars($rowlistCoo["id_grupomaestro"]) . '">
                                <input style=" border-radius: 6px; height: 30px; background: #c3c68a; color: white; width: 65px;" type="submit" value="Ver listado"  name="VerVideConfNueva">
                        </form>
                        </li>
                    </ul>
                </div>';
            } else {
                $data['datos_Videoconferencias'] .= '<div class="dropdown-item" style="background-color: #d9dcac; border-left: 5px solid #c3c68a; padding: 15px; border-radius: 8px; font-size: 10px; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); margin-bottom: 10px !important;"><strong style="color: #686956;" class="dropdown-item"></strong>
                    <ul style="padding-left: 20px; margin: 10px 0 0;">
                        <li><strong>Videoconferencia:</strong> ' . htmlspecialchars($rowlistCoo["nomvideoCon"]) . '</li>
                        <li><strong>Fecha y hora de inicio:</strong> ' . htmlspecialchars($rowlistCoo["fechainicio"]) . '</li>
                        <li><strong>Estatus:</strong> ' . htmlspecialchars($rowlistCoo["status"]) . '</li>
                        <li><strong>Programa:</strong> ' . htmlspecialchars($rowlistCoo["nomProg"]) . '</li>
                        <li><strong>Convocatoria:</strong> ' . htmlspecialchars($rowlistCoo["nomConv"]) . '</li>
                        <li><strong>Asignatura:</strong> ' . htmlspecialchars($rowlistCoo["nomAsig"]) . '</li>
                        <form action="listarClases.php" method="post">
                                <input type="hidden" name = "idGrupoMaestroNotVC" value="' . htmlspecialchars($rowlistCoo["id_grupomaestro"]) . '">
                                <input style=" border-radius: 6px; height: 30px; background: #c3c68a; color: white; width: 65px;" type="submit" value="Ver listado"  name="VerVideConfNueva">
                        </form>
                        </li>
                    </ul>
                </div>';
            }
            $numero++;
        }

        // Notificación de videoconferencias nuevas
        // Preparación de los datos de respuesta
        // $data['conteo_VideoconferenciasProx'] = "<b style='" . ($conteo_VideoconferenciasProx >= 1 ? "color:red; animation: 2s linear infinite spinner-grow; display: inline-block;font-size: 10px;" : "color:green; display: inline-block;font-size: 10px;") . "'>" . $conteo_VideoconferenciasProx . "</b>";

        // Construcción de la lista de grupos
        $data['datos_VideoconferenciasProx'] = '<li class="dropdown-header menuencabezadosty" style="text-align: center;font-weight: bold;text-transform: uppercase;color: #0018ff;">VIDEOCONFERENCIAS PRÓXIMAS</li>
                                           <li class="dropdown-divider"></li>';

        while ($rowlistCoo = mysqli_fetch_array($resultsqlListProximas)) {

            if ($quienve == 6 || $quienve == 7) {
                $data['datos_VideoconferenciasProx'] .= '<div class="dropdown-item" style="background-color: #d9dcac; border-left: 5px solid #c3c68a; padding: 15px; border-radius: 8px; font-size: 10px; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); margin-bottom: 10px !important;"><strong style="color: #686956;" class="dropdown-item"></strong>
                    <ul style="padding-left: 20px; margin: 10px 0 0;">
                        <li><strong>Videoconferencia:</strong> ' . htmlspecialchars($rowlistCoo["nomvideoCon"]) . '</li>
                        <li><strong>Fecha y hora de inicio:</strong> ' . htmlspecialchars($rowlistCoo["fechainicio"]) . '</li>
                        <li><strong>Estatus:</strong> ' . htmlspecialchars($rowlistCoo["status"]) . '</li>
                        <li><strong>Programa:</strong> ' . htmlspecialchars($rowlistCoo["nomProg"]) . '</li>
                        <li><strong>Convocatoria:</strong> ' . htmlspecialchars($rowlistCoo["nomConv"]) . '</li>
                        <li><strong>Asignatura:</strong> ' . htmlspecialchars($rowlistCoo["nomAsig"]) . '</li>
                        <form action="listarClasesCoo.php" method="post">
                                <input type="hidden" name = "idGrupoMaestroNotVC" value="' . htmlspecialchars($rowlistCoo["id_grupomaestro"]) . '">
                                <input style=" border-radius: 6px; height: 30px; background: #c3c68a; color: white; width: 65px;" type="submit" value="Ver listado"  name="VerVideConfNueva">
                        </form>
                        </li>
                    </ul>
                </div>';
            } else {
                $data['datos_VideoconferenciasProx'] .= '<div class="dropdown-item" style="background-color: #edbd71; border-left: 5px solid #f39c12; padding: 15px; border-radius: 8px; font-size: 10px; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); margin-bottom: 10px !important;"><strong style="color: #686956;" class="dropdown-item"></strong>
                    <ul style="padding-left: 20px; margin: 10px 0 0;">
                        <li><strong>Videoconferencia:</strong> ' . htmlspecialchars($rowlistCoo["nomvideoCon"]) . '</li>
                        <li><strong>Fecha y hora de inicio:</strong> ' . htmlspecialchars($rowlistCoo["fechainicio"]) . '</li>
                        <li><strong>Estatus:</strong> ' . htmlspecialchars($rowlistCoo["status"]) . '</li>
                        <li><strong>Programa:</strong> ' . htmlspecialchars($rowlistCoo["nomProg"]) . '</li>
                        <li><strong>Convocatoria:</strong> ' . htmlspecialchars($rowlistCoo["nomConv"]) . '</li>
                        <li><strong>Asignatura:</strong> ' . htmlspecialchars($rowlistCoo["nomAsig"]) . '</li>
                        <form action="listarClases.php" method="post">
                                <input type="hidden" name = "idGrupoMaestroNotVC" value="' . htmlspecialchars($rowlistCoo["id_grupomaestro"]) . '">
                                <input style=" border-radius: 6px; height: 30px; background: #f39c12; color: white; width: 65px;" type="submit" value="Ver listado"  name="VerVideConfNueva">
                        </form>
                        </li>
                    </ul>
                </div>';
            }
            $numero++;
        }
    }
} else {
    $data['Error_BajaEst'] = "Acceso denegado para bajas estudiantes.";
}
// Videoconferencias NOTIFICACIONES termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

header('Content-Type: application/json');
if (array_key_exists('Error', $data)) {
    http_response_code(400);
}
error_log(print_r($data, true)); // Muestra el contenido del array de datos
echo json_encode($data);
