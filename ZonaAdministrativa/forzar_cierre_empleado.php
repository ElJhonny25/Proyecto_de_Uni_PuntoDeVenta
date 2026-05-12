<?php
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);
$idTurnoEmp = $data['id'] ?? '';

try {
    $stmt = $conn->prepare("UPDATE TURNO_EMPLEADO SET ESTADO = 'Cerrado', HORA_FIN = GETDATE() WHERE ID_TURNO_EMP = ?");
    $stmt->execute([$idTurnoEmp]);
    echo json_encode(["status" => "success", "message" => "Turno finalizado por el administrador."]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>