<?php
/**
 * Admin settings class file.
 *
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Admin Settings page.
 */
class Settings
{

    private const GROUP = 'actra-smtp_group';
    private const PAGE = 'actra-smtp';

    public function __construct()
    {
        add_action(hook_name: 'admin_menu', callback: [$this, 'add_menu_page']);
        add_action(hook_name: 'admin_init', callback: [$this, 'register_settings']);
    }

    public function add_menu_page(): void
    {
        add_options_page(
            page_title: 'Actra SMTP',
            menu_title: 'Actra SMTP',
            capability: 'manage_options',
            menu_slug: Settings::PAGE,
            callback: [$this, 'render_settings_page']
        );
    }

    public function register_settings(): void
    {
        $fields = [
            'actra-smtp_sender_email' => ['label' => 'From-Email', 'type' => 'email'],
            'actra-smtp_hostname' => ['label' => 'SMTP-Hostname', 'type' => 'text'],
            'actra-smtp_username' => ['label' => 'SMTP-Username', 'type' => 'text'],
            'actra-smtp_password' => ['label' => 'SMTP-Password', 'type' => 'password'],
            'actra-smtp_port' => ['label' => 'SMTP-Port', 'type' => 'number'],
            'actra-smtp_tls' => [
                'label' => 'SMTP-TLS',
                'type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No']
            ],
        ];

        add_settings_section(
            id: 'actra-smtp_main',
            title: 'Connection Settings',
            callback: '__return_null',
            page: Settings::PAGE
        );

        foreach ($fields as $id => $args) {
            $sanitize_callback = 'sanitize_text_field';
            if ('email' === $args['type']) {
                $sanitize_callback = 'sanitize_email';
            } elseif ('number' === $args['type']) {
                $sanitize_callback = 'absint';
            }

            register_setting(
                option_group: Settings::GROUP,
                option_name: $id,
                args: ['sanitize_callback' => $sanitize_callback]
            );
            add_settings_field(
                id: $id,
                title: $args['label'],
                callback: [$this, 'render_field'],
                page: Settings::PAGE,
                section: 'actra-smtp_main',
                args: array_merge(['id' => $id], $args)
            );
        }
    }

    public function render_field(array $args): void
    {
        $value = get_option(option: $args['id'], default_value: '');

        if ('select' === $args['type']) {
            echo '<select name="' . esc_attr(text: $args['id']) . '" class="postform">';
            foreach ($args['options'] as $val => $label) {
                echo '<option value="' . esc_attr(text: $val) . '" ' . selected(
                        selected: $value,
                        current: $val,
                        display: false
                    ) . '>' . esc_html(text: $label) . '</option>';
            }
            echo '</select>';
            return;
        }

        printf(
            '<input type="%1$s" name="%2$s" value="%3$s" class="regular-text">',
            esc_attr(text: $args['type']),
            esc_attr(text: $args['id']),
            esc_attr(text: $value)
        );
    }

    public function render_settings_page(): void
    {
        ?>
      <div class="wrap">
        <h1>SMTP Settings</h1>
        <hr class="wp-header-end">
        <form method="post" action="options.php">
            <?php
            settings_fields(option_group: Settings::GROUP);
            do_settings_sections(page: Settings::PAGE);
            submit_button();
            ?>
        </form>
      </div>
        <?php
    }
}
