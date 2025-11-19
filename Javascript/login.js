document.addEventListener('DOMContentLoaded', () => {
    // Reutilizar código para menú hamburguesa
    const hamburguesa = document.getElementById('hamburguesa');
    const navegacion = document.getElementById('navegacionDelSitio');
    if (hamburguesa && navegacion) {
        hamburguesa.addEventListener('click', () => navegacion.classList.toggle('menu-visible'));
    }

    const loginForm = document.getElementById('loginForm');
    const mensajeDiv = document.getElementById('mensaje-login');

    if (loginForm) {
        loginForm.addEventListener('submit', async (evento) => {
            evento.preventDefault();
            mensajeDiv.textContent = ''; // Limpiar mensajes previos

            const username = document.getElementById('username')?.value;
            const password = document.getElementById('password')?.value;

            if (!username || !password) {
                alert('Por favor, completa ambos campos.');
                return;
            }

            try {
                const respuesta = await fetch('src/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });

                const data = await respuesta.json();

                if (respuesta.ok) {
                    // Login exitoso, redirigir a index.html
                    window.location.href = 'index.html';
                } else {
                    mensajeDiv.textContent = data.message || 'Error en el inicio de sesión.';
                    mensajeDiv.style.color = 'red';
                }

            } catch (error) {
                console.error('Error de conexión:', error);
                mensajeDiv.textContent = 'No se pudo conectar con el servidor.';
                mensajeDiv.style.color = 'red';
            }
        });
    }
});