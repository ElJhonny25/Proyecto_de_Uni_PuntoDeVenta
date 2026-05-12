<?php
require_once '../Conexion.php';
try {
    // Agregamos "AS CARGO" para que el Javascript lo reconozca
    $sql = "SELECT TE.ID_TURNO_EMP, E.NOMBRE, C.NOMBRE_CARGO AS CARGO, TE.HORA_INICIO 
            FROM TURNO_EMPLEADO TE
            INNER JOIN EMPLEADO E ON TE.ID_EMPLEADO = E.ID_EMPLEADO
            INNER JOIN CARGO C ON E.ID_CARGO = C.ID_CARGO
            WHERE TRIM(TE.ESTADO) = 'Abierto' OR TE.ESTADO = 'Abierto'";
            
    $res = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "data" => $res]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>