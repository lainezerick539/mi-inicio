const API_URL = 'insert.php';

function showStatusMessage(message, isError = false) {
    const statusDiv = document.getElementById('status-message');
    statusDiv.textContent = message;
    statusDiv.className = isError ? 'status-message error' : 'status-message success';
    statusDiv.style.opacity = '1';
    
    setTimeout(() => {
        statusDiv.style.opacity = '0';
    }, 3000);
}

async function loadUserProfile() {
    try {
        const response = await fetch(`${API_URL}?action=load_profile`);
        const data = await response.json();

        if (data.success) {
            document.getElementById('nombre').value = data.user.nombre || '';
            document.getElementById('username').value = data.user.username || '';
            document.getElementById('biografia').value = data.user.biografia || '';
            document.getElementById('email').value = data.user.correo_electronico || '';
            document.getElementById('telefono').value = data.user.telefono || '';
            document.getElementById('user-display-name').textContent = data.user.nombre || data.user.username || 'Usuario';
            
            document.getElementById('current-email-display').textContent = data.user.correo_electronico || 'N/A';
            document.getElementById('idioma').value = data.user.idioma || 'es';
            
            renderActivityList('historial-compras-list', data.activity.compras, 'no-compras', (item) => `
                <div class="data-item">
                    <span>${item.fecha} - ${item.producto}</span>
                    <span class="monto">$${item.monto.toFixed(2)}</span>
                </div>
            `);
            
            renderActivityList('favoritos-list', data.activity.favoritos, 'no-favoritos', (item) => `
                <div class="data-item">
                    <span>${item.nombre}</span>
                    <button class="boton-pequeno">Ver</button>
                </div>
            `);

        } else {
            showStatusMessage(`Error al cargar datos: ${data.message}`, true);
        }
    } catch (error) {
        showStatusMessage('Error de conexión con el servidor.', true);
    }
}

function renderActivityList(containerId, items, emptyId, renderItem) {
    const container = document.getElementById(containerId);
    const emptyMessage = document.getElementById(emptyId);
    container.innerHTML = ''; 

    if (items && items.length > 0) {
        emptyMessage.style.display = 'none';
        items.forEach(item => {
            const div = document.createElement('div');
            div.innerHTML = renderItem(item);
            container.appendChild(div.firstChild);
        });
    } else {
        emptyMessage.textContent = 'No hay datos registrados.';
        emptyMessage.style.display = 'block';
        container.appendChild(emptyMessage);
    }
}


async function handleFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    let action;
    if (form.id === 'form-update-info') action = 'update_info';
    else if (form.id === 'form-change-password') action = 'change_password';
    else if (form.id === 'form-change-email') action = 'change_email';
    else if (form.id === 'form-update-preferences') action = 'update_preferences';
    else return;

    formData.append('action', action);

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();

        if (result.success) {
            showStatusMessage(result.message || '¡Cambios guardados con éxito!');
            if (action === 'update_info') loadUserProfile(); 
            if (action === 'change_password') form.reset();
        } else {
            showStatusMessage(result.message || 'Error al guardar los cambios.', true);
        }
    } catch (error) {
        showStatusMessage('Error de conexión con el servidor. Inténtalo de nuevo.', true);
    }
}


document.addEventListener('DOMContentLoaded', () => {
    
    const navLinks = document.querySelectorAll('.perfil-sidebar .nav-link');
    const contentPanels = document.querySelectorAll('.perfil-panel');

    function switchPanel(targetId) {
        contentPanels.forEach(panel => {
            panel.classList.remove('active');
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            link.removeAttribute('aria-current');
        });

        const targetPanel = document.getElementById(targetId);
        const targetLink = document.querySelector(`.nav-link[data-target="${targetId}"]`);

        if (targetPanel && targetLink) {
            targetPanel.classList.add('active');
            targetLink.classList.add('active');
            targetLink.setAttribute('aria-current', 'true');
        }
    }

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('data-target');
            if (targetId) {
                window.history.pushState(null, '', `#${targetId}`);
                switchPanel(targetId);
            }
        });
    });

    const initialHash = window.location.hash ? window.location.hash.substring(1) : 'info-general';
    switchPanel(initialHash);


    const themeTogglePanel = document.getElementById('theme-toggle-panel');
    const themeStatus = document.getElementById('theme-status');
    const themeToggleButton = document.getElementById('theme-toggle'); 

    if (themeTogglePanel && themeStatus && themeToggleButton) {
        
        function updateThemeStatus() {
            const isDarkMode = document.body.classList.contains('dark-mode');
            themeStatus.textContent = isDarkMode ? 'Oscuro' : 'Claro';
            themeTogglePanel.textContent = `Alternar Modo (${themeStatus.textContent})`;
        }

        themeTogglePanel.addEventListener('click', () => {
            themeToggleButton.click(); 
        });

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "class") {
                    updateThemeStatus();
                }
            });
        });
        observer.observe(document.body, { attributes: true }); 

        updateThemeStatus();
    }

    const forms = document.querySelectorAll('.perfil-panel form');
    forms.forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });

    loadUserProfile();
});