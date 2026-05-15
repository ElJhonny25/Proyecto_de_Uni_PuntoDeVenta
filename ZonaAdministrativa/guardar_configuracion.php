<?php
require_once '../Conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['configuraciones']) || !is_array($data['configuraciones'])) {
    echo json_encode(["status" => "error", "message" => "Formato inválido"]);
    exit;
}

try {
    // En guardar_configuracion.php, reemplaza el MERGE por:
$stmtCheck = $conn->prepare("SELECT COUNT(*) FROM CONFIGURACION WHERE CLAVE = ?");
$stmtUpdate = $conn->prepare("UPDATE CONFIGURACION SET VALOR = ? WHERE CLAVE = ?");
$stmtInsert = $conn->prepare("INSERT INTO CONFIGURACION (CLAVE, VALOR) VALUES (?, ?)");

foreach ($data['configuraciones'] as $config) {
    $stmtCheck->execute([$config['clave']]);
    if ($stmtCheck->fetchColumn() > 0) {
        $stmtUpdate->execute([$config['valor'], $config['clave']]);
    } else {
        $stmtInsert->execute([$config['clave'], $config['valor']]);
    }
}
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>