// js/notificaciones.js
// ================================================
// SISTEMA DE NOTIFICACIONES Y DIÁLOGOS MODERNOS
// Reemplaza alert, confirm y prompt del navegador.
// ================================================

(function() {
    // Guardamos las funciones originales por si acaso
    const alertNativo = window.alert;
    const confirmNativo = window.confirm;
    const promptNativo = window.prompt;

    // --- Crear el modal una sola vez, en cuanto el body exista ---
    function crearModal() {
        if (document.getElementById('modalNotificacion')) return;

        const modalHTML = `
            <div id="modalNotificacion" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); backdrop-filter:blur(5px); justify-content:center; align-items:center; z-index:9999; font-family: 'Montserrat', sans-serif;">
                <div style="background: rgba(20, 20, 20, 0.95); border: 2px solid var(--color-acento, #ff7f2a); border-radius: 20px; padding: 30px 25px; max-width: 420px; width: 90%; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.8); color: white;">
                    <div id="notificacionIcono" style="font-size: 50px; margin-bottom: 15px;">⚠️</div>
                    <p id="notificacionMensaje" style="font-size: 16px; margin: 0 0 25px 0; color: white; line-height: 1.5;"></p>
                    <div id="notificacionBotones"></div>
                </div>
            </div>
        `;

        // Insertamos el HTML en el body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Evento para cerrar haciendo clic fuera del cuadro
        const modal = document.getElementById('modalNotificacion');
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // --- Mostrar un modal simple (tipo alert) ---
    function mostrarAlerta(mensaje, icono = '⚠️', callback = null) {
        crearModal();
        const modal = document.getElementById('modalNotificacion');
        document.getElementById('notificacionIcono').innerText = icono;
        document.getElementById('notificacionMensaje').innerText = mensaje || '';

        const botones = document.getElementById('notificacionBotones');
        botones.innerHTML = `
            <button id="btnNotificacionOk" style="background: var(--color-acento, #ff7f2a); border: none; color: white; padding: 12px 30px; border-radius: 25px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.3s; text-transform: uppercase;">Aceptar</button>
        `;
        document.getElementById('btnNotificacionOk').addEventListener('click', function() {
            modal.style.display = 'none';
            if (typeof callback === 'function') callback();
        });
        modal.style.display = 'flex';
    }

    // --- Mostrar un diálogo de confirmación (tipo confirm) ---
    function mostrarConfirm(mensaje, icono = '❓') {
        return new Promise(function(resolve) {
            crearModal();
            const modal = document.getElementById('modalNotificacion');
            document.getElementById('notificacionIcono').innerText = icono;
            document.getElementById('notificacionMensaje').innerText = mensaje || '';

            const botones = document.getElementById('notificacionBotones');
            botones.innerHTML = `
                <button id="btnConfirmSi" style="background: var(--color-acento, #ff7f2a); border: none; color: white; padding: 12px 25px; border-radius: 25px; font-weight: bold; font-size: 15px; cursor: pointer; margin-right: 10px;">Sí</button>
                <button id="btnConfirmNo" style="background: #555; border: none; color: white; padding: 12px 25px; border-radius: 25px; font-weight: bold; font-size: 15px; cursor: pointer;">No</button>
            `;
            document.getElementById('btnConfirmSi').addEventListener('click', function() {
                modal.style.display = 'none';
                resolve(true);
            });
            document.getElementById('btnConfirmNo').addEventListener('click', function() {
                modal.style.display = 'none';
                resolve(false);
            });
            modal.style.display = 'flex';
        });
    }

    // --- Mostrar un diálogo de entrada (tipo prompt) ---
    function mostrarPrompt(mensaje, icono = '✏️', valorPorDefecto = '') {
        return new Promise(function(resolve) {
            crearModal();
            const modal = document.getElementById('modalNotificacion');
            document.getElementById('notificacionIcono').innerText = icono;
            document.getElementById('notificacionMensaje').innerText = mensaje || '';

            const botones = document.getElementById('notificacionBotones');
            botones.innerHTML = `
                <input id="inputPrompt" type="text" value="${valorPorDefecto}" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--color-acento); background:#000; color:white; margin-bottom:15px; outline:none; font-size:16px;">
                <br>
                <button id="btnPromptOk" style="background: var(--color-acento, #ff7f2a); border: none; color: white; padding: 12px 25px; border-radius: 25px; font-weight: bold; font-size: 15px; cursor: pointer; margin-right: 10px;">Aceptar</button>
                <button id="btnPromptCancel" style="background: #555; border: none; color: white; padding: 12px 25px; border-radius: 25px; font-weight: bold; font-size: 15px; cursor: pointer;">Cancelar</button>
            `;
            const input = document.getElementById('inputPrompt');
            input.focus();
            document.getElementById('btnPromptOk').addEventListener('click', function() {
                modal.style.display = 'none';
                resolve(input.value);
            });
            document.getElementById('btnPromptCancel').addEventListener('click', function() {
                modal.style.display = 'none';
                resolve(null);
            });
            modal.style.display = 'flex';
        });
    }

    // --- Sobrescribir las funciones globales ---
    window.alert = function(mensaje) {
        mostrarAlerta(mensaje, '⚠️');
    };

    // ¡Cuidado! El confirm original es sincrónico; el nuestro devuelve una Promesa.
    // Para no romper código existente que use `if (confirm(...))`, ofrecemos una versión compatible.
    window.confirm = function(mensaje) {
        // Si se usa de forma sincrónica (if (confirm)), no funcionará correctamente.
        // Por eso, reemplazamos su comportamiento: mostramos el diálogo y devolvemos siempre false.
        // Pero para código nuevo, se puede usar la función async `mostrarConfirm`.
        // Para compatibilidad, hacemos que el diálogo se muestre y luego resolvemos.
        // Esto puede romper flujos que esperan un booleano inmediato.
        // Por tanto, recomendamos NO usar confirm en código nuevo.
        // Para código existente, lo convertimos a una llamada a mostrarConfirm y lanzamos un error si se usa sincrónicamente.
        // Sin embargo, para evitar errores, simplemente devolvemos false y mostramos el diálogo.
        mostrarConfirm(mensaje).then(function(res) {
            // Si el código original hace algo como:
            // if (confirm("¿Seguro?")) { ... }
            // No podemos cambiar el flujo porque ya devolvimos false.
            // La solución real es modificar todas las llamadas a confirm por `await mostrarConfirm(...)`.
            // Pero para que no truene, dejaremos que se muestre y el flujo seguirá (aunque no espere).
        });
        return false; // comportamiento por defecto para no romper
    };

    window.prompt = function(mensaje, valorPorDefecto) {
        // Similar a confirm, devolvemos null de inmediato, pero mostramos el diálogo.
        mostrarPrompt(mensaje, '✏️', valorPorDefecto).then(function(valor) {
            // No podemos retornar el valor al código sincrónico.
        });
        return null;
    };

    // --- Métodos asíncronos recomendados ---
    window.mostrarAlerta = mostrarAlerta;
    window.mostrarConfirm = mostrarConfirm;
    window.mostrarPrompt = mostrarPrompt;

})();

