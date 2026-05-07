<?php
session_start();
require_once '../Conexion.php';

// ⭐ LA SOLUCIÓN ESTRELLA: Validamos que el dato exista, pero permitimos que sea "0"
if (!isset($_POST['folio']) || $_POST['folio'] === '') {
    echo json_encode(['status' => 'error', 'message' => 'Folio no recibido.']);
    exit;
}

$folio = $_POST['folio'];

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