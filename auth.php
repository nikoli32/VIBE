<!-- PHP by Kyle -->
<?php
require "db.php";

$action = $_POST["action"] ?? null;

if ($action === "register") {
    registerUser($conn);
} elseif ($action === "login") {
    loginUser($conn);
}

function registerUser($conn) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("INSERT INTO Users(name, email, password_hash, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $ip);

    if ($stmt->execute()) {
        echo "REGISTER_SUCCESS";
    } else {
        echo "REGISTER_FAILED";
    }
}

function loginUser($conn) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, password_hash FROM Users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo "LOGIN_FAILED";
        return;
    }

    $stmt->bind_result($uid, $hash);
    $stmt->fetch();

    if (password_verify($password, $hash)) {
        session_start();
        $_SESSION["user_id"] = $uid;
        echo "LOGIN_SUCCESS";
    } else {
        echo "LOGIN_FAILED";
    }
}
?>
