<?php
require_once '../Conexion.php';

try {
    // 1. Buscamos el turno abierto
    $sqlTurno = "SELECT TOP 1 ID_TURNO FROM TURNO_GENERAL WHERE ESTADO = 'Abierto' ORDER BY ID_TURNO DESC";
    $stmtTurno = $conn->prepare($sqlTurno);
    $stmtTurno->execute();
    $turno = $stmtTurno->fetch(PDO::FETCH_ASSOC);

    if (!$turno) {
        echo json_encode(["status" => "error", "message" => "No hay un Turno General abierto. Abre el turno en el Dashboard."]);
        exit;
    }

    $idTurno = $turno['ID_TURNO'];

    // 2. Recibimos los datos del cliente
    $telefono = $_POST['telefono'] ?? '';
    $nombre = $_POST['nombre'] ?? 'Cliente Gral';
    $domicilio = $_POST['domicilio'] ?? '';
    $colonia = $_POST['colonia'] ?? '';

    // 3. Calculamos el siguiente número de ORDEN para ESTE turno
    $sqlOrden = "SELECT ISNULL(MAX(NUM_ORDEN), 0) + 1 AS SIGUIENTE FROM CUENTA WHERE ID_TURNO = :idTurno";
    $stmtOrden = $conn->prepare($sqlOrden);
    $stmtOrden->bindParam(':idTurno', $idTurno);
    $stmtOrden->execute();
    $numOrden = $stmtOrden->fetch(PDO::FETCH_ASSOC)['SIGUIENTE'];

    // 4. Insertamos la cuenta en SQL Server para "apartar" el FOLIO
    $sqlInsert = "INSERT INTO CUENTA (NUM_ORDEN, ID_TURNO, TIPO_ORDEN, IDENTIFICADOR, NOMBRE_CLIENTE, ESTADO) 
                  VALUES (:orden, :turno, 'Domicilio', :tel, :nombre, 'Pendiente')";
    
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bindParam(':orden', $numOrden);
    $stmtInsert->bindParam(':turno', $idTurno);
    $stmtInsert->bindParam(':tel', $telefono);
    $stmtInsert->bindParam(':nombre', $nombre);
    $stmtInsert->execute();

    // Obtenemos el FOLIO que SQL Server generó automáticamente
    $folioGenerado = $conn->lastInsertId();

    echo json_encode([
        "status" => "success", 
        "folio" => $folioGenerado, 
        "orden" => $numOrden,
        "id_turno" => $idTurno
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>