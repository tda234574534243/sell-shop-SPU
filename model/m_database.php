<?php
    class M_database {
        public $CONFIG_serverName = "localhost";
        public $CONFIG_userName = "root";
        public $CONFIG_password = "";
        public $CONFIG_dbName = "salespage";
        public $conn, $query = null;
        public function __construct() {
            $this->conn = new mysqli($this->CONFIG_serverName, $this->CONFIG_userName, 
                                        $this->CONFIG_password, $this->CONFIG_dbName);
        }
        public function setQuery($sql_query) {
            $this->query = $sql_query;
        }
        public function excuteQuery() {
                $result = $this->conn->query($this->query);
                if ($result === false) {
                    $logDir = __DIR__ . '/../logs';
                    if (!is_dir($logDir)) {
                        @mkdir($logDir, 0755, true);
                    }
                    $message = date('Y-m-d H:i:s') . " | MySQL Error: " . $this->conn->error . " | Query: " . $this->query . PHP_EOL;
                    @error_log($message, 3, $logDir . '/db_errors.log');
                }
                return $result;
        }
        public function close() {
            $this->conn->close();
        }

        public function getConnection() {
            return $this->conn;
        }

        public function real_escape_string($string) {
            return $this->conn->real_escape_string($string);
        }
    }
?>