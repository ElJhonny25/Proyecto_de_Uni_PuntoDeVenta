<?php
session_start();
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);
$nip = $data['nip'] ?? '';

try {
    $esAdmin = false;
    $idEmpleado = null;
    $nombre = "Administrador";

    if ($nip === "4375879703") { 
        $esAdmin = true; 
    } else {
        $stmt = $conn->prepare("SELECT E.ID_EMPLEADO, E.NOMBRE, C.NOMBRE_CARGO 
                                FROM EMPLEADO E 
                                INNER JOIN CARGO C ON E.ID_CARGO = C.ID_CARGO 
                                WHERE E.NIP = ?");
        $stmt->execute([$nip]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$empleado) throw new Exception("NIP incorrecto.");
        
        $idEmpleado = $empleado['ID_EMPLEADO'];
        $nombre = $empleado['NOMBRE'];
        if ($empleado['NOMBRE_CARGO'] === 'Administrador') {
            $esAdmin = true;
        }
    }

    $stmtGral = $conn->query("SELECT TOP 1 ID_TURNO FROM TURNO_GENERAL WHERE TRIM(ESTADO) = 'Abierto' ORDER BY ID_TURNO DESC");
    $turnoGral = $stmtGral->fetch(PDO::FETCH_ASSOC);
    if (!$turnoGral) throw new Exception("No hay ningún turno general abierto en el restaurante.");
    $idTurnoGral = $turnoGral['ID_TURNO'];

    if ($esAdmin) {
        // --- 🛡️ CANDADO 1: MESAS SIN COBRAR (Usando TRIM por seguridad) ---
        $stmtCuentas = $conn->prepare("SELECT COUNT(*) as Pendientes FROM CUENTA WHERE ID_TURNO = ? AND TRIM(ESTADO) IN ('Pendiente', 'en-camino')");
        $stmtCuentas->execute([$idTurnoGral]);
        $pendientes = (int)$stmtCuentas->fetch(PDO::FETCH_ASSOC)['Pendientes'];

        if ($pendientes > 0) {
            throw new Exception("🛑 BLOQUEO DE SEGURIDAD: Hay $pendientes cuentas sin cobrar.");
        }

        // --- 🛡️ CANDADO 2: EMPLEADOS ACTIVOS ---
        // Excluimos al propio Administrador ($idEmpleado) de este conteo, para que él sí pueda cerrar.
        $empIdParam = $idEmpleado ? $idEmpleado : 0; 
        $stmtPersonal = $conn->prepare("SELECT COUNT(*) as Activos FROM TURNO_EMPLEADO WHERE ID_TURNO_GENERAL = ? AND TRIM(ESTADO) = 'Abierto' AND ID_EMPLEADO != ?");
        $stmtPersonal->execute([$idTurnoGral, $empIdParam]);
        $empleadosActivos = (int)$stmtPersonal->fetch(PDO::FETCH_ASSOC)['Activos'];

        if ($empleadosActivos > 0) {
            throw new Exception("🛑 BLOQUEO: Hay $empleadosActivos empleados con turno abierto. Ciérrales su turno desde el Gestor primero.");
        }

        // Si todo está cuadrado, cerramos el turno personal del Admin y cerramos el Restaurante
        if ($idEmpleado) {
            $conn->exec("UPDATE TURNO_EMPLEADO SET ESTADO = 'Cerrado', HORA_FIN = GETDATE() WHERE ID_EMPLEADO = $idEmpleado AND TRIM(ESTADO) = 'Abierto'");
        }
        $stmtClose = $conn->prepare("UPDATE TURNO_GENERAL SET ESTADO = 'Cerrado', FECHA_CIERRE = GETDATE() WHERE ID_TURNO = ?");
        $stmtClose->execute([$idTurnoGral]);

        echo json_encode(["status" => "success", "message" => "✅ Turno General CERRADO exitosamente.", "hora" => date('H:i:s')]);

    } else {
        // EMPLEADO NORMAL CIERRA SU TURNO
        $stmtTurnoEmp = $conn->prepare("SELECT ID_TURNO_EMP FROM TURNO_EMPLEADO WHERE ID_EMPLEADO = ? AND TRIM(ESTADO) = 'Abierto'");
        $stmtTurnoEmp->execute([$idEmpleado]);
        $turnoEmp = $stmtTurnoEmp->fetch(PDO::FETCH_ASSOC);

        if (!$turnoEmp) throw new Exception("No tienes un turno abierto para cerrar.");

        $stmtCloseEmp = $conn->prepare("UPDATE TURNO_EMPLEADO SET ESTADO = 'Cerrado', HORA_FIN = GETDATE() WHERE ID_TURNO_EMP = ?");
        $stmtCloseEmp->execute([$turnoEmp['ID_TURNO_EMP']]);

        echo json_encode(["status" => "success", "message" => "👤 Salida registrada para $nombre.", "hora" => date('H:i:s')]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>