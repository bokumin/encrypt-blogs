<?php
class Encrypt_Blogs_Admin {
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_admin_menu() {
        add_options_page(
            'Encrypt Blogs Settings',
            'Encrypt Blogs',
            'manage_options',
            'encrypt-blogs',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting(
            'encrypt_blogs_options', 
            'encrypt_blogs_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array(
                    'encryption_method' => 'php',
                    'php_passphrase' => '',
                    'gpg_public_key' => '',
                    'gpg_private_key' => '',
                    'gpg_passphrase' => ''
                )
            )
        );
    }

    public function sanitize_settings($input) {
        $sanitized_input = array();
        
        // Sanitize encryption method
        $sanitized_input['encryption_method'] = sanitize_text_field($input['encryption_method']);
        
        // Sanitize PHP passphrase
        $sanitized_input['php_passphrase'] = sanitize_text_field($input['php_passphrase']);
        
        // Sanitize GPG keys and passphrase
        $sanitized_input['gpg_public_key'] = sanitize_textarea_field($input['gpg_public_key']);
        $sanitized_input['gpg_private_key'] = sanitize_textarea_field($input['gpg_private_key']);
        $sanitized_input['gpg_passphrase'] = sanitize_text_field($input['gpg_passphrase']);
        
        return $sanitized_input;
    }

    public function enqueue_admin_scripts($hook) {
        // Only load on our settings page
        if ('settings_page_encrypt-blogs' !== $hook) {
            return;
        }

        wp_enqueue_script(
            'encrypt-blogs-admin',
            ENCRYPT_BLOGS_PLUGIN_URL . 'js/admin.js',
            array('jquery'),
            ENCRYPT_BLOGS_VERSION,
            true
        );

        wp_add_inline_script('encrypt-blogs-admin', '
            jQuery(document).ready(function($) {
                $("input[name=\'encrypt_blogs_settings[encryption_method]\']").change(function() {
                    var selected = $(this).val();
                    $(".php-settings, .gpg-settings").hide();
                    if (selected === "php") {
                        $(".php-settings").show();
                    } else if (selected === "gpg") {
                        $(".gpg-settings").show();
                    }
                });
            });
        ');
    }

    public function render_settings_page() {
        $settings = get_option('encrypt_blogs_settings');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('encrypt_blogs_options');
                do_settings_sections('encrypt_blogs_options');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Encryption Method</th>
                        <td>
                            <label>
                                <input type="radio" name="encrypt_blogs_settings[encryption_method]" 
                                       value="php" <?php checked($settings['encryption_method'], 'php'); ?>>
                                PHP Encryption
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="encrypt_blogs_settings[encryption_method]" 
                                       value="gpg" <?php checked($settings['encryption_method'], 'gpg'); ?>>
                                GPG Encryption
                            </label>
                        </td>
                    </tr>
                    <tr class="php-settings">
                        <th scope="row">PHP Passphrase</th>
                        <td>
                            <input type="password" name="encrypt_blogs_settings[php_passphrase]" 
                                   value="<?php echo esc_attr($settings['php_passphrase']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr class="gpg-settings">
                        <th scope="row">GPG Public Key</th>
                        <td>
                            <textarea name="encrypt_blogs_settings[gpg_public_key]" rows="5" class="large-text"><?php 
                                echo esc_textarea($settings['gpg_public_key']); 
                            ?></textarea>
                        </td>
                    </tr>
                    <tr class="gpg-settings">
                        <th scope="row">GPG Private Key</th>
                        <td>
                            <textarea name="encrypt_blogs_settings[gpg_private_key]" rows="5" class="large-text"><?php 
                                echo esc_textarea($settings['gpg_private_key']); 
                            ?></textarea>
                        </td>
                    </tr>
                    <tr class="gpg-settings">
                        <th scope="row">GPG Passphrase</th>
                        <td>
                            <input type="password" name="encrypt_blogs_settings[gpg_passphrase]" 
                                   value="<?php echo esc_attr($settings['gpg_passphrase']); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

