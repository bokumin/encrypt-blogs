<?php
class Encrypt_Blogs_Block {
    public function init() {
        add_action('init', array($this, 'register_block'));
    }

    public function register_block() {
        register_block_type('encrypt-blogs/encrypted-content', array(
            'editor_script' => 'encrypt-blogs-editor',
            'render_callback' => array($this, 'render_block')
        ));

        wp_register_script(
            'encrypt-blogs-editor',
            ENCRYPT_BLOGS_PLUGIN_URL . 'js/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'),
            ENCRYPT_BLOGS_VERSION
        );
    }

    public function render_block($attributes, $content) {
        $encryptor = new Encrypt_Blogs_Encryptor();
        
        // Check if encryption is needed based on time
        if (!$encryptor->should_encrypt($attributes)) {
            return $content;
        }

        // Encrypt content
        $encrypted = $encryptor->encrypt($content);

        // Return based on display mode
        switch ($attributes['displayMode']) {
            case 'hidden':
                return '';
            case 'encrypted':
                return '<div class="encrypted-content">' . esc_html($encrypted) . '</div>';
            case 'message':
                return '<div class="encrypted-message">This content is encrypted</div>';
            case 'redacted':
                return '<div class="redacted-content">█████████████████████</div>';
            default:
                return '';
        }
    }
}
