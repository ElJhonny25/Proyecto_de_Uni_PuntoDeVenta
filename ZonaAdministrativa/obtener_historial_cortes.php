<?php
require_once '../Conexion.php';

$fecha = $_GET['fecha'] ?? '';

try {
    $sql = "SELECT ID_TURNO, FECHA_APERTURA, FECHA_CIERRE, FONDO_CAJA 
            FROM TURNO_GENERAL 
            WHERE TRIM(ESTADO) = 'Cerrado'";
    $params = [];

    if (!empty($fecha)) {
        $sql .= " AND CONVERT(date, FECHA_APERTURA) = ?";
        $params[] = $fecha;
    }

    $sql .= " ORDER BY ID_TURNO DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Para cada turno, calculamos el total de ventas
    foreach ($turnos as &$turno) {
        $stmtV = $conn->prepare("SELECT ISNULL(SUM(TOTAL), 0) AS total FROM CUENTA WHERE ID_TURNO = ? AND ESTADO = 'Pagado'");
        $stmtV->execute([$turno['ID_TURNO']]);
        $turno['total'] = (float) $stmtV->fetch(PDO::FETCH_ASSOC)['total'];
    }

    echo json_encode(["status" => "success", "data" => $turnos]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>