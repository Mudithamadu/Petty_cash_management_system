<?php
require_once 'config.php';

// User registration function
function registerUser($username, $email, $password, $role)
{
    global $conn;

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
    $stmt->execute();

    // Close the statement
    $stmt->close();
}

// User login function
function loginUser($username, $password)
{
    global $conn;

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            // Password is correct, start a session and store the user ID and role
            session_start();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            return true;
        }
    }

    // Close the statement
    $stmt->close();

    return false;
}

// Fetch petty cash records
function fetchPettyCashRecords($userId, $filter = null)
{
    global $conn;

    $query = "SELECT * FROM petty_cash WHERE user_id = ?";
    $params = array($userId);

    if ($filter === 'daily') {
        $query .= " AND DATE(date) = CURDATE()";
    } elseif ($filter === 'weekly') {
        $query .= " AND WEEK(date) = WEEK(CURDATE())";
    } elseif ($filter === 'yearly') {
        $query .= " AND YEAR(date) = YEAR(CURDATE())";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $records = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $records;
}