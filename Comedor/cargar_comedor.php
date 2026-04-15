<?php
// Conexion/cargar_comedor.php
require_once 'Conexion.php';

try {
    // 1. Traer todas las áreas
    $areas = [];
    $stmtA = $conn->query("SELECT NOMBRE_AREA FROM AREA");
    while ($row = $stmtA->fetch(PDO::FETCH_ASSOC)) {
        $areas[] = $row['NOMBRE_AREA'];
    }

    // 2. Traer las mesas con el nombre de su área (haciendo JOIN)
    $mesas = [];
    $sqlM = "SELECT m.IDENTIFICADOR as nombre, a.NOMBRE_AREA as area, m.POSICION_X, m.POSICION_Y, m.ESTADO 
             FROM MESA m
             JOIN AREA a ON m.ID_AREA = a.ID_AREA";
             
    $stmtM = $conn->query($sqlM);
    
    while ($row = $stmtM->fetch(PDO::FETCH_ASSOC)) {
        $mesas[] = [
            "nombre" => $row['nombre'],
            "area" => $row['area'],
            "left" => $row['POSICION_X'] . "px", // Le volvemos a pegar los "px" para el HTML
            "top" => $row['POSICION_Y'] . "px",
            "estado" => $row['ESTADO']
        ];
    }

    echo json_encode(["status" => "success", "areas" => $areas, "mesas" => $mesas]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
}
?>