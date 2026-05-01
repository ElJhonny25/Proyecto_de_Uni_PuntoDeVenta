<?php
require_once '../Conexion.php'; // Ajusta la ruta a tu conexión

$mesa = $_POST['mesa'] ?? '';
$productosJSON = $_POST['productos'] ?? '[]';
$productos = json_decode($productosJSON, true);

try {
    // 1. Verificamos que exista un turno abierto
    $stmtTurno = $conn->query("SELECT TOP 1 ID_TURNO FROM TURNO_GENERAL WHERE ESTADO = 'Abierto' ORDER BY ID_TURNO DESC");
    $turno = $stmtTurno->fetch(PDO::FETCH_ASSOC);
    if(!$turno) {
        throw new Exception("No hay turno abierto. Abre el turno en caja primero.");
    }
    $idTurno = $turno['ID_TURNO'];

    // 2. Buscamos si la mesa ya tiene una cuenta PENDIENTE en este turno
    $sqlCuenta = "SELECT FOLIO FROM CUENTA WHERE IDENTIFICADOR = :mesa AND ESTADO = 'Pendiente' AND ID_TURNO = :turno";
    $stmtCuenta = $conn->prepare($sqlCuenta);
    $stmtCuenta->execute([':mesa' => $mesa, ':turno' => $idTurno]);
    $cuenta = $stmtCuenta->fetch(PDO::FETCH_ASSOC);

    $folio = 0;

    if($cuenta) {
        // Si ya tenía cuenta abierta (ej. pidieron más cosas), usamos ese Folio
        $folio = $cuenta['FOLIO'];
    } else {
        // Si es la primera orden, calculamos qué número de orden le toca y creamos la cuenta
        $stmtOrden = $conn->prepare("SELECT ISNULL(MAX(NUM_ORDEN), 0) + 1 AS SIGUIENTE FROM CUENTA WHERE ID_TURNO = :turno");
        $stmtOrden->execute([':turno' => $idTurno]);
        $numOrden = $stmtOrden->fetch(PDO::FETCH_ASSOC)['SIGUIENTE'];

        $sqlInsertCuenta = "INSERT INTO CUENTA (NUM_ORDEN, ID_TURNO, TIPO_ORDEN, IDENTIFICADOR, ESTADO) 
                            VALUES (:orden, :turno, 'Comedor', :mesa, 'Pendiente');
                            SELECT SCOPE_IDENTITY() AS FOLIO;"; // Truco SQL Server para obtener el ID generado
        
        $stmtInsert = $conn->prepare($sqlInsertCuenta);
        $stmtInsert->execute([':orden' => $numOrden, ':turno' => $idTurno, ':mesa' => $mesa]);
        
        // Avanzamos al siguiente resultado para capturar el FOLIO
        $stmtInsert->nextRowset(); 
        $folio = $stmtInsert->fetch(PDO::FETCH_ASSOC)['FOLIO'];
    }

    // 3. Guardamos los platillos en DETALLE_CUENTA
    $sqlDetalle = "INSERT INTO DETALLE_CUENTA (FOLIO_CUENTA, ID_PRODUCTO, CANTIDAD, PRECIO_UNITARIO, SUBTOTAL) 
                   VALUES (:folio, :idProd, :cant, :precio, :subtotal)";
    $stmtDet = $conn->prepare($sqlDetalle);

    $totalSuma = 0;
    foreach($productos as $prod) {
        $stmtDet->execute([
            ':folio' => $folio,
            ':idProd' => $prod['id'],
            ':cant' => $prod['cantidad'],
            ':precio' => $prod['precioUnitario'],
            ':subtotal' => $prod['subtotal']
        ]);
        $totalSuma += $prod['subtotal'];
    }

    // 4. Actualizamos el Total a pagar en la tabla CUENTA
    $sqlUpdate = "UPDATE CUENTA SET TOTAL = TOTAL + :suma WHERE FOLIO = :folio";
    $conn->prepare($sqlUpdate)->execute([':suma' => $totalSuma, ':folio' => $folio]);

    echo json_encode(["status" => "success", "folio" => $folio, "message" => "Comanda registrada en BD"]);

} catch(Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>