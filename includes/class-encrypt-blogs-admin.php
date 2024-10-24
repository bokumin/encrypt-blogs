<?php
class Encrypt_Blogs_Admin {
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
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
        register_setting('encrypt_blogs_options', 'encrypt_blogs_settings');
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
                    <tr class="php-settings" style="display: <?php echo $settings['encryption_method'] === 'php' ? 'table-row' : 'none'; ?>">
                        <th scope="row">PHP Passphrase</th>
                        <td>
                            <input type="password" name="encrypt_blogs_settings[php_passphrase]" 
                                   value="<?php echo esc_attr($settings['php_passphrase']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr class="gpg-settings" style="display: <?php echo $settings['encryption_method'] === 'gpg' ? 'table-row' : 'none'; ?>">
                        <th scope="row">GPG Public Key</th>
                        <td>
                            <textarea name="encrypt_blogs_settings[gpg_public_key]" rows="5" class="large-text"><?php 
                                echo esc_textarea($settings['gpg_public_key']); 
                            ?></textarea>
                        </td>
                    </tr>
                    <tr class="gpg-settings" style="display: <?php echo $settings['encryption_method'] === 'gpg' ? 'table-row' : 'none'; ?>">
                        <th scope="row">GPG Private Key</th>
                        <td>
                            <textarea name="encrypt_blogs_settings[gpg_private_key]" rows="5" class="large-text"><?php 
                                echo esc_textarea($settings['gpg_private_key']); 
                            ?></textarea>
                        </td>
                    </tr>
                    <tr class="gpg-settings" style="display: <?php echo $settings['encryption_method'] === 'gpg' ? 'table-row' : 'none'; ?>">
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

