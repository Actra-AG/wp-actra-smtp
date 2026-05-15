<?php
/**
 * Plugin Name: Actra SMTP
 * Description: A minimal, object-oriented SMTP plugin for WordPress.
 * Version:     1.0.0
 * Author:      Actra AG
 * Author URI:  https://www.actra.ch
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * License:     GPLv2 or later
 * Text Domain: actra-smtp
 * @package     ActraSmtp
 */

namespace Actra\Smtp;

if ( ! defined( constant_name: 'ABSPATH' ) ) {
	exit;
}

define( constant_name: 'ACTRA_SMTP_VERSION', value: '1.0.0' );
define( constant_name: 'ACTRA_SMTP_PATH', value: plugin_dir_path( file: __FILE__ ) );
define( constant_name: 'ACTRA_SMTP_URL', value: plugin_dir_url( file: __FILE__ ) );

require_once ACTRA_SMTP_PATH . 'includes/Autoloader.php';

$autoloader = new Autoloader();
$autoloader->add_namespace( prefix: 'Actra\Smtp\\', dir: ACTRA_SMTP_PATH . 'includes/' );
$autoloader->register();

add_action( hook_name: 'plugins_loaded', callback: function() {
	Core\Plugin::instance()->run();
} );
