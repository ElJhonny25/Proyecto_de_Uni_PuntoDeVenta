<?php
session_start();
require_once '../Conexion.php';

try {
    // 1. Buscamos el turno abierto
    $sqlTurno = "SELECT TOP 1 ID_TURNO FROM TURNO_GENERAL WHERE ESTADO = 'Abierto' ORDER BY ID_TURNO DESC";
    $turno = $conn->query($sqlTurno)->fetch(PDO::FETCH_ASSOC);

    if (!$turno) {
        echo json_encode(["status" => "error", "message" => "No hay un Turno abierto."]);
        exit;
    }
    $idTurno = $turno['ID_TURNO'];

    // 2. Recibimos los datos
    $telefono = $_POST['telefono'] ?? '0000000000';
    $nombre = $_POST['nombre'] ?? 'Cliente Gral';
    
    // Generamos un código temporal único (Ej. TEMP-58291)
    $codigoTmp = "TEMP-" . rand(100000, 999999);

    // 3. Calculamos la orden
    $sqlOrden = "SELECT ISNULL(MAX(NUM_ORDEN), 0) + 1 AS SIGUIENTE FROM CUENTA WHERE ID_TURNO = :idTurno";
    $stmtOrden = $conn->prepare($sqlOrden);
    $stmtOrden->execute([':idTurno' => $idTurno]);
    $numOrden = $stmtOrden->fetch(PDO::FETCH_ASSOC)['SIGUIENTE'];

    // 4. INSERT PURO (Sin funciones complejas que choquen con tu servidor)
    $sqlInsert = "INSERT INTO CUENTA (NUM_ORDEN, ID_TURNO, TIPO_ORDEN, IDENTIFICADOR, NOMBRE_CLIENTE, ESTADO) 
                  VALUES (:orden, :turno, 'Domicilio', :identificador, :nombre, 'Pendiente')";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->execute([
        ':orden' => $numOrden, 
        ':turno' => $idTurno, 
        ':identificador' => $codigoTmp, 
        ':nombre' => $nombre
    ]);

    // 5. SELECT PURO: Vamos a buscar el folio de la fila que acabamos de crear
    $sqlGet = "SELECT FOLIO FROM CUENTA WHERE IDENTIFICADOR = :identificador AND ID_TURNO = :turno";
    $stmtGet = $conn->prepare($sqlGet);
    $stmtGet->execute([':identificador' => $codigoTmp, ':turno' => $idTurno]);
    $cuentaNueva = $stmtGet->fetch(PDO::FETCH_ASSOC);

    if (!$cuentaNueva) {
        throw new Exception("Error de lectura: SQL Server guardó el dato pero no lo devolvió.");
    }

    $folio = $cuentaNueva['FOLIO'];
    
    // 6. Renombramos la orden a FOLIO-X para que PuntoVenta.html lo entienda
    $identificadorFinal = "FOLIO-" . $folio;
    $conn->prepare("UPDATE CUENTA SET IDENTIFICADOR = :final WHERE FOLIO = :folio")
         ->execute([':final' => $identificadorFinal, ':folio' => $folio]);

    echo json_encode([
        "status" => "success", 
        "folio" => $folio, 
        "orden" => $numOrden,
        "identificador" => $identificadorFinal
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>