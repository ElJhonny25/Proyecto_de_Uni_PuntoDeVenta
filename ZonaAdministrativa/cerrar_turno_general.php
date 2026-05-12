<?php
session_start();
require_once '../Conexion.php';

try {
    // Buscamos el turno general abierto
    $stmtGral = $conn->query("SELECT TOP 1 ID_TURNO FROM TURNO_GENERAL WHERE TRIM(ESTADO) = 'Abierto' ORDER BY ID_TURNO DESC");
    $turnoGral = $stmtGral->fetch(PDO::FETCH_ASSOC);
    
    if (!$turnoGral) {
        throw new Exception("No hay ningún turno de restaurante abierto actualmente.");
    }
    $idTurnoGral = $turnoGral['ID_TURNO'];

    // --- 🛡️ CANDADO 1: CUENTAS SIN COBRAR (BARRIDO GLOBAL) ---
    // Quitamos el "WHERE ID_TURNO_GENERAL = ?" para cazar cuentas fantasma
    $stmtCuentas = $conn->query("SELECT COUNT(*) FROM CUENTA WHERE TRIM(ESTADO) IN ('Pendiente', 'en-camino')");
    $pendientes = $stmtCuentas->fetchColumn();

    if ($pendientes > 0) {
        throw new Exception("🛑 BLOQUEO: Hay $pendientes cuentas sin cobrar en el sistema. Cóbralas o cancélalas todas antes de cerrar la caja.");
    }

    // --- 🛡️ CANDADO 2: EMPLEADOS ACTIVOS (BARRIDO GLOBAL) ---
    // Caza a cualquier empleado que siga abierto, no importa de qué turno sea
    $stmtPersonal = $conn->query("SELECT COUNT(*) FROM TURNO_EMPLEADO WHERE TRIM(ESTADO) = 'Abierto' OR ESTADO = 'Abierto'");
    $empleadosActivos = $stmtPersonal->fetchColumn();

    if ($empleadosActivos > 0) {
        throw new Exception("🛑 BLOQUEO: Hay $empleadosActivos empleados trabajando (o turnos fantasma atorados). Ve al 'Gestor de Turnos' y fuerza la salida de TODOS antes de cerrar la caja.");
    }

    // --- SI PASA LOS CANDADOS: CERRAMOS EL TURNO GENERAL ---
    $conn->prepare("UPDATE TURNO_GENERAL SET ESTADO = 'Cerrado', FECHA_CIERRE = GETDATE() WHERE ID_TURNO = ?")->execute([$idTurnoGral]);

    echo json_encode(["status" => "success", "message" => "✅ CIERRE EXITOSO: La caja ha cuadrado y no quedó nada pendiente."]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>