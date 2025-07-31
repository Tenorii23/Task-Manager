<?php
$servername = "localhost";
$username = "root";
$password = ""; // Empty password for XAMPP
$dbname = "projektpt";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>