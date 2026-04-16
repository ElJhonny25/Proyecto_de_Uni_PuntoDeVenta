<?php
// Conexion/borrar_empleado.php
require_once 'Proyecto_de_Uni_PuntoDeVenta/Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_empleado'])) {
    try {
        $sql = "DELETE FROM EMPLEADO WHERE ID_EMPLEADO = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $data['id_empleado']);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se pudo eliminar."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>