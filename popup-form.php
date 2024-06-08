<?php
/**
 * Plugin Name: Formulario de Contacto
 * Plugin URI: https://www.lasdoceen.com
 * Description: Un plugin que añade un formulario emergente de contacto en WordPress.
 * Version: 1.0
 * Author: Álvaro Barrena Revilla
 * Author URI: https://github.com/alvarobarrena02
 */

// Enqueue scripts and styles
function popup_form() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    wp_enqueue_style('styles-css', plugins_url('style.css', __FILE__));
    wp_enqueue_style('sweetalert2-css', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css');
    wp_enqueue_script('scripts-js', plugins_url('script.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('sweetalert2-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js', array('jquery'), null, true);

    // Inyecta la variable ajaxurl
    wp_localize_script('scripts-js', 'ajax_object', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'popup_form');

// Mostrar el icono del sobre en la parte inferior derecha
function mostrar_sobre() {
    echo '<div id="icono-sobre"><a><img loading="lazy" decoding="async" src="' . plugins_url('email-importante.png', __FILE__) . '" width="45" height="50"></a></div>';
}
add_action('wp_footer', 'mostrar_sobre');

// Procesar el formulario y guardar los datos en la base de datos
function procesar_formulario() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'contactos';

    $nombre = sanitize_text_field($_POST['nombre']);
    $apellidos = sanitize_text_field($_POST['apellidos']);
    $asunto = sanitize_text_field($_POST['asunto']);
    $email = sanitize_email($_POST['email']);
    $telefono = sanitize_text_field($_POST['telefono']);

    // Validación del nombre
    if (empty($nombre)) {
        wp_send_json_error('El nombre no es válido, vuelve a intentarlo');
        return;
    }

    // Validación de los apellidos
    if (empty($apellidos)) {
        wp_send_json_error('Los apellidos no son válidos, vuelve a intentarlo');
        return;
    }

    // Validación del asunto
    if (empty($asunto) || strlen($asunto) < 2 || strlen($asunto) > 50) {
        wp_send_json_error('El asunto no es válido, vuelve a intentarlo');
        return;
    }

    // Validación del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        wp_send_json_error('El correo electrónico no es válido, vuelve a intentarlo');
        return;
    }

    // Validación del teléfono
    if (!preg_match('/^\+?[0-9]{9,15}$/', $telefono)) {
        wp_send_json_error('El número de teléfono no es válido, vuelve a intentarlo');
        return;
    }

    // Validación de los checkboxes
    if (!isset($_POST['politica_privacidad']) || !isset($_POST['consentimiento_datos'])) {
        wp_send_json_error('Debes aceptar la política de privacidad y dar tu consentimiento para el tratamiento de datos');
        return;
    }

    $datos = array(
        'nombre' => sanitize_text_field($_POST['nombre']),
        'apellidos' => sanitize_text_field($_POST['apellidos']),
        'email' => sanitize_email($_POST['email']),
        'telefono' => sanitize_text_field($_POST['telefono']),
        'asunto' => sanitize_text_field($_POST['asunto']),
        'mensaje' => sanitize_textarea_field($_POST['mensaje'])
    );
    // Preparación de consultas SQL
    $formato = array('%s','%s','%s','%s','%s','%s');

    $wpdb->insert($tabla, $datos, $formato);
    wp_send_json_success('Formulario enviado con éxito');
}
add_action('wp_ajax_procesar_formulario', 'procesar_formulario');
add_action('wp_ajax_nopriv_procesar_formulario', 'procesar_formulario');


// Crear la tabla de contactos en la base de datos
function crear_tabla_contactos() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'contactos';
    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50),
        apellidos VARCHAR(50),
        email VARCHAR(100),
        telefono VARCHAR(20),
        asunto VARCHAR(100),
        mensaje TEXT
    )";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('plugins_loaded', 'crear_tabla_contactos');

// Añadir la pestaña de contacto en el menú de administración
function contactos_menu() {
    add_menu_page(
        'Mensajes',
        'Mensajes',
        'manage_options',
        'mensajes',
        'mostrar_mensajes',
        'dashicons-email',
        6
    );
}
add_action('admin_menu', 'contactos_menu');

// Descargar CSV
function descargar_csv() {
    if (isset($_POST['download_csv'])) {
        global $wpdb;
        $tabla = $wpdb->prefix . 'contactos';
        $resultados = $wpdb->get_results("SELECT * FROM $tabla");

        $filename = 'contactos.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment;filename='.$filename);

        $output = fopen('php://output', 'w');
        // Agregar BOM (Byte Order Mark) para que Excel pueda abrir el archivo CSV correctamente con caracteres UTF-8
        fputs($output, "\xEF\xBB\xBF");

        // fputcsv($output, array('Nombre', 'Apellidos', 'Email', 'Teléfono'));

        foreach ($resultados as $resultado) {
            fputcsv($output, array($resultado->nombre, $resultado->apellidos, $resultado->email, $resultado->telefono));
        }
        fclose($output);
        exit;
    }
}
add_action('admin_init', 'descargar_csv');

// Mostrar los mensajes en el backend de WordPress
function mostrar_mensajes() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'contactos';

    // Manejar la eliminación de mensajes
    if (isset($_POST['delete_contact'])) {
        $id = intval($_POST['contact_id']);
        $wpdb->delete($tabla, array('id' => $id));
        echo '<div class="updated"><p>Mensaje eliminado.</p></div>';
    }

    // Obtener los mensajes
    $resultados = $wpdb->get_results("SELECT * FROM $tabla");

    // Mostrar los mensajes en una tabla
    echo '<div class="wrap"><h1>Mensajes de Contacto</h1>';
    echo '<form method="post" action="" style="margin-top: 10px;">';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Nombre</th><th>Apellidos</th><th>Email</th><th>Teléfono</th><th>Asunto</th><th>Mensaje</th><th>Acciones</th></tr></thead><tbody>';
    foreach ($resultados as $resultado) {
        echo '<tr>';
        echo '<td>' . $resultado->id . '</td>';
        echo '<td>' . esc_html($resultado->nombre) . '</td>'; // Escapar el HTML con función esc_html() para evitar ataques XSS
        echo '<td>' . esc_html($resultado->apellidos) . '</td>';
        echo '<td>' . esc_html($resultado->email) . '</td>';
        echo '<td>' . esc_html($resultado->telefono) . '</td>';
        echo '<td>' . esc_html($resultado->asunto) . '</td>';
        echo '<td>' . esc_html($resultado->mensaje) . '</td>';
        echo '<td><form method="post" action=""><input type="hidden" name="contact_id" value="' . $resultado->id . '"><input type="submit" name="delete_contact" value="Borrar" class="button button-danger" style="background-color: #ff0000; color: #fff; border: 1px solid #ff0000;"></form></td>';
        echo '</tr>';
    }
    echo '</tbody></table></form>';

    echo '<form method="post" action="" style="margin-top: 10px;">';
    echo '<input type="submit" name="download_csv" value="Descargar CSV" class="button button-primary">';
    echo '</form></div>';
}
?>
