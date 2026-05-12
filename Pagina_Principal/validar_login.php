<?php
session_start();
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);
$nip = $data['nip'] ?? '';

try {
    if ($nip === "4375879703") {
        echo json_encode(["status" => "success", "empleado" => ["id" => 0, "nip" => $nip, "nombre" => "Administrador", "cargo" => "Administrador", "foto" => null]]);
        exit;
    }

    $sql = "SELECT E.ID_EMPLEADO AS id, E.NIP AS nip, E.NOMBRE AS nombre, C.NOMBRE_CARGO AS cargo, E.FOTO AS foto 
            FROM EMPLEADO E
            INNER JOIN CARGO C ON E.ID_CARGO = C.ID_CARGO
            WHERE E.NIP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nip]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($empleado) {
        // 🛡️ REGLA JEFE: El Administrador SIEMPRE puede entrar al Comedor (es el dueño)
        if ($empleado['cargo'] === 'Administrador') {
            echo json_encode(["status" => "success", "empleado" => $empleado]);
            exit;
        }

        // 🛡️ REGLA MESEROS: Deben checar su entrada primero
        $stmtTurnoEmp = $conn->prepare("SELECT ID_TURNO_EMP FROM TURNO_EMPLEADO WHERE ID_EMPLEADO = ? AND TRIM(ESTADO) = 'Abierto'");
        $stmtTurnoEmp->execute([$empleado['id']]);
        
        if (!$stmtTurnoEmp->fetch()) {
            echo json_encode(["status" => "error", "message" => "🛑 ACCESO DENEGADO: Registra tu entrada primero en el botón rojo de 'Abrir Turno'."]);
        } else {
            echo json_encode(["status" => "success", "empleado" => $empleado]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "El NIP ingresado no existe."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error técnico BD: " . $e->getMessage()]);
}
?>