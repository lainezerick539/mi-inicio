document.getElementById('year').textContent = new Date().getFullYear();

document.getElementById('formulario-contacto').addEventListener('submit', function(e) {
    e.preventDefault();

    const boton = this.querySelector('button');
    const textoOriginal = boton.innerHTML;

    boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    boton.disabled = true;

    setTimeout(() => {
        alert('¡Gracias por tu mensaje! Te contactaremos pronto.');
        this.reset();
        boton.innerHTML = textoOriginal;
        boton.disabled = false;
    }, 1500);
});
