<?php
// cargar_menu.php
require_once '../Conexion.php';

try {
    // 1. Obtener todas las categorías
    $sqlCategorias = "SELECT ID_CATEGORIA, NOMBRE FROM CATEGORIA ORDER BY ID_CATEGORIA";
    $stmtCat = $conn->prepare($sqlCategorias);
    $stmtCat->execute();
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener todos los productos activos
    $sqlProductos = "SELECT ID_PRODUCTO, NOMBRE, PRECIO, ID_CATEGORIA 
                     FROM PRODUCTO WHERE ACTIVO = 1 ORDER BY NOMBRE";
    $stmtProd = $conn->prepare($sqlProductos);
    $stmtProd->execute();
    $productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "categorias" => $categorias,
        "productos" => $productos
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
}
?>