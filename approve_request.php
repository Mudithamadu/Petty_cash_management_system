<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

if (isset($_GET['id'])) {
    $request_id = $_GET['id'];
    
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Fetch the requested amount
        $stmt = $conn->prepare("SELECT requested_amount FROM requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $requested_amount = $row['requested_amount'];
        $stmt->close();

        // Update the request status to 'approved'
        $stmt = $conn->prepare("UPDATE requests SET status = 'approved' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        // Add the requested amount to the main fund
        $stmt = $conn->prepare("UPDATE main_fund SET balance = balance + ? WHERE id = 1");
        $stmt->bind_param("d", $requested_amount);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $conn->commit();

        // Redirect back to the view requests page
        header("Location: view_requests.php");
        exit;
    } catch (Exception $e) {
        // An error occurred; rollback the transaction
        $conn->rollback();
        echo "An error occurred: " . $e->getMessage();
    }
} else {
    // If no ID was provided, redirect to the dashboard
    header("Location: manager_dashboard.php");
    exit;
}
?>