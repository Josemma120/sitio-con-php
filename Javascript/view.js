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
                    // recargar lista y limpiar formulario
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

    // Cargar usuarios al inicio
    cargarUsuarios();

    async function cargarUsuarios() {
        try {
            const respuesta = await fetch('src/guardar.php');
            const datos = await respuesta.json();

            // Si backend devuelve objeto de error, mostrar en consola
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

        datos.forEach(function(usuario, index) {
            const itemLista = document.createElement('li');
            // La BD devuelve { usuario: "...", password: "..." }
            // Mostrar nombre y usar índice si quieres un identificador
            itemLista.textContent = `#${index + 1}: ${usuario.usuario}`;
            lista.appendChild(itemLista);
        });
    }
});