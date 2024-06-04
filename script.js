jQuery(document).ready(function($) {
    $('#icono-sobre').click(function() {
        if ($('#popup-form').length === 0) {
            $('body').append('<div id="overlay"></div>'); // Se crea un div para difuminar el fondo
            $('body').append('<div id="popup-form">' +
                '<h2>Contacta con nosotros</h2>' +
                '<span id="cerrar-popup">×</span>' +
                '<form id="contact-form">' +
                '<label for="nombre" class="fcustom">Nombre <input type="text" id="nombre" name="nombre"></label>' +
                '<label for="apellidos" class="fcustom">Apellidos <input type="text" id="apellidos" name="apellidos"></label>' +
                '<label for="email" class="fcustom">Email <input type="email" id="email" name="email"></label>' +
                '<label for="telefono" class="fcustom">Teléfono <input type="text" id="telefono" name="telefono"></label>' +
                '<label for="asunto" class="fcustom-full">Asunto <input type="text" id="asunto" name="asunto"></label>' +
                '<label for="mensaje" class="fcustom-full">Mensaje <textarea id="mensaje" name="mensaje"></textarea></label>' +
                '<label for="politica-privacidad" class="acepto"><input type="checkbox" id="politica-privacidad" name="politica_privacidad"> Acepto la Política de Privacidad y que mis datos serán almacenados para esta solicitud GDPR.</label>' +
                '<label for="consentimiento-datos" class="acepto"><input type="checkbox" id="consentimiento-datos" name="consentimiento_datos"> Consiento el tratamiento de mis datos personales con la finalidad de enviarle invitaciones a cursos y talleres similares al solicitado.</label>' +
                '<input type="submit" value="Enviar">' +
                '</form>' +
                '</div>');
        }

        $('#contact-form').on('submit', function(event) {
            event.preventDefault();

            var nombre = $('#nombre').val();
            var apellidos = $('#apellidos').val();
            var asunto = $('#asunto').val();
            var email = $('#email').val();
            var telefono = $('#telefono').val();
            var politicaPrivacidad = $('#politica-privacidad').prop('checked');
            var consentimientoDatos = $('#consentimiento-datos').prop('checked');

            var emailRegex = /^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/;
            var telefonoRegex = /^\+?[0-9]{9,15}$/;

            if (nombre.trim() === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, introduce tu nombre correctamente.'
                });
                return;
            }

            if (apellidos.trim() === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, introduce tus apellidos correctamente.'
                });
                return;
            }

            if (asunto.trim() === '' || asunto.length < 2 || asunto.length > 50) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, introduce un asunto válido (2-50 caracteres).'
                });
                return;
            }

            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, introduce un correo electrónico válido.'
                });
                return;
            }

            if (!telefonoRegex.test(telefono)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, introduce un número de teléfono válido.'
                });
                return;
            }

            if (!politicaPrivacidad || !consentimientoDatos) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debes aceptar la política de privacidad y dar tu consentimiento para el tratamiento de datos.'
                });
                return;
            }

            var datos = $(this).serialize();
            $.ajax({
                url: ajax_object.ajaxurl,
                type: 'POST',
                data: datos + '&action=procesar_formulario',
                success: function(response) {
                    if(response.success){
                        $('#overlay, #popup-form').remove();
                        Swal.fire({
                            icon: 'success',
                            title: 'Enviado',
                            text: '¡Formulario enviado con éxito!'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error: ' + response.data
                        });
                    }
                },
                error: function(response) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un error al enviar el formulario. Inténtalo de nuevo.'
                    });
                }
            });
        });

        // Cerrar al hacer clic en la X
        $('#cerrar-popup').click(function() {
            $('#overlay, #popup-form').remove();
        });

        // Cerrar al hacer clic fuera del formulario
        $('#overlay').click(function() {
            $('#overlay, #popup-form').remove();
        });

        // Cerrar al presionar la tecla Esc
        $(document).keyup(function(e) {
            if (e.key === "Escape") { // Esc key
                $('#overlay, #popup-form').remove();
            }
        });
    });
});