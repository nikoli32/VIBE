<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "db.php";

$user_id = $_SESSION["user_id"] ?? null;

if (!$user_id) {
    echo "NOT_LOGGED_IN";
    exit;
}

$action = $_GET["action"] ?? "list";

if ($action === "list") listFavorites($conn, $user_id);
if ($action === "add") addFavorite($conn, $user_id);
if ($action === "remove") removeFavorite($conn, $user_id);

function listFavorites($conn, $uid) {
    $stmt = $conn->prepare("
        SELECT E.*
        FROM Favorites F
        JOIN Events E ON F.event_id_fave = E.event_id
        WHERE F.user_id_fave = ?
    ");

    if (!$stmt) {
        echo json_encode(["error" => $conn->error]);
        return;
    }

    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function addFavorite($conn, $uid) {
    if (!isset($_POST["event_id"]) || empty($_POST["event_id"])) {
        echo "MISSING_EVENT_ID";
        return;
    }

    $event_id = (int)$_POST["event_id"];

    // Check if already favorited
    $check = $conn->prepare("SELECT user_id_fave FROM Favorites WHERE user_id_fave = ? AND event_id_fave = ?");
    $check->bind_param("ii", $uid, $event_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "ALREADY_FAVORITED";
        return;
    }

    $stmt = $conn->prepare("INSERT INTO Favorites (user_id_fave, event_id_fave) VALUES (?, ?)");

    if (!$stmt) {
        echo "PREPARE_FAILED: " . $conn->error;
        return;
    }

    $stmt->bind_param("ii", $uid, $event_id);

    if ($stmt->execute()) {
        echo "FAVORITE_ADDED";
    } else {
        echo "FAVORITE_FAILED: " . $stmt->error;
    }

    $stmt->close();
}

function removeFavorite($conn, $uid) {
    if (!isset($_POST["event_id"])) {
        echo "MISSING_EVENT_ID";
        return;
    }

    $event_id = (int)$_POST["event_id"];

    $stmt = $conn->prepare("DELETE FROM Favorites WHERE user_id_fave=? AND event_id_fave=?");

    if (!$stmt) {
        echo "PREPARE_FAILED: " . $conn->error;
        return;
    }

    $stmt->bind_param("ii", $uid, $event_id);
    $stmt->execute();

    echo "FAVORITE_REMOVED";
    $stmt->close();
}
?>