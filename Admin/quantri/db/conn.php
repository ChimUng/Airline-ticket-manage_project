<?php
    $servername = "localhost";
    $username = "root";
    $password = "Thanh@123";
    $dbname = "flightbooking";


// ket noi mysql
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }
    // echo "Connected successfully";
?>
