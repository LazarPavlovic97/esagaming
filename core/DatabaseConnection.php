<?php
    namespace App\Core;

    class DatabaseConnection {
        private $connection = null;

        public function __construct(DatabaseConfiguration $conf) {
            $this->connection = new \PDO(
                $conf->getConnectionString(),
                $conf->getUsername(),
                $conf->getPassword()
            );
        }

        public function getConnection(): \PDO {
            return $this->connection;
        }
    }
