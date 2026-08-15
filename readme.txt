=== Actra SMTP ===
Contributors: jayq1982
Tags: smtp, mail, email, phpmailer, delivery
Requires at least: 6.3
Tested up to: 7.0.4
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/GPL-2.0.html

A minimal, object-oriented SMTP plugin for WordPress with zero external dependencies.

== Description ==

Actra SMTP is designed for simplicity and performance. It uses the native PHPMailer library included in WordPress core to route all emails through your preferred SMTP server.

== Installation ==

1. Upload the actra-smtp folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your SMTP settings under 'Settings > Actra SMTP'.

== Frequently Asked Questions ==

= Does this plugin support Gmail/Outlook? =
Yes, as long as you provide the correct SMTP host and credentials.

= Can I store SMTP settings in wp-config.php instead of the database? =
Yes. SMTP settings can be defined in wp-config.php using constants. Constants take priority over values stored in the database.

Available constants:

define('ACTRA_SMTP_FROM_EMAIL', 'noreply@example.com');
define('ACTRA_SMTP_HOST', 'smtp.example.com');
define('ACTRA_SMTP_USERNAME', 'user@example.com');
define('ACTRA_SMTP_PASSWORD', 'your-smtp-password');
define('ACTRA_SMTP_PORT', 587);
define('ACTRA_SMTP_TLS', true);

You may define individual constants, but using the full set is recommended to avoid mixed configuration sources. When a constant is defined, the matching field in the plugin settings screen is disabled and cannot be changed there.

= Can I define an empty SMTP password in wp-config.php? =
Yes. For local environments that do not require SMTP authentication, such as some DDEV or Mailpit setups, you can intentionally define empty credentials:

define('ACTRA_SMTP_USERNAME', '');
define('ACTRA_SMTP_PASSWORD', '');

= How do wp-config.php constants work in WordPress multisite? =
In WordPress multisite installations, all sites share the same wp-config.php file. Any ACTRA_SMTP_* constant defined there applies network-wide to all sites in the multisite network.

Database settings remain site-specific. This means each site can have its own SMTP settings in the database unless a matching ACTRA_SMTP_* constant is defined.

If you need different SMTP settings for each site, configure them through each site's settings screen and avoid defining global ACTRA_SMTP_* constants.

= Is the SMTP password displayed in the WordPress admin area? =
No. The saved SMTP password is never displayed in the settings screen. If a password exists in the database, the field remains empty and can be left unchanged.

= What happens to passwords saved before this update? =
Existing plaintext passwords remain supported for backwards compatibility. When a new password is saved through the settings screen, it is stored encrypted in the WordPress database.

= Is it compatible with other mail plugins? =
You should only have one SMTP plugin active at a time to avoid conflicts.

== Screenshots ==

1. The Actra SMTP settings page with smart defaults and clear authentication hints.

== Changelog ==

= 1.1.0 =
* Security: Encrypt SMTP passwords stored in the WordPress database.
* Security: Do not display the saved SMTP password in the settings screen.
* New: Allow defining SMTP settings via wp-config.php constants.
* New: Allow individual wp-config.php constants while recommending the full set.
* New: Support intentionally empty wp-config.php credentials for local environments.
* Compatibility: Keep support for existing plaintext database passwords.

= 1.0.6 =
* Update: Plugin assets (banner and icons) added
* Tested up to WordPress 7.0.4

= 1.0.5 =
* Revert custom password sanitization to ensure valid passwords remain unchanged.

= 1.0.4 =
* Refactor sanitization and general code structure.
* Implement custom sanitization for password fields.

= 1.0.2 =
* Initial release.
