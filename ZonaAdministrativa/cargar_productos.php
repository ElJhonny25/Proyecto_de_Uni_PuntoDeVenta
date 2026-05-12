<?php
session_start();
require_once '../Conexion.php';

try {
    // Solo traemos los que tienen ACTIVO = 1
    $sql = "SELECT ID_PRODUCTO, NOMBRE, PRECIO, ID_CATEGORIA FROM PRODUCTO WHERE ACTIVO = 1 ORDER BY ID_PRODUCTO DESC";
    $stmt = $conn->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $productos]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error al cargar la lista: " . $e->getMessage()]);
}
?>