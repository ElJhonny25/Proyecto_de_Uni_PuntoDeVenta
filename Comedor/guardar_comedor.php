<?php
// Conexion/guardar_comedor.php
require_once 'Proyecto_de_Uni_PuntoDeVenta/Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['areas']) && isset($data['mesas'])) {
    try {
        $conn->beginTransaction();

        // 1. Limpiamos las mesas anteriores (NO borramos las áreas para no romper otros registros)
        $conn->exec("DELETE FROM MESA");

        // 2. Sincronizamos las Áreas y guardamos sus IDs en un diccionario
        $mapaAreas = [];
        $stmtBuscaArea = $conn->prepare("SELECT ID_AREA FROM AREA WHERE NOMBRE_AREA = :nombre");
        $stmtInsertaArea = $conn->prepare("INSERT INTO AREA (NOMBRE_AREA) OUTPUT INSERTED.ID_AREA VALUES (:nombre)");

        foreach ($data['areas'] as $areaNombre) {
            $stmtBuscaArea->execute([':nombre' => $areaNombre]);
            $rowArea = $stmtBuscaArea->fetch(PDO::FETCH_ASSOC);

            if ($rowArea) {
                // El área ya existe, guardamos su ID
                $mapaAreas[$areaNombre] = $rowArea['ID_AREA'];
            } else {
                // Es un área nueva, la insertamos y atrapamos su nuevo ID
                $stmtInsertaArea->execute([':nombre' => $areaNombre]);
                $rowInsert = $stmtInsertaArea->fetch(PDO::FETCH_ASSOC);
                $mapaAreas[$areaNombre] = $rowInsert['ID_AREA'];
            }
        }

        // 3. Guardamos las Mesas
        $sqlMesa = "INSERT INTO MESA (IDENTIFICADOR, ID_AREA, POSICION_X, POSICION_Y, ESTADO) 
                    VALUES (:identificador, :id_area, :px, :py, :estado)";
        $stmtMesa = $conn->prepare($sqlMesa);
        
        foreach ($data['mesas'] as $mesa) {
            // Limpiamos los "px" para que SQL Server reciba un número entero (INT)
            $posX = (int) str_replace('px', '', $mesa['left']);
            $posY = (int) str_replace('px', '', $mesa['top']);
            $idArea = $mapaAreas[$mesa['area']]; // Usamos la llave foránea correcta

            $stmtMesa->execute([
                ':identificador' => $mesa['nombre'],
                ':id_area' => $idArea,
                ':px' => $posX,
                ':py' => $posY,
                ':estado' => $mesa['estado']
            ]);
        }

        $conn->commit();
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Faltan datos."]);
}
?>