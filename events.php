<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "db.php";

$action = $_GET["action"] ?? "list";

if ($action === "list") listEvents($conn);
if ($action === "add") addEvent($conn);
if ($action === "delete") deleteEvent($conn);
if ($action === "my_events") listMyEvents($conn);

function listEvents($conn) {
    $result = $conn->query("SELECT * FROM Events ORDER BY event_datetime ASC");

    if (!$result) {
        echo json_encode(["error" => $conn->error]);
        return;
    }

    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function listMyEvents($conn) {
    if (!isset($_SESSION["user_id"])) {
        echo json_encode([]);
        return;
    }

    $user_id = $_SESSION["user_id"];

    $stmt = $conn->prepare("SELECT * FROM Events WHERE creator_user_id = ? OR creator_user_id IS NULL ORDER BY event_datetime ASC");

    if (!$stmt) {
        echo json_encode(["error" => $conn->error]);
        return;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function addEvent($conn) {
    // Check if user is logged in
    if (!isset($_SESSION["user_id"])) {
        echo "NOT_LOGGED_IN";
        return;
    }


    // Check if all required fields are present
    $required = ['event_id', 'event_location', 'artists', 'event_type', 'event_datetime', 'venue_name'];

    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo "MISSING_FIELD: " . $field;
            return;
        }
    }

    $event_id = (int)$_POST["event_id"];
    $description = $_POST["event_description"] ?? "";
    $creator_user_id = $_SESSION["user_id"];

    $stmt = $conn->prepare("
        INSERT INTO Events
        (event_id, event_location, artists, event_type, event_datetime, venue_name, event_description, creator_user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo "PREPARE_FAILED: " . $conn->error;
        return;
    }

    $stmt->bind_param("issssssi",
        $event_id,
        $_POST["event_location"],
        $_POST["artists"],
        $_POST["event_type"],
        $_POST["event_datetime"],
        $_POST["venue_name"],
        $description,
        $creator_user_id
    );

    if ($stmt->execute()) {
        echo "EVENT_ADDED";
        header('Location: VIBEProfile.html');
    } else {
        echo "EVENT_FAILED: " . $stmt->error;
    }

    $stmt->close();
}

function deleteEvent($conn) {
    if (!isset($_POST["event_id"])) {
        echo "MISSING_EVENT_ID";
        return;
    }

    $stmt = $conn->prepare("DELETE FROM Events WHERE event_id=?");

    if (!$stmt) {
        echo "PREPARE_FAILED: " . $conn->error;
        return;
    }

    $stmt->bind_param("i", $_POST["event_id"]);
    $stmt->execute();

    echo "EVENT_DELETED";
    $stmt->close();
}
?>