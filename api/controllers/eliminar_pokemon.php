<?php
include __DIR__ . '/../config/conexion.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("⚠ Acceso no permitido.");
}
if (!isset($_SESSION["id_usuario"])) {
    die("⚠ Debes iniciar sesión primero.");
}
$id_usuario = $_SESSION["id_usuario"];

if (!isset($_POST['id_compra'], $_POST['id_pokemon'])) {
    die("⚠ Faltan datos para eliminar.");
}

$id_compra = intval($_POST['id_compra']);
$id_pokemon = intval($_POST['id_pokemon']);

// Eliminar el detalle de compra
$sql = "DELETE FROM detalle_compra WHERE id_compra = ? AND id_pokemon = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $id_compra, $id_pokemon);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Si ya no quedan detalles, puedes eliminar la compra completa (opcional)
    $sql_check = "SELECT COUNT(*) as total FROM detalle_compra WHERE id_compra = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("i", $id_compra);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result()->fetch_assoc();
    if ($result_check['total'] == 0) {
        $sql_del = "DELETE FROM compras WHERE id_compra = ?";
        $stmt_del = $conexion->prepare($sql_del);
        $stmt_del->bind_param("i", $id_compra);
        $stmt_del->execute();
        $stmt_del->close();
    }
    $stmt_check->close();
    header("Location: ../controllers/ver_compras.php?msg=eliminado");
    exit();
} else {
    die("⚠ No se pudo eliminar el Pokémon.");
}
$stmt->close();
$conexion->close();
?>
