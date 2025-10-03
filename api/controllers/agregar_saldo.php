<?php
session_start();
include __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION["id_usuario"])) {
    die("⚠️ Debes iniciar sesión primero.");
}

if (isset($_POST["dinero"]) && is_numeric($_POST["dinero"])) {
    $dinero = floatval($_POST["dinero"]);
    $id_usuario = $_SESSION["id_usuario"];

    // Consulta: aumentar saldo
    $sql = "UPDATE usuarios SET saldo = saldo + ? WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("di", $dinero, $id_usuario);

    if ($stmt->execute()) {
        echo "<script>alert('Saldo actualizado correctamente'); window.location.href='../public/menu.html';</script>";
    } else {
        echo "Error al actualizar el saldo: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo " Invalido.";
}
?>
