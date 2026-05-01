<?php
require_once '../Conexion.php'; 

try {
    // 1. Buscar el turno abierto con tus nombres de columnas EXACTOS
    $sqlTurno = "SELECT ID_TURNO, FONDO_CAJA, FECHA_APERTURA FROM TURNO_GENERAL WHERE ESTADO = 'Abierto'";
    $stmtTurno = $conn->query($sqlTurno);
    $turno = $stmtTurno->fetch(PDO::FETCH_ASSOC);

    if (!$turno) {
        echo json_encode(['status' => 'error', 'message' => 'No hay un turno abierto actualmente.']);
        exit;
    }

    $idTurno = $turno['ID_TURNO'];

    // 2. Sumar las ventas y los tickets de este turno
    $sqlVentas = "SELECT ISNULL(SUM(TOTAL), 0) as TOTAL_VENDIDO, COUNT(FOLIO) as NUM_TICKETS 
                  FROM CUENTA WHERE ID_TURNO = :turno AND ESTADO = 'Pagado'";
    $stmtVentas = $conn->prepare($sqlVentas);
    $stmtVentas->execute([':turno' => $idTurno]);
    $ventas = $stmtVentas->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'turno' => $idTurno,
        'fechaApertura' => $turno['FECHA_APERTURA'],
        'fondoInicial' => $turno['FONDO_CAJA'],
        'totalVentas' => $ventas['TOTAL_VENDIDO'],
        'numTickets' => $ventas['NUM_TICKETS'],
        'totalEnCaja' => $turno['FONDO_CAJA'] + $ventas['TOTAL_VENDIDO']
    ]);

} catch(Exception $e) {
    // Si SQL falla, enviamos el error real para que no se quede mudo
    echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . $e->getMessage()]);
}
?>