<?php
session_start();
require_once '../Conexion.php';

try {
    $stmtA = $conn->query("SELECT NOMBRE_AREA FROM AREA ORDER BY ID_AREA ASC");
    $areas = $stmtA->fetchAll(PDO::FETCH_COLUMN);

    // Solo cargamos las mesas que estén activas
    $stmtM = $conn->query("SELECT M.IDENTIFICADOR as nombre, A.NOMBRE_AREA as area, M.POSICION_X, M.POSICION_Y, M.ESTADO 
                           FROM MESA M 
                           INNER JOIN AREA A ON M.ID_AREA = A.ID_AREA 
                           WHERE M.ACTIVO = 1");
    $mesasDB = $stmtM->fetchAll(PDO::FETCH_ASSOC);
    
    $mesas = [];
    foreach ($mesasDB as $m) {
        $mesas[] = [
            "nombre" => $m['nombre'],
            "area" => $m['area'],
            "left" => $m['POSICION_X'] . "px",
            "top" => $m['POSICION_Y'] . "px",
            "estado" => $m['ESTADO']
        ];
    }

    echo json_encode(["status" => "success", "areas" => $areas, "mesas" => $mesas]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>