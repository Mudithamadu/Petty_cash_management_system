<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $request_id = $_POST['request_id'];
    $requested_amount = $_POST['requested_amount'];

    // Insert the data into the requests table
    $stmt = $conn->prepare("INSERT INTO requests (request_id, requested_amount, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param("idi", $request_id, $requested_amount, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    // Redirect to the index page
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Request</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Add Replenishment Request</h1>
        <form action="add_request.php" method="post">
            <div class="form-group">
                <label for="request_id">Request ID</label>
                <input type="number" id="request_id" name="request_id" required>
            </div>
            <div class="form-group">
                <label for="requested_amount">Requested Amount</label>
                <input type="number" id="requested_amount" name="requested_amount" step="0.01" required>
            </div>
            <div class="button-group">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="index.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</body>
</html>
