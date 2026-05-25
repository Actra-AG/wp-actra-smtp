=== Actra SMTP ===
Contributors: jayq1982
Tags: smtp, mail, email, phpmailer, delivery
Requires at least: 6.3
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.0.5
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

= Is it compatible with other mail plugins? =
You should only have one SMTP plugin active at a time to avoid conflicts.

== Screenshots ==

1. The Actra SMTP settings page with smart defaults and clear authentication hints.

== Changelog ==

= 1.0.5 =
* Revert custom password sanitization to ensure valid passwords remain unchanged.

= 1.0.4 =
* Refactor sanitization and general code structure.
* Implement custom sanitization for password fields.

= 1.0.2 =
* Initial release.
