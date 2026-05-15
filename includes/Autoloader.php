<?php
/**
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp;

/**
 * Minimal PSR-4 Autoloader.
 */
class Autoloader
{
    private array $prefixes = [];

    public function register(): void
    {
        spl_autoload_register(callback: [$this, 'load']);
    }

    public function add_namespace(string $prefix, string $dir): void
    {
        $key = trim(string: $prefix, characters: '\\') . '\\';
        $this->prefixes[$key] = rtrim(
                string: $dir,
                characters: DIRECTORY_SEPARATOR
            ) . '/';
    }

    public function load(string $class): void
    {
        foreach ($this->prefixes as $prefix => $dir) {
            if (str_starts_with(haystack: $class, needle: $prefix)) {
                $file = $dir . str_replace(
                        search: '\\',
                        replace: '/',
                        subject: substr(
                            string: $class,
                            offset: strlen(
                                string: $prefix
                            )
                        )
                    ) . '.php';
                if (file_exists(filename: $file)) {
                    require $file;
                    return;
                }
            }
        }
    }
}
