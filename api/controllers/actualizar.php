<?php
session_start();
include __DIR__ . '/../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$nombre = $_POST["nombre"];
	$password = $_POST["password"];
	$telefono = $_POST["telefono"];

	// Asegúrate de tener el ID del usuario en sesión
	if (!isset($_SESSION["id_usuario"])) {
		die("⚠ No has iniciado sesión.");
	}

	$usuario_id = $_SESSION["id_usuario"];

	// Consulta SQL corregida
	$sql = "UPDATE usuarios SET nombre = ?, password = ?, telefono = ? WHERE id_usuario = ?";
	$stmt = $conexion->prepare($sql);
	$stmt->bind_param("sssi", $nombre, $password, $telefono, $usuario_id);

	if ($stmt->execute()) {
		echo "<script>alert('✅ Usuario actualizado correctamente'); window.location.href='../public/login.html';</script>";
	} else {
		echo "❌ Error al actualizar el usuario: " . $stmt->error;
	}

	$stmt->close();
}

mysqli_close($conexion);
?>