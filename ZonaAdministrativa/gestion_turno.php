<?php
// Asegúrate de que esta ruta hacia tu conexión sea la correcta
require_once '../Conexion.php'; 

$accion = $_POST['accion'] ?? '';

try {
    if ($accion === 'abrir') {
        $fondo = $_POST['fondo'] ?? 0;

        // 1. Verificar que no haya un turno ya abierto
        $sqlCheck = "SELECT ID_TURNO FROM TURNO_GENERAL WHERE ESTADO = 'Abierto'";
        $stmtCheck = $conn->query($sqlCheck);
        if ($stmtCheck->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe un turno abierto. Ciérralo primero.']);
            exit;
        }

        // 2. Insertar el nuevo turno
        $sqlInsert = "INSERT INTO TURNO_GENERAL (FECHA_APERTURA, FONDO_CAJA, ESTADO) 
                      VALUES (GETDATE(), :fondo, 'Abierto')";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->execute([':fondo' => $fondo]);

        echo json_encode(['status' => 'success', 'message' => 'Turno abierto correctamente.']);

    } elseif ($accion === 'cerrar') {
        $idTurno = $_POST['id_turno'] ?? null;

        if (!$idTurno) {
            // Si por alguna razón no llega el ID desde el HTML, buscamos el activo
            $sqlCheck = "SELECT ID_TURNO FROM TURNO_GENERAL WHERE ESTADO = 'Abierto'";
            $stmtCheck = $conn->query($sqlCheck);
            $turno = $stmtCheck->fetch();
            if($turno) {
                $idTurno = $turno['ID_TURNO'];
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No hay turno abierto para cerrar.']);
                exit;
            }
        }

        // 3. Actualizar turno a cerrado
        $sqlUpdate = "UPDATE TURNO_GENERAL SET ESTADO = 'Cerrado', FECHA_CIERRE = GETDATE() WHERE ID_TURNO = :id";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([':id' => $idTurno]);

        echo json_encode(['status' => 'success', 'message' => 'Turno cerrado con éxito.']);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
    }

} catch (Exception $e) {
    // Si SQL Server marca algún error (como el del Trigger de hace rato), 
    // lo atrapamos aquí para que JavaScript lo entienda y te lo muestre en una alerta bonita.
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>