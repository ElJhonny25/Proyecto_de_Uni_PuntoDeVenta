<?php
require_once '../Conexion.php'; // Ajusta la ruta a tu conexión

$folio = $_POST['folio'] ?? null;

if (!$folio) {
    echo json_encode(['status' => 'error', 'message' => 'Folio no recibido.']);
    exit;
}

try {
    // Actualizamos el estado de la cuenta en SQL Server
    $sql = "UPDATE CUENTA SET ESTADO = 'Pagado' WHERE FOLIO = :folio";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':folio' => $folio]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Cuenta cobrada con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró la cuenta o ya estaba pagada.']);
    }

} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>