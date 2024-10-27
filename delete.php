<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Get the price of the record being deleted
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT price FROM petty_cash WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($price);
$stmt->fetch();
$stmt->close();

// Update the main fund
$stmt = $conn->prepare("UPDATE main_fund SET balance = balance + ? WHERE id = 1");
$stmt->bind_param("d", $price);
$stmt->execute();
$stmt->close();

// Delete the record
$stmt = $conn->prepare("DELETE FROM petty_cash WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

header("Location: index.php");
exit;
