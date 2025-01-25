<?php
/**
 * Plugin Name: Sendfy Form Elementor
 * Plugin URI: https://sendfy.app/
 * Description: This plugin allows you to send Form Elementor notifications to Sendfy.
 * Version: 1.0.0
 * Author: Luis Pimenta
 * Author URI: https://luispimenta.me
 * Text Domain: sendfy-form-elementor
 * License: GPL v2 or later
 * Requires at least: 6.0
 * Tested up to: 6.0
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package Sendfy_Form_Elementor
 */

add_action( 'elementor_pro/forms/new_record', function($record, $handler) {
	$raw_fields = $record->get('fields');
	$fields = [];
	foreach ($raw_fields as $id => $field) {
		$fields[$id] = $field['value'];
	}
  $options = get_option('sendfy_fe_plugin');
  $sendfy_fe_x_api_key = ! empty($options['sendfy_fe_x_api_key']) ? $options['sendfy_fe_x_api_key'] : '';
  $sendfy_fe_instance_id = ! empty($options['sendfy_fe_instance_id']) ? $options['sendfy_fe_instance_id'] : '';
  $webhook = "https://app.sendfy.app/api/v1/send_message.json";
	$body = wp_json_encode($fields);
	$response = wp_remote_post($webhook, [
		'body'   => $body,
		'method' => 'POST',
		'headers'     => [
			'Content-Type' => 'application/json',
      'x-api-key'  => $sendfy_fe_x_api_key,
      'instance-id'  => $sendfy_fe_instance_id
		],
	]);
	if (is_wp_error($response)) {
		$error_message = $response->get_error_message();
		echo "Something went wrong: $error_message";
		echo 'Response:<pre>';
		print_r( $response );
		echo '</pre>';
	} else {
		return $response;
	}
}, 10, 2);

add_action('admin_menu', 'add_page');

if ( !function_exists( 'add_page' ) ) {
  function add_page() {
    add_options_page('Sendfy Form Elementor', 'Sendfy Form Elementor', 'manage_options', 'plugin', 'plugin_options_frontpage');
  }
}

function plugin_options_frontpage() {
  ?>
    <div class="wrap">
      <?php screen_icon('users'); ?><h2>Sendfy Form Elementor</h2>
      <form action="options.php" method="post">
        <?php settings_fields('plugin_options'); ?>
        <?php do_settings_sections('plugin'); ?>
        <table class="form-table">
          <tr valign="top">
            <td colspan="2">
              <input name="Submit" type="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
            </td>
          </tr>
        </table>
      </form>
    </div>
  <?php
}

add_action('admin_menu', 'sendfy_create_plugin_menu');
function sendfy_create_plugin_menu() {
  add_menu_page(
    'Sendfy Form Elementor', //page title
    'Sendfy FE', //menu title
    'administrator', //capability
    'sendfy-fe-plugin',  //page url
    'sendfy_fe_plugin_settings_page' //callback
  );
}

add_action('admin_init', 'register_sendfy_fe_plugin_settings');
function register_sendfy_fe_plugin_settings()
{
  if (isset($_POST['must_submit']) == 'form_submitted') {
    // user access validation
    if (!current_user_can('manage_options')) {
      wp_die('are you cheating');
    }
    // check if submit form
    $options['sendfy_fe_x_api_key']  = isset($_POST['sendfy_fe_x_api_key']) ? sanitize_text_field($_POST['sendfy_fe_x_api_key']) : '';
    $options['sendfy_fe_instance_id']  = isset($_POST['sendfy_fe_instance_id']) ? sanitize_text_field($_POST['sendfy_fe_instance_id']) : '';
    update_option('sendfy_fe_plugin', $options);
  }
}

function sendfy_fe_plugin_settings_page() {
  $options = get_option('sendfy_fe_plugin');
  $sendfy_fe_x_api_key = ! empty($options['sendfy_fe_x_api_key']) ? $options['sendfy_fe_x_api_key'] : '';
  $sendfy_fe_instance_id = ! empty($options['sendfy_fe_instance_id']) ? $options['sendfy_fe_instance_id'] : '';
  ?>
  <div class="wrap">
    <h1>Sendfy Form Elementor</h1>
    <form method="post"action="">
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Sendfy x-api-key
            <a href="https://app.sendfy.app/whatsapp_instances" target="_blank">?</a>
          </th>
          <td>
            <input type="text" name="sendfy_fe_x_api_key" value="<?php echo esc_attr($sendfy_fe_x_api_key); ?>" style="width: 50%;" />
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">Sendfy instance-id
            <a href="https://app.sendfy.app/whatsapp_instances" target="_blank">?</a>
          </th>
          <td>
            <input type="text" name="sendfy_fe_instance_id" value="<?php echo esc_attr($sendfy_fe_instance_id); ?>" style="width: 50%;" />
          </td>
        </tr>
      </table>
      <input type="hidden" name="must_submit" value="form_submitted">
      <?php submit_button();?>
    </form>
  </div>
<?php }?>
