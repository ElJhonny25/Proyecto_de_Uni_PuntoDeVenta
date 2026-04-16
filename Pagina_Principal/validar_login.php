<?php
// Conexion/validar_login.php
require_once '../Conexion.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nip'])) {
    try {
        // AGREGAMOS LA COLUMNA FOTO EN LA CONSULTA
        $sql = "SELECT ID_EMPLEADO, NOMBRE, APELLIDO_P, NIP, ID_CARGO, FOTO FROM EMPLEADO WHERE NIP = :nip";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nip', $data['nip']);
        $stmt->execute();
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empleado) {
            $nombresCargos = [1 => "Mesero", 2 => "Capitán de meseros", 3 => "Administrador", 4 => "Gerente"];
            
            // AGREGAMOS LA FOTO AL OBJETO DE SESIÓN
            $datosSesion = [
                "id" => $empleado['ID_EMPLEADO'],
                "nip" => $empleado['NIP'],
                "nombre" => $empleado['NOMBRE'] . " " . $empleado['APELLIDO_P'],
                "cargo" => isset($nombresCargos[$empleado['ID_CARGO']]) ? $nombresCargos[$empleado['ID_CARGO']] : "Empleado",
                "foto" => $empleado['FOTO']
            ];

            echo json_encode(["status" => "success", "empleado" => $datosSesion]);
        } else {
            echo json_encode(["status" => "error", "message" => "La contraseña no existe."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se recibió el NIP."]);
}
?>