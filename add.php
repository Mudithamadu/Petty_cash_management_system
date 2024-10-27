<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

$error = '';
$main_fund_balance = 0.00;

// Get the current balance of the main fund
$result = $conn->query("SELECT balance FROM main_fund WHERE id = 1");
if ($result && $row = $result->fetch_assoc()) {
    $main_fund_balance = $row['balance'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $price = $_POST['price'];
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($amount) || empty($description) || empty($date) || empty($price)) {
        $error = 'All fields are required';
    } elseif ($price > $main_fund_balance) {
        $error = 'Insufficient funds in the main account';
    } else {
        // Deduct the price from the main fund balance
        $new_balance = $main_fund_balance - $price;
        $conn->begin_transaction();

        try {
            // Insert the record into the database
            $stmt = $conn->prepare("INSERT INTO petty_cash (amount, description, date, price, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("dssdi", $amount, $description, $date, $price, $user_id);

            if ($stmt->execute()) {
                // Update the main fund balance
                $stmt = $conn->prepare("UPDATE main_fund SET balance = ? WHERE id = 1");
                $stmt->bind_param("d", $new_balance);
                $stmt->execute();

                $conn->commit();

                header("Location: index.php");
                exit;
            } else {
                $conn->rollback();
                $error = 'Error adding record';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Error processing transaction';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Record - Petty Cash Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Add Record</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="alert alert-info">Main Fund Balance: $<?php echo number_format($main_fund_balance, 2); ?></div>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" name="amount" id="amount" class="form-control" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" name="price" id="price" class="form-control" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Record</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
