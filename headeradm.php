<?php
session_start();
date_default_timezone_set('America/Mexico_City');

$tiempo_maximo_inactividad = 900; // 15 minutos en segundos
if (isset($_SESSION['ultima_actividad'])) {
    $inactividad = time() - $_SESSION['ultima_actividad'];
    if ($inactividad > $tiempo_maximo_inactividad && $_SESSION["rolusu"] != "Administrador") {
        echo '<script type="text/javascript">
                 window.location.href = "logout.php"; // Redirige a logout
              </script>';
        die();
    }
}
// Actualizar la última actividad
$_SESSION['ultima_actividad'] = time();
?>
<script type="text/javascript">
    let timeout;
    let autoRedirectTimeout;
    let intervaloContador; // Declarar la variable aquí
    let tiempoMaximoInactivo = <?php echo $tiempo_maximo_inactividad; ?>; // tiempo en segundos
    let tiempoRestante = tiempoMaximoInactivo; // Tiempo restante para la sesión
    function mostrarContador() {
        const minutos = Math.floor(tiempoRestante / 60);
        const segundos = tiempoRestante % 60;
        const tiempoTexto = `Quedan ${minutos}:${segundos < 10 ? '0' : ''}${segundos} minutos para que tu sesión expire.`;
        // Actualiza el contenido del contador
        document.getElementById('contador_tiempoRestanteSesion').innerText = tiempoTexto;
        // Decrementar el tiempo restante
        tiempoRestante--;
        if (tiempoRestante < 0) {
            clearInterval(intervaloContador);
        }
    }

    function resetTimer() {
        clearTimeout(timeout);
        clearTimeout(autoRedirectTimeout);
        clearInterval(intervaloContador); // Limpiar el intervalo anterior
        tiempoRestante = tiempoMaximoInactivo; // Reiniciar el tiempo
        // Iniciar el contador
        intervaloContador = setInterval(mostrarContador, 1000);
        // Configurar la alerta de sesión expirada
        timeout = setTimeout(function () {
            // Solo muestra la alerta si NO es administrador
            if ("<?php echo $_SESSION['rolusu']; ?>" != "Administrador") {
                Swal.fire({
                    title: 'Sesión expirada',
                    text: 'Tu sesión ha expirado por inactividad.',
                    icon: 'warning',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "logout.php";
                    }
                });
                autoRedirectTimeout = setTimeout(function () {
                    window.location.href = "logout.php";
                }, 3000);
            } else {
                // Para el administrador, puedes decidir no hacer nada o reiniciar el timer
                resetTimer(); // Reinicia el contador si es administrador
            }
        }, tiempoMaximoInactivo * 1000);
    }
    // Iniciar al cargar la página
    window.onload = resetTimer;
    // Detectar actividad del usuario
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;
</script>
<?php
require_once("bdd.php");
if (!isset($_SESSION["rolusu"])) {
    echo "<script>alert('No tienes acceso a esta página.');</script>";
    echo "<script>window.location.href='login.php'</script>";
}
$qryRol = "SELECT usuario.rol, status_usuario, rol_permisos.ruta as ruta 
FROM usuario 
JOIN rol_permisos ON rol_permisos.rol = usuario.rol
WHERE usuario.rol= '" . $_SESSION["rolusu"] . "' AND status_usuario = 'Activo'";
//echo $qryRol;
$resulRol = mysqli_query($conexion, $qryRol);
if (!$resulRol) {
    die("Error en la consulta SQL: " . mysqli_error($conexion));
}
$paginaactual = basename($_SERVER['PHP_SELF']);
$bandera = 0;
while ($rowdasp = mysqli_fetch_assoc($resulRol)) {
    if (trim($paginaactual) === trim($rowdasp["ruta"])) {
        $bandera = 1;
        break;
    }
}

if ($bandera == 0) {
    echo "<script>alert('Sin permisos suficientes.');window.location.href='index.php'</script>";
}
?>
<meta charset="UTF-8">

<style>
    .menuencabezadosty {
        text-align: center;
        font-weight: bold;
        text-transform: uppercase;
        color: #1f3070 !important;
        text-shadow: 2px -2px #d5d5d594;
        text-wrap: wrap !important;
    }
</style>
<?php
//configuraciones para Información en el sistema >>>>>>>>>
$selConfiguraciones = "select * from configuracion";
$resultConfig = mysqli_query($conexion, $selConfiguraciones);
while ($rowConfig = mysqli_fetch_assoc($resultConfig)) {
    //echo $rowConfig['descripcion'].": ".$rowConfig['valor']."<br>";
    switch ($rowConfig['descripcion']) {
        case 'Sistema_ico_pestana':
            $Sistema_ico_pestana = $rowConfig['valor'];
            break;
        case 'Sistema_nombre_proyecto_mayusculas':
            $Sistema_nombre_proyecto_mayusculas = $rowConfig['valor'];
            break;
        case 'Sistema_nombre_proyecto_minusculas':
            $Sistema_nombre_proyecto_minusculas = $rowConfig['valor'];
            break;
        case 'Sistema_direccion':
            $Sistema_direccion = $rowConfig['valor'];
            break;
        case 'Sistema_telefonos':
            $Sistema_telefonos = $rowConfig['valor'];
            break;
        case 'Sistema_whatsapp_liga':
            $Sistema_whatsapp_liga = $rowConfig['valor'];
            break;
        case 'Sistema_whatsapp_numero':
            $Sistema_whatsapp_numero = $rowConfig['valor'];
            break;
        case 'Sistema_liga_eventos':
            $Sistema_liga_eventos = $rowConfig['valor'];
            break;
        case 'Sistema_liga_noticias':
            $Sistema_liga_noticias = $rowConfig['valor'];
            break;
        case 'Sistema_liga_articulos':
            $Sistema_liga_articulos = $rowConfig['valor'];
            break;
        case 'Sistema_liga_sitio_oficial':
            $Sistema_liga_sitio_oficial = $rowConfig['valor'];
            break;
        case 'Sistema_liga_facebook':
            $Sistema_liga_facebook = $rowConfig['valor'];
            break;
        case 'Sistema_liga_linkedin':
            $Sistema_liga_linkedin = $rowConfig['valor'];
            break;
        case 'Sistema_liga_twiter':
            $Sistema_liga_twiter = $rowConfig['valor'];
            break;
        case 'Sistema_liga_youtube':
            $Sistema_liga_youtube = $rowConfig['valor'];
            break;
        case 'Sistema_horario_de_atencion':
            $Sistema_horario_de_atencion = $rowConfig['valor'];
            break;
        case 'Sistema_liga_instagram':
            $Sistema_liga_instagram = $rowConfig['valor'];
            break;
        case 'Sistema_liga_tiktok':
            $Sistema_liga_tiktok = $rowConfig['valor'];
            break;
        case 'Sistema_url_de_imagen_para_plantilla_de_correos':
            $Sistema_url_de_imagen_para_plantilla_de_correos = $rowConfig['valor'];
            break;
        //Personalización visual imagenes y colores inicio
        case 'Sistema_logo_principal': // es el logo blanco principal
            $Sistema_logo_principal = $rowConfig['valor'];
            break;
        case 'Sistema_logo_secundario': // es el logo blanco solo el escudo
            $Sistema_logo_secundario = $rowConfig['valor'];
            break;
        case 'Sistema_logo_terciario': // es el logo blanco con el nombre completo
            $Sistema_logo_terciario = $rowConfig['valor'];
            break;
        case 'Sistema_logo_contraido':
            $Sistema_logo_contraido = $rowConfig['valor'];
            break;
        case 'Sistema_logo_terciariot':
            $Sistema_logo_terciariot = $rowConfig['valor'];
            break;
        case 'Sistema_cabeza_correo':
            $Sistema_cabeza_correo = $rowConfig['valor'];
            break;
        case 'Sistema_color_primario':
            $Sistema_color_primario = $rowConfig['valor'];
            break;
        case 'Sistema_color_secundario':
            $Sistema_secundario = $rowConfig['valor'];
            break;
        case 'Sistema_color_letra':
            $Sistema_letra = $rowConfig['valor'];
            break;
        case 'Sistema_color_botones':
            $Sistema_color_botones = $rowConfig['valor'];
            break;
        case 'Sistema_nombre_completo_proyecto':
            $Sistema_nombre_completo_proyecto = $rowConfig['valor'];
            break;
        case 'Sistema_dominio_del_proyecto':
            $Sistema_dominio_del_proyecto = $rowConfig['valor'];
            break;
        case 'Sistema_correo_de_contacto':
            $Sistema_correo_de_contacto = $rowConfig['valor'];
            break;
        case 'Sistema_correo_ti':
            $Sistema_correo_ti = $rowConfig['valor'];
            break;
        case 'Sistema_MAIL_HOST':
            $Sistema_MAIL_HOST = $rowConfig['valor'];
            break;
        case 'Sistema_MAIL_Port':
            $Sistema_MAIL_Port = $rowConfig['valor'];
            break;
        case 'Sistema_MAIL_Username':
            $Sistema_MAIL_Username = $rowConfig['valor'];
            break;
        case 'Sistema_MAIL_Password':
            $Sistema_MAIL_Password = $rowConfig['valor'];
            break;
        case 'Sistema_MAIL_ALIAS':
            $Sistema_MAIL_ALIAS = $rowConfig['valor'];
            break;
        case 'Sistema_nombre_sistema':
            $Sistema_nombre_sistema = $rowConfig['valor'];
            break;
        case 'Sistema_extensiones_contacto':
            $Sistema_extensiones_contacto = $rowConfig['valor'];
            break;
        case 'Sistema_nombre_completo_gen':
            $Sistema_nombre_completo_gen = $rowConfig['valor'];
            break;
        case 'Sistema_telefono_sis':
            $Sistema_telefono_sis = $rowConfig['valor'];
            break;
        case 'Sistema_Color_Encabezado_tablas':
            $Sistema_Color_Encabezado_tablas = $rowConfig['valor'];
            break;
        case 'Sistema_Color_Encabezado_tablas_letra':
            $Sistema_Color_Encabezado_tablas_letra = $rowConfig['valor'];
            break;
        case 'Sistema_logoCE':
            $Sistema_logoCE = $rowConfig['valor'];
            break;
        default:
            break;
    }
} //configuraciones para información en el sistema cierre <<<<<<<



$domain = $_SERVER['HTTP_HOST']; // Obtiene el dominio actual

if ($domain == '10.10.10.207') {
    $title = 'LOCAL ' . $Sistema_nombre_proyecto_mayusculas;
} else {
    $title = 'Produccion ' . $Sistema_nombre_proyecto_mayusculas;
}


// require_once("funcionSoporte.php");
?>

<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 8px;
    }

    button {
        margin-bottom: 10px;
    }

    table.dataTable {
        width: 100% !important;
        border-collapse: collapse;
    }

    table.dataTable th,
    table.dataTable td {
        padding: 8px;
        text-align: left;
    }




    /* MENU LATERAL IZQUIERDO */

    /* Color del menu lateral inicia*/
    body.theme-carbon .hc-offcanvas-nav .nav-container,
    body.theme-carbon .hc-offcanvas-nav .nav-wrapper,
    body.theme-carbon .hc-offcanvas-nav ul {
        background:
            <?php echo $Sistema_color_primario;
            ?>
        ;
    }

    /* Color del menu lateral termina*/
    /* Hovers de las opciones del menu lateral izquierdo */
    body.theme-carbon .hc-offcanvas-nav:not(.touch-device) li:not(.nav-item-custom) a:not([disabled]):hover {
        background:
            <?php echo $backgroundColors;
            ?>
        ;
    }

    /* Hovers de las opciones del menu lateral izquierdo */


    /* MENU LATERAL IZQUIERDO */


    /* Profile details */
    #navbarCollapse i .profile-details {
        background-color:
            <?php echo $backgroundColorbtn;
            ?>
        ;
    }

    /* Modal header */
    .modal-xl .modal-header {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
        transform: translatex(0px) translatey(0px);
    }

    .modal-lg .modal-header {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
    }

    .modal-m .modal-header {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
    }

    .modal-dialog .modal-header {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
    }

    span.title {
        color:
            <?php echo $backgroundColorL;
            ?>
        ;
    }

    .pt-3 {
        padding-top: 0rem !important;
    }

    .sidebar {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
    }

    .sidebar-menu li a {
        color:
            <?php echo $backgroundColorL;
            ?>
        ;
    }

    .sidebar-menu li a:focus,
    .sidebar-menu li a:hover {
        color:
            <?php echo $backgroundColorL;
            ?>
        ;
        text-decoration: none;
        background-color:
            <?php echo $backgroundColors;
            ?>
        ;
    }

    /* Profile details */
    #navbarCollapse i .profile-details {
        background-color:
            <?php echo $backgroundColorbtn;
            ?>
        ;
    }

    /* Modal header */
    .modal-xl .modal-header {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
    }

    .modal-lg .modal-header {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
    }

    .modal-m .modal-header {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
    }

    .modal-dialog .modal-header {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
    }

    .sidebar-menu>li.dropdown ul.dropdown-menu {
        background-color:
            <?php echo $backgroundColors;
            ?>
        ;

    }

    /* color para la mayoría de botones inicio */
    .btn {
        background-color:
            <?php echo $Sistema_color_primario;
            ?>
        ;
        color:
            <?php echo $backgroundColorL;
            ?>
        ;
        ;
    }

    .btn:hover {
        background-color:
            <?php echo $backgroundColors;
            ?>
        ;
        color:
            <?php echo $backgroundColorL;
            ?>
        ;
        ;
    }


    .iconosenmenutop {
        position: absolute;
        margin: 0px 0px 0px 0px;
    }

    .navbar-expand-lg .navbar-nav .nav-link {
        padding-right: 1px !important;
        padding-left: 1px !important;
    }

    /* color para la mayoría de botones fin */

    .nav-link-noflecha .sub-arrow {
        border-top: 0px !important;
        border-color: gray !important;
    }

    .nav-link {
        color: gray !important;
    }
</style>
<title><?php echo $title; ?></title>
<link rel="icon" href="imagenes/<?php echo $Sistema_ico_pestana; ?>" sizes="32x32" type="image/png">
<link rel="stylesheet" href="archivosmenu2/bootstrap.min.css">
<link href="archivosmenu2/jquery.smartmenus.bootstrap-4.css" rel="stylesheet">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4" style="padding: 0px; margin-bottom: 0px !important;">
    <div class="container">
        <a class="toggle" href="#" style="margin-right: 7px;">
            <span class="navbar-toggler-icon"></span><!-- abrir menu lateral -->
        </a>
        <a class="navbar-brand" href="index.php"><b>Hielo</b></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="nav navbar-nav mr-auto">
            </ul>
            <ul class="nav navbar-nav">

                <!-- >>>>>>>>>>>>>> PERFIL inicia >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" style="color: white !important; ">
                        <?php echo "&nbsp;&nbsp;" . $_SESSION["nombreusuariocorto"] . " " .
                            '<img src="perfil/' . $_SESSION['fotoPerfil'] . '" alt="' . $Sistema_nombre_proyecto_mayusculas . '"
                                    style="height: 30px;width: auto;border-radius: 50px;" >' ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header"
                            style="text-align: center;font-weight: bold;text-transform: uppercase;color: #000;">
                            <center>
                                <div style="background-color: #9d9d9d2b;border-radius: 15px;">
                                    <br>
                                    <img src="perfil/<?php echo $_SESSION['fotoPerfil']; ?>"
                                        style="height: auto;width: 230px; border-radius: 15px;padding: 0px 10px 0px 10px;">
                                    <p><b><?php if (isset($_SESSION["nombreusuariocorto"])) {
                                        echo $_SESSION["nombreusuariocorto"];
                                    } ?></b> </p>
                                    <p style=" word-wrap: break-word; white-space: normal; ">Cargo:<br><b>
                                            <?php if (isset($_SESSION["rolusu"])) {
                                                echo $_SESSION["rolusu"];
                                            } ?></b>
                                    </p>
                                </div>

                            </center>
                        </li>
                        <li class="dropdown-divider"></li>
                        <br>
                        <li class="dropdown"><a class="dropdown-item" href="datosPersonales.php"
                                style="border-radius: 6px; background: rgb(60, 130, 100); color: white; width: 180px; margin-left: 18%; text-align: center;">Mi
                                perfil</a></li><br>
                        <li class="dropdown"><a class="dropdown-item" href="logout.php"
                                style="border-radius: 6px; background: rgb(255,0,0); color: white; width: 180px; margin-left: 18%; text-align: center;">Cerrar
                                sesión</a></li>
                        <br>
                    </ul>
                </li>

                <!-- <<<<<<<<<<<<<<<<< PERFIL termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->

            </ul>
        </div>
    </div>
</nav>
<div id="contador_tiempoRestanteSesion" style="word-wrap: break-word;"></div>
<script>
    function ajax_notificaciones(url, role) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState === 4) { // La petición ha finalizado
                if (this.status == 200) { // La respuesta fue exitosa
                    try {
                        var data = JSON.parse(this.responseText); // Parseamos la respuesta JSON
                        if (data.error) {
                            console.error(data.error); // Manejo de errores en la respuesta
                            return;
                        }
                        switch (role) {
                            case 1: // administrador
                                document.getElementById('conteo_total_CE').innerHTML = data.conteo_total_CE;
                                document.getElementById('conteo_pendientes').innerHTML = data.conteo_pendientes;
                                document.getElementById('datos_estud_pendientes').innerHTML = data
                                    .datos_estud_pendientes;
                                break;

                            case 2: // Recursos Humanos
                                document.getElementById('conteo_total_RH').innerHTML = data.conteo_total_RH;
                                document.getElementById('datos_DocPendSubir_RH').innerHTML = data.datos_DocPendSubir_RH;
                                document.getElementById('conteo_DocPendSubir_RH').innerHTML = data
                                    .conteo_DocPendSubir_RH;
                                break;
                            case 3: // Asesores
                                document.getElementById('conteo_total_ASE').innerHTML = data.conteo_total_ASE;
                                document.getElementById('datos_grupoSinAceptar_ASE').innerHTML = data
                                    .datos_grupoSinAceptar_ASE;
                                document.getElementById('conteo_grupoSinAceptar_ASE').innerHTML = data
                                    .conteo_grupoSinAceptar_ASE;
                                break;
                            default:
                                break;
                        }
                        //console.log(data); // Muestra toda la data en la consola
                    } catch (e) {
                        console.error('Error al parsear la respuesta JSON.', e); // Manejo de errores de parseo
                    }
                } else {
                    console.error('Error en la solicitud:', this.status); // Manejo de errores de la solicitud
                }
            }
        };

        xhttp.open("GET", 'ajax_notificaciones.php', true);
        xhttp.send();
    }
    // SUPONIENDO QUE TENGAS UNA VARIABLE PHP QUE CONTENGA EL ROL DEL USUARIO
    var userRole = '<?php echo $_SESSION["rolusu"]; ?>'; // Cambia esto según tu lógica
    ajax_notificaciones('ajax_notificaciones.php', userRole);
</script>
<!-- <?php echo '<script type="text/javascript">alert("La sesion es: ' . $_SESSION["rolusu"] . '");</script>'; ?> -->
<!-- MENU TOP termina <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->

<style>
    #container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        height: 100%;
        background: linear-gradient(-134deg, #39e069 0%, #2d632a 100%);
        font-family: 'Raleway', sans-serif;
        text-align: center;
        color: #fffce1;
    }
</style>
<!DOCTYPE html>
<html lang="spanish">

<head>

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:200,300,400,600,700">
    <link rel="stylesheet" href="archivosmenu2/demo.css?ver=6.1.5">
    <script src="archivosmenu2/hc-offcanvas-nav.js?ver=6.1.5"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script><!-- jQuery 3.7.1 sin el no funciona el menu-->


    <!--Estilos de HEADER ADM-->
    <link rel="stylesheet" href="archivosmenu2/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="archivosmenu2/css/next-sidebar.css" type="text/css">
    <link rel="stylesheet" href="archivosmenu2/css/perfect-scrollbar.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.1/css/all.css" type="text/css">
    <link rel="stylesheet" href="archivosmenu2/css/sweetalert2.min.css" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="archivosmenu2/css/bootstrap-icons.css">
    <link rel="stylesheet" href="archivosmenu2/css/estilo.css" type="text/css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="archivosmenu2/css/logouth.css" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>


    <script src="archivosmenu2/js/perfect-scrollbar.min.js"></script>
    <script src="archivosmenu2/js/next-sidebar.js"></script>
    <script src="archivosmenu2/js/sweetalert2.all.min.js"></script>
    <script src="archivosmenu2/js/bootstrap.min.js"></script>
    <script src="archivosmenu2/js/sweetalert2.min.js"></script>
    <script src="archivosmenu2/js/popper.min.js"></script>

    <style>
        /* ESTILOS PARA LOS ICONOS EN LOS MENUS ABRE*/
        .menu_icono {
            font-family: 'Material Icons';
            font-weight: normal;
            font-style: normal;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            text-indent: 0;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'liga';
            display: inline-block;
            width: 19px;
            height: 19px;
            margin-right: 15px;
            font-size: 23px;
            vertical-align: top;
        }

        /* ESTILOS PARA LOS ICONOS EN LOS MENUS CIERRA*/
    </style>
    <!--Estilos de HEADER ADM-->
</head>

<body class="theme-carbon">
    <!-- theme-default Color del fondo-->
    <div id="containerx">
        <header>
            <div class="wrapper cf">
                <nav id="main-nav">
                    <ul class="first-nav">
                        <li class="logo">
                            <a href="index.php"
                                style="margin-right: 20px;background: #ffffff00;border-bottom: 1px solid #dfdfdf;border-radius: 0px;text-align: center;">
                                <!-- Agrega un enlace al inicio del sitio -->
                                <img style='width: auto; margin: -58px 0px 6px 8px;height: 80px; background-color: #ffffff; border-radius: 15px;'
                                    src='imagenes/<?php echo $Sistema_logo_terciariot; ?>' alt="Logo">
                            </a>
                        </li>


                        <!-- ADMINISTRADOR MENU LATERAL INICIA>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>-->
                        <?php
                        if ($_SESSION["rolusu"] == "Administrador") {
                            ?>
                            <li class="analytics">
                                <a href="lista_personal.php">Listado de personal</a>
                                <!--
                            <ul>
                                <li class="difference">
                                    <a href="lista_personal.php">Listado de personal</a>
                                </li>
                                <li class="portrait">
                                    <a href="registro_docxestudiante.php">Registro y gestión de documentación</a>
                                </li>
                            </ul> 
                            -->
                            </li>
                            <li class="admin_panel_settings">
                                <a href="roles.php">Roles de usuario</a>
                            </li>
                            <li class="admin_panel_settings">
                                <a href="maquinas.php">Maquinaria</a>
                            </li>
                            <li class="admin_panel_settings">
                                <a href="ciclodeproduccion.php">Producción</a>
                            </li>
                            <li class="cruelty_free">
                                <a href="testeosderoles.php">Testeos de roles</a>
                            </li>

                            <li class="build">
                                <a style="font-size: 14px;" href="configuraciones.php">Configuraciones generales</a>
                            </li>

                            <?php
                        }
                        ?>
                        <!-- ADMINISTRADOR MENU LATERAL CIERRA<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<-->
                    </ul>

                    <style>
                        .bottom-nav {
                            position: relative !important;
                            margin-bottom: 0px !important;
                        }

                        .bottom_rol_s {
                            padding: 0px !important;
                            text-align: center !important;
                            background-color: #494949 !important;
                            margin-bottom: 0px !important;
                        }
                    </style>
                    <ul class="bottom-nav bottom_rol_s">
                        <li class="account_star">
                            <i class="fas fa-universal-access" style="font-size: 28px;"></i>
                            <p class="bottom_rol_s"> Rol: <b><?php echo $_SESSION["rolusu"]; ?></b></p>
                        </li>
                    </ul>



                </nav>
            </div>
        </header>
        <script>
            (function ($) {
                'use strict';
                // call our plugin
                var Nav = new hcOffcanvasNav('#main-nav', {
                    disableAt: false,
                    customToggle: '.toggle',
                    levelSpacing: 40,
                    navTitle: '', // titulo general
                    levelTitles: true, //mostrar titulos en los despliegues
                    levelTitleAsBack: true,
                    pushContent: false,
                    labelClose: false
                });
                // add new items to original nav
                $('#main-nav').find('li.add').children('a').on('click', function () {
                    var $this = $(this);
                    var $li = $this.parent();
                    var items = eval('(' + $this.attr('data-add') + ')');
                    $li.before('<li class="new"><a href="#">' + items[0] + '</a></li>');
                    items.shift();
                    if (!items.length) {
                        $li.remove();
                    } else {
                        $this.attr('data-add', JSON.stringify(items));
                    }
                    Nav.update(true); // update DOM
                });
            })(jQuery);
        </script>

        <script>

        </script>
    </div>
</body>

</html>
<script src="archivosmenu2/popper.min.js"></script>
<script src="archivosmenu2/bootstrap.min.js"></script>
<script type="text/javascript" src="archivosmenu2/jquery.smartmenus.js"></script>
<script type="text/javascript" src="archivosmenu2/jquery.smartmenus.bootstrap-4.js"></script>

<style>
    select {
        appearance: auto !important;
    }

    .alert {
        position: relative;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid transparent;
        border-radius: 0.25rem;
        margin-bottom: 0px;
    }

    /* .dropdown-item {
    padding: 0px;
} */
    .lioption_menu_superior_notificacion {
        background-color: #ebebeb !important;
        color: #296744 !important;

    }

    .lioption_menu_superior_notificacion:hover {
        background-color: #6b6b6b !important;
        color: #ffffff !important;
    }
</style>