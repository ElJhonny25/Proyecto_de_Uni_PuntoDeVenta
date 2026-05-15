// js/config.js
(async function cargarConfiguracionGlobal() {
    console.log('config.js: cargando configuración desde BD...');
    try {
        const resp = await fetch('../ZonaAdministrativa/obtener_configuracion.php');
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const json = await resp.json();
        console.log('config.js: respuesta del servidor:', json);
        if (json.status !== 'success') return;

        const config = json.data;
        const root = document.documentElement;

        // Color de acento
        if (config['color_acento']) {
            root.style.setProperty('--color-acento', config['color_acento']);
            localStorage.setItem('--color-acento', config['color_acento']);
        }

        // Logo (se guarda como data URL)
        if (config['logo']) {
            localStorage.setItem('logoRestaurante', config['logo']);
            aplicarLogoGlobal(config['logo']);
        }

        // Fondo
        if (config['fondo']) {
            localStorage.setItem('bgRestaurante', config['fondo']);
            document.body.style.backgroundImage = `url(${config['fondo']})`;
            document.body.style.backgroundSize = 'cover';
            document.body.style.backgroundPosition = 'center';
            document.body.style.backgroundAttachment = 'fixed';
        }
    } catch (e) {
        console.error('config.js: error al cargar configuración:', e);
        // Si falla la carga, dejamos que los valores de localStorage se usen como fallback
    }
})();

function aplicarLogoGlobal(logoDataUrl) {
    // Busca el logo en todas las páginas que tienen el formato común
    const selectores = [
        { disk: '#logoDisk', img: '#uploadedLogo', text: '#logoText' },
        { disk: '.logo-disk', img: '.uploaded-logo-image', text: '.logo-text-area' }
    ];
    selectores.forEach(s => {
        const disk = document.querySelector(s.disk);
        const img = document.querySelector(s.img);
        const text = document.querySelector(s.text);
        if (img) {
            img.src = logoDataUrl;
            img.style.display = 'block';
        }
        if (text) text.style.display = 'none';
        if (disk) {
            disk.style.border = 'none';
            disk.style.background = 'transparent';
            disk.style.boxShadow = 'none';
        }
    });
}



