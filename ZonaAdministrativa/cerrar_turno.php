<?php
session_start();
require_once '../Conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$nip = $data['nip'] ?? '';

try {
    // 1. Identificar quién intenta cerrar
    if ($nip === "4375879703" || $nip === "27") { 
        $esAdmin = true; 
    } else {
        $stmt = $conn->prepare("SELECT ID_EMPLEADO, NOMBRE FROM EMPLEADO WHERE NIP = ?");
        $stmt->execute([$nip]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$empleado) throw new Exception("NIP incorrecto.");
        $esAdmin = false;
        $idEmpleado = $empleado['ID_EMPLEADO'];
    }

    // 2. Buscar el turno general abierto
    $stmtGral = $conn->query("SELECT TOP 1 ID_TURNO, ESTADO FROM TURNO_GENERAL WHERE ESTADO = 'Abierto' ORDER BY ID_TURNO DESC");
    $turnoGral = $stmtGral->fetch(PDO::FETCH_ASSOC);
    if (!$turnoGral) throw new Exception("No hay ningún turno abierto actualmente.");
    $idTurnoGral = $turnoGral['ID_TURNO'];

    if ($esAdmin) {
        // --- 🛡️ CANDADO 1: CUENTAS PENDIENTES ---
        // Verificamos si hay mesas o pedidos a domicilio que no se han cobrado
        $stmtCuentas = $conn->prepare("SELECT COUNT(*) as Pendientes FROM CUENTA WHERE ID_TURNO = ? AND ESTADO IN ('Pendiente', 'en-camino')");
        $stmtCuentas->execute([$idTurnoGral]);
        $pendientes = $stmtCuentas->fetch(PDO::FETCH_ASSOC)['Pendientes'];

        if ($pendientes > 0) {
            throw new Exception("🛑 BLOQUEO DE SEGURIDAD: Hay $pendientes cuentas sin cobrar. Debes liquidar todas las mesas y pedidos antes de cerrar el turno.");
        }

        // --- 🛡️ CANDADO 2: TURNOS DE MESEROS ---
        // Verificamos si algún mesero o repartidor olvidó checar su salida
        $stmtPersonal = $conn->prepare("SELECT COUNT(*) as Activos FROM TURNO_EMPLEADO WHERE ID_TURNO_GENERAL = ? AND ESTADO = 'Abierto'");
        $stmtPersonal->execute([$idTurnoGral]);
        $empleadosActivos = $stmtPersonal->fetch(PDO::FETCH_ASSOC)['Activos'];

        if ($empleadosActivos > 0) {
            throw new Exception("🛑 BLOQUEO DE SEGURIDAD: Hay $empleadosActivos empleados con turno abierto. Pídeles que chequen su salida o ciérrales el turno desde el Gestor.");
        }

        // Si pasó ambos candados, cerramos el turno general
        $stmtClose = $conn->prepare("UPDATE TURNO_GENERAL SET ESTADO = 'Cerrado', FECHA_CIERRE = GETDATE() WHERE ID_TURNO = ?");
        $stmtClose->execute([$idTurnoGral]);

        echo json_encode(["status" => "success", "message" => "✅ Turno General CERRADO. Caja cuadrada y personal liberado.", "hora" => date('H:i:s')]);

    } else {
        // Lógica para que el mesero cierre SU PROPIO turno
        $stmtTurnoEmp = $conn->prepare("SELECT ID_TURNO_EMP FROM TURNO_EMPLEADO WHERE ID_EMPLEADO = ? AND ESTADO = 'Abierto'");
        $stmtTurnoEmp->execute([$idEmpleado]);
        $turnoEmp = $stmtTurnoEmp->fetch(PDO::FETCH_ASSOC);

        if (!$turnoEmp) throw new Exception("No tienes un turno abierto para cerrar.");

        $stmtCloseEmp = $conn->prepare("UPDATE TURNO_EMPLEADO SET ESTADO = 'Cerrado', HORA_FIN = GETDATE() WHERE ID_TURNO_EMP = ?");
        $stmtCloseEmp->execute([$turnoEmp['ID_TURNO_EMP']]);

        echo json_encode(["status" => "success", "message" => "👤 Salida registrada para " . $empleado['NOMBRE'] . ". ¡Buen descanso!", "hora" => date('H:i:s')]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>