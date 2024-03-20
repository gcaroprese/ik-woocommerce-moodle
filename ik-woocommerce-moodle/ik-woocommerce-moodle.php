<?php
/*
Plugin Name: IK Woo Moodle (Conector entre Woocommerce y Moodle)
Description: Relaciona compras de Woocommerce con Moodle y crea usuario en la plataforma de estudios
Version: 2.1.3
Author: Gabriel Caroprese
Author URI: https://inforket.com/
Requires at least: 5.3
Requires PHP: 7.2
*/ 

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$wooMoodleDir = dirname( __FILE__ );
$wooMoodlePublicDir = plugin_dir_url(__FILE__ );
define( 'IK_WOOMOODLE_PLUGIN_DIR', $wooMoodleDir );
define( 'IK_WOOMOODLE_PLUGIN_PUBLIC', $wooMoodlePublicDir );

//I add plugin functions
require_once($wooMoodleDir . '/include/init.php');
require_once($wooMoodleDir . '/include/functions.php');

?>