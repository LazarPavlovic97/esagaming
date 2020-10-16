<?php
    namespace App\Core;

    class Field {
        private $isEditable;
        private $validator;

        public function __construct(Validator $validator, $isEditable = true) {
            $this->validator = $validator;
            $this->isEditable = $isEditable;
        }

        public function isEditable() {
            return $this->isEditable;
        }

        public function isFieldValueValid($value) {
            return $this->validator->isValid($value);
        }
    }
