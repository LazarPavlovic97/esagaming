<?php
    namespace App\Core;

    use \App\Core\DatabaseConnection;
    use \PDO;

    abstract class Model {
        private $dbCon;

        public function __construct(DatabaseConnection &$dbCon) {
            $this->dbCon = $dbCon;
        }

        protected function &getDatabaseConnection(): DatabaseConnection {
            return $this->dbCon;
        }

        final protected function getTableName(): string {
            $imeKlase = static::class;

            $matches = [];
            \preg_match('@^App\\\Models\\\((?:[A-Z][a-z]+)+)Model$@', $imeKlase, $matches);

            $grupa = $matches[1];

            $grupaSaDonjomCrtom = \preg_replace('|[A-Z]|', '_$0', $grupa);

            $malimSlovima = strtolower($grupaSaDonjomCrtom);

            $tableName = \substr($malimSlovima, 1);

            return $tableName;
        }

        public function getAll(): array {
            $tableName = $this->getTableName();
            $pdo = $this->dbCon->getConnection();
            $sql = 'SELECT * FROM ' . $tableName .';';
            $prep = $pdo->prepare($sql);
            $items = [];

            if ($prep) {
                $res = $prep->execute();

                if ($res) {
                    $items = $prep->fetchAll(PDO::FETCH_OBJ);
                }
            }

            return $items;
        }

        public function getById(int $id): ?\stdClass {
            $tableName = $this->getTableName();
            $pdo = $this->dbCon->getConnection();
            $sql = 'SELECT * FROM ' . $tableName .' WHERE ' . $tableName .'_id = ?;';
            $prep = $pdo->prepare($sql);
            $item = null;

            if ($prep) {
                $prep->execute( [ $id ] );
    
                $item = $prep->fetch(PDO::FETCH_OBJ);

                if (!$item) {
                    $item = null;
                }
            }

            return $item;
        }

        final protected function isFieldNameValid(string $fieldName) {
            return boolval(preg_match('#^[a-z]+[a-z0-9_]*[a-z0-9]$#', $fieldName));
        }

        public function getAllByFieldName(string $fieldName, $value) {
            if (!$this->isFieldNameValid($fieldName)) {
                throw new \Exception('Ime polja nije ispravno!');
            }

            $tableName = $this->getTableName();
            $pdo = $this->dbCon->getConnection();
            $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $fieldName . ' = ?;';
            $prep = $pdo->prepare($sql);
            $items = [];

            if ($prep) {
                $prep->execute( [ $value ] );

                $items = $prep->fetchAll(PDO::FETCH_OBJ);

                if (!$items) {
                    $items = [];
                }
            }

            return $items;
        }

        public function getByFieldName(string $fieldName, $value) {
            if (!$this->isFieldNameValid($fieldName)) {
                throw new \Exception('Ime polja nije ispravno!');
            }

            $tableName = $this->getTableName();
            $pdo = $this->dbCon->getConnection();
            $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $fieldName . ' = ?;';
            $prep = $pdo->prepare($sql);
            $item = null;

            if ($prep) {
                $prep->execute( [ $value ] );

                $item = $prep->fetch(PDO::FETCH_OBJ);

                if (!$item) {
                    $item = null;
                }
            }

            return $item;
        }

        protected function getFields() {
            return [];
        }

        final protected function isFieldNameSupported($name) {
            if (!$this->isFieldNameValid($name)) {
                return false;
            }

            $supportedFields = $this->getFields();

            foreach ($supportedFields as $supportedFieldName => $supportedField) {
                if ($supportedFieldName == $name) {
                    return true;
                }
            }

            return false;
        }

        final public function add(array $data) {
            $supportedFields = $this->getFields();
            $requestedFields = array_keys($data);

            foreach ($requestedFields as $requestedField) {
                if (!$this->isFieldNameSupported($requestedField)) {
                    throw new \Exception('Field name ' . $requestedField . ' is not supported in this model.');
                }

                $requestedValue = $data[$requestedField];

                if (!$supportedFields[$requestedField]->isEditable()) {
                    throw new \Exception('Field ' . $requestedField . ' is not editable.');
                }

                if (!$supportedFields[$requestedField]->isFieldValueValid($requestedValue)) {
                    throw new \Exception('The value for the field ' . $requestedField . ' is not valid.');
                }
            }

            $tableName = $this->getTableName();

            foreach ($requestedFields as &$requestedField) {
                $requestedField = "`{$requestedField}`";
            }

            $fieldNamesForSQL = implode(', ', $requestedFields);
            $placeHolders = str_repeat('?,', count($requestedFields));
            $placeHolders = \substr($placeHolders, 0, strlen($placeHolders)-1);

            $sql = "INSERT `{$tableName}` ({$fieldNamesForSQL}) VALUES ({$placeHolders});";

            $pdo = $this->dbCon->getConnection();
            $prep = $pdo->prepare($sql);

            if (!$prep) {
                return false;
            }

            $res = $prep->execute( array_values($data) );

            if (!$res) {
                return false;
            }

            return $pdo->lastInsertId();
        }

        /**
         * $auctionModel = new AuctionModel(....);
         * $auctionModel->editById(11, [
         *     'title'       => 'Novi naziv',
         *     'ends_at'     => '2019-04-11 10:00:00',
         *     'category_id' => 4
         * ]);
         *
         * # UPDATE `auction` SET title = ?, ends_at = ?, category_id = ? WHERE auction_id = ?;
         * # [ 'Novi naziv', '2019-04-11 10:00:00', 4, 11 ]
         */
        final public function editById($id, array $data) {
            $supportedFields = $this->getFields();
            $requestedFields = array_keys($data);

            foreach ($requestedFields as $requestedField) {
                if (!$this->isFieldNameSupported($requestedField)) {
                    throw new \Exception('Field name ' . $requestedField . ' is not supported in this model.');
                }

                $requestedValue = $data[$requestedField];

                if (!$supportedFields[$requestedField]->isEditable()) {
                    throw new \Exception('Field ' . $requestedField . ' is not editable.');
                }

                if (!$supportedFields[$requestedField]->isFieldValueValid($requestedValue)) {
                    throw new \Exception('The value for the field ' . $requestedField . ' is not valid.');
                }
            }

            $tableName = $this->getTableName();

            $nizParova = [];

            foreach ($requestedFields as $requestedFieldName) {
                $nizParova[] = "`{$requestedFieldName}` = ?";
            }

            $sqlPartNames = implode(', ', $nizParova);

            $sql = "UPDATE `{$tableName}` SET {$sqlPartNames} WHERE `{$tableName}_id` = ?;";

            $pdo = $this->dbCon->getConnection();
            $prep = $pdo->prepare($sql);

            if (!$prep) {
                return false;
            }

            $vrednostZaUpitnike = array_values($data);
            $vrednostZaUpitnike[] = $id;

            return $prep->execute( $vrednostZaUpitnike );
        }

        final public function deleteById($id) {
            $tableName = $this->getTableName();

            $sql = "DELETE FROM `{$tableName}` WHERE `{$tableName}_id` = ?;";

            $pdo = $this->dbCon->getConnection();
            $prep = $pdo->prepare($sql);

            if (!$prep) {
                return false;
            }

            return $prep->execute( [ $id ] );
        }
    }
