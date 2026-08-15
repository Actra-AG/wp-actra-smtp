<?php
/**
 * SMTP password provider class file.
 *
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp\Core;

if (!defined(constant_name: 'ABSPATH')) {
    exit;
}

/**
 * Provides the final SMTP password from the configured source.
 */
class SmtpPasswordProvider
{
    private const PASSWORD_CONSTANT = 'ACTRA_SMTP_PASSWORD';
    private const PASSWORD_OPTION = 'actra-smtp_password';

    public static function has_configured_password_constant(): bool
    {
        return defined(constant_name: SmtpPasswordProvider::PASSWORD_CONSTANT);
    }

    public function get_password(): string
    {
        if (SmtpPasswordProvider::has_configured_password_constant()) {
            return (string)constant(name: SmtpPasswordProvider::PASSWORD_CONSTANT);
        }

        return Encryption::decrypt(
            raw_value: (string)get_option(option: SmtpPasswordProvider::PASSWORD_OPTION, default_value: '')
        );
    }
}