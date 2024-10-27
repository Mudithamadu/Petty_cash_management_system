<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

$error = '';

// Fetch the record to edit
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM petty_cash WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$record = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $price = $_POST['price'];

    // Validate input
    if (empty($amount) || empty($description) || empty($date) || empty($price)) {
        $error = 'All fields are required';
    } else {
        // Update the record in the database
        $stmt = $conn->prepare("UPDATE petty_cash SET amount = ?, description = ?, date = ?, price = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("dssdii", $amount, $description, $date, $price, $id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $error = 'Error updating record';
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
    <title>Edit Record - Petty Cash Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Edit Record</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>">
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" name="amount" id="amount" class="form-control" step="0.01" value="<?php echo $record['amount']; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" required><?php echo $record['description']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control" value="<?php echo $record['date']; ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" name="price" id="price" class="form-control" step="0.01" value="<?php echo $record['price']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Record</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>