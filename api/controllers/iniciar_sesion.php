<?php
session_start();
include __DIR__ . '/../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM usuarios WHERE nombre = ? AND password = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $nombre, $password);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        // Guardar datos en la sesión
        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["nombre"] = $usuario["nombre"];

        // Redirigir al menú HTML
        header("Location: ../public/menu.html");
        exit();
    } else {
        echo "Usuario o contraseña incorrectos.";
    }

    $stmt->close();
}

mysqli_close($conexion);
