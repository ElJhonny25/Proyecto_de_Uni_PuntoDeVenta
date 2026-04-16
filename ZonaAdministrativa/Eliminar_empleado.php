<?php
// Eliminar_empleado.php
require_once '../Conexion.php';

try {
    // Agregamos FOTO a la consulta
    $sql = "SELECT ID_EMPLEADO, NOMBRE, APELLIDO_P, APELLIDO_M, ID_CARGO, FOTO FROM EMPLEADO";
    $stmt = $conn->query($sql);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapeo simple de cargos
    $nombresCargos = [1 => "Mesero", 2 => "Capitán", 3 => "Administrador", 4 => "Gerente"];
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
            background-color: var(--bg-principal, #e6e6e6);
            font-family: 'Montserrat', sans-serif;
            margin: 0; padding: 20px;
            display: flex; justify-content: center; align-items: flex-start; min-height: 100vh;
        }
        .container {
            background-color: var(--bg-paneles, #4a4a4a);
            padding: 30px; border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            width: 90%; max-width: 1000px; color: white;
        }
        h2 { text-align: center; color: var(--color-acento, #ff7f2a); margin-top: 0; }
        
        .top-buttons { display: flex; justify-content: space-between; margin-bottom: 20px; }
        
        /* Estilos de la tabla */
        table { width: 100%; border-collapse: collapse; background-color: rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.2); vertical-align: middle; }
        th { background-color: var(--color-acento, #ff7f2a); color: white; font-weight: bold; }
        tr:hover { background-color: rgba(255,255,255,0.2); }
        
        /* Botones */
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-family: 'Montserrat', sans-serif; text-decoration: none; display: inline-block;}
        .btn-edit { background-color: #f0ad4e; color: white; margin-right: 5px; }
        .btn-delete { background-color: #d9534f; color: white; }
        .btn-volver { background-color: #5bc0de; color: white; }
        .btn-nuevo { background-color: #5cb85c; color: white; }
        .btn:hover { filter: brightness(0.9); }

        /* Modal de Edición */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 100; }
        .modal-content { background-color: var(--bg-paneles, #4a4a4a); padding: 30px; border-radius: 10px; width: 400px; color: white; max-height: 90vh; overflow-y: auto; }
        .modal-input { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: none; box-sizing: border-box; font-family: 'Montserrat', sans-serif;}
        .modal-content label { font-size: 14px; display: block; margin-bottom: 5px; }
        
        .img-circle { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-acento, #ff7f2a); }
        .img-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-acento, #ff7f2a); margin: 0 auto 15px auto; display: block; }
    </style>
</head>
<body>

    <div class="container">
        <div class="top-buttons">
            <a href="Pagina_Principal/Pagina_Principal.html" class="btn btn-volver">⬅ Volver al Inicio</a>
            <a href="ZonaAdministrativa/Empleados.html" class="btn btn-nuevo">➕ Ingresar Nuevo Personal</a>
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
                            <img src="<?= $emp['FOTO'] ?>" class="img-circle">
                            <input type="hidden" id="raw-foto-<?= $emp['ID_EMPLEADO'] ?>" value="<?= $emp['FOTO'] ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/50?text=Sin+Foto" class="img-circle">
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
                        <button class="btn btn-delete" onclick="eliminarEmpleado(<?= $emp['ID_EMPLEADO'] ?>, '<?= $emp['NOMBRE'] ?>')">Eliminar</button>
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
            <h2 style="margin-bottom: 20px;">Editar Datos</h2>
            <input type="hidden" id="editId">
            
            <img id="editPreviewImg" class="img-preview" src="" alt="Vista previa">
            
            <label>Cambiar Foto (Opcional)</label>
            <input type="file" id="editFoto" class="modal-input" accept="image/*" style="background-color: white; padding: 7px;" onchange="previsualizarEditFoto(event)">
            
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
            </select>
            
            <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                <button class="btn btn-delete" onclick="cerrarEdicion()">Cancelar</button>
                <button class="btn" style="background-color: var(--color-acento); color: white;" onclick="guardarEdicion()">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script>
        // Cargar colores del tema
        const root = document.documentElement;
        const colorInputs = { 'colorBgPrincipal': '--bg-principal', 'colorBgPaneles': '--bg-paneles', 'colorAcento': '--color-acento' };
        function loadSavedColors() {
            for (const cssVar of Object.values(colorInputs)) {
                const savedColor = localStorage.getItem(cssVar);
                if (savedColor) root.style.setProperty(cssVar, savedColor);
            }
        }
        document.addEventListener("DOMContentLoaded", loadSavedColors);

        // --- LÓGICA DE ELIMINAR ---
        async function eliminarEmpleado(id, nombre) {
            if(confirm(`¿Estás seguro de que deseas eliminar permanentemente a ${nombre}?`)) {
                try {
                    let req = await fetch('ZonaAdministrativa/borrar_empleado.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_empleado: id })
                    });
                    let res = await req.json();
                    if(res.status === 'success') {
                        document.getElementById('fila-' + id).remove(); 
                    } else {
                        alert("Error: " + res.message);
                    }
                } catch(e) { alert("Error de conexión al eliminar."); }
            }
        }

        // --- LÓGICA DE FOTOS Y EDICIÓN ---
        function abrirEdicion(id) {
            document.getElementById('editId').value = id;
            document.getElementById('editNombre').value = document.getElementById('nom-' + id).innerText;
            document.getElementById('editApPaterno').value = document.getElementById('app-' + id).innerText;
            document.getElementById('editApMaterno').value = document.getElementById('apm-' + id).innerText;
            document.getElementById('editCargo').value = document.getElementById('car-' + id).getAttribute('data-idcargo');
            
            // Cargar la foto actual en el modal
            let fotoActual = document.getElementById('raw-foto-' + id).value;
            document.getElementById('editPreviewImg').src = fotoActual ? fotoActual : 'https://via.placeholder.com/100?text=Sin+Foto';
            document.getElementById('editFoto').value = ''; // Limpiamos el input de archivo
            
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function cerrarEdicion() {
            document.getElementById('modalEditar').style.display = 'none';
        }

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

        function leerFotoBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => resolve(reader.result);
                reader.onerror = error => reject(error);
            });
        }

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

            if(!datos.nombre || !datos.apellido_p) { alert("Nombre y Apellido Paterno son obligatorios."); return; }

            // Si seleccionó una foto nueva, la convertimos a texto y la agregamos a los datos
            if (fotoInput) {
                datos.foto = await leerFotoBase64(fotoInput);
            }

            try {
                let req = await fetch('ZonaAdministrativa/actualizar_empleado.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });
                let res = await req.json();
                
                if(res.status === 'success') {
                    alert("Datos actualizados correctamente.");
                    location.reload(); 
                } else {
                    alert("Error: " + res.message);
                }
            } catch(e) { alert("Error de conexión al actualizar."); }
        }
    </script>
</body>
</html>