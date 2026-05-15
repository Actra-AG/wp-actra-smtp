<?php
/**
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Actra\Smtp\Admin\Settings;

/**
 * Main Plugin Controller.
 */
class Plugin {

	protected static $instance = null;

	public static function instance(): Plugin {
		if ( null === Plugin::$instance ) {
			Plugin::$instance = new Plugin();
		}
		return Plugin::$instance;
	}

	protected function __construct() {}

	public function run(): void {
		if ( is_admin() ) {
			new Settings();
		}
		
		new Mailer();
	}
}
