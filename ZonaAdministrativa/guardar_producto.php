<?php
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nombre']) && isset($data['precio']) && isset($data['id_categoria'])) {
    try {
        // Como la tabla tiene ACTIVO BIT DEFAULT 1, SQL Server lo llenará automáticamente
        $sql = "INSERT INTO PRODUCTO (NOMBRE, PRECIO, ID_CATEGORIA) 
                VALUES (:nombre, :precio, :id_categoria)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nombre' => trim($data['nombre']),
            ':precio' => $data['precio'],
            ':id_categoria' => $data['id_categoria']
        ]);
        
        echo json_encode(["status" => "success", "message" => "¡Platillo agregado al menú de SOFTWADZ exitosamente!"]);
        
    } catch (PDOException $e) {
        // Manejo de errores amigable si se viola alguna regla (como intentar poner precio 0)
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Faltan datos para registrar el producto."]);
}
?>