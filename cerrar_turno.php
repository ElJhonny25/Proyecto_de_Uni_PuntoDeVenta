<?php
// Conexion/cerrar_turno.php
require_once 'Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nip'])) {
    try {
        // 1. Validar que el NIP exista
        $stmtEmp = $conn->prepare("SELECT ID_EMPLEADO, NOMBRE FROM EMPLEADO WHERE NIP = :nip");
        $stmtEmp->bindParam(':nip', $data['nip']);
        $stmtEmp->execute();
        $empleado = $stmtEmp->fetch(PDO::FETCH_ASSOC);

        if (!$empleado) {
            echo json_encode(["status" => "error", "message" => "NIP incorrecto. Empleado no encontrado."]);
            exit;
        }

        $nombre_emp = $empleado['NOMBRE'];

        // 2. Buscar el turno que está abierto actualmente
        $stmt = $conn->query("SELECT ID_TURNO FROM TURNO WHERE ESTADO = 'Abierto'");
        $turno = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$turno) {
            echo json_encode(["status" => "error", "message" => "No hay ningún turno abierto para cerrar."]);
            exit;
        }

        $id = $turno['ID_TURNO'];

        // 3. Actualizamos ese turno poniéndole la hora de cierre
        $sql = "UPDATE TURNO SET FECHA_CIERRE = GETDATE(), ESTADO = 'Cerrado' WHERE ID_TURNO = $id";
        $conn->exec($sql);

        // 4. Obtenemos la hora exacta de cierre
        $stmtHora = $conn->query("SELECT CONVERT(varchar, FECHA_CIERRE, 120) as HORA FROM TURNO WHERE ID_TURNO = $id");
        $rowHora = $stmtHora->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success", 
            "hora" => $rowHora['HORA'], 
            "message" => "Turno CERRADO exitosamente por $nombre_emp."
        ]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se envió ningún NIP."]);
}
?>