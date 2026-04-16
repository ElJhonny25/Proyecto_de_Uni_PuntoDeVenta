<?php
// cerrar_turno.php
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nip'])) {
    try {
        // 1. Buscar al empleado por su NIP
        $sqlEmpleado = "SELECT ID_EMPLEADO, NOMBRE FROM EMPLEADO WHERE NIP = :nip";
        $stmtEmp = $conn->prepare($sqlEmpleado);
        $stmtEmp->bindParam(':nip', $data['nip']);
        $stmtEmp->execute();
        $empleado = $stmtEmp->fetch(PDO::FETCH_ASSOC);

        if (!$empleado) {
            echo json_encode(["status" => "error", "message" => "El NIP ingresado no existe."]);
            exit;
        }

        $idEmpleado = $empleado['ID_EMPLEADO'];
        $nombreEmpleado = $empleado['NOMBRE'];

        // 2. Verificar si ESTE empleado tiene un turno abierto para cerrarlo
        $sqlCheck = "SELECT ID_TURNO FROM TURNO WHERE ID_EMPLEADO = :id_empleado AND ESTADO = 'Abierto'";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id_empleado', $idEmpleado);
        $stmtCheck->execute();
        
        $turnoAbierto = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($turnoAbierto) {
            $idTurno = $turnoAbierto['ID_TURNO'];

            // 3. Actualizar solo ese turno a 'Cerrado' y poner la fecha de cierre
            $sqlUpdate = "UPDATE TURNO 
                          SET ESTADO = 'Cerrado', FECHA_CIERRE = GETDATE() 
                          OUTPUT CONVERT(varchar, INSERTED.FECHA_CIERRE, 120) AS HORA_CIERRE
                          WHERE ID_TURNO = :id_turno";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':id_turno', $idTurno);
            $stmtUpdate->execute();

            $resultado = $stmtUpdate->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "status" => "success", 
                "message" => "Turno cerrado correctamente para $nombreEmpleado. ¡Buen trabajo!",
                "hora" => $resultado['HORA_CIERRE']
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Hola $nombreEmpleado, no tienes ningún turno abierto en este momento."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se recibió el NIP."]);
}
?>