<?php
    namespace App\Core\Fingerprint;

    class BasicFingerprintProvider implements FingerprintProvider {
        public function provideFingerprint(): string {
            $ua = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING);
            $ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING);
            $string = $ua . '|' . $ip;
            $hash = hash('sha512', $string);
            return hash('sha256', $hash);
        }
    }
