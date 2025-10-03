<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$base_de_datos = "pokemon";

$conexion = mysqli_connect($servidor, $usuario, $password, $base_de_datos);
if (!$conexion) {
	die("Fallo la conexión: " . mysqli_connect_error());
}
?>
