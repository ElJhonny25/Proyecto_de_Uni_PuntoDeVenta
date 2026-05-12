<?php
session_start();
require_once '../Conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$id_producto = $data['id'] ?? '';

if ($id_producto === '') {
    echo json_encode(["status" => "error", "message" => "ID no recibido."]);
    exit;
}

try {
    // Borrado lógico: Solo lo ocultamos
    $sql = "UPDATE PRODUCTO SET ACTIVO = 0 WHERE ID_PRODUCTO = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([intval($id_producto)]);

    echo json_encode(["status" => "success", "message" => "Platillo eliminado del menú."]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error BD: " . $e->getMessage()]);
}
?>