<?php
// Conexion/abrir_turno.php
require_once 'Conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nip'])) {
    try {
        $stmtEmp = $conn->prepare("SELECT ID_EMPLEADO, NOMBRE FROM EMPLEADO WHERE NIP = :nip");
        $stmtEmp->bindParam(':nip', $data['nip']);
        $stmtEmp->execute();
        $empleado = $stmtEmp->fetch(PDO::FETCH_ASSOC);

        if (!$empleado) {
            echo json_encode(["status" => "error", "message" => "NIP incorrecto. Empleado no encontrado."]);
            exit;
        }

        $id_empleado = $empleado['ID_EMPLEADO'];
        $nombre_emp = $empleado['NOMBRE'];

        $check = $conn->query("SELECT COUNT(*) FROM TURNO WHERE ESTADO = 'Abierto'");
        if ($check->fetchColumn() > 0) {
            echo json_encode(["status" => "error", "message" => "Ya existe un turno abierto. Debes cerrarlo primero."]);
            exit;
        }

        // SOLUCIÓN: Usamos OUTPUT para que SQL nos devuelva la hora en el mismo paso
        $sql = "INSERT INTO TURNO (ID_EMPLEADO, ESTADO) 
                OUTPUT CONVERT(varchar, INSERTED.FECHA_APERTURA, 120) AS HORA 
                VALUES (:id_emp, 'Abierto')";
        
        $stmtInsert = $conn->prepare($sql);
        $stmtInsert->bindParam(':id_emp', $id_empleado);
        $stmtInsert->execute();
        
        // Atrapamos la hora que nos devolvió el OUTPUT
        $row = $stmtInsert->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success", 
            "hora" => $row['HORA'], 
            "message" => "Turno ABIERTO exitosamente por $nombre_emp."
        ]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se envió ningún NIP."]);
}
?>