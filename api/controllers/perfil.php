<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include __DIR__ . '/../config/conexion.php';

if(!isset($_SESSION["id_usuario"])){
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
if($resultado_fav->num_rows > 0){
    while($row = $resultado_fav->fetch_assoc()){
        $id_api = $row['id_pokemon'];
        $api_url = "https://pokeapi.co/api/v2/pokemon/{$id_api}/";
        $pokemon_json = @file_get_contents($api_url);

        if($pokemon_json){
            $pokemon_data = json_decode($pokemon_json, true);
            $nombre = ucfirst($pokemon_data['name']);
            $tipo = ucfirst($pokemon_data['types'][0]['type']['name']);
            $pokemones_favoritos[] = [
                'id' => $id_api,
                'nombre' => $nombre,
                'tipo' => $tipo
            ];
        } else {
            $pokemones_favoritos[] = [
                'id' => $id_api,
                'nombre' => 'Pokémon no encontrado en la API',
                'tipo' => ''
            ];
        }
    }
}
$stmt_fav->close();
$stmt_usuario->close();
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../public/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de usuario</title>
</head>
<body>
    <header class="header">
        <div class="logo">POKESTORE</div>
        <nav class="nav">
            <ul>
                <li><a href="../public/menu.html">Inicio</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="carrito.php">Carrito</a></li>
                <li><a href="cerrarSesion.php">cerrar Sesion</a></li>
            </ul>
        </nav>
    </header>
    <section>
        <h2 class="bienvenida">Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></h2>
        <p>Teléfono: <?php echo htmlspecialchars($usuario['telefono']); ?></p>
        <h3>Pokemones Favoritos</h3>
        
        <?php if(count($pokemones_favoritos) > 0): ?>
            <div class="contenedor">
                <?php foreach($pokemones_favoritos as $poke): ?>
                    <div class="card">
                        <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/<?php echo htmlspecialchars($poke['id']); ?>.png" alt="<?php echo htmlspecialchars($poke['nombre']); ?>" >
                        <div class="card">
                            <strong><?php echo htmlspecialchars($poke['nombre']); ?></strong>
                            <?php if($poke['tipo']): ?> - Tipo: <?php echo htmlspecialchars($poke['tipo']); ?><?php endif; ?>
                            <?php
                            // Obtener peso y altura desde la API
                            $api_url = "https://pokeapi.co/api/v2/pokemon/{$poke['id']}/";
                            $pokemon_json = @file_get_contents($api_url);
                            if($pokemon_json){
                                $pokemon_data = json_decode($pokemon_json, true);
                                $peso = $pokemon_data['weight'] / 10; // hectogramos a kg
                                $altura = $pokemon_data['height'] / 10; // decímetros a metros
                                echo " - Peso: " . htmlspecialchars($peso) . " kg";
                                echo " - Altura: " . htmlspecialchars($altura) . " m";
                            }
                            ?>
                        </div>
                    </div>
                
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No tienes pokemones favoritos.</p>
        <?php endif; ?>
    </section>
</body>
</html>
