<footer class="footer">
    <p class="mb-0">
        &copy; <?php echo date("Y"); ?> <span class="creator">Creado por Tomas Gomez</span>
    </p>
</footer>

<style>
    /* Estructura para que el footer siempre esté abajo sin tapar el contenido */
    html, body {
        height: 100%;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    #page-container {
        display: flex;
        flex-direction: column;
        min-height: 100vh; /* Asegura que el contenido ocupe toda la pantalla */
    }

    #content-wrap {
        flex: 1; /* Ocupa el espacio restante, empujando el footer hacia abajo */
    }

    /* Estilos mejorados para el footer */
    .footer {
        background: #ffffff; /* Fondo blanco */
        color: #333333; /* Texto oscuro para un buen contraste */
        text-align: center;
        padding: 15px 0;
        width: 100%;
        font-family: 'Poppins', sans-serif; /* Fuente moderna y elegante */
        font-size: 14px;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1); /* Sombra suave arriba */
        position: relative; /* No se superpone al contenido */
        bottom: 0;
        left: 0;
    }

    /* Resalta el nombre del creador con un color moderno */
    .creator {
        color: rgb(0, 0, 0); /* Color oscuro */
        font-weight: 600; /* Un poco más grueso para destacar */
    }

    /* Importar la fuente Poppins si aún no está en el proyecto */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
</style>
