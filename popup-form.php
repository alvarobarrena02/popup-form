<?php
/**
 * Plugin Name: Formulario de Contacto
 * Plugin URI: https://www.lasdoceen.com
 * Description: Un plugin que añade un formulario emergente de contacto en WordPress.
 * Version: 1.0
 * Author: Álvaro Barrena Revilla
 * Author URI: https://github.com/alvarobarrena02
 */

function popup_form() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    wp_enqueue_style('styles-css', plugins_url('style.css', __FILE__));
    wp_enqueue_script('scripts-js', plugins_url('script.js', __FILE__), array('jquery'), null, true);

    // Inyecta la variable ajaxurl
    wp_localize_script('scripts-js', 'ajax_object', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'popup_form');


function mostrar_sobre() {
    echo '<div id="icono-sobre"><a><img loading="lazy" decoding="async" src="' . plugins_url('email-importante.png', __FILE__) . '" width="45" height="50"></a></div>';
}
    add_action('wp_footer', 'mostrar_sobre');

function procesar_formulario() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'contactos';

    $email = $_POST['email'];
    $telefono = $_POST['telefono'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        wp_send_json_error('El correo electrónico no es válido');
        return;
    }

    if (!preg_match('/^[0-9]{10,14}$/', $telefono)) {
        wp_send_json_error('El número de teléfono no es válido');
        return;
    }

    $datos = array(
        'nombre' => $_POST['nombre'],
        'apellidos' => $_POST['apellidos'],
        'email' => $email,
        'telefono' => $telefono,
        'asunto' => $_POST['asunto'],
        'mensaje' => $_POST['mensaje']
    );
    $wpdb->insert($tabla, $datos);
}
add_action('wp_ajax_procesar_formulario', 'procesar_formulario');
add_action('wp_ajax_nopriv_procesar_formulario', 'procesar_formulario');

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
?>
