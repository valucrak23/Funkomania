<div class="content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="text-center mb-4"><i class="bi bi-envelope-fill"></i> Contacto</h2>
            <p class="text-center text-muted mb-5">
                ¿Tienes alguna pregunta o comentario? Completa el formulario y nos pondremos en contacto contigo.
            </p>

            <form action="secciones/contacto_procesar.php" method="post" class="row g-3 admin-form-container" novalidate>
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre y Apellido *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,50}" 
                           title="Ingrese un nombre válido (2-50 caracteres, solo letras y espacios)"
                           required>
                    <div class="invalid-feedback">
                        Por favor ingrese su nombre completo.
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Correo electrónico *</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                           title="Ingrese un email válido"
                           required>
                    <div class="invalid-feedback">
                        Por favor ingrese un email válido.
                    </div>
                </div>
                <div class="col-12">
                    <label for="mensaje" class="form-label">Mensaje *</label>
                    <textarea class="form-control" id="mensaje" name="mensaje" rows="5" 
                              minlength="10" maxlength="500"
                              title="El mensaje debe tener entre 10 y 500 caracteres"
                              required></textarea>
                    <div class="invalid-feedback">
                        El mensaje debe tener al menos 10 caracteres.
                    </div>
                    <div class="form-text text-end mt-1">
                        <span id="contador">0</span>/500
                    </div>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg btn-tematico">
                        <i class="bi bi-send"></i> Enviar mensaje
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    (function() {
        'use strict';
        var forms = document.querySelectorAll('form[novalidate]');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // Contador de caracteres
    const mensajeTextarea = document.getElementById('mensaje');
    if (mensajeTextarea) {
        mensajeTextarea.addEventListener('input', function() {
            const contador = document.getElementById('contador');
            contador.textContent = this.value.length;
        });
    }
});
</script>