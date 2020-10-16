<?php
    namespace App\Validators;

    use App\Core\Validator;

    class DateTimeValidator implements Validator {
        private $allowedDate;
        private $allowedTime;

        public function __construct() {
            $this->allowedDate = true;
            $this->allowedTime = true;
        }

        public function &allowDate(): DateTimeValidator {
            $this->allowedDate = true;
            return $this;
        }

        public function &allowTime(): DateTimeValidator {
            $this->allowedTime = true;
            return $this;
        }

        public function &disallowDate(): DateTimeValidator {
            $this->allowedDate = false;

            if ($this->allowedTime == false) {
                $this->allowedTime = true;
            }

            return $this;
        }

        public function &disallowTime(): DateTimeValidator {
            $this->allowedTime = false;

            if ($this->allowedDate == false) {
                $this->allowedDate = true;
            }

            return $this;
        }

        public function isValid(string $value) {
            $components = [];

            if ($this->allowedDate) {
                $components[] = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
            }

            if ($this->allowedTime) {
                $components[] = '[0-9]{2}:[0-9]{2}:[0-9]{2}';
            }

            $pattern = \implode(' ', $components);

            return \preg_match('/^' . $pattern . '$/', $value);
        }
    }
