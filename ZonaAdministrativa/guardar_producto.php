<?php
session_start();
require_once '../Conexion.php';

// Leemos el JSON tal como lo tenías originalmente
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nombre']) && isset($data['precio']) && isset($data['id_categoria'])) {
    try {
        $nombre = trim($data['nombre']);
        $precio = $data['precio'];
        $id_categoria = $data['id_categoria'];

        // Tu INSERT original (SQL Server llenará el ACTIVO automáticamente con 1)
        $sql = "INSERT INTO PRODUCTO (NOMBRE, PRECIO, ID_CATEGORIA) 
                VALUES (:nombre, :precio, :id_categoria)";
        
        $stmt = $conn->prepare($sql);
        
        // bindParam vincula la variable directamente y evita el bug del valor NULL
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':id_categoria', $id_categoria);
        
        $stmt->execute();
        
        echo json_encode(["status" => "success", "message" => "¡Platillo agregado al menú de SOFTWADZ exitosamente!"]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Faltan datos para registrar el producto."]);
}
?>