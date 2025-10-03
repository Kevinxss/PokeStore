<?php
session_start();
include __DIR__ . '/../config/conexion.php';

$usuario_id = $_SESSION['id_usuario'] ?? 0;

// Consulta para obtener los productos del carrito del usuario
$sql = "SELECT id_carrito, id_pokemon, cantidad FROM carrito WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

echo "<h2>Carrito de Compras</h2>";

if ($resultado->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Pokemon</th><th>Cantidad</th><th>Total</th></tr>";
    while($row = $resultado->fetch_assoc()) {
        $total = $row['cantidad'];
        echo "<tr>";
        echo "<td>".$row['id_carrito']."</td>";
        echo "<td>".$row['id_pokemon']."</td>";
        echo "<td>".$row['cantidad']."</td>";
        echo "<td>$".$total."</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "El carrito está vacío.";
}

$stmt->close();
$conexion->close();
