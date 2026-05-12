<?php
session_start();
require_once '../Conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? '';
$nombre = $data['nombre'] ?? '';
$precio = $data['precio'] ?? '';
$id_categoria = $data['id_categoria'] ?? '';

if ($id === '' || $nombre === '' || $precio === '' || $id_categoria === '') {
    echo json_encode(["status" => "error", "message" => "Faltan datos para actualizar."]);
    exit;
}

try {
    $nombreLimpio = trim($nombre);
    $precioLimpio = floatval($precio);
    $categoriaLimpia = intval($id_categoria);
    $idLimpio = intval($id);

    $sql = "UPDATE PRODUCTO SET NOMBRE = ?, PRECIO = ?, ID_CATEGORIA = ? WHERE ID_PRODUCTO = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nombreLimpio, $precioLimpio, $categoriaLimpia, $idLimpio]);

    echo json_encode(["status" => "success", "message" => "¡Platillo actualizado correctamente!"]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error BD: " . $e->getMessage()]);
}
?>