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
        // Update the request status to 'rejected'
        $stmt = $conn->prepare("UPDATE requests SET status = 'rejected' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
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