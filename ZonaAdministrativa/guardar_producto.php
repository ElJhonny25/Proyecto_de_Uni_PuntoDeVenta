<?php
session_start();
require_once '../Conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nombre']) && isset($data['precio']) && isset($data['id_categoria'])) {
    try {
        $nombre = trim($data['nombre']);
        $precio = floatval($data['precio']);
        // Forzamos a entero por seguridad
        $id_categoria = intval($data['id_categoria']);

        // Validación extra: si por error llega 0, lo rechazamos
        if ($id_categoria <= 0) {
            echo json_encode(["status" => "error", "message" => "Categoría inválida (0 o vacía)."]);
            exit;
        }

        $sql = "INSERT INTO PRODUCTO (NOMBRE, PRECIO, ID_CATEGORIA) 
                VALUES (:nombre, :precio, :id_categoria)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nombre'       => $nombre,
            ':precio'       => $precio,
            ':id_categoria' => $id_categoria
        ]);
        
        echo json_encode(["status" => "success", "message" => "¡Platillo agregado al menú exitosamente!"]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Faltan datos para registrar el producto."]);
}
?>