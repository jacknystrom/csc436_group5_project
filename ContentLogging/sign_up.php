<?php
session_start();
require 'includes/database-connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $pass = $_POST['password']; // Passwords should not be sanitized, as they are hashed
    $confirm_password = $_POST['confirm_password'];
    $f = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
    $l = filter_input(INPUT_POST, 'lname', FILTER_SANITIZE_STRING);
    $e = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$e) {
        die("Invalid email address.");
    }

    if ($pass !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bindValue(1, $user, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result) {
        die("Username already exists.");
    }

    // Generate new userID as a 6-character string with leading zeros
    $UID = $pdo->query("SELECT MAX(CAST(userID AS UNSIGNED)) FROM users");
    $maxUserID = $UID->fetchColumn();
    $userID = str_pad($maxUserID + 1, 6, '0', STR_PAD_LEFT);

    // Insert new user into the database
    $stmt = $pdo->prepare("INSERT INTO users (userID, username, password, fname, lname, email, avg_usr_rating) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $userID, PDO::PARAM_STR);
    $stmt->bindValue(2, $user, PDO::PARAM_STR);
    $stmt->bindValue(3, $pass, PDO::PARAM_STR);
    $stmt->bindValue(4, $f, PDO::PARAM_STR);
    $stmt->bindValue(5, $l, PDO::PARAM_STR);
    $stmt->bindValue(6, $e, PDO::PARAM_STR);
    $stmt->bindValue(7, null, PDO::PARAM_NULL);

    if ($stmt->execute()) {
        $_SESSION['userID'] = $userID; // Store user in session
        $_SESSION['username'] = $user; // Store username in session
        header("Location: home.php"); // Redirect to a protected page
        exit();
    } else {
        echo "Error: " . $stmt->errorInfo()[2];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .signup-container {
            width: 300px;
            margin: 0 auto;
            text-align: center;
            padding: 20px;
            box-shadow: 0 4px 8px rgb(255, 255, 255);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            text-align: left;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        p {
            margin-top: 15px;
        }
        p a {
            color: #007BFF;
            text-decoration: none;
        }
        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <form action="sign_up.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="fname">First Name:</label>
                <input type="text" id="fname" name="fname" required>
            </div>
            <div class="form-group">
                <label for="lname">Last Name:</label>
                <input type="text" id="lname" name="lname" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html>