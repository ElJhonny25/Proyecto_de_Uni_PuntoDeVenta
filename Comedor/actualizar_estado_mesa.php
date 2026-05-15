<?php
require_once '../Conexion.php';

$mesa = $_POST['mesa'] ?? '';
$estado = $_POST['estado'] ?? '';

if (empty($mesa) || empty($estado)) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

try {
    // Limpiar espacios por seguridad
    $mesa = trim($mesa);
    $estado = trim($estado);
    
    $sql = "UPDATE MESA SET ESTADO = ? WHERE IDENTIFICADOR = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$estado, $mesa]);
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>