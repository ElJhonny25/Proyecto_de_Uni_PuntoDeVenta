<?php
require_once '../Conexion.php'; 

$accion = $_POST['accion'] ?? '';

try {
    if ($accion === 'estado') {
        // Revisa si hay algún turno abierto
        $sql = "SELECT TOP 1 ID_TURNO, FECHA_APERTURA, FONDO_CAJA FROM TURNO_GENERAL WHERE ESTADO = 'Abierto' ORDER BY ID_TURNO DESC";
        $stmt = $conn->query($sql);
        $turno = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($turno) {
            echo json_encode(["status" => "abierto", "data" => $turno]);
        } else {
            echo json_encode(["status" => "cerrado"]);
        }
    } 
    
    elseif ($accion === 'abrir') {
        $fondo = $_POST['fondo'] ?? 0;
        
        // Verificamos por seguridad que no haya ya uno abierto
        $stmtCheck = $conn->query("SELECT COUNT(*) FROM TURNO_GENERAL WHERE ESTADO = 'Abierto'");
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("Ya existe un turno abierto. Ciérralo primero.");
        }

        $sql = "INSERT INTO TURNO_GENERAL (FONDO_CAJA, ESTADO) VALUES (:fondo, 'Abierto')";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fondo', $fondo);
        $stmt->execute();
        
        echo json_encode(["status" => "success", "message" => "Turno abierto con $fondo en caja."]);
    } 
    
    elseif ($accion === 'cerrar') {
        $idTurno = $_POST['id_turno'] ?? 0;
        
        $sql = "UPDATE TURNO_GENERAL SET ESTADO = 'Cerrado', FECHA_CIERRE = GETDATE() WHERE ID_TURNO = :id AND ESTADO = 'Abierto'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $idTurno);
        $stmt->execute();
        
        echo json_encode(["status" => "success", "message" => "Turno cerrado correctamente."]);
    }
    
    else {
        throw new Exception("Acción no válida.");
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>