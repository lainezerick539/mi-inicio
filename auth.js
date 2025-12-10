document.addEventListener('DOMContentLoaded', () => {
    const profileLinkLi = document.getElementById('profile-link-li');
    const loginLi = document.getElementById('login-li');
    const registerLi = document.getElementById('register-li');
    const logoutLi = document.getElementById('logout-li');
    const logoutButton = document.getElementById('logout-button');

    fetch('check_session.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('La respuesta de la red no fue correcta');
            }
            return response.json();
        })
        .then(data => {
            if (data.loggedIn) {
                profileLinkLi.style.display = 'list-item';
                logoutLi.style.display = 'list-item';
                loginLi.style.display = 'none';
                registerLi.style.display = 'none';
            } else {
                profileLinkLi.style.display = 'none';
                logoutLi.style.display = 'none';
                loginLi.style.display = 'list-item';
                registerLi.style.display = 'list-item';
            }
        })
        .catch(error => {
            console.error('Error al verificar la sesión:', error);
            profileLinkLi.style.display = 'none';
            logoutLi.style.display = 'none';
            loginLi.style.display = 'list-item';
            registerLi.style.display = 'list-item';
        });

    if (logoutButton) {
        logoutButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = 'logout.php';
        });
    }
});