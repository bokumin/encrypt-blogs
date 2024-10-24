<?php
class Encrypt_Blogs_Encryptor {
    private $settings;

    public function init() {
        $this->settings = get_option('encrypt_blogs_settings');
    }

    public function should_encrypt($attributes) {
        if (empty($attributes['startDate']) && empty($attributes['endDate'])) {
            return true;
        }

        $current_time = current_time('timestamp');
        $start_time = !empty($attributes['startDate']) ? 
            strtotime($attributes['startDate']) : $current_time;
        $end_time = !empty($attributes['endDate']) ? 
            strtotime($attributes['endDate']) : $current_time;

        return $current_time >= $start_time && $current_time <= $end_time;
    }

    public function encrypt($content) {
        if ($this->settings['encryption_method'] === 'gpg') {
            return $this->gpg_encrypt($content);
        }
        return $this->php_encrypt($content);
    }

    private function php_encrypt($content) {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt(
            $content,
            $cipher,
            $this->settings['php_passphrase'],
            0,
            $iv
        );
        return base64_encode($iv . $encrypted);
    }

    private function gpg_encrypt($content) {
        // Create temporary files
        $input_file = tempnam(sys_get_temp_dir(), 'gpg_input_');
        file_put_contents($input_file, $content);

        $output_file = tempnam(sys_get_temp_dir(), 'gpg_output_');
        $key_file = tempnam(sys_get_temp_dir(), 'gpg_key_');
        file_put_contents($key_file, $this->settings['gpg_public_key']);

        // Import key and encrypt
        $command = sprintf(
            'gpg --import %s && gpg --encrypt --recipient-file %s --output %s %s',
            escapeshellarg($key_file),
            escapeshellarg($key_file),
            escapeshellarg($output_file),
            escapeshellarg($input_file)
        );

        exec($command, $output, $return_var);

        // Cleanup
        unlink($input_file);
        unlink($key_file);

        if ($return_var !== 0) {
            error_log('GPG encryption failed: ' . implode("\n", $output));
            return false;
        }

        $encrypted = file_get_contents($output_file);
        unlink($output_file);

        return base64_encode($encrypted);
    }
}
