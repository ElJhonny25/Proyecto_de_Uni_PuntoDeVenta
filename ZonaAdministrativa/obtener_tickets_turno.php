<?php
require_once '../Conexion.php';

$idTurno = $_GET['id_turno'] ?? '';

if (empty($idTurno)) {
    echo json_encode(["status" => "error", "message" => "Falta ID del turno"]);
    exit;
}

try {
    // Obtener cuentas del turno
    $sql = "SELECT FOLIO, NUM_ORDEN, TIPO_ORDEN, IDENTIFICADOR, TOTAL 
            FROM CUENTA 
            WHERE ID_TURNO = ? AND ESTADO = 'Pagado'
            ORDER BY NUM_ORDEN";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$idTurno]);
    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Para cada cuenta, obtener sus productos
    foreach ($cuentas as &$cuenta) {
        $stmtD = $conn->prepare("SELECT p.NOMBRE, d.CANTIDAD, d.PRECIO_UNITARIO, d.SUBTOTAL 
                                 FROM DETALLE_CUENTA d 
                                 JOIN PRODUCTO p ON d.ID_PRODUCTO = p.ID_PRODUCTO 
                                 WHERE d.FOLIO_CUENTA = ?");
        $stmtD->execute([$cuenta['FOLIO']]);
        $cuenta['items'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(["status" => "success", "data" => $cuentas]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>