<?php
// Salimos de la carpeta Comedor para buscar la Conexion
require_once '../Conexion.php'; 
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['areas']) && isset($data['mesas'])) {
    try {
        $conn->beginTransaction();

        // 1. BORRADO LÓGICO DE MESAS: "Apagamos" las mesas que ya no están en la pantalla
        $nombresMesas = array_column($data['mesas'], 'nombre');
        if (count($nombresMesas) > 0) {
            $placeholders = implode(',', array_fill(0, count($nombresMesas), '?'));
            $sqlDelM = "UPDATE MESA SET ACTIVO = 0 WHERE IDENTIFICADOR NOT IN ($placeholders)";
            $stmtDelM = $conn->prepare($sqlDelM);
            $stmtDelM->execute($nombresMesas);
        } else {
            $conn->exec("UPDATE MESA SET ACTIVO = 0");
        }

        // 2. ELIMINAR ÁREAS (¡Esta era la pieza faltante!)
        // Borramos físicamente de SQL Server las áreas que ya quitaste en la pantalla
        if (count($data['areas']) > 0) {
            $placeholdersA = implode(',', array_fill(0, count($data['areas']), '?'));
            $sqlDelA = "DELETE FROM AREA WHERE NOMBRE_AREA NOT IN ($placeholdersA)";
            $stmtDelA = $conn->prepare($sqlDelA);
            $stmtDelA->execute($data['areas']);
        }

        // 3. REGISTRAR O ACTUALIZAR ÁREAS
        foreach ($data['areas'] as $nombreArea) {
            $stmtA = $conn->prepare("SELECT ID_AREA FROM AREA WHERE NOMBRE_AREA = ?");
            $stmtA->execute([$nombreArea]);
            $areaExistente = $stmtA->fetchColumn(); 

            // Si no devolvió un ID, la insertamos
            if (!$areaExistente) {
                $conn->prepare("INSERT INTO AREA (NOMBRE_AREA) VALUES (?)")->execute([$nombreArea]);
            }
        }

        // 4. REGISTRAR, ACTUALIZAR Y "ENCENDER" MESAS
        foreach ($data['mesas'] as $mesa) {
            $stmtA2 = $conn->prepare("SELECT ID_AREA FROM AREA WHERE NOMBRE_AREA = ?");
            $stmtA2->execute([$mesa['area']]);
            $idArea = $stmtA2->fetchColumn();

            $posX = (int) str_replace('px', '', $mesa['left']);
            $posY = (int) str_replace('px', '', $mesa['top']);

            $stmtM = $conn->prepare("SELECT ID_MESA FROM MESA WHERE IDENTIFICADOR = ?");
            $stmtM->execute([$mesa['nombre']]);
            $idMesaExistente = $stmtM->fetchColumn(); 

            if ($idMesaExistente) {
                $sqlUpd = "UPDATE MESA SET ID_AREA = ?, POSICION_X = ?, POSICION_Y = ?, ESTADO = ?, ACTIVO = 1 WHERE ID_MESA = ?";
                $conn->prepare($sqlUpd)->execute([$idArea, $posX, $posY, $mesa['estado'], $idMesaExistente]);
            } else {
                $sqlIns = "INSERT INTO MESA (IDENTIFICADOR, ID_AREA, POSICION_X, POSICION_Y, ESTADO, ACTIVO) VALUES (?, ?, ?, ?, ?, 1)";
                $conn->prepare($sqlIns)->execute([$mesa['nombre'], $idArea, $posX, $posY, $mesa['estado']]);
            }
        }

        $conn->commit();
        echo json_encode(["status" => "success"]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
}
?>