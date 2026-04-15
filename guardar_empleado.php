<?php
// Conexion/guardar_empleado.php
require_once 'Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);

// Verificamos que lleguen los datos mínimos obligatorios
if (isset($data['nombre']) && isset($data['apellido_p']) && isset($data['nip']) && isset($data['id_cargo'])) {
    try {
        // La foto puede venir vacía, así que la validamos
        $foto = isset($data['foto']) && !empty($data['foto']) ? $data['foto'] : null;
        $apm = isset($data['apellido_m']) ? trim($data['apellido_m']) : '';

        $sql = "INSERT INTO EMPLEADO (NOMBRE, APELLIDO_P, APELLIDO_M, NIP, ID_CARGO, FOTO) 
                VALUES (:nom, :app, :apm, :nip, :cargo, :foto)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nom' => trim($data['nombre']),
            ':app' => trim($data['apellido_p']),
            ':apm' => $apm,
            ':nip' => $data['nip'],
            ':cargo' => $data['id_cargo'],
            ':foto' => $foto
        ]);
        
        echo json_encode(["status" => "success", "message" => "¡Empleado registrado correctamente en SOFTWADZ!"]);
    } catch (PDOException $e) {
        // Si el NIP ya existe, SQL lanzará un error que atrapamos aquí
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios (Nombre, Apellido o NIP)."]);
}
?>