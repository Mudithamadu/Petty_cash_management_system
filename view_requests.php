<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Fetch only pending requests
$requests = [];
$stmt = $conn->prepare("SELECT r.*, u.username FROM requests r JOIN users u ON r.user_id = u.id WHERE r.status = 'pending' ORDER BY r.request_id DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pending Requests - Petty Cash Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>View Pending Requests</h1>
            <a href="manager_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>User</th>
                    <th>Requested Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No pending requests found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo $request['request_id']; ?></td>
                            <td><?php echo $request['username']; ?></td>
                            <td>$<?php echo number_format($request['requested_amount'], 2); ?></td>
                            <td>
                                <a href="approve_request.php?id=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                <a href="reject_request.php?id=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>