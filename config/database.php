<?php
class Database
{
    private $host = "localhost";
    private $db_name = "ecommerce_database";
    private $username = "root";
    private $password = "";
    private $conn;
    private $debug = true; // Debug flag

    public static function getBaseURL()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . '/Ecommerce_System_Inventory_and_Supply_Chain_Management';
    }

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            if ($this->debug) {
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOStatement'));
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                error_log("Database connection established successfully");
            }

        } catch (PDOException $e) {
            if ($this->debug) {
                error_log("Connection Error: " . $e->getMessage());
                error_log("Error Code: " . $e->getCode());
                error_log("Error File: " . $e->getFile());
                error_log("Error Line: " . $e->getLine());
                error_log("Error Trace: " . $e->getTraceAsString());
            }
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}

define('BASE_URL', Database::getBaseURL());
