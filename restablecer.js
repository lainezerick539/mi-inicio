document.addEventListener('DOMContentLoaded', function() {
const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    const tokenInput = document.getElementById('token');
    const mensajeDiv = document.getElementById('mensaje-respuesta');

    if (token) {
        tokenInput.value = token;
    } else {
        mensajeDiv.innerText = "Error: Token no encontrado. Asegúrate de usar el enlace correcto.";
        mensajeDiv.className = 'mensaje error';
    }

    const form = document.getElementById('form-restablecer');

    form.addEventListener('submit', function(e) {

        e.preventDefault(); 

        const formData = new FormData(this);

        mensajeDiv.innerText = '';
        mensajeDiv.className = '';

        fetch('restablecer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            
            mensajeDiv.innerText = data.message;

            if (data.status === 'success') {
                mensajeDiv.className = 'mensaje success';
                form.reset(); 
            } else {
                mensajeDiv.className = 'mensaje error';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mensajeDiv.innerText = 'Error de conexión. Por favor, intenta de nuevo.';
            mensajeDiv.className = 'mensaje error';
        });
    });
});