<?php
session_start();

//Check if user is logged in: redirect if so
if (isset($_SESSION["user_id"])) {
    header("Location: VIBEProfile.html");
    exit();
}



require ("db.php");

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, name, email, password_hash FROM Users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password_hash"])) {

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];

            header("Location: VIBEProfile.html");
            exit();
        }
    }
    $error = "Invalid email or password.";
}
?>

<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>

<h2>Login</h2>

<?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Log In</button>
</form>

</body>
</html>
