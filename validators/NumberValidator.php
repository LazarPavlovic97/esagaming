<?php
    namespace App\Validators;

    use App\Core\Validator;

    class NumberValidator implements Validator {
        private $isSigned;
        private $isReal;
        private $maxIntegerDigits;
        private $maxDecimalDigits;

        public function __construct() {
            $this->isSigned = true;
            $this->isReal   = true;
            $this->maxIntegerDigits = 10;
            $this->maxDecimalDigits = 2;
        }

        public function &setInteger(): NumberValidator {
            $this->isReal = false;
            $this->maxDecimalDigits = 0;
            return $this;
        }
        
        public function &setReal(): NumberValidator {
            $this->isReal = true;
            return $this;
        }

        public function &setUnsigned(): NumberValidator {
            $this->isSigned = false;
            return $this;
        }

        public function &setSigned(): NumberValidator {
            $this->isSigned = true;
            return $this;
        }

        public function &setMaxIntegerDigits(int $lenght): NumberValidator {
            $this->maxIntegerDigits = max(1, $lenght);
            return $this;
        }

        public function &setMaxDecimalDigits(int $lenght): NumberValidator {
            $this->maxDecimalDigits = max(0, $lenght);
            return $this;
        }

        public function isValid(string $value) {
            if ($this->isSigned == false) {
                if ($value < 0) {
                    return false;
                }
            }

            if ($this->isReal == false) {
                $value = floatval($value);

                $ostatak = $value % 1.;

                if ($ostatak != 0) {
                    return false;
                }
            }

            $ceoDeo = strval(intval($value));

            if (strlen($ceoDeo) > $this->maxIntegerDigits) {
                return false;
            }

            $brojStr = strval(floatval($value));

            $deloviBroja = explode('.', $brojStr);

            if (!isset($deloviBroja[1])) {
                return true;
            }

            $decimalniDeo = $deloviBroja[1];

            if (strlen($decimalniDeo) > $this->maxDecimalDigits) {
                return false;
            }

            return true;
        }
    }
    