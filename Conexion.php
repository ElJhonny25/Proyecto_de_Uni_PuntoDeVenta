<?php
require_once '../Conexion.php';

// CORRECCIÓN: Doble barra invertida para que PHP lo lea bien
$serverName = "PICAS\\SQLEXPRESS"; 
$database = "restaurante";

try {
    // Usaremos Autenticación de Windows como lo tenías
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$database");
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Si descomentas la línea de abajo y entras a este archivo, verás si conectó
    // echo "Conectado a SQL Server";
    
} catch(PDOException $e) {
    // Cambiamos el "die" por un echo en formato JSON para que JavaScript no marque error de conexión, sino que nos diga el error real de SQL
    echo json_encode(["status" => "error", "message" => "Fallo en SQL: " . $e->getMessage()]);
    exit;
}
?>