<?php
session_start();
require_once '../Conexion.php';

$mesa = $_POST['mesa'] ?? '';

if (empty($mesa)) {
    echo json_encode(["status" => "error", "message" => "Falta el identificador de la mesa"]);
    exit;
}

try {
    // Buscar la cuenta pendiente de la mesa
    $stmt = $conn->prepare("SELECT FOLIO FROM CUENTA WHERE IDENTIFICADOR = ? AND ESTADO = 'Pendiente'");
    $stmt->execute([$mesa]);
    $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cuenta) throw new Exception("No hay cuenta pendiente para esta mesa.");

    // Eliminar detalles
    $conn->prepare("DELETE FROM DETALLE_CUENTA WHERE FOLIO_CUENTA = ?")->execute([$cuenta['FOLIO']]);
    // Eliminar cuenta
    $conn->prepare("DELETE FROM CUENTA WHERE FOLIO = ?")->execute([$cuenta['FOLIO']]);

    // Liberar mesa
    $conn->prepare("UPDATE MESA SET ESTADO = 'libre' WHERE IDENTIFICADOR = ?")->execute([$mesa]);

    echo json_encode(["status" => "success", "message" => "Cuenta cancelada y mesa liberada."]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>