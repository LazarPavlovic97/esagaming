<?php
    namespace App\Validators;

    use App\Core\Validator;

    class BitValidator implements Validator {
        public function isValid(string $value) {
            return in_array($value, [0, 1]);
        }
    }
