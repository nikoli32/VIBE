<?php
require ("db.php");

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO Users (user_id, name, email, password_hash) VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$id = rand(100000, 999999); // random user_id
$stmt->bind_param("isss", $id, $name, $email, $password_hash);

if ($stmt->execute()) {
    echo "Registration successful!";
    header("Location: VIBEProfile.html");
    exit();
} else {
    echo "Error: " . $stmt->error;
}
?>
