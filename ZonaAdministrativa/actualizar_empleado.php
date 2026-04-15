<?php
// Conexion/actualizar_empleado.php
require_once 'Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_empleado']) && isset($data['nombre']) && isset($data['apellido_p'])) {
    try {
        // Verificar si se envió una imagen nueva
        if (isset($data['foto']) && !empty($data['foto'])) {
            // Actualizamos todo, incluyendo la foto
            $sql = "UPDATE EMPLEADO 
                    SET NOMBRE = :nom, APELLIDO_P = :app, APELLIDO_M = :apm, ID_CARGO = :cargo, FOTO = :foto 
                    WHERE ID_EMPLEADO = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':foto', $data['foto']);
        } else {
            // Actualizamos los datos pero respetamos la foto que ya tenía
            $sql = "UPDATE EMPLEADO 
                    SET NOMBRE = :nom, APELLIDO_P = :app, APELLIDO_M = :apm, ID_CARGO = :cargo 
                    WHERE ID_EMPLEADO = :id";
            $stmt = $conn->prepare($sql);
        }

        // Parámetros comunes
        $stmt->bindParam(':nom', $data['nombre']);
        $stmt->bindParam(':app', $data['apellido_p']);
        $stmt->bindParam(':apm', $data['apellido_m']);
        $stmt->bindParam(':cargo', $data['id_cargo']);
        $stmt->bindParam(':id', $data['id_empleado']);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se pudo actualizar."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios."]);
}
?>