<?php
session_start();
include __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION["id_usuario"])) {
	die("⚠️ Debes iniciar sesión primero.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$id_usuario = $_SESSION["id_usuario"];
	$id_pokemon = $_POST["id_pokemon"];

	// 1. Evitar duplicados en favoritos
	$checkFav = $conexion->prepare("SELECT id_favorito FROM favoritos WHERE id_usuario = ? AND id_pokemon = ?");
	$checkFav->bind_param("ii", $id_usuario, $id_pokemon);
	$checkFav->execute();
	$checkFav->store_result();

	if ($checkFav->num_rows > 0) {
		echo "<script>alert('Ya esta en tus favoritos ⚠️'); window.location.href='../public/menu.html';</script>";
	} else {
		// Insertar en favoritos
		$insert = $conexion->prepare("INSERT INTO favoritos (id_usuario, id_pokemon) VALUES (?, ?)");
		$insert->bind_param("ii", $id_usuario, $id_pokemon);
		if ($insert->execute()) {
			echo "<script>alert('Agregado a tus favoritos'); window.location.href='../public/menu.html';</script>";
		} else {
			echo "❌ Error al agregar a favoritos.";
		}
		$insert->close();
	}

	$checkFav->close();
	$conexion->close();

	echo "<br><a href='../controllers/ver_favoritos.php'>📌 Ver tus Favoritos</a>";
}
?>