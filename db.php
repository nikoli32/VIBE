<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "VIBE_SQL_BACKEND";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    $db_error = ("Database connection failed: " . $conn->connect_error);
}
?>
