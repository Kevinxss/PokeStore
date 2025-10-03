<?php
session_start();
include __DIR__ . '/../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (!isset($_SESSION["id_usuario"])) {
		die("Debes iniciar sesión primero.");
	}

	$id_usuario = $_SESSION["id_usuario"];

	$id_pokemon = $_POST["id_pokemon"];
	$nombre_personalizado = $_POST["nombre_personalizado"];
	$peso = $_POST["peso"];
	$altura = $_POST["altura"];
	$fecha_creacion = $_POST["fecha_creacion"];
	$nombre_pokemon = $_POST["nombre_pokemon"];
	$tipo = $_POST["tipo"];
	$sprite_url = $_POST["sprite_url"];

	// Insertar en mascotas (ahora con todas las columnas)
	$sql = "INSERT INTO mascotas (id_pokemon, nombre_personalizado, peso, altura, fecha_creacion, nombre_pokemon, tipo, sprite_url) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
	$stmt = $conexion->prepare($sql);
	$stmt->bind_param("isidssss", $id_pokemon, $nombre_personalizado, $peso, $altura, $fecha_creacion, $nombre_pokemon, $tipo, $sprite_url);

	if ($stmt->execute()) {
		$id_mascota = $conexion->insert_id;

		// Relación usuario-mascota
		$sql_relacion = "INSERT INTO usuarios_mascotas (id_usuario, id_mascota) VALUES (?, ?)";
		$stmt_rel = $conexion->prepare($sql_relacion);
		$stmt_rel->bind_param("ii", $id_usuario, $id_mascota);

		if ($stmt_rel->execute()) {
			echo "Mascota adoptada y asignada al usuario exitosamente.";
		} else {
			echo "Error al asignar mascota: " . $stmt_rel->error;
		}

		$stmt_rel->close();
	} else {
		echo "Error al adoptar la mascota: " . $stmt->error;
	}

	$stmt->close();
}
?>