<?php

/*
Template: Init IK Woo Moodle
Author: Gabriel Caroprese / Inforket.com
Update Date: 07/06/2021
*/

// Mensaje si Woocommerce no está configurado
add_action( 'admin_notices', 'ik_woomoodle_dependencies' );

function ik_woomoodle_dependencies() {
    if (!class_exists('woocommerce')) {
    echo '<div class="error"><p>' . __( 'Atención: El plugin "Asociación de compras Woocommerce - Moodle" depende de tener instalado y activado Woocommerce para funcionar correctamente.' ) . '</p></div>';
    }
}

// Agrego menú de panel de configuració en el Admin de Wordpress
add_action('admin_menu', 'ik_woomoodle_menu');
function ik_woomoodle_menu(){
    add_menu_page('Asociación Woocommerce Moodle - Panel', 'Woo-Moodle', 'manage_options', 'ik_woomoodle_panel', 'ik_woomoodle_panel', IK_WOOMOODLE_PLUGIN_PUBLIC.'/img/woo-moodle-icon.png' );
    add_submenu_page('ik_woomoodle_panel', 'Asociación Woocommerce Moodle - Conexión', 'Conexión', 'manage_options', 'ik_woomoodle_panel', 'ik_woomoodle_panel' );
    add_submenu_page('ik_woomoodle_panel', 'Asociación Woocommerce Moodle - Información', 'Información', 'manage_options', 'ik_woomoodle_panel_info', 'ik_woomoodle_panel_info' );
    add_submenu_page('ik_woomoodle_panel', 'Asociación Woocommerce Moodle - Mensajes', 'Mensajes', 'manage_options', 'ik_woomoodle_panel_messages', 'ik_woomoodle_panel_messages' );
}

// Creo la página del panel de configuración del plugin
function ik_woomoodle_panel(){
   echo '
   <style>
   h1 { text-align: center; }
   </style>';
   include(IK_WOOMOODLE_PLUGIN_DIR.'/templates/ik_woomoodle_panel.php');
}

// Creo la página del panel de configuración básica del plugin
function ik_woomoodle_panel_info(){
   echo '
   <style>
   h1 { text-align: center; }
   </style>';
   include(IK_WOOMOODLE_PLUGIN_DIR.'/templates/ik_woomoodle_info.php');
}

// Creo la página del panel de configuración de mensajes del plugin
function ik_woomoodle_panel_messages(){
   echo '
   <style>
   h1 { text-align: center; }
   </style>';
   include(IK_WOOMOODLE_PLUGIN_DIR.'/templates/ik_woomoodle_messages.php');
}

?>