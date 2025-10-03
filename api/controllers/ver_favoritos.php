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

// Obtener pokemones favoritos
$sql_fav = "SELECT id_pokemon FROM favoritos WHERE id_usuario = ?";
$stmt_fav = $conexion->prepare($sql_fav);
$stmt_fav->bind_param("i", $usuario_id);
$stmt_fav->execute();
$resultado_fav = $stmt_fav->get_result();

$pokemones_favoritos = [];
if ($resultado_fav->num_rows > 0) {
    while ($row = $resultado_fav->fetch_assoc()) {
        $id_api = $row['id_pokemon'];
        $api_url = "https://pokeapi.co/api/v2/pokemon/{$id_api}/";
        $pokemon_json = @file_get_contents($api_url);

        if ($pokemon_json) {
            $pokemon_data = json_decode($pokemon_json, true);
            $pokemones_favoritos[] = [
                'id' => $id_api,
                'nombre' => ucfirst($pokemon_data['name']),
                'tipo' => ucfirst($pokemon_data['types'][0]['type']['name']),
                'peso' => $pokemon_data['weight'] / 10,
                'altura' => $pokemon_data['height'] / 10
            ];
        } else {
            $pokemones_favoritos[] = [
                'id' => $id_api,
                'nombre' => 'Pokémon no encontrado en la API',
                'tipo' => '',
                'peso' => null,
                'altura' => null
            ];
        }
    }
}

$stmt_fav->close();
$stmt_usuario->close();
$conexion->close();

// Renderizamos vista
include __DIR__ . '/../public/ver_favoritos.html';
