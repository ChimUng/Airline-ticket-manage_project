<?php
require_once "../includes/db_connect.php";

class Review {
    private $conn;
    private $table = "reviews";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAllreview() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>