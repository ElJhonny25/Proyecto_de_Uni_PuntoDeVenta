<?php
session_start();
require_once '../Conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$idDetalle = $data['id_detalle'] ?? ''; // ID del registro en DETALLE_CUENTA

if (empty($idDetalle)) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

try {
    // Obtener el folio y el subtotal antes de eliminar
    $stmt = $conn->prepare("SELECT FOLIO_CUENTA, SUBTOTAL FROM DETALLE_CUENTA WHERE ID_DETALLE = ?");
    $stmt->execute([$idDetalle]);
    $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$detalle) throw new Exception("Producto no encontrado.");

    $folio = $detalle['FOLIO_CUENTA'];
    $subtotal = $detalle['SUBTOTAL'];

    // Eliminar el detalle
    $stmtDel = $conn->prepare("DELETE FROM DETALLE_CUENTA WHERE ID_DETALLE = ?");
    $stmtDel->execute([$idDetalle]);

    // Actualizar el total de la cuenta
    $stmtUpd = $conn->prepare("UPDATE CUENTA SET TOTAL = TOTAL - ? WHERE FOLIO = ?");
    $stmtUpd->execute([$subtotal, $folio]);

    echo json_encode(["status" => "success", "message" => "Producto eliminado de la cuenta."]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>