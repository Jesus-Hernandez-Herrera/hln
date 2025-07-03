<?php
include "bdd.php";
$query=$conexion->query("select * from estado where id_pais=$_GET[pais_id] order by nombre asc");
$estados = array();
while($r=$query->fetch_object()){ $estados[]=$r; }
if(count($estados)>0){
echo "<option value='1'>-- No dejó --</option>";
foreach ($estados as $s) {
	echo "<option value='$s->id'>$s->nombre</option>";
}
}else{
echo "<option value='1'>-- No dejó --</option>";
}
?>