<?php
include __DIR__ . '/../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $password = $_POST["password"];
    $telefono = $_POST["telefono"];

    $sql = "INSERT INTO usuarios (nombre, password, telefono) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $nombre, $password, $telefono);

    if ($stmt->execute()) {
        echo "<script>alert('Usuario registrado'); window.location.href='../public/index.html';</script>";
    } else {
        echo "Error al registrar el usuario: " . $stmt->error;
    }

    $stmt->close();
}

mysqli_close($conexion);
