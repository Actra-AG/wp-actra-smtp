# Actra SMTP

[![License: GPLv2 or later](https://img.shields.io/badge/License-GPLv2%20or%20later-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

A minimal, object-oriented SMTP plugin for WordPress with zero external dependencies.

![Settings Page](assets/screenshot-1.png)

## Overview

Actra SMTP is built for developers who prioritize clean code and performance. It bridges the gap between WordPress's
core mailing functionality and your SMTP provider without the bloat of traditional SMTP plugins.

### Key Features

#### Smart Defaults

The plugin defaults to port 587, the industry standard for secure SMTP, making it "plug-and-play" for most modern
hosting environments.

- **Zero External Dependencies**: No Composer, no vendor folders, no external libraries.
- **Modern PHP**: Written for PHP 8.0+ using named arguments and strict typing.
- **Minimal OOP Footprint**: Lightweight PSR-4 autoloader and a singleton-based core.
- **Developer Friendly**: Cleanly namespaced and easy to extend.

## Installation

1. Download or clone the repository into your `wp-content/plugins/` directory.
2. Ensure the folder name is `actra-smtp`.
3. Activate the plugin in the WordPress Admin.
4. Go to **Settings > Actra SMTP** to enter your credentials.

## Configuration

The plugin provides fields for:

- **From-Email**: The email address used as the sender.
- **SMTP Hostname**: Your SMTP provider's host (e.g., `smtp.example.com`).
- **SMTP Username/Password**: Your authentication credentials.
- **SMTP Port**: Usually `587` (TLS) or `465` (SSL).
- **SMTP-TLS**: Toggle between Yes (TLS) or No.

### Database configuration

By default, Actra SMTP stores its settings in the WordPress database.

The saved SMTP password is never displayed in the WordPress admin area. When a password is saved through **Settings >
Actra SMTP**, it is stored encrypted in the database. Existing plaintext passwords from older versions remain supported
and are used as a backwards-compatible fallback.

### wp-config.php configuration

SMTP settings can also be defined in `wp-config.php` using constants. Constants take priority over values stored in the
database. This is useful for local development, deployment automation, staging/production environments, and keeping
secrets out of the database.

You may define individual constants, but using the full set is recommended to avoid mixed configuration sources.

```php
define('ACTRA_SMTP_FROM_EMAIL', 'noreply@example.com');
define('ACTRA_SMTP_HOST', 'smtp.example.com');
define('ACTRA_SMTP_USERNAME', 'user@example.com');
define('ACTRA_SMTP_PASSWORD', 'your-smtp-password');
define('ACTRA_SMTP_PORT', 587);
define('ACTRA_SMTP_TLS', true);
```

Place these constants above this line in `wp-config.php`:

```php
/* That's all, stop editing! Happy publishing. */
```

When a constant is defined, the matching field in the plugin settings screen is disabled and cannot be changed there.

For local environments that do not require authentication, such as some DDEV or Mailpit setups, you may intentionally
define empty credentials:

```php
define('ACTRA_SMTP_USERNAME', '');
define('ACTRA_SMTP_PASSWORD', '');
```

### Multisite behavior

In WordPress multisite installations, all sites share the same `wp-config.php` file. Therefore, any `ACTRA_SMTP_*`
constant defined in `wp-config.php` applies network-wide to all sites in the multisite network.

Database settings remain site-specific because they are stored using normal WordPress options. This means each site can
have its own SMTP settings in the database unless a matching `ACTRA_SMTP_*` constant is defined.

If you need different SMTP settings for each site in a multisite network, configure them through each site's settings
screen and avoid defining global `ACTRA_SMTP_*` constants.

### Configuration priority

For each SMTP setting, the final value is resolved in this order:

1. The matching constant from `wp-config.php`, if defined.
2. The value stored in the WordPress database.

For the SMTP password, database values are decrypted automatically. Existing plaintext database passwords are still
supported for backwards compatibility.

## Developer Notes

This plugin is built with a custom autoloader found in `includes/Autoloader.php`. To add new functionality, simply add
classes to the `includes/` directory using the `Actra\Smtp` namespace.

---

*Created by [Actra AG](https://www.actra.ch)*
