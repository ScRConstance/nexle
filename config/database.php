<?php
class Database
{
    private $host = "178.128.109.9";
    private $db_name = "entrance_test";
    private $username = "test01";
    private $password = "PlsDoNotShareThePass123@";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
        }
        catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>