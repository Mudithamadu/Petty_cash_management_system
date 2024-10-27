<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Fetch the main fund balance
$main_fund_balance = 0.00;
$result = $conn->query("SELECT balance FROM main_fund WHERE id = 1");
if ($result && $row = $result->fetch_assoc()) {
    $main_fund_balance = $row['balance'];
}

// Fetch the count of pending requests
$stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM requests WHERE status = 'pending'");
$stmt->execute();
$stmt->bind_result($pending_count);
$stmt->fetch();
$stmt->close();

// Fetch the count of approved requests
$stmt = $conn->prepare("SELECT COUNT(*) as approved_count FROM requests WHERE status = 'approved'");
$stmt->execute();
$stmt->bind_result($approved_count);
$stmt->fetch();
$stmt->close();

// Fetch recent notifications (e.g., last 5 pending requests)
$notifications = [];
$stmt = $conn->prepare("SELECT r.request_id, u.username, r.requested_amount, r.created_at 
                        FROM requests r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.status = 'pending' 
                        ORDER BY r.created_at DESC 
                        LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

$notification_count = count($notifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Petty Cash Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .notification-dropdown {
            min-width: 300px;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manager Dashboard</h1>
            <div>
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell"></i> Notifications 
                        <?php if ($notification_count > 0): ?>
                            <span class="badge badge-light"><?php echo $notification_count; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right notification-dropdown">
                        <?php if (empty($notifications)): ?>
                            <div class="dropdown-item">No new notifications</div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="dropdown-item">
                                    <strong><?php echo htmlspecialchars($notification['username']); ?></strong> requested 
                                    $<?php echo number_format($notification['requested_amount'], 2); ?>
                                    <br>
                                    <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center" href="view_requests.php">View all requests</a>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        <!-- Rest of the dashboard content -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Requests</h5>
                        <p class="card-text"><?php echo $pending_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Approved Requests</h5>
                        <p class="card-text"><?php echo $approved_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Main Fund Balance</h5>
                        <p class="card-text">$<?php echo number_format($main_fund_balance, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <a href="view_requests.php" class="btn btn-primary">View Pending Requests</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>