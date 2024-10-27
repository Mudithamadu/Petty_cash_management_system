<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
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

// Get the filter type from the query string
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Prepare the SQL query based on the filter
$sql = "SELECT * FROM petty_cash WHERE user_id = " . $_SESSION['user_id'];

switch ($filter) {
    case 'daily':
        $sql .= " AND DATE(date) = CURDATE()";
        break;
    case 'monthly':
        $sql .= " AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())";
        break;
    case 'yearly':
        $sql .= " AND YEAR(date) = YEAR(CURDATE())";
        break;
}

$sql .= " ORDER BY date DESC";

// Fetch petty cash records
$records = [];
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petty Cash Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Petty Cash Management System</h1>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        <div class="alert alert-info">Main Fund Balance: $<?php echo number_format($main_fund_balance, 2); ?></div>
        <div class="mb-3">
            <a href="add.php" class="btn btn-primary mr-2">Add Record</a>
            <a href="report.php" class="btn btn-secondary mr-2">View Report</a>
            <a href="add_request.php" class="btn btn-danger mr-2">Add Replenishment Request</a>
        </div>
        <div class="mb-3">
            <h4>Filter Records:</h4>
            <a href="index.php?filter=all" class="btn btn-outline-primary mr-2 <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
            <a href="index.php?filter=daily" class="btn btn-outline-primary mr-2 <?php echo $filter === 'daily' ? 'active' : ''; ?>">Daily</a>
            <a href="index.php?filter=monthly" class="btn btn-outline-primary mr-2 <?php echo $filter === 'monthly' ? 'active' : ''; ?>">Monthly</a>
            <a href="index.php?filter=yearly" class="btn btn-outline-primary mr-2 <?php echo $filter === 'yearly' ? 'active' : ''; ?>">Yearly</a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="6" class="text-center">No records found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo $record['id']; ?></td>
                            <td><?php echo $record['description']; ?></td>
                            <td><?php echo $record['date']; ?></td>
                            <td><?php echo $record['price']; ?></td>
                            <td><?php echo $record['amount']; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
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