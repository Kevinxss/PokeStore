<?php
session_start();
include __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION["id_usuario"])) {
    die("⚠ Debes iniciar sesión primero.");
}

$id_usuario = $_SESSION["id_usuario"];

try {
    $conexion->begin_transaction();

    // 1. Eliminar detalle_compra (dependiente de compras)
    $stmtDetalle = $conexion->prepare("DELETE FROM detalle_compra WHERE id_compra IN (SELECT id_compra FROM compras WHERE id_usuario = ?)");
    $stmtDetalle->bind_param("i", $id_usuario);
    $stmtDetalle->execute();
    $stmtDetalle->close();

    // 2. Eliminar compras
    $stmtCompras = $conexion->prepare("DELETE FROM compras WHERE id_usuario = ?");
    $stmtCompras->bind_param("i", $id_usuario);
    $stmtCompras->execute();
    $stmtCompras->close();

    // 3. Eliminar carrito
    $stmtCarrito = $conexion->prepare("DELETE FROM carrito WHERE id_usuario = ?");
    $stmtCarrito->bind_param("i", $id_usuario);
    $stmtCarrito->execute();
    $stmtCarrito->close();

    // 4. Eliminar favoritos
    $stmtFavoritos = $conexion->prepare("DELETE FROM favoritos WHERE id_usuario = ?");
    $stmtFavoritos->bind_param("i", $id_usuario);
    $stmtFavoritos->execute();
    $stmtFavoritos->close();

    // 5. Eliminar usuario
    $stmtUsuario = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmtUsuario->bind_param("i", $id_usuario);
    $stmtUsuario->execute();
    $stmtUsuario->close();

    $conexion->commit();
    session_destroy();

    echo "<script>alert('✅ Usuario y todos sus datos fueron eliminados.'); window.location.href='../public/index.html';</script>";

} catch (Exception $e) {
    $conexion->rollback();
    die("❌ Error al eliminar: " . $e->getMessage());
}

mysqli_close($conexion);
?>
