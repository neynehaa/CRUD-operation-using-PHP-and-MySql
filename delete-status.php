<?php
session_start();
require 'config.php';

// Redirect if user is not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check if a status ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid status ID.";
    exit();
}

$status_id = $_GET['id'];

// Delete the status from the database
$stmt = $conn->prepare("DELETE FROM create_statuses WHERE id = ?");
$stmt->bind_param("i", $status_id);
$stmt->execute();
$stmt->close();

header("Location: homepage.php");
exit();
?>
