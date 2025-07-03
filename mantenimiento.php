<?php
require_once("bdd.php");
require_once("headergeneral.php"); // agregamos el header general
session_start();





?>




<!--estilos para los botones de los formularios -->
<style>
	/* .botonformulario {
		border-radius: 5px;
		padding: 8px;
		background-color: red;
		color: white;
		font-family: 'Font Awesome 5 Pro';
		transition: all 0.5s ease-out;
	}

	.botonformulario:hover {
		border-radius: 5px;
		padding: 8px;
		background-color: red;
		color: white;
		font-family: 'Font Awesome 5 Pro';
		transition: all 0.5s ease-out;
	} */

	/* Blog details */
	.contact-section center .blog_details {
		border-top-right-radius: 20px;
		border-top-left-radius: 20px;
		border-bottom-left-radius: 20px;
		border-bottom-right-radius: 20px;
		min-height: 617px;
		transform: translatex(-35px) translatey(-27px) !important;
		background-color: rgba(0, 0, 0, 0.58);
		display: inline-block;
	}

	/* Heading */
	.contact-section center h1 {
		color: #ffffff;
		transform: translatex(0px) translatey(30px);

	}

	/* Heading */
	.contact-section center h5 {
		color: #ffffff;
	}

	/* Form Division */
	.blog_details .form-group form {
		color: #ffffff;
		background-color: rgba(2, 2, 2, 0);
		transform: translatex(3px) translatey(7px);
	}

	/* Body */
	body {
		background-image: url("https://images.unsplash.com/photo-1595856898575-9d187bd32fd6?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wzNTc5fDB8MXxzZWFyY2h8OHx8bWFudGVuaW1pZW50b3xlbnwwfHx8fDE2ODYzNTUxNzF8MA&ixlib=rb-4.0.3&q=80&w=2560");

		background-repeat: no-repeat;
		background-size: cover;
	}

	/* Centred block */
	.contact-section center {
		transform: translatex(0px) translatey(0px);
		min-width: 0px;
		min-height: 0px;
		max-height: 0px;
		height: 0px;
	}

	/* Centred block */
	.contact-section .container .row center {
		width: 100% !important;
	}

	/* Blog details */
	.contact-section .container .row center .col-sm-6 .form-group .blog_details {
		width: 109% !important;
		transform: translatex(-33px) translatey(66px) !important;

	}

	/* Button */
	.blog_details form .btn-primary {
		position: relative;
		left: 3px;
		transform: translatex(222px) translatey(25px);
		border-top-left-radius: 20px;
		border-top-right-radius: 20px;
		border-bottom-left-radius: 20px;
		border-bottom-right-radius: 20px;
	}

	/* Form group */
	.blog_details .form-group {
		min-height: 75px;
		height: 75px;
		transform: translatex(-4px) translatey(-24px);
	}

	.blog_details form {
		transform: translatex(8px) translatey(-67px);
		color: #ffffff;
	}

	/* Button */
	.blog_details form .btn-primary {
		transform: translatex(210px) translatey(38px) !important;
	}

	nav {
		color: rgb(33, 37, 41);
		background-image: linear-gradient(-92deg, #4b79a1 23%, #283e51 70%);
	}
</style>
<br>


<section class="contact-section area-padding">
	<div class="container">
		<div class="row">
			<center>
				<div class="col-sm-6">
					<div class="form-group">

						<div class="blog_details">

							<img src="imagenes/mantenimiento.png" width="300px">
							<h1>Actualmente nos encontramos en mantenimiento espere un momento...</h1>
							<div class="col-sm-6">
								<div class="form-group">

									<br>
								</div>




							</div>

						</div>

					</div>
			</center>
</section>




<?php

//require_once("footer.php"); // agregamos el footer
?>