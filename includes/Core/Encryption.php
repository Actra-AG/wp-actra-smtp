<?php
/**
 * Encryption helper class file.
 *
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp\Core;

if (!defined(constant_name: 'ABSPATH')) {
    exit;
}

/**
 * Handles encryption and decryption of sensitive option values.
 */
class Encryption
{
    private const METHOD = 'AES-256-GCM';
    private const KEY_CONTEXT = 'actra_smtp_secure_encryption_key';
    private const PREFIX = 'actra-smtp-gcm:';
    private const TAG_LENGTH = 16;

    public static function encrypt(string $value): string
    {
        if ('' === $value) {
            return $value;
        }

        $iv_length = openssl_cipher_iv_length(cipher_algo: Encryption::METHOD);

        if (false === $iv_length) {
            return $value;
        }

        $iv = random_bytes($iv_length);
        $tag = '';

        $encrypted = openssl_encrypt(
            data: $value,
            cipher_algo: Encryption::METHOD,
            passphrase: Encryption::get_key(),
            options: 0,
            iv: $iv,
            tag: $tag,
            tag_length: Encryption::TAG_LENGTH
        );

        if (false === $encrypted || Encryption::TAG_LENGTH !== strlen(string: $tag)) {
            return $value;
        }

        return Encryption::PREFIX . base64_encode(string: $iv . $tag . $encrypted);
    }

    public static function decrypt(string $raw_value): string
    {
        if ('' === $raw_value) {
            return $raw_value;
        }

        if (!str_starts_with(haystack: $raw_value, needle: Encryption::PREFIX)) {
            return $raw_value;
        }

        $encoded = substr(string: $raw_value, offset: strlen(string: Encryption::PREFIX));
        $decoded = base64_decode(string: $encoded, strict: true);

        if (false === $decoded) {
            return $raw_value;
        }

        $iv_length = openssl_cipher_iv_length(cipher_algo: Encryption::METHOD);

        if (false === $iv_length || strlen(string: $decoded) <= $iv_length + Encryption::TAG_LENGTH) {
            return $raw_value;
        }

        $iv = substr(string: $decoded, offset: 0, length: $iv_length);
        $tag = substr(string: $decoded, offset: $iv_length, length: Encryption::TAG_LENGTH);
        $encrypted = substr(string: $decoded, offset: $iv_length + Encryption::TAG_LENGTH);

        $decrypted = openssl_decrypt(
            data: $encrypted,
            cipher_algo: Encryption::METHOD,
            passphrase: Encryption::get_key(),
            options: 0,
            iv: $iv,
            tag: $tag
        );

        return false !== $decrypted ? $decrypted : $raw_value;
    }

    private static function get_key(): string
    {
        return wp_hash(Encryption::KEY_CONTEXT);
    }
}