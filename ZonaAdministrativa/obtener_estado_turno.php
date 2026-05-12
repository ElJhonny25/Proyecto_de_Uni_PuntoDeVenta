<?php
session_start();
require_once '../Conexion.php';

try {
    $sql = "SELECT TOP 1 ID_TURNO, FECHA_APERTURA, FONDO_CAJA 
            FROM TURNO_GENERAL 
            WHERE TRIM(ESTADO) = 'Abierto' 
            ORDER BY ID_TURNO DESC";
    $stmt = $conn->query($sql);
    $turno = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($turno) {
        echo json_encode([
            "status" => "success",
            "turno" => $turno['ID_TURNO'],
            "fondoInicial" => $turno['FONDO_CAJA'],
            "fechaApertura" => $turno['FECHA_APERTURA']
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No hay un turno abierto actualmente."
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}
?>