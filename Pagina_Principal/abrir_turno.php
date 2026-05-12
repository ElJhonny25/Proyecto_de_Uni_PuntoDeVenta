<?php
session_start();
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);
$nip = $data['nip'] ?? '';

try {
    $esAdmin = false;
    $idEmpleado = null;
    $nombre = "Administrador";

    // 1. Identificar si es la llave maestra o un empleado real
    if ($nip === "4375879703") {
        $esAdmin = true;
    } else {
        $stmt = $conn->prepare("SELECT E.ID_EMPLEADO, E.NOMBRE, C.NOMBRE_CARGO 
                                FROM EMPLEADO E 
                                INNER JOIN CARGO C ON E.ID_CARGO = C.ID_CARGO 
                                WHERE E.NIP = ?");
        $stmt->execute([$nip]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$empleado) throw new Exception("NIP incorrecto o empleado no encontrado.");
        
        $idEmpleado = $empleado['ID_EMPLEADO'];
        $nombre = $empleado['NOMBRE'];
        if ($empleado['NOMBRE_CARGO'] === 'Administrador') {
            $esAdmin = true;
        }
    }

    // Buscar si el restaurante ya está abierto
    $stmtGral = $conn->query("SELECT TOP 1 ID_TURNO FROM TURNO_GENERAL WHERE TRIM(ESTADO) = 'Abierto' ORDER BY ID_TURNO DESC");
    $turnoGral = $stmtGral->fetch(PDO::FETCH_ASSOC);

    if ($esAdmin) {
        if (!$turnoGral) {
            // El Jefe abre el restaurante
            $conn->exec("INSERT INTO TURNO_GENERAL (ESTADO, FECHA_APERTURA) VALUES ('Abierto', GETDATE())");
            $idTurnoGral = $conn->lastInsertId(); // Tomamos el ID que se acaba de crear
        } else {
            $idTurnoGral = $turnoGral['ID_TURNO'];
        }

        // Magia: Le abrimos su turno de empleado al Jefe automáticamente para que pueda cobrar
        if ($idEmpleado) {
            $stmtCheck = $conn->prepare("SELECT ID_TURNO_EMP FROM TURNO_EMPLEADO WHERE ID_EMPLEADO = ? AND TRIM(ESTADO) = 'Abierto'");
            $stmtCheck->execute([$idEmpleado]);
            if (!$stmtCheck->fetch()) {
                $stmtIn = $conn->prepare("INSERT INTO TURNO_EMPLEADO (ID_TURNO_GENERAL, ID_EMPLEADO, ESTADO, HORA_INICIO) VALUES (?, ?, 'Abierto', GETDATE())");
                $stmtIn->execute([$idTurnoGral, $idEmpleado]);
            }
        }
        echo json_encode(["status" => "success", "message" => "🏢 RESTAURANTE ABIERTO / ACTIVO. Bienvenido $nombre.", "hora" => date('H:i:s')]);

    } else {
        // ES UN MESERO O REPARTIDOR
        if (!$turnoGral) throw new Exception("🛑 El Administrador aún no ha abierto el restaurante.");
        $idTurnoGral = $turnoGral['ID_TURNO'];

        $stmtTurnoEmp = $conn->prepare("SELECT ID_TURNO_EMP FROM TURNO_EMPLEADO WHERE ID_EMPLEADO = ? AND TRIM(ESTADO) = 'Abierto'");
        $stmtTurnoEmp->execute([$idEmpleado]);
        if ($stmtTurnoEmp->fetch()) throw new Exception("Ya tienes un turno abierto en este momento.");

        $sql = "INSERT INTO TURNO_EMPLEADO (ID_TURNO_GENERAL, ID_EMPLEADO, ESTADO, HORA_INICIO) VALUES (?, ?, 'Abierto', GETDATE())";
        $stmtInsert = $conn->prepare($sql);
        $stmtInsert->execute([$idTurnoGral, $idEmpleado]);
        
        echo json_encode(["status" => "success", "message" => "👤 ENTRADA REGISTRADA: Hola $nombre, buen turno.", "hora" => date('H:i:s')]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>