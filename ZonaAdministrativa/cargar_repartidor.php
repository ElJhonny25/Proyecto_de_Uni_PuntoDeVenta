<?php
require_once '../Conexion.php'; 

try {
    // Corregimos el nombre a ID_CARGO y quitamos la validación de ACTIVO
    $sql = "SELECT ID_EMPLEADO, NOMBRE FROM EMPLEADO WHERE ID_CARGO = 5";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $repartidores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $repartidores]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>