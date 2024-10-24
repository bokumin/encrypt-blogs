<?php
class Encrypt_Blogs_Encryptor {
    private $settings;
    private $wp_filesystem;
    
    public function init() {
        $this->settings = get_option('encrypt_blogs_settings');
        
        // Initialize WP_Filesystem
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }
        $this->wp_filesystem = $wp_filesystem;
    }

    public function should_encrypt($attributes) {
        if (empty($attributes['startDate']) && empty($attributes['endDate'])) {
            return true;
        }

        $current_time = current_time('timestamp');
        
        // Parse start date with default values
        $start_parts = $this->parse_date_with_defaults($attributes['startDate'] ?? '');
        $start_time = $this->create_timestamp($start_parts);

        // Parse end date with default values
        $end_parts = $this->parse_date_with_defaults($attributes['endDate'] ?? '');
        $end_time = $this->create_timestamp($end_parts);

        return $current_time >= $start_time && $current_time <= $end_time;
    }

    /**
     * Parses date string and fills in missing parts with defaults
     * Supports formats like:
     * - Full: "2024-03-25 14:30"
     * - Date only: "2024-03-25"
     * - Month and day: "03-25"
     * - Time only: "14:30"
     */
    private function parse_date_with_defaults($date_string) {
        $current_year = gmdate('Y');
        $current_month = gmdate('m');
        $current_day = gmdate('d');
        $current_hour = gmdate('H');
        $current_minute = gmdate('i');

        $parts = [
            'year' => $current_year,
            'month' => $current_month,
            'day' => $current_day,
            'hour' => $current_hour,
            'minute' => $current_minute
        ];

        if (empty($date_string)) {
            return $parts;
        }

        // Check if time is included
        if (strpos($date_string, ' ') !== false) {
            list($date_part, $time_part) = explode(' ', $date_string);
        } else {
            $date_part = $date_string;
            $time_part = '';
            
            // Check if it's just a time
            if (preg_match('/^\d{1,2}:\d{1,2}$/', $date_string)) {
                $date_part = '';
                $time_part = $date_string;
            }
        }

        // Parse date part if exists
        if (!empty($date_part)) {
            $date_segments = explode('-', $date_part);
            $segment_count = count($date_segments);

            if ($segment_count >= 3) {
                $parts['year'] = $date_segments[0];
                $parts['month'] = $date_segments[1];
                $parts['day'] = $date_segments[2];
            } elseif ($segment_count == 2) {
                $parts['month'] = $date_segments[0];
                $parts['day'] = $date_segments[1];
            }
        }

        // Parse time part if exists
        if (!empty($time_part)) {
            $time_segments = explode(':', $time_part);
            if (count($time_segments) >= 2) {
                $parts['hour'] = $time_segments[0];
                $parts['minute'] = $time_segments[1];
            }
        }

        // Validate and pad values
        $parts['month'] = str_pad($parts['month'], 2, '0', STR_PAD_LEFT);
        $parts['day'] = str_pad($parts['day'], 2, '0', STR_PAD_LEFT);
        $parts['hour'] = str_pad($parts['hour'], 2, '0', STR_PAD_LEFT);
        $parts['minute'] = str_pad($parts['minute'], 2, '0', STR_PAD_LEFT);

        return $parts;
    }

    /**
     * Creates a timestamp from parsed date parts
     */
    private function create_timestamp($parts) {
        return strtotime(sprintf(
            '%s-%s-%s %s:%s:00',
            $parts['year'],
            $parts['month'],
            $parts['day'],
            $parts['hour'],
            $parts['minute']
        ));
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
        // Create temporary files using WP_Filesystem
        $temp_dir = get_temp_dir();
        $input_file = $temp_dir . 'gpg_input_' . wp_generate_password(12, false);
        $output_file = $temp_dir . 'gpg_output_' . wp_generate_password(12, false);
        $key_file = $temp_dir . 'gpg_key_' . wp_generate_password(12, false);

        // Write content to temporary files using WP_Filesystem
        $this->wp_filesystem->put_contents($input_file, $content);
        $this->wp_filesystem->put_contents($key_file, $this->settings['gpg_public_key']);

        // Import key and encrypt
        $command = sprintf(
            'gpg --import %s && gpg --encrypt --recipient-file %s --output %s %s',
            escapeshellarg($key_file),
            escapeshellarg($key_file),
            escapeshellarg($output_file),
            escapeshellarg($input_file)
        );
        exec($command, $output, $return_var);

        // Cleanup temporary files
        wp_delete_file($input_file);
        wp_delete_file($key_file);

        if ($return_var !== 0) {
            error_log('GPG encryption failed: ' . implode("\n", $output));
            return false;
        }

        // Get encrypted content using WP_Filesystem
        $encrypted = $this->wp_filesystem->get_contents($output_file);
        wp_delete_file($output_file);

        return base64_encode($encrypted);
    }
}
