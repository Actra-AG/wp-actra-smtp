=== Actra SMTP ===
Contributors: actra-ag
Tags: smtp, mail, email, phpmailer, delivery
Requires at least: 6.3
Tested up to: 6.9.4
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A minimal, object-oriented SMTP plugin for WordPress with zero external dependencies.

== Description ==

Actra SMTP is built for developers who prioritize clean code and performance. It bridges the gap between WordPress's core mailing functionality and your SMTP provider without the bloat of traditional SMTP plugins.

=== Key Features ===

*   **Zero External Dependencies**: No Composer, no vendor folders, no external libraries.
*   **Modern PHP**: Written for PHP 8.0+ using named arguments and strict typing.
*   **Minimal OOP Footprint**: Lightweight PSR-4 autoloader and a singleton-based core.
*   **Developer Friendly**: Cleanly namespaced and easy to extend.

== Installation ==

1. Upload the wp-actra-smtp folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your SMTP settings under Settings > Actra SMTP.

== Frequently Asked Questions ==

= Does this plugin support Gmail/Outlook? =
Yes, as long as you provide the correct SMTP host and credentials.

= Is it compatible with other mail plugins? =
You should only have one SMTP plugin active at a time to avoid conflicts.

== Screenshots ==

1. The Actra SMTP settings page shows connection fields for SMTP hostname, port, and credentials.

== Changelog ==

= 1.0.0 =
* Initial release.
