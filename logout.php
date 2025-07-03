<?php
require_once("bdd.php");

	session_start();

   

	$sql= "SELECT DATE_FORMAT(NOW(), '%Y-%m-%d-%H-%i-%s') AS fechaformat ";
	$resultsql = mysqli_query($conexion,$sql);
	
	while ($row = mysqli_fetch_assoc($resultsql)) {
	  $fechaformat=$row['fechaformat'];
	 
	} 
	
	if (isset($_SESSION['sesion_id'])) {
		$inicio_sesion_query = "SELECT usuarioaccesos.id idIniSes FROM usuarioaccesos WHERE usuarioaccesos.id_usuini=" . $_SESSION['sesion_id'] . " and usuarioaccesos.estatususuini='Activo'";
		$resultini = mysqli_query($conexion, $inicio_sesion_query);
		
		while($rowFechSal = mysqli_fetch_assoc($resultini)) {
			$idIniSes = $rowFechSal['idIniSes'];
		}
	
		$update_query = "UPDATE usuarioaccesos 
						 SET usuarioaccesos.fechafinususal = '$fechaformat' 
						 WHERE usuarioaccesos.id = " . $idIniSes;
		$resulupdate_query = mysqli_query($conexion, $update_query);
	
		$select = "SELECT usuarioaccesos.fechausuini, usuarioaccesos.fechafinususal 
				   FROM usuarioaccesos WHERE usuarioaccesos.id = " . $idIniSes;
		$resulselect = mysqli_query($conexion, $select);
	
		while ($rowCalHor = mysqli_fetch_assoc($resulselect)) {
			$fechausuini = $rowCalHor['fechausuini'];
			$fechafinususal = $rowCalHor['fechafinususal'];
	
			$fechaInicio = new DateTime($fechausuini);
			$fechaFin = new DateTime($fechafinususal);
	
			// Calcula solo horas y minutos
			$intervalo = $fechaInicio->diff($fechaFin);
	
			$tiempoIntera = $intervalo->format('%h horas, %i minutos');
	
			$updatetiempo = "UPDATE usuarioaccesos 
							 SET usuarioaccesos.tiempoIntera = '$tiempoIntera' 
							 WHERE usuarioaccesos.id = " . $idIniSes;
			$resulupdatetiempo = mysqli_query($conexion, $updatetiempo);
		}
	}
	
	 
			unset($_SESSION["idusuario"]);
		unset($_SESSION["nombreusuariocorto"]);
		unset($_SESSION["nombrelargo"]);
	   unset($_SESSION["correousu"]);
	   unset($_SESSION["id_roles"]);
	   unset($_SESSION["sesion_id"]);
	 
		session_destroy();
		header("Location: login.php");
	exit;
?>
