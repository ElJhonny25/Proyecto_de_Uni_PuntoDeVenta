<?php
require_once '../Conexion.php';

try {
    $stmt = $conn->query("SELECT CLAVE, VALOR FROM CONFIGURACION");
    $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // CLAVE => VALOR

    echo json_encode([
        "status" => "success",
        "data" => $configs
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>