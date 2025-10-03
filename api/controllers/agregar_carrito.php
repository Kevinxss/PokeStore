<?php
session_start();
include __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION["id_usuario"])) {
	die("⚠️ Debes iniciar sesión primero.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$id_usuario = $_SESSION["id_usuario"];
	$id_pokemon = $_POST["id_pokemon"];
	$cantidad = $_POST["cantidad"] ?? 1;

	// 1. Revisar si ya está en carrito
	$sql = "SELECT id_carrito, cantidad FROM carrito WHERE id_usuario = ? AND id_pokemon = ?";
	$stmt = $conexion->prepare($sql);
	$stmt->bind_param("ii", $id_usuario, $id_pokemon);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($row = $result->fetch_assoc()) {
		// Si ya está → aumentar cantidad
		$nuevaCantidad = $row["cantidad"] + $cantidad;
		$update = $conexion->prepare("UPDATE carrito SET cantidad = ? WHERE id_carrito = ?");
		$update->bind_param("ii", $nuevaCantidad, $row["id_carrito"]);
		$update->execute();
		$update->close();
	} else {
		// Si no está → insertarlo
		$insert = $conexion->prepare("INSERT INTO carrito (id_usuario, id_pokemon, cantidad) VALUES (?, ?, ?)");
		$insert->bind_param("iii", $id_usuario, $id_pokemon, $cantidad);
		$insert->execute();
		$insert->close();
	}

	$stmt->close();

	echo " <script>alert('Se agrego a tu carrito ✅'); window.location.href='../controllers/ver_carrito.php';</script>";
}
?>