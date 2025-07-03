<?php
session_start();
require_once("bdd.php");

    //configuraciones para Información en el sistema >>>>>>>>>
    $selConfiguraciones = "select * from configuracion";
    $resultConfig =  mysqli_query($conexion, $selConfiguraciones);
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
                $Sistema_logo_principal  = $rowConfig['valor'];
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
                $Sistema_color_primario  = $rowConfig['valor'];
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

    if ($domain == 'localhost') {
        $title = 'LOCAL ' . $Sistema_nombre_proyecto_mayusculas;
    } else {
        $title = '' . $Sistema_nombre_proyecto_mayusculas;
    }


    ?>

    <style>
        /* MENU LATERAL IZQUIERDO */

            /* Color del menu lateral inicia*/
            body.theme-carbon .hc-offcanvas-nav .nav-container,
            body.theme-carbon .hc-offcanvas-nav .nav-wrapper,
            body.theme-carbon .hc-offcanvas-nav ul {
                background: <?php echo $Sistema_color_primario; ?>;
            }
            /* Color del menu lateral termina*/
            /* Hovers de las opciones del menu lateral izquierdo */
            body.theme-carbon .hc-offcanvas-nav:not(.touch-device) li:not(.nav-item-custom) a:not([disabled]):hover {
                background: <?php echo $backgroundColors; ?>;
            }
            /* Hovers de las opciones del menu lateral izquierdo */


        /* MENU LATERAL IZQUIERDO */


        /* Profile details */
        #navbarCollapse i .profile-details {
            background-color: <?php echo  $backgroundColorbtn; ?>;
        }

        /* Modal header */
        .modal-xl .modal-header {
            background-color: <?php echo $Sistema_color_primario; ?>;
            transform: translatex(0px) translatey(0px);
        }

        .modal-lg .modal-header {
            background-color: <?php echo $Sistema_color_primario; ?>;
        }

        .modal-m .modal-header {
            background-color: <?php echo $Sistema_color_primario; ?>;
        }

        .modal-dialog .modal-header {
            background-color: <?php echo $Sistema_color_primario; ?>;
        }

        span.title {
            color: <?php echo $backgroundColorL; ?>;
        }

        .pt-3 {
            padding-top: 0rem !important;
        }

        .sidebar {
            background-color: <?php echo  $Sistema_color_primario; ?>;
        }

        .sidebar-menu li a {
            color: <?php echo  $backgroundColorL; ?>;
        }

        .sidebar-menu li a:focus,
        .sidebar-menu li a:hover {
            color: <?php echo  $backgroundColorL; ?>;
            text-decoration: none;
            background-color: <?php echo  $backgroundColors; ?>;
        }

        /* Profile details */
        #navbarCollapse i .profile-details {
            background-color: <?php echo  $backgroundColorbtn; ?>;
        }

        /* Modal header */
        .modal-xl .modal-header {
            background-color: <?php echo  $Sistema_color_primario; ?>;
        }

        .modal-lg .modal-header {
            background-color: <?php echo  $Sistema_color_primario; ?>;
        }

        .modal-m .modal-header {
            background-color: <?php echo  $Sistema_color_primario; ?>;
        }

        .modal-dialog .modal-header {
            background-color: <?php echo  $Sistema_color_primario; ?>;
        }

        .sidebar-menu>li.dropdown ul.dropdown-menu {
            background-color: <?php echo  $backgroundColors; ?>;

        }

        /* color para la mayoría de botones inicio */
        .btn {
            background-color: <?php echo  $Sistema_color_primario; ?>;
            color: <?php echo  $backgroundColorL; ?>;
            ;
        }

        .btn:hover {
            background-color: <?php echo  $backgroundColors; ?>;
            color: <?php echo  $backgroundColorL; ?>;
            ;
        }

        /* color para la mayoría de botones fin */
    </style>




<link rel="icon" href="imagenes/<?php echo $Sistema_ico_pestana;?>" sizes="32x32" type="image/png">
    <title><?php echo $title; ?></title>

<link rel="stylesheet" href="archivosmenu2/bootstrap.min.css">
<link href="archivosmenu2/jquery.smartmenus.bootstrap-4.css" rel="stylesheet">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">

  <div class="container">
    <a class="toggle" href="#" style="margin-right: 7px;">
      <span class="navbar-toggler-icon"></span><!-- abrir menu lateral -->
    </a>
    <a class="navbar-brand" href="#"><b><?php echo $Sistema_nombre_proyecto_mayusculas; ?></b></a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavDropdown">

      <!-- Left nav -->
      <ul class="nav navbar-nav mr-auto">
        <!-- Botón para mostrar el sidebar -->

        <!-- <li class="nav-item"><a class="nav-link" href="#">Link</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Link</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Link</a></li>
        <li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#">Dropdown</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Action</a></li>
            <li><a class="dropdown-item disabled" href="#">Disabled link</a></li>
            <li><a class="dropdown-item" href="#">Something else here</a></li>
            <li class="dropdown-divider"></li>
            <li class="dropdown-header">Nav header</li>
            <li><a class="dropdown-item" href="#">Separated link</a></li>
            <li class="dropdown"><a class="dropdown-item dropdown-toggle" href="#">One more separated link</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Action</a></li>
                <li><a class="dropdown-item" href="#">Another action</a></li>
                <li class="dropdown"><a class="dropdown-item dropdown-toggle" href="#">A long sub menu</a>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><a class="dropdown-item" href="#">One more link</a></li>
                  </ul>
                </li>
                <li><a class="dropdown-item" href="#">Another link</a></li>
                <li><a class="dropdown-item" href="#">One more link</a></li>
              </ul>
            </li>
          </ul>
        </li> -->
      </ul>

      <!-- Right nav -->
      <ul class="nav navbar-nav">
        <li class="nav-item"><a class="nav-link" href="login.php">Iniciar sesión</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- sidebar -->

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

    <script  src="https://code.jquery.com/jquery-3.7.1.js"  integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="  crossorigin="anonymous"></script>
    <script src="archivosmenu2/js/perfect-scrollbar.min.js"></script>
    <script src="archivosmenu2/js/next-sidebar.js"></script>
    <script src="archivosmenu2/js/sweetalert2.all.min.js"></script>
    <script src="archivosmenu2/js/bootstrap.min.js"></script>
    <script src="archivosmenu2/js/sweetalert2.min.js"></script>
    <script src="archivosmenu2/js/popper.min.js"></script>
  <!--Estilos de HEADER ADM-->
</head>

<body class="theme-carbon"><!-- theme-default Color del fondo-->
  <div id="containerx">
    <header>
      <div class="wrapper cf">
        <nav id="main-nav">
          <ul class="first-nav">
            <li class="logo">
              <a href="#home" style="margin-right: 20px;background: #ffffff00;border-bottom: 1px solid #dfdfdf;border-radius: 0px;"> <!-- Agrega un enlace al inicio del sitio -->
              <img style='width: auto; margin: -58px 0px 6px 8px;height: 50px;background-color: #ffffff; border-radius: 15px;' src="imagenes/<?php echo $Sistema_logo_terciariot; ?>" alt="Logo">
              </a>
            </li>
            <li class="iniciar">
              <a href="login.php">Iniciar sesión</a>
            </li>
          </ul>

        </nav>
      </div>
    </header>
    <script>
      (function($) {
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
        $('#main-nav').find('li.add').children('a').on('click', function() {
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
  </div>
</body>
</html>
<!-- sidebar cierre-->
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