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
        $current_year = date('Y');
        $current_month = date('m');
        $current_day = date('d');
        $current_hour = date('H');
        $current_minute = date('i');

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
