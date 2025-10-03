<?php
session_start();
include __DIR__ . '/../config/conexion.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_usuario = $_SESSION["id_usuario"] ?? 0;

$sql = "SELECT id_pokemon, cantidad from carrito 
        where id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
if (!$stmt->execute()) {
    echo "Error en la consulta: " . $stmt->error;
}
$resultado = $stmt->get_result();

$productos = [];
$total = 0;

while($row = $resultado->fetch_assoc()){
    $id_pokemon = $row['id_pokemon'];
    $cantidad = $row['cantidad'];

    //consultamos los datos
    $api_url = "https://pokeapi.co/api/v2/pokemon/{$id_pokemon}/";
    $poke_data = json_decode(file_get_contents($api_url), true);
    $precio = $poke_data['weight']?? 0;

    $subtotal = $precio * $cantidad;
    $total += $subtotal;
    $productos[] = [
        'id_pokemon' => $id_pokemon,
        'cantidad' => $cantidad,
        'subtotal' => $subtotal
    ];
}

if(count($productos)>0){
    //hace compra
    $sql_compra = "INSERT INTO compras (id_usuario, total, fecha_compra) VALUES (?, ?, NOW())";
    $stmt_compra = $conexion->prepare($sql_compra);
    $stmt_compra->bind_param("id", $id_usuario, $total);
    $stmt_compra->execute();
    $id_compra = $conexion->insert_id;

    //detalle compra
    $sql_detalle = "INSERT INTO detalle_compra (id_compra, id_pokemon, cantidad, subtotal) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conexion->prepare($sql_detalle);
    foreach ($productos as $prod) {
        $stmt_detalle->bind_param("iiid", $id_compra, $prod['id_pokemon'], $prod['cantidad'], $prod['subtotal']);
        $stmt_detalle->execute();
    }

    //Vaciar el carrito
    $sql_borrar = "DELETE FROM carrito WHERE id_usuario = ?";
    $stmt_borrar = $conexion->prepare($sql_borrar);
    $stmt_borrar->bind_param("i", $id_usuario);
    $stmt_borrar->execute();

    echo "<script>
        alert('Compra realizada con éxito. Total: $total');
        window.location.href = '../public/menu.html';
    </script>";
}else {
    echo "El carrito está vacío.";
}

$stmt->close();
$conexion->close();
?>
