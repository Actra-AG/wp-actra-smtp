<?php
/**
 * SMTP configuration provider class file.
 *
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp\Core;

if (!defined(constant_name: 'ABSPATH')) {
    exit;
}

/**
 * Provides final SMTP configuration values from wp-config.php constants or database options.
 */
class SmtpConfigProvider
{
    private const CONSTANTS = [
        'actra-smtp_sender_email' => 'ACTRA_SMTP_FROM_EMAIL',
        'actra-smtp_hostname' => 'ACTRA_SMTP_HOST',
        'actra-smtp_username' => 'ACTRA_SMTP_USERNAME',
        'actra-smtp_password' => 'ACTRA_SMTP_PASSWORD',
        'actra-smtp_port' => 'ACTRA_SMTP_PORT',
        'actra-smtp_tls' => 'ACTRA_SMTP_TLS',
    ];

    public static function has_constant_for_option(string $option_name): bool
    {
        return isset(SmtpConfigProvider::CONSTANTS[$option_name])
            && defined(constant_name: SmtpConfigProvider::CONSTANTS[$option_name]);
    }

    public static function has_any_config_constant(): bool
    {
        foreach (SmtpConfigProvider::CONSTANTS as $constant_name) {
            if (defined(constant_name: $constant_name)) {
                return true;
            }
        }

        return false;
    }

    public function get_sender_email(): string
    {
        return $this->get_string_value(
            option_name: 'actra-smtp_sender_email',
            default_value: ''
        );
    }

    public function get_host(): string
    {
        return $this->get_string_value(
            option_name: 'actra-smtp_hostname',
            default_value: ''
        );
    }

    public function get_username(): string
    {
        return $this->get_string_value(
            option_name: 'actra-smtp_username',
            default_value: ''
        );
    }

    public function get_password(): string
    {
        if (SmtpConfigProvider::has_constant_for_option(option_name: 'actra-smtp_password')) {
            return (string)$this->get_constant_value(option_name: 'actra-smtp_password');
        }

        return Encryption::decrypt(
            raw_value: (string)get_option(option: 'actra-smtp_password', default_value: '')
        );
    }

    public function get_port(): int
    {
        if (SmtpConfigProvider::has_constant_for_option(option_name: 'actra-smtp_port')) {
            return absint(maybe: $this->get_constant_value(option_name: 'actra-smtp_port'));
        }

        return (int)get_option(option: 'actra-smtp_port', default_value: 587);
    }

    public function is_tls_enabled(): bool
    {
        if (SmtpConfigProvider::has_constant_for_option(option_name: 'actra-smtp_tls')) {
            return $this->normalize_bool(
                value: $this->get_constant_value(option_name: 'actra-smtp_tls'),
                default_value: true
            );
        }

        return 'yes' === get_option(option: 'actra-smtp_tls', default_value: 'yes');
    }

    private function get_string_value(string $option_name, string $default_value): string
    {
        if (SmtpConfigProvider::has_constant_for_option(option_name: $option_name)) {
            return (string)$this->get_constant_value(option_name: $option_name);
        }

        return (string)get_option(option: $option_name, default_value: $default_value);
    }

    private function get_constant_value(string $option_name): mixed
    {
        return constant(name: SmtpConfigProvider::CONSTANTS[$option_name]);
    }

    private function normalize_bool(mixed $value, bool $default_value): bool
    {
        if (is_bool(value: $value)) {
            return $value;
        }

        $normalized = strtolower(string: trim(string: (string)$value));

        if (in_array(needle: $normalized, haystack: ['1', 'true', 'yes', 'on'], strict: true)) {
            return true;
        }

        if (in_array(needle: $normalized, haystack: ['0', 'false', 'no', 'off', ''], strict: true)) {
            return false;
        }

        return $default_value;
    }
}