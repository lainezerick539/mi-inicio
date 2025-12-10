<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - ShopGo</title>
    
    <link rel="stylesheet" href="contacto.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="contenedor">
        <header>
            <div class="barra-navegacion">

                <a href="menu.php" class="marca">
                    <div class="logo">
                <img src="logo.png" alt="Logo" class="logo-img">
              </div>
                </a>

                <button id="theme-toggle" class="theme-btn">
                    <ion-icon id="theme-icon" name="contrast-outline"></ion-icon>
                </button>

                <nav class="navegacion" aria-label="Principal">
                    <a href="sobre.html">Sobre nosotros</a>
                    <a href="servicios.html">Servicios</a>
                    <a href="contacto.html" class="activo">Contacto</a>
                </nav>
            </div>
        </header>

        <main>
            <div class="contenido-contacto">
                <h1>Contáctanos</h1>
                <p class="destacado">Cuéntanos sobre tu proyecto y te responderemos a la brevedad.</p>

                <form id="formulario-contacto">
                    <div class="fila">
                        <div class="campo-grupo">
                            <label for="nombre">Nombre</label>
                            <div class="campo-con-icono">
                                <i class="fas fa-user"></i>
                                <input id="nombre" name="nombre" type="text" required placeholder="Tu nombre completo">
                            </div>
                        </div>

                        <div class="campo-grupo">
                            <label for="email">Correo electrónico</label>
                            <div class="campo-con-icono">
                                <i class="fas fa-envelope"></i>
                                <input id="email" name="email" type="email" required placeholder="tu.correo@ejemplo.com">
                            </div>
                        </div>
                    </div>

                    <div class="campo-grupo">
                        <label for="mensaje">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" required placeholder="Describe tu proyecto o consulta..."></textarea>
                    </div>

                    <div class="acciones-formulario">
                        <button type="submit"><i class="fas fa-paper-plane"></i> Enviar Mensaje</button>
                    </div>
                </form>
            </div>
        </main>

        <footer>
            <p>© <span id="year"></span> ShopGo. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script src="theme-toggle.js"></script>
    <script>
        document.getElementById("year").textContent = new Date().getFullYear();

        document.getElementById('formulario-contacto').addEventListener('submit', function(e){
            e.preventDefault();
            const btn = this.querySelector('button');
            const txt = btn.innerHTML;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            btn.disabled = true;

            setTimeout(() => {
                alert('¡Gracias por tu mensaje! Te contactaremos pronto.');
                this.reset();
                btn.innerHTML = txt;
                btn.disabled = false;
            }, 1500);
        });
    </script>
</body>
</html>
