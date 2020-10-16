<?php
    namespace App\Core;

    class DatabaseConfiguration {
        private $host;
        private $base;
        private $user;
        private $pass;

        public function __construct(string $host, string $base, string $user, string $pass) {
            $this->host = $host;
            $this->base = $base;
            $this->user = $user;
            $this->pass = $pass;
        }

        public function getConnectionString(): string {
            return 'mysql:host=' . $this->host . ';dbname=' . $this->base . ';charset=utf8';
        }

        public function getUsername(): string {
            return $this->user;
        }

        public function getPassword(): string {
            return $this->pass;
        }
    }
