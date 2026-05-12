<?php
session_start();
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);
$nip = $data['nip'] ?? '';

try {
    // LLAVE MAESTRA: El único que no necesita checar entrada (Uso de emergencia)
    if ($nip === "4375879703") {
        echo json_encode(["status" => "success", "empleado" => ["id" => 0, "nip" => $nip, "nombre" => "SOPORTE", "cargo" => "Administrador", "foto" => null]]);
        exit;
    }

    // Buscamos al empleado y su cargo
    $sql = "SELECT E.ID_EMPLEADO AS id, E.NIP AS nip, E.NOMBRE AS nombre, C.NOMBRE_CARGO AS cargo, E.FOTO AS foto 
            FROM EMPLEADO E
            INNER JOIN CARGO C ON E.ID_CARGO = C.ID_CARGO
            WHERE E.NIP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nip]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($empleado) {
        // 🛡️ REGLA DE ORO: Validar que el empleado TENGA TURNO ABIERTO
        // Buscamos cualquier registro que diga 'Abierto' (quitando espacios con TRIM)
        $stmtTurno = $conn->prepare("SELECT ID_TURNO_EMP FROM TURNO_EMPLEADO WHERE ID_EMPLEADO = ? AND (TRIM(ESTADO) = 'Abierto' OR ESTADO = 'Abierto')");
        $stmtTurno->execute([$empleado['id']]);
        $turnoActivo = $stmtTurno->fetch();
        
        if (!$turnoActivo) {
            // BLOQUEO TOTAL: Si no hay registro de entrada, no pasa, sea quien sea.
            echo json_encode(["status" => "error", "message" => "🛑 ACCESO DENEGADO: No has registrado tu entrada. Primero da clic en 'Abrir Turno' con tu NIP."]);
        } else {
            // Solo si tiene turno, lo dejamos entrar
            echo json_encode(["status" => "success", "empleado" => $empleado]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "El NIP ingresado no existe en el sistema."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error técnico: " . $e->getMessage()]);
}
?>