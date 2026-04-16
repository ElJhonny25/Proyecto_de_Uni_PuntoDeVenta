<?php
// abrir_turno.php
require_once 'Proyecto_de_Uni_PuntoDeVenta/Conexion.php';
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

        // 2. Verificar si ESTE empleado ya tiene un turno abierto
        $sqlCheck = "SELECT ID_TURNO FROM TURNO WHERE ID_EMPLEADO = :id_empleado AND ESTADO = 'Abierto'";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id_empleado', $idEmpleado);
        $stmtCheck->execute();

        if ($stmtCheck->fetch()) {
            echo json_encode(["status" => "error", "message" => "Hola $nombreEmpleado, ya tienes un turno abierto actualmente."]);
        } else {
            // 3. Abrir el turno exclusivo para este empleado
            // Usamos OUTPUT INSERTED para devolver la hora exacta del servidor SQL
            $sqlInsert = "INSERT INTO TURNO (ID_EMPLEADO, ESTADO) 
                          OUTPUT CONVERT(varchar, INSERTED.FECHA_APERTURA, 120) AS HORA 
                          VALUES (:id_empleado, 'Abierto')";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bindParam(':id_empleado', $idEmpleado);
            $stmtInsert->execute();
            
            $resultado = $stmtInsert->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "status" => "success", 
                "message" => "¡Turno abierto exitosamente para $nombreEmpleado!",
                "hora" => $resultado['HORA']
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se recibió el NIP."]);
}
?>