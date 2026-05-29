<?php
session_start();
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);
$nip = $data['nip'] ?? '';

try {
    // LLAVE MAESTRA
    if ($nip === "4375879703") {
        echo json_encode(["status" => "success", "empleado" => ["id" => 0, "nip" => $nip, "nombre" => "SOPORTE", "cargo" => "Administrador", "foto" => null]]);
        exit;
    }

    // Buscar empleado y cargo
    $sql = "SELECT E.ID_EMPLEADO AS id, E.NIP AS nip, E.NOMBRE AS nombre, C.NOMBRE_CARGO AS cargo, E.FOTO AS foto 
            FROM EMPLEADO E
            INNER JOIN CARGO C ON E.ID_CARGO = C.ID_CARGO
            WHERE E.NIP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nip]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        echo json_encode(["status" => "error", "message" => "El NIP ingresado no existe en el sistema."]);
        exit;
    }

    // Verificar si ya tiene turno abierto
    $stmtTurno = $conn->prepare("SELECT ID_TURNO_EMP FROM TURNO_EMPLEADO WHERE ID_EMPLEADO = ? AND (TRIM(ESTADO) = 'Abierto' OR ESTADO = 'Abierto')");
    $stmtTurno->execute([$empleado['id']]);
    $turnoActivo = $stmtTurno->fetch();

    if (!$turnoActivo) {
        // Intentar abrir turno automáticamente
        $stmtGral = $conn->query("SELECT TOP 1 ID_TURNO FROM TURNO_GENERAL WHERE TRIM(ESTADO) = 'Abierto' ORDER BY ID_TURNO DESC");
        $turnoGral = $stmtGral->fetch(PDO::FETCH_ASSOC);

        if (!$turnoGral) {
            echo json_encode(["status" => "error", "message" => "🛑 No hay turno general abierto. Pide al administrador que abra el restaurante primero."]);
            exit;
        }

        $idTurnoGral = $turnoGral['ID_TURNO'];
        $conn->prepare("INSERT INTO TURNO_EMPLEADO (ID_TURNO_GENERAL, ID_EMPLEADO, ESTADO, HORA_INICIO) VALUES (?, ?, 'Abierto', GETDATE())")
             ->execute([$idTurnoGral, $empleado['id']]);
    }

    echo json_encode(["status" => "success", "empleado" => $empleado]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error técnico: " . $e->getMessage()]);
}
?>