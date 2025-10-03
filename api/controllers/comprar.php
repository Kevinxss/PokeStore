<?php
session_start();
include __DIR__ . '/../config/conexion.php';

// 1. Verificar método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("⚠ Acceso no permitido.");
}

// 2. Verificar sesión
if (!isset($_SESSION["id_usuario"])) {
    die("⚠ Debes iniciar sesión primero.");
}
// 3. Obtener ID y sanitizar
$id_usuario = $_SESSION["id_usuario"];
$id_pokemon = intval($_POST["id_pokemon"] ?? 0);
$cantidad = max(1, intval($_POST["cantidad"] ?? 1)); // mínimo 1

// 4. Validación de ID
if ($id_pokemon <= 0) {
    die("⚠ ID de Pokémon inválido.");
}

// 5. Consultar PokéAPI
$pokeapi_url = "https://pokeapi.co/api/v2/pokemon/$id_pokemon";
$pokeapi_response = file_get_contents($pokeapi_url);

if (!$pokeapi_response) {
    die("⚠ No se pudo obtener datos del Pokémon desde la PokéAPI.");
}

$pokemon_data = json_decode($pokeapi_response, true);

// 6. Extraer datos
$nombre = ucfirst($pokemon_data["name"]);
$altura = $pokemon_data["height"]; // en decímetros
$peso_api = $pokemon_data["weight"]; // en hectogramos
$imagen = $pokemon_data["sprites"]["other"]["official-artwork"]["front_default"] ?? '';

$tipo_principal = $pokemon_data["types"][0]["type"]["name"] ?? null;
$tipo_secundario = $pokemon_data["types"][1]["type"]["name"] ?? null;

// 7. Validación de peso
if ($peso_api <= 0) {
    die("⚠ El Pokémon no tiene peso válido.");
}

// 8. Calcular precio y subtotal
$precio = $peso_api; // puedes aplicar otra fórmula si deseas
$subtotal = $precio * $cantidad;

// 7. Descontar saldo
$stmtUpdate = $conexion->prepare("UPDATE usuarios SET saldo = saldo - ? WHERE id_usuario = ?");
$stmtUpdate->bind_param("di", $subtotal, $id_usuario);
$stmtUpdate->execute();
$stmtUpdate->close();

// 8. Insertar en compras
$stmtCompra = $conexion->prepare("INSERT INTO compras (id_usuario, total) VALUES (?, ?)");
$stmtCompra->bind_param("id", $id_usuario, $subtotal);
$stmtCompra->execute();
$id_compra = $stmtCompra->insert_id;
$stmtCompra->close();

// 9. Insertar en detalle_compra
$stmtDetalle = $conexion->prepare("INSERT INTO detalle_compra (id_compra, id_pokemon, cantidad, subtotal) VALUES (?, ?, ?, ?)");
$stmtDetalle->bind_param("iiid", $id_compra, $id_pokemon, $cantidad, $subtotal);
$stmtDetalle->execute();
$stmtDetalle->close();

// 10. Confirmación
echo "<script>
        alert('✅ Compra realizada con éxito! Total pagado: $$subtotal');
        window.location.href='../controllers/ver_compras.php';
      </script>";
