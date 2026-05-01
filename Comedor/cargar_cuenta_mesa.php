<?php
require_once '../Conexion.php'; // Ajusta la ruta a tu conexión

$mesa = $_GET['mesa'] ?? '';

try {
    // 1. Buscamos el Folio pendiente de esta mesa
    $sql = "SELECT FOLIO, TOTAL FROM CUENTA WHERE IDENTIFICADOR = :mesa AND ESTADO = 'Pendiente'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':mesa' => $mesa]);
    $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cuenta) {
        echo json_encode(['status' => 'empty']);
        exit;
    }

    // 2. Extraemos el detalle cruzando con la tabla PRODUCTO para obtener el nombre
    $sqlDet = "SELECT p.NOMBRE, d.CANTIDAD, d.PRECIO_UNITARIO, d.SUBTOTAL 
               FROM DETALLE_CUENTA d 
               JOIN PRODUCTO p ON d.ID_PRODUCTO = p.ID_PRODUCTO 
               WHERE d.FOLIO_CUENTA = :folio";
    
    $stmtDet = $conn->prepare($sqlDet);
    $stmtDet->execute([':folio' => $cuenta['FOLIO']]);
    $items = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success', 
        'folio' => $cuenta['FOLIO'], 
        'total' => $cuenta['TOTAL'], 
        'items' => $items
    ]);

} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>