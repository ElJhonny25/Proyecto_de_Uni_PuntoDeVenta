<?php
require_once '../Conexion.php'; // Ajusta la ruta a tu archivo de conexión

try {
    // 1. Verificamos cuál es el turno que está actualmente abierto
    $sqlTurno = "SELECT ID_TURNO FROM TURNO_GENERAL WHERE ESTADO = 'Abierto'";
    $stmtTurno = $conn->query($sqlTurno);
    $turno = $stmtTurno->fetch(PDO::FETCH_ASSOC);

    if (!$turno) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró ningún turno abierto para cerrar.']);
        exit;
    }

    $idTurno = $turno['ID_TURNO'];

    // 2. Ejecutamos el cierre poniendo la fecha actual
    $sqlUpdate = "UPDATE TURNO_GENERAL 
                  SET ESTADO = 'Cerrado', 
                      FECHA_CIERRE = GETDATE() 
                  WHERE ID_TURNO = :turno";
    
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->execute([':turno' => $idTurno]);

    echo json_encode(['status' => 'success', 'message' => 'Turno cerrado y corte de caja realizado con éxito.']);

} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>