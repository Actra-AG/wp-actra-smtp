<?php
/**
 * Admin settings class file.
 *
 * @package ActraSmtp
 */

declare(strict_types=1);

namespace Actra\Smtp\Admin;

use Actra\Smtp\Core\Encryption;
use Actra\Smtp\Core\SmtpConfigProvider;

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
  private const SENDER_EMAIL_OPTION = 'actra-smtp_sender_email';
  private const HOSTNAME_OPTION = 'actra-smtp_hostname';
  private const USERNAME_OPTION = 'actra-smtp_username';
  private const PASSWORD_OPTION = 'actra-smtp_password';
  private const PORT_OPTION = 'actra-smtp_port';
  private const TLS_OPTION = 'actra-smtp_tls';

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
        Settings::SENDER_EMAIL_OPTION => [
            'label' => 'From-Email',
            'type' => 'email',
            'required' => true,
            'placeholder' => 'you@example.com',
        ],
        Settings::HOSTNAME_OPTION => [
            'label' => 'SMTP-Hostname',
            'type' => 'text',
            'required' => true,
            'placeholder' => 'smtp.example.com or localhost',
        ],
        Settings::USERNAME_OPTION => [
            'label' => 'SMTP-Username',
            'type' => 'text',
            'description' => 'Leave empty if your SMTP server does not require authentication.',
        ],
        Settings::PASSWORD_OPTION => [
            'label' => 'SMTP-Password',
            'type' => 'password',
            'description' => 'Leave empty if your SMTP server does not require authentication.',
        ],
        Settings::PORT_OPTION => [
            'label' => 'SMTP-Port',
            'type' => 'number',
            'required' => true,
            'default' => 587,
        ],
        Settings::TLS_OPTION => [
            'label' => 'SMTP-TLS',
            'type' => 'select',
            'options' => ['yes' => 'Yes', 'no' => 'No'],
        ],
    ];

    add_settings_section(
        id: 'actra-smtp_main',
        title: 'Connection Settings',
        callback: [$this, 'render_settings_section'],
        page: Settings::PAGE
    );

    foreach ($fields as $id => $args) {
      $register_args = [
          'type' => 'string',
          'default' => $args['default'] ?? '',
      ];

      switch ($args['type']) {
        case 'email':
          $register_args['sanitize_callback'] = [$this, 'sanitize_sender_email'];
          break;
        case 'number':
          $register_args['type'] = 'integer';
          $register_args['sanitize_callback'] = [$this, 'sanitize_port'];
          break;
        case 'password':
          $register_args['sanitize_callback'] = [$this, 'sanitize_password'];
          break;
        case 'select':
          $register_args['sanitize_callback'] = [$this, 'sanitize_tls'];
          break;
        default:
          $register_args['sanitize_callback'] = [$this, 'sanitize_text'];
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

  public function render_settings_section(): void
  {
    if (!SmtpConfigProvider::has_any_config_constant()) {
      return;
    }

    echo '<p class="description">One or more SMTP settings are defined in <strong>wp-config.php</strong>. Fields controlled by constants are disabled here, and the constant values take priority over database settings.</p>';
  }

  public function render_field(array $args): void
  {
    if ('password' === $args['type']) {
      $this->render_password_field(args: $args);
      return;
    }

    $value = get_option(option: $args['id'], default_value: $args['default'] ?? '');
    $has_constant = SmtpConfigProvider::has_constant_for_option(option_name: $args['id']);

    if ($has_constant) {
      $value = $this->get_display_value_for_constant_field(args: $args);
    }

    if ('select' === $args['type']) {
      $this->render_select_field(args: $args, value: (string)$value, disabled: $has_constant);
    } else {
      $this->render_input_field(args: $args, value: (string)$value, disabled: $has_constant);
    }

    if ($has_constant) {
      echo '<p class="description" style="color: green;">This setting is defined in <strong>wp-config.php</strong> and cannot be changed here.</p>';
      return;
    }

    if (!empty($args['description'])) {
      printf('<p class="description">%s</p>', esc_html(text: $args['description']));
    }
  }

  public function render_password_field(array $args): void
  {
    $has_constant = SmtpConfigProvider::has_constant_for_option(option_name: Settings::PASSWORD_OPTION);
    $has_saved_password = '' !== (string)get_option(option: $args['id'], default_value: '');

    if ($has_constant) {
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

  public function sanitize_sender_email($value): string
  {
    if (SmtpConfigProvider::has_constant_for_option(option_name: Settings::SENDER_EMAIL_OPTION)) {
      return (string)get_option(option: Settings::SENDER_EMAIL_OPTION, default_value: '');
    }

    return sanitize_email(email: (string)$value);
  }

  public function sanitize_text($value): string
  {
    $option_name = $this->get_current_option_name();

    if (null !== $option_name && SmtpConfigProvider::has_constant_for_option(option_name: $option_name)) {
      return (string)get_option(option: $option_name, default_value: '');
    }

    return sanitize_text_field(str: (string)$value);
  }

  public function sanitize_password($value): string
  {
    $value = (string)$value;

    if (SmtpConfigProvider::has_constant_for_option(option_name: Settings::PASSWORD_OPTION)) {
      return (string)get_option(option: Settings::PASSWORD_OPTION, default_value: '');
    }

    if ('' === $value) {
      return (string)get_option(option: Settings::PASSWORD_OPTION, default_value: '');
    }

    return Encryption::encrypt(value: $value);
  }

  public function sanitize_port($value): int
  {
    if (SmtpConfigProvider::has_constant_for_option(option_name: Settings::PORT_OPTION)) {
      return (int)get_option(option: Settings::PORT_OPTION, default_value: 587);
    }

    return absint(maybe: $value);
  }

  public function sanitize_tls($value): string
  {
    if (SmtpConfigProvider::has_constant_for_option(option_name: Settings::TLS_OPTION)) {
      return (string)get_option(option: Settings::TLS_OPTION, default_value: 'yes');
    }

    return 'yes' === $value ? 'yes' : 'no';
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

  private function render_select_field(array $args, string $value, bool $disabled): void
  {
    echo '<select name="' . esc_attr(text: $args['id']) . '" class="postform"' . disabled(
            disabled: $disabled,
            current: true,
            display: false
        ) . '>';

    foreach ($args['options'] as $val => $label) {
      echo '<option value="' . esc_attr(text: $val) . '" ' . selected(
              selected: $value,
              current: $val,
              display: false
          ) . '>' . esc_html(text: $label) . '</option>';
    }

    echo '</select>';
  }

  private function render_input_field(array $args, string $value, bool $disabled): void
  {
    printf(
        '<input type="%1$s" name="%2$s" value="%3$s" class="regular-text"%4$s%5$s%6$s>',
        esc_attr(text: $args['type']),
        esc_attr(text: $args['id']),
        esc_attr(text: $value),
        !empty($args['required']) && !$disabled ? ' required' : '',
        !empty($args['placeholder']) ? ' placeholder="' . esc_attr(text: $args['placeholder']) . '"' : '',
        disabled(disabled: $disabled, current: true, display: false)
    );
  }

  private function get_display_value_for_constant_field(array $args): string
  {
    $config_provider = new SmtpConfigProvider();

    return match ($args['id']) {
      Settings::SENDER_EMAIL_OPTION => $config_provider->get_sender_email(),
      Settings::HOSTNAME_OPTION => $config_provider->get_host(),
      Settings::USERNAME_OPTION => $config_provider->get_username(),
      Settings::PORT_OPTION => (string)$config_provider->get_port(),
      Settings::TLS_OPTION => $config_provider->is_tls_enabled() ? 'yes' : 'no',
      default => '',
    };
  }

  private function get_current_option_name(): ?string
  {
    global $wp_current_filter;

    if (!is_array(value: $wp_current_filter)) {
      return null;
    }

    foreach (array_reverse(array: $wp_current_filter) as $filter_name) {
      if (str_starts_with(haystack: $filter_name, needle: 'sanitize_option_')) {
        return substr(string: $filter_name, offset: strlen(string: 'sanitize_option_'));
      }
    }

    return null;
  }
}