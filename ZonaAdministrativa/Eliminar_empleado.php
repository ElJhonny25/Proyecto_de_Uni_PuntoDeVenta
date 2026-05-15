<?php
// Eliminar_empleado.php
require_once '../Conexion.php';

try {
    // Agregamos FOTO a la consulta
    $sql = "SELECT ID_EMPLEADO, NOMBRE, APELLIDO_P, APELLIDO_M, ID_CARGO, FOTO FROM EMPLEADO";
    $stmt = $conn->query($sql);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapeo simple de cargos
    $nombresCargos = [1 => "Mesero", 2 => "Capitán", 3 => "Administrador", 4 => "Gerente", 5 => "Repartidor"];
} catch (PDOException $e) {
    die("Error al cargar empleados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Personal - SOFTWADZ</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-principal, #1a1e24);
            font-family: 'Montserrat', sans-serif;
            margin: 0; padding: 20px;
            display: flex; justify-content: center; align-items: flex-start; min-height: 100vh;
            background-size: cover; background-position: center; background-attachment: fixed;
        }
        .container {
            background-color: rgba(20, 20, 20, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px; border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            width: 90%; max-width: 1000px; color: white;
        }
        h2 { text-align: center; color: var(--color-acento, #ff7f2a); margin-top: 0; }
        
        .top-buttons { display: flex; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        
        /* Estilos de la tabla */
        table { width: 100%; border-collapse: collapse; background-color: rgba(255,255,255,0.05); border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.2); vertical-align: middle; }
        th { background-color: var(--color-acento, #ff7f2a); color: white; font-weight: bold; }
        tr:hover { background-color: rgba(255,255,255,0.1); }
        
        /* Botones */
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-family: 'Montserrat', sans-serif; text-decoration: none; display: inline-block;}
        .btn-edit { background-color: #f0ad4e; color: white; margin-right: 5px; }
        .btn-delete { background-color: #d9534f; color: white; }
        .btn-volver { background-color: #5bc0de; color: white; }
        .btn-nuevo { background-color: #5cb85c; color: white; }
        .btn:hover { filter: brightness(0.9); }

        /* Modal de Edición */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background-color: rgba(20, 20, 20, 0.95); backdrop-filter: blur(10px); border: 1px solid var(--color-acento); padding: 30px; border-radius: 10px; width: 400px; color: white; max-height: 90vh; overflow-y: auto; box-shadow: 0 15px 35px rgba(0,0,0,0.8); }
        .modal-input { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.5); color: white; box-sizing: border-box; font-family: 'Montserrat', sans-serif; outline: none; }
        .modal-content label { font-size: 14px; display: block; margin-bottom: 5px; color: #ccc; }
        select.modal-input option { background-color: #1a1e24; color: white; }
        
        .img-circle { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-acento, #ff7f2a); }
        .img-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-acento, #ff7f2a); margin: 0 auto 15px auto; display: block; }
    </style>
    <script src="../js/notificaciones.js"></script>
    <script src="../js/config.js"></script>
</head>
<body>

    <div class="container">
        <div class="top-buttons">
            <a href="AdminDashboard.html" class="btn btn-volver">⬅ Volver al Inicio</a>
            <a href="Empleados.html" class="btn btn-nuevo">➕ Ingresar Nuevo Personal</a>
        </div>
        
        <h2>Gestión de Personal</h2>

        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>ID</th>
                    <th>Nombre(s)</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Cargo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $emp): ?>
                <tr id="fila-<?= $emp['ID_EMPLEADO'] ?>">
                    <td>
                        <?php if (!empty($emp['FOTO'])): ?>
                            <img src="<?= $emp['FOTO'] ?>" class="img-circle" alt="Foto">
                            <input type="hidden" id="raw-foto-<?= $emp['ID_EMPLEADO'] ?>" value="<?= $emp['FOTO'] ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/50?text=Sin+Foto" class="img-circle" alt="Sin foto">
                            <input type="hidden" id="raw-foto-<?= $emp['ID_EMPLEADO'] ?>" value="">
                        <?php endif; ?>
                    </td>
                    <td><?= $emp['ID_EMPLEADO'] ?></td>
                    <td id="nom-<?= $emp['ID_EMPLEADO'] ?>"><?= htmlspecialchars($emp['NOMBRE']) ?></td>
                    <td id="app-<?= $emp['ID_EMPLEADO'] ?>"><?= htmlspecialchars($emp['APELLIDO_P']) ?></td>
                    <td id="apm-<?= $emp['ID_EMPLEADO'] ?>"><?= htmlspecialchars($emp['APELLIDO_M']) ?></td>
                    <td id="car-<?= $emp['ID_EMPLEADO'] ?>" data-idcargo="<?= $emp['ID_CARGO'] ?>">
                        <?= isset($nombresCargos[$emp['ID_CARGO']]) ? $nombresCargos[$emp['ID_CARGO']] : 'Desconocido' ?>
                    </td>
                    <td>
                        <button class="btn btn-edit" onclick="abrirEdicion(<?= $emp['ID_EMPLEADO'] ?>)">Editar</button>
                        <button class="btn btn-delete" onclick="eliminarEmpleado(<?= $emp['ID_EMPLEADO'] ?>, '<?= htmlspecialchars($emp['NOMBRE']) ?>')">Eliminar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($empleados)): ?>
                    <tr><td colspan="7" style="text-align:center;">No hay empleados registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="modal" id="modalEditar">
        <div class="modal-content">
            <h2 style="margin-bottom: 20px; color: var(--color-acento);">Editar Datos</h2>
            <input type="hidden" id="editId">
            
            <img id="editPreviewImg" class="img-preview" src="" alt="Vista previa">
            
            <label>Cambiar Foto (Opcional)</label>
            <input type="file" id="editFoto" class="modal-input" accept="image/*" style="background-color: white; color: black; padding: 7px;" onchange="previsualizarEditFoto(event)">
            
            <label>Nombre(s)</label>
            <input type="text" id="editNombre" class="modal-input">
            
            <label>Apellido Paterno</label>
            <input type="text" id="editApPaterno" class="modal-input">
            
            <label>Apellido Materno</label>
            <input type="text" id="editApMaterno" class="modal-input">
            
            <label>Cargo</label>
            <select id="editCargo" class="modal-input">
                <option value="1">Mesero</option>
                <option value="2">Capitán de meseros</option>
                <option value="3">Administrador</option>
                <option value="4">Gerente</option>
                <option value="5">Repartidor</option>
            </select>
            
            <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                <button class="btn btn-delete" onclick="cerrarEdicion()">Cancelar</button>
                <button class="btn" style="background-color: var(--color-acento); color: white;" onclick="guardarEdicion()">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script>
        // --- ELIMINAR EMPLEADO ---
        async function eliminarEmpleado(id, nombre) {
            const confirmado = await mostrarConfirm(`¿Estás seguro de que deseas eliminar permanentemente a ${nombre}?`);
            if (!confirmado) return;

            try {
                let req = await fetch('borrar_empleado.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_empleado: id })
                });
                let res = await req.json();
                if (res.status === 'success') {
                    document.getElementById('fila-' + id).remove();
                    await mostrarAlerta('Empleado eliminado correctamente.', '🗑️');
                } else {
                    await mostrarAlerta("Error: " + res.message, '❌');
                }
            } catch(e) {
                await mostrarAlerta("Error de conexión al eliminar.", '❌');
            }
        }

        // --- ABRIR MODAL DE EDICIÓN ---
        function abrirEdicion(id) {
            document.getElementById('editId').value = id;
            document.getElementById('editNombre').value = document.getElementById('nom-' + id).innerText;
            document.getElementById('editApPaterno').value = document.getElementById('app-' + id).innerText;
            document.getElementById('editApMaterno').value = document.getElementById('apm-' + id).innerText;
            document.getElementById('editCargo').value = document.getElementById('car-' + id).getAttribute('data-idcargo');
            
            let fotoActual = document.getElementById('raw-foto-' + id).value;
            document.getElementById('editPreviewImg').src = fotoActual ? fotoActual : 'https://via.placeholder.com/100?text=Sin+Foto';
            document.getElementById('editFoto').value = '';
            
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function cerrarEdicion() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        // Previsualizar foto al seleccionar archivo
        function previsualizarEditFoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('editPreviewImg').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        // Convertir foto a Base64 comprimida (ligera)
        function leerFotoBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        const MAX_WIDTH = 300;
                        const MAX_HEIGHT = 300;
                        let width = img.width;
                        let height = img.height;
                        if (width > height) {
                            if (width > MAX_WIDTH) { height *= MAX_WIDTH / width; width = MAX_WIDTH; }
                        } else {
                            if (height > MAX_HEIGHT) { width *= MAX_HEIGHT / height; height = MAX_HEIGHT; }
                        }
                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);
                        resolve(canvas.toDataURL('image/jpeg', 0.7));
                    };
                    img.onerror = error => reject(error);
                };
                reader.onerror = error => reject(error);
                reader.readAsDataURL(file);
            });
        }

        // --- GUARDAR EDICIÓN ---
        async function guardarEdicion() {
            let id = document.getElementById('editId').value;
            let fotoInput = document.getElementById('editFoto').files[0];
            
            let datos = {
                id_empleado: id,
                nombre: document.getElementById('editNombre').value.trim(),
                apellido_p: document.getElementById('editApPaterno').value.trim(),
                apellido_m: document.getElementById('editApMaterno').value.trim(),
                id_cargo: document.getElementById('editCargo').value
            };

            if (!datos.nombre || !datos.apellido_p) {
                await mostrarAlerta("Nombre y Apellido Paterno son obligatorios.", '⚠️');
                return;
            }

            if (fotoInput) {
                datos.foto = await leerFotoBase64(fotoInput);
            }

            try {
                let req = await fetch('actualizar_empleado.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });
                let res = await req.json();
                
                if (res.status === 'success') {
                    await mostrarAlerta("Datos actualizados correctamente.", '✅');
                    location.reload(); 
                } else {
                    await mostrarAlerta("Error: " + res.message, '❌');
                }
            } catch(e) {
                await mostrarAlerta("Error de conexión al actualizar.", '❌');
            }
        }
    </script>
</body>
</html>