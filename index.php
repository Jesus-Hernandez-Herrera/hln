<?php
require_once("bdd.php");
require_once("headeradm.php");
// Tu lógica existente aquí
require_once("bdd.php");
// Actualizar la hora de la última actividad
$_SESSION['ultima_actividad'] = time();

if ($_SESSION["mostrarbienvenida"] == 1) {
?>
<script>
alert("Bienvenido al sistema de <?php echo $Sistema_nombre_proyecto_mayusculas; ?>");

var nom = '<?php echo $_SESSION['nombrelargo']; ?>';
var tip = '<?php echo $_SESSION['rolusu']; ?>';
var intentos = '<?php echo $_SESSION['intentos']; ?>';

Swal.fire({
    imageUrl: 'imagenes/<?php echo $logocont; ?>',
    customClass: {
        image: 'custom-image-class' // Agrega una clase personalizada para la imagen
    },
    imageHeight: '225',
    title: '¡BIENVENIDO! <br/> ' + nom + '.',
    text: 'Rol del usuario: ' + rol + '.',
    color: '#716add',
    showConfirmButton: false,
    timer: 3000
})
// Aplica un estilo CSS para establecer la anchura máxima de la imagen como 'auto'
$('.custom-image-class').css('max-width', '100%');
</script>
<?php
	$_SESSION["mostrarbienvenida"] = 2;
} 

?>

<section class="contact-section area-padding">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <html>

                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF8">
                    </head>

                    <body>
                        <div class="blog_details">
                            <!-- Elimina la etiqueta <center> -->
                            <div class="logo"></div>
                            <div class="text-content">
                                <h1>Bienvenido al sistema de <?php echo $Sistema_nombre_proyecto_minusculas; ?></h1>
                            </div>
                        </div>
                        <br>
                        <br>
                        <div style="height: 400px; width: auto;" id="carouselExampleControlsNoTouching"
                            class="carousel slide" data-bs-touch="false">
                            <div class="carousel-inner" style="height: 400px; width: auto;">
                                <div class="carousel-item active">
                                    <center>
                                        <img src="imagenes/1.jpg" class="d-block w-100" alt=""
                                            style="height: 400px !important; width: auto !important;">
                                    </center>
                                </div>
                                <div class="carousel-item">
                                    <center>
                                        <img src="imagenes/2.jpg" class="d-block w-100" alt=""
                                            style="height: 400px !important; width: auto !important;">
                                    </center>
                                </div>
                                <div class="carousel-item">
                                    <center>
                                        <img src="imagenes/3.jpg" class="d-block w-100" alt=""
                                            style="height: 400px !important;width: auto !important;">
                                    </center>
                                </div>
                                <div class="carousel-item">
                                    <center>
                                        <img src="imagenes/4.jpg" class="d-block w-100" alt=""
                                            style="height: 400px !important;width: auto !important;">
                                    </center>
                                </div>
                                <div class="carousel-item">
                                    <center>
                                        <img src="imagenes/5.jpg" class="d-block w-100" alt=""
                                            style="height: 400px !important;width: auto !important;">
                                    </center>
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" style="BACKGROUND-COLOR: #0000004f;"
                                data-bs-target="#carouselExampleControlsNoTouching" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" style="BACKGROUND-COLOR: #0000004f;"
                                data-bs-target="#carouselExampleControlsNoTouching" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </body>

                    </html>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
require_once("footer.php");
?>
<style>
.blog_details {
    background-image: url('imagenes/bienve.png');
    /* Especifica la imagen */
    background-size: contain;
    /* Hace que la imagen cubra todo el fondo */
    background-position: center;
    /* Centra la imagen en el fondo */
    background-repeat: no-repeat;
    /* Evita que la imagen se repita */
    padding: 20px 30px 20px 35px;
    box-shadow: 0px 10px 20px 0px #0000003b;
    font-size: 18px;
    color: #333;
    border-radius: 15px;
    color: #333;
    font-family: 'Arial', sans-serif;
    border: 1px solid #ddd;
    /* text-align: center; -- Elimina esto del contenedor principal si solo quieres centrar el texto en la media query */

    display: flex;
    align-items: center;
    /* justify-content: space-between; -- Comentado como antes */
    flex-wrap: wrap;
    text-align: center;
    /* Alinea el texto a la izquierda por defecto */
    position: relative;
    width: 100%;
    max-width: 800px;
    /* Opcional: Limitar el ancho máximo */
    margin: 0 auto;
    /* Opcional: Centrar el div */
}

/* Estilos para el div contenedor del logo */
.blog_details .logo {
    flex-shrink: 0;
    /* Evita que el logo se encoja */
    /* Añadir estilos para centrar la imagen dentro de este div si es necesario */
    display: flex;
    /* Convertir el contenedor del logo en un flex container */
    justify-content: center;
    /* Centrar horizontalmente la imagen dentro del div .logo */
    align-items: center;
    /* Centrar verticalmente la imagen dentro del div .logo */
    margin-right: 20px;
    /* Espacio a la derecha cuando está al lado del texto */
}

/* Estilos para la imagen en sí */
.blog_details .logo-img {
    /* Usamos la nueva clase para apuntar directamente a la imagen */
    max-width: 100px;
    /* Limita el ancho máximo de la imagen */
    height: auto;
    /* Mantiene la relación de aspecto */
    display: block;
    /* Asegura que la imagen se comporte como un bloque para centrado con margin: auto (opcional) */
    /* margin: 0 auto; -- Otra forma de centrar la imagen dentro de su contenedor si no usas display: flex en .logo */
}

.blog_details .text-content {
    flex-grow: 1;
    color: white;
    /* flex-basis: 0; */
    background-color: rgba(0, 0, 0, 0.6);
    /* Negro con 60% de opacidad */
}

/* Media Query para pantallas más pequeñas (ejemplo: móviles) */
@media (max-width: 600px) {
    .blog_details {
        flex-direction: column;
        text-align: center;
        /* Centra el texto dentro del contenedor .blog_details */
        padding: 15px;
        align-items: center;
        /* Centra horizontalmente los hijos directos (div.logo y div.text-content) */
    }

    /* Estilos para el div contenedor del logo cuando está apilado */
    .blog_details .logo {
        margin-right: 0;
        /* Elimina el margen a la derecha */
        margin-bottom: 15px;
        /* Añade margen debajo */
        /* Asegura que el contenedor del logo ocupe todo el ancho para que align-items: center funcione */
        width: 100%;
        /* display: flex; justify-content: center; align-items: center; -- Ya definidos arriba, se aplican aquí también */
    }

    /* Estilos para la imagen en sí cuando está apilada */
    .blog_details .logo-img {
        max-width: 80px;
        /* Opcional: Hacer la imagen un poco más pequeña */
        /* margin: 0 auto; -- Ya centrado por el display: flex en .logo */
    }


    .blog_details .text-content {
        flex-grow: 1;
        /* ... otros estilos del text-content ... */

        /* Añadir un fondo semitransparente */
        background-color: rgba(0, 0, 0, 0.6);
        /* Negro con 60% de opacidad */
        padding: 10px;
        /* Añadir un poco de relleno alrededor del texto */
        border-radius: 5px;
        /* Opcional: bordes redondeados para el fondo */
    }

    .blog_details .text-content h1 {
        color: white;
        /* Asegúrate de que el texto sea blanco */
        /* text-shadow: none; -- Elimina la sombra si usas un fondo */
    }

    /* Añadir una sombra al texto */
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    /* x-offset y-offset blur color */
}
}





.distinctive-element {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    background-color: #ff7f50;
    border-radius: 50%;
}
</style>