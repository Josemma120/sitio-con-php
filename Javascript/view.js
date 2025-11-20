document.addEventListener('DOMContentLoaded', () => {
    const hamburguesa = document.getElementById('hamburguesa');
    const navegacion = document.getElementById('navegacionDelSitio');
    const cerrarMenu = document.getElementById('cerrar-menu');
    const formulario = document.getElementById('crearUsuarioForm');
    const listaHTML = document.getElementById('listaUsuarios');

    if (hamburguesa && navegacion) {
        hamburguesa.addEventListener('click', () => navegacion.classList.toggle('menu-visible'));
    }
    if (cerrarMenu && navegacion) {
        cerrarMenu.addEventListener('click', () => navegacion.classList.remove('menu-visible'));
    }
    document.querySelectorAll('#navegacionDelSitio a').forEach(enlace => {
        enlace.addEventListener('click', () => {
            if (navegacion) navegacion.classList.remove('menu-visible');
        });
    });
    
    if (formulario) {
        formulario.addEventListener('submit', async function(evento) {
            evento.preventDefault();
            const nombre = document.getElementById('username')?.value ?? '';
            const contrasena = document.getElementById('password')?.value ?? '';

            try {
                const respuesta = await fetch('src/guardar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: nombre, password: contrasena })
                });

                const json = await respuesta.json();
                if (respuesta.ok) {
                    cargarUsuarios();
                    formulario.reset();
                } else {
                    console.error('Server error:', json);
                    alert(json.error ?? 'Error del servidor');
                }
            } catch (error) {
                console.error("Error:", error);
                alert('Error de conexión');
            }
        });
    }

    if (listaHTML) {
        listaHTML.addEventListener('click', async (evento) => {
            if (evento.target.matches('.btn-eliminar')) {
                const username = evento.target.dataset.username;
                if (confirm(`¿Estás seguro de que quieres eliminar a "${username}"?`)) {
                    eliminarUsuario(username);
                }
            }
        });
    }

    async function eliminarUsuario(username) {
        try {
            const respuesta = await fetch('src/guardar.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: username })
            });

            const json = await respuesta.json();
            if (respuesta.ok) {
                cargarUsuarios();
            } else {
                alert(json.error || 'No se pudo eliminar el usuario.');
            }
        } catch (error) {
            console.error('Error al eliminar:', error);
            alert('Error de conexión al intentar eliminar.');
        }
    }

    cargarUsuarios();

    async function cargarUsuarios() {
        try {
            const respuesta = await fetch('src/guardar.php');
            const datos = await respuesta.json();

            if (!Array.isArray(datos)) {
                console.error('Respuesta inesperada:', datos);
                mostrarUsuariosEnPantalla([], listaHTML);
                return;
            }

            mostrarUsuariosEnPantalla(datos, listaHTML);
        } catch (error) {
            console.error("Error al cargar usuarios:", error);
        }
    }

    function mostrarUsuariosEnPantalla(datos, lista) {
        if (!lista) return;
        lista.innerHTML = '';

        if (datos.length === 0) {
            lista.innerHTML = '<li>No hay usuarios registrados.</li>';
            return;
        }

        datos.forEach(function(usuario) {
            const itemLista = document.createElement('li');
            
            const nombreUsuario = document.createElement('span');
            nombreUsuario.textContent = usuario.usuario;

            const btnEliminar = document.createElement('button');
            btnEliminar.textContent = 'Eliminar';
            btnEliminar.className = 'btn-eliminar';
            btnEliminar.dataset.username = usuario.usuario;

            itemLista.appendChild(nombreUsuario);
            itemLista.appendChild(btnEliminar);
            lista.appendChild(itemLista);
        });
    }
});