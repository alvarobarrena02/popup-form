jQuery(document).ready(function($) {
    $('#icono-sobre').click(function() {
        if ($('#popup-form').length === 0) {
            $('body').append('<div id="overlay"></div>'); // Se crea un div para difuminar el fondo
            $('body').append('<div id="popup-form">' +
                '<h2>Contacta con nosotros</h2>' +
                '<span id="cerrar-popup">×</span>' +
                '<form id="contact-form">' +
                '<label for="nombre" class="fcustom">Nombre: <input type="text" id="nombre" name="nombre"></label>' +
                '<label for="apellidos" class="fcustom">Apellidos: <input type="text" id="apellidos" name="apellidos"></label>' +
                '<label for="email" class="fcustom">Email: <input type="email" id="email" name="email"></label>' +
                '<label for="telefono" class="fcustom">Teléfono: <input type="text" id="telefono" name="telefono"></label>' +
                '<label for="asunto" class="fcustom">Asunto: <input type="text" id="asunto" name="asunto"></label>' +
                '<label for="mensaje" class="fcustom">Mensaje: <textarea id="mensaje" name="mensaje"></textarea></label>' +
                '<label for="politica-privacidad"><input type="checkbox" id="politica-privacidad" name="politica-privacidad"> Acepto la Política de Privacidad y que mis datos serán almacenados para esta solicitud GDPR.</label>' +
                '<label for="consentimiento-datos"><input type="checkbox" id="consentimiento-datos" name="consentimiento-datos"> Consiente el tratamiento de sus datos personales con la finalidad de enviarle invitaciones a cursos y talleres similares al solicitado.</label>' +
                '<br><input type="submit" value="Enviar">' +
                '</form>' +
                '</div>');
        }

        $('#contact-form').on('submit', function(event) {
            event.preventDefault();
            var datos = $(this).serialize();
            $.ajax({
                url: ajax_object.ajaxurl,
                type: 'POST',
                data: datos + '&action=procesar_formulario',
                success: function(response) {
                    $('#overlay, #popup-form').remove();
                    alert("¡Formulario enviado con éxito!.");
                },
                error: function(response) {
                    alert("Hubo un error al enviar el formulario. Inténtelo de nuevo.");
                }
            });
        });

        $('#cerrar-popup').click(function() {
            $('#overlay, #popup-form').remove();
        });
    });
});