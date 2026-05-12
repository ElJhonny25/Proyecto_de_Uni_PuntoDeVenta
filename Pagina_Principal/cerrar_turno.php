<?php
session_start();
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);
$nip = $data['nip'] ?? '';

try {
    // 1. Validar quién cierra
    $stmtUser = $conn->prepare("SELECT E.ID_EMPLEADO, C.NOMBRE_CARGO 
                                FROM EMPLEADO E 
                                INNER JOIN CARGO C ON E.ID_CARGO = C.ID_CARGO 
                                WHERE E.NIP = ?");
    $stmtUser->execute([$nip]);
    $quienCierra = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Si es admin o la llave maestra, puede cerrar el restaurante
    if (($quienCierra && $quienCierra['NOMBRE_CARGO'] === 'Administrador') || $nip === "4375879703") {
        
        $idEmpleadoAdmin = $quienCierra ? $quienCierra['ID_EMPLEADO'] : 0;

        // --- 🛡️ CANDADO 1: ¿HAY CUENTAS ABIERTAS? ---
        $stmtCuentas = $conn->query("SELECT COUNT(*) FROM CUENTA WHERE TRIM(ESTADO) IN ('Pendiente', 'en-camino')");
        if ($stmtCuentas->fetchColumn() > 0) {
            throw new Exception("🛑 ERROR: Hay mesas ocupadas o pedidos en ruta. Cóbralos todos antes de cerrar caja.");
        }

        // --- 🛡️ CANDADO 2: ¿HAY EMPLEADOS ACTIVOS? ---
        // Buscamos a CUALQUIERA que no sea el admin que está cerrando
        $stmtPersonal = $conn->prepare("SELECT COUNT(*) FROM TURNO_EMPLEADO WHERE (TRIM(ESTADO) = 'Abierto' OR ESTADO = 'Abierto') AND ID_EMPLEADO != ?");
        $stmtPersonal->execute([$idEmpleadoAdmin]);
        $empleadosOlvidados = $stmtPersonal->fetchColumn();

        if ($empleadosOlvidados > 0) {
            throw new Exception("🛑 BLOQUEO: Hay $empleadosOlvidados empleados con turno abierto. Cierra sus sesiones primero.");
        }

        // --- TODO LIMPIO: PROCEDEMOS AL CIERRE ---
        // Cerramos el turno del Admin
        $conn->prepare("UPDATE TURNO_EMPLEADO SET ESTADO = 'Cerrado', HORA_FIN = GETDATE() WHERE ID_EMPLEADO = ? AND (TRIM(ESTADO) = 'Abierto' OR ESTADO = 'Abierto')")
             ->execute([$idEmpleadoAdmin]);

        // Cerramos el Turno General
        $conn->exec("UPDATE TURNO_GENERAL SET ESTADO = 'Cerrado', FECHA_CIERRE = GETDATE() WHERE TRIM(ESTADO) = 'Abierto'");

        echo json_encode(["status" => "success", "message" => "✅ CIERRE EXITOSO: Restaurante cerrado y personal liberado.", "hora" => date('H:i:s')]);

    } else {
        // Lógica para mesero normal cerrando SU sesión
        $stmtEmp = $conn->prepare("SELECT ID_TURNO_EMP FROM TURNO_EMPLEADO WHERE ID_EMPLEADO = ? AND (TRIM(ESTADO) = 'Abierto' OR ESTADO = 'Abierto')");
        $stmtEmp->execute([$quienCierra['ID_EMPLEADO']]);
        $turno = $stmtEmp->fetch();

        if (!$turno) throw new Exception("No tienes una entrada registrada para este turno.");

        $conn->prepare("UPDATE TURNO_EMPLEADO SET ESTADO = 'Cerrado', HORA_FIN = GETDATE() WHERE ID_TURNO_EMP = ?")
             ->execute([$turno['ID_TURNO_EMP']]);

        echo json_encode(["status" => "success", "message" => "Salida registrada. ¡Hasta mañana!", "hora" => date('H:i:s')]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>