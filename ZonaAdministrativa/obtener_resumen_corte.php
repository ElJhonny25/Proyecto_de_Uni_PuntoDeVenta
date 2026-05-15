<?php
session_start();
require_once '../Conexion.php';

try {
    // Buscar turno general abierto
    $sql = "SELECT TOP 1 ID_TURNO, FECHA_APERTURA, FONDO_CAJA 
            FROM TURNO_GENERAL 
            WHERE TRIM(ESTADO) = 'Abierto' 
            ORDER BY ID_TURNO DESC";
    $stmt = $conn->query($sql);
    $turno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$turno) {
        echo json_encode([
            "status" => "error",
            "message" => "No hay un turno abierto actualmente."
        ]);
        exit;
    }

    $idTurno = $turno['ID_TURNO'];

    // Total de ventas
    $sqlTotal = "SELECT ISNULL(SUM(TOTAL), 0) AS total FROM CUENTA WHERE ID_TURNO = ? AND ESTADO = 'Pagado'";
    $stmt = $conn->prepare($sqlTotal);
    $stmt->execute([$idTurno]);
    $totalVentas = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de tickets pagados
    $sqlTickets = "SELECT COUNT(*) AS num FROM CUENTA WHERE ID_TURNO = ? AND ESTADO = 'Pagado'";
    $stmt = $conn->prepare($sqlTickets);
    $stmt->execute([$idTurno]);
    $numTickets = (int) $stmt->fetch(PDO::FETCH_ASSOC)['num'];

    // Ventas comedor
    $sqlComedor = "SELECT ISNULL(SUM(TOTAL), 0) AS total FROM CUENTA WHERE ID_TURNO = ? AND ESTADO = 'Pagado' AND TIPO_ORDEN = 'Comedor'";
    $stmt = $conn->prepare($sqlComedor);
    $stmt->execute([$idTurno]);
    $ventaComedor = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Ventas domicilio
    $sqlDomicilio = "SELECT ISNULL(SUM(TOTAL), 0) AS total FROM CUENTA WHERE ID_TURNO = ? AND ESTADO = 'Pagado' AND TIPO_ORDEN = 'Domicilio'";
    $stmt = $conn->prepare($sqlDomicilio);
    $stmt->execute([$idTurno]);
    $ventaDomicilio = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        "status" => "success",
        "turno" => $turno['ID_TURNO'],
        "fondoInicial" => $turno['FONDO_CAJA'],
        "fechaApertura" => $turno['FECHA_APERTURA'],
        "total" => $totalVentas,
        "tickets" => $numTickets,
        "comedor" => $ventaComedor,
        "domicilio" => $ventaDomicilio
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>