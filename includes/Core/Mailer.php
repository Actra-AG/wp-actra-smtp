<?php
/**
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp\Core;

/**
 * Handles the SMTP mailing logic.
 */
class Mailer {

	public function __construct() {
		add_action( hook_name: 'phpmailer_init', callback: [ $this, 'configure_phpmailer' ] );
		add_action( hook_name: 'wp_mail_failed', callback: [ $this, 'log_errors' ] );
	}

	public function configure_phpmailer( $phpmailer ): void {
		$username = get_option( option: 'actra-smtp_username' );

		$phpmailer->isSMTP();
		$phpmailer->Host       = get_option( option: 'actra-smtp_hostname' );
		$phpmailer->SMTPAuth   = ! empty( $username );
		$phpmailer->Username   = $username;
		$phpmailer->Password   = get_option( option: 'actra-smtp_password' );
		$phpmailer->Port       = (int) get_option( option: 'actra-smtp_port', default_value: 587 );
		$phpmailer->SMTPSecure = 'yes' === get_option( option: 'actra-smtp_tls', default_value: 'yes' ) ? 'tls' : '';
		$phpmailer->From       = get_option( option: 'actra-smtp_sender_email' );
		$phpmailer->FromName   = get_option( option: 'actra-smtp_sender_email' );
	}

	public function log_errors( $error ): void {
		if ( defined( constant_name: 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( message: 'Actra SMTP Error: ' . $error->get_error_message() );
		}
	}
}
