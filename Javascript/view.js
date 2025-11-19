const hamburguesa = document.getElementById('hamburguesa');
const navegacion = document.getElementById('navegacionDelSitio');
const cerrarMenu = document.getElementById('cerrar-menu');

hamburguesa.addEventListener('click', () => {
    navegacion.classList.toggle('menu-visible');
});

cerrarMenu.addEventListener('click', () => {
    navegacion.classList.remove('menu-visible');
});

// Cerrar menú al hacer clic en un enlace
document.querySelectorAll('#navegacionDelSitio a').forEach(enlace => {
    enlace.addEventListener('click', () => {
        navegacion.classList.remove('menu-visible');
    });
});
