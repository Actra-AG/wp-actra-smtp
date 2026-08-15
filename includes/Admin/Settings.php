<?php
/**
 * Admin settings class file.
 *
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp\Admin;

use Actra\Smtp\Core\Encryption;
use Actra\Smtp\Core\SmtpPasswordProvider;

if (!defined(constant_name: 'ABSPATH')) {
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
        'actra-smtp_sender_email' => [
            'label' => 'From-Email',
            'type' => 'email',
            'required' => true,
            'placeholder' => 'you@example.com'
        ],
        'actra-smtp_hostname' => [
            'label' => 'SMTP-Hostname',
            'type' => 'text',
            'required' => true,
            'placeholder' => 'smtp.example.com or localhost'
        ],
        'actra-smtp_username' => [
            'label' => 'SMTP-Username',
            'type' => 'text',
            'description' => 'Leave empty if your SMTP server does not require authentication.'
        ],
        'actra-smtp_password' => [
            'label' => 'SMTP-Password',
            'type' => 'password',
            'description' => 'Leave empty if your SMTP server does not require authentication.'
        ],
        'actra-smtp_port' => [
            'label' => 'SMTP-Port',
            'type' => 'number',
            'required' => true,
            'default' => 587
        ],
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
      $register_args = [
          'type' => 'string',
          'default' => $args['default'] ?? '',
      ];
      switch ($args['type']) {
        case 'email':
          $register_args['sanitize_callback'] = 'sanitize_email';
          break;
        case 'number':
          $register_args['type'] = 'integer';
          $register_args['sanitize_callback'] = 'absint';
          break;
        case 'password':
          $register_args['sanitize_callback'] = [$this, 'sanitize_password'];
          break;
        default:
          $register_args['sanitize_callback'] = 'sanitize_text_field';
          break;
      }

      register_setting(
          option_group: Settings::GROUP,
          option_name: $id,
          args: $register_args
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
    $value = get_option(option: $args['id'], default_value: $args['default'] ?? '');

    if ('password' === $args['type']) {
      $this->render_password_field(args: $args);
      return;
    }

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
    } else {
      printf(
          '<input type="%1$s" name="%2$s" value="%3$s" class="regular-text"%4$s%5$s>',
          esc_attr(text: $args['type']),
          esc_attr(text: $args['id']),
          esc_attr(text: $value),
          !empty($args['required']) ? ' required' : '',
          !empty($args['placeholder']) ? ' placeholder="' . esc_attr(text: $args['placeholder']) . '"' : ''
      );
    }

    if (!empty($args['description'])) {
      printf('<p class="description">%s</p>', esc_html(text: $args['description']));
    }
  }

  public function render_password_field(array $args): void
  {
    $is_defined = SmtpPasswordProvider::has_configured_password_constant();
    $has_saved_password = '' !== (string)get_option(option: $args['id'], default_value: '');

    if ($is_defined) {
      printf(
          '<input type="password" name="%1$s" value="********" disabled="disabled" class="regular-text" autocomplete="new-password">',
          esc_attr(text: $args['id'])
      );

      echo '<p class="description" style="color: green;">Password is defined in <strong>wp-config.php</strong> and cannot be changed here.</p>';
      return;
    }

    printf(
        '<input type="password" name="%1$s" value="" placeholder="%2$s" autocomplete="new-password" class="regular-text">',
        esc_attr(text: $args['id']),
        esc_attr(text: $has_saved_password ? '••••••••' : '')
    );

    if ($has_saved_password) {
      echo '<p class="description">A password is saved. Leave this field empty to keep the current password, or enter a new one to overwrite it.</p>';
      return;
    }

    if (!empty($args['description'])) {
      printf('<p class="description">%s</p>', esc_html(text: $args['description']));
    }
  }

  public function sanitize_password($value): string
  {
    $value = (string)$value;

    if (SmtpPasswordProvider::has_configured_password_constant()) {
      return (string)get_option(option: 'actra-smtp_password', default_value: '');
    }

    if ('' === $value) {
      return (string)get_option(option: 'actra-smtp_password', default_value: '');
    }

    return Encryption::encrypt(value: $value);
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
