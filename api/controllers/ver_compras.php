<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../public/index.html");
    exit();
}

$usuario_id = $_SESSION['id_usuario'] ?? 0;
 
// Obtener datos de usuario
$sql_usuario = "SELECT nombre, telefono FROM usuarios WHERE id_usuario = ?";
$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$resultado_usuario = $stmt_usuario->get_result();
$usuario = $resultado_usuario->fetch_assoc();

// Obtener compras con sus pokemones
$sql_compras = "
    SELECT c.id_compra, c.fecha_compra, d.id_pokemon, d.cantidad, d.subtotal 
    FROM compras c
    INNER JOIN detalle_compra d ON c.id_compra = d.id_compra
    WHERE c.id_usuario = ?
    ORDER BY c.fecha_compra DESC
";
$stmt = $conexion->prepare($sql_compras);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$pokemones_comprados = [];
if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $id_api = $row['id_pokemon'];
        $api_url = "https://pokeapi.co/api/v2/pokemon/{$id_api}/";
        $pokemon_json = @file_get_contents($api_url);

        if ($pokemon_json) {
            $pokemon_data = json_decode($pokemon_json, true);
            $nombre = ucfirst($pokemon_data['name']);
            $tipo = ucfirst($pokemon_data['types'][0]['type']['name']);
            $peso = $pokemon_data['weight'] / 10;
            $altura = $pokemon_data['height'] / 10;
        } else {
            $nombre = "Pokémon no encontrado";
            $tipo = "";
            $peso = null;
            $altura = null;
        }

        $pokemones_comprados[] = [
            'id_compra' => $row['id_compra'],
            'fecha' => $row['fecha_compra'],
            'id' => $id_api,
            'nombre' => $nombre,
            'tipo' => $tipo,
            'peso' => $peso,
            'altura' => $altura,
            'cantidad' => $row['cantidad'],
            'subtotal' => $row['subtotal']
        ];
    }
}

$stmt->close();
$stmt_usuario->close();
$conexion->close();

// Renderizar vista
include __DIR__ . '/../public/ver_compras.html';
