<?php
class Database {
    private $host = "localhost";
    private $dbname = "flightbooking";
    private $username = "root";
    private $password = "";
    public $conn;
    public function connect(){
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Kết nối thất bại: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>