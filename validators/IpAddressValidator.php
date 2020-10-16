<?php
    namespace App\Validators;

    use App\Core\Validator;

    class IpAddressValidator implements Validator {
        public function isValid(string $value) {
            return \preg_match('|^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$|', $value);
        }
    }
