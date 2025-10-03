<?php
session_start();
include __DIR__ . '/../config/conexion.php';

// Verificar sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../public/index.html");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$nombre_usuario = $_SESSION["nombre"];

// Inicializamos variables
$nombre = "";
$tipo = "";
$resultados = [];

// Capturar datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);

    // Consulta SQL dinámica
    $sql = "
        SELECT m.* 
        FROM mascotas m
        INNER JOIN usuarios_mascotas um ON m.id_mascota = um.id_mascota
        WHERE um.id_usuario = ?
    ";

    if (!empty($nombre)) {
        $sql .= " AND m.nombre_pokemon LIKE ?";
    }
    if (!empty($tipo)) {
        $sql .= " AND m.tipo = ?";
    }
    $sql .= " ORDER BY m.tipo ASC, m.nombre_pokemon ASC";

    $stmt = $conexion->prepare($sql);

    // Vincular parámetros dinámicamente
    if (!empty($nombre) && !empty($tipo)) {
        $like_nombre = "%$nombre%";
        $stmt->bind_param("iss", $id_usuario, $like_nombre, $tipo);
    } elseif (!empty($nombre)) {
        $like_nombre = "%$nombre%";
        $stmt->bind_param("is", $id_usuario, $like_nombre);
    } elseif (!empty($tipo)) {
        $stmt->bind_param("is", $id_usuario, $tipo);
    } else {
        $stmt->bind_param("i", $id_usuario);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    // Agrupar resultados por tipo
    while ($fila = $resultado->fetch_assoc()) {
        $resultados[$fila['tipo']][] = $fila;
    }

    $stmt->close();
}

// Renderizar vista (puedes crear una vista buscar_pokemon.html si lo deseas)
// ...

$conexion->close();
