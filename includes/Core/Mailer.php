<?php
/**
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp\Core;

if (!defined(constant_name: 'ABSPATH')) {
    exit;
}

/**
 * Handles the SMTP mailing logic.
 */
class Mailer
{
    private SmtpConfigProvider $config_provider;

    public function __construct()
    {
        $this->config_provider = new SmtpConfigProvider();

        add_action(hook_name: 'phpmailer_init', callback: [$this, 'configure_phpmailer']);
        add_action(hook_name: 'wp_mail_failed', callback: [$this, 'log_errors']);
    }

    public function configure_phpmailer($phpmailer): void
    {
        $username = $this->config_provider->get_username();

        $phpmailer->isSMTP();
        $phpmailer->Host = $this->config_provider->get_host();
        $phpmailer->SMTPAuth = '' !== $username;
        $phpmailer->Username = $username;
        $phpmailer->Password = $this->config_provider->get_password();
        $phpmailer->Port = $this->config_provider->get_port();
        $phpmailer->SMTPSecure = $this->config_provider->is_tls_enabled() ? 'tls' : '';
        $phpmailer->From = $this->config_provider->get_sender_email();
        $phpmailer->FromName = $this->config_provider->get_sender_email();
    }

    public function log_errors($error): void
    {
        if (defined(constant_name: 'WP_DEBUG') && WP_DEBUG) {
            error_log(message: 'Actra SMTP Error: ' . $error->get_error_message());
        }
    }
}