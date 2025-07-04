<?php
ob_start(); // Start output buffering
session_start();
require 'includes/database-connection.php';

// Initialize variables
$user = '';
$pass = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING); // Sanitize username
    $pass = $_POST['password']; // Password should not be sanitized since it's hashed

    // Use prepared statement to prevent SQL injection
    $stmt = $pdo->prepare("SELECT userID FROM `users` WHERE username = ? AND password = SHA2(?, 256)");
    $stmt->bindValue(1, $user, PDO::PARAM_STR);
    $stmt->bindValue(2, $pass, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['userID'] = $result['userID'];
        $_SESSION['username'] = $user;

        // Redirect based on userID
        if ((int)$result['userID'] < 6) {
            header("Location: home_admin.php");
        } else {
            header("Location: home.php");
        }
        exit();
    } else {
        echo "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        form {
            display: inline-block;
            text-align: left;
            margin-top: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="submit"], .signup-button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover, .signup-button:hover {
            background-color: #0056b3;
        }
        .signup-button {
            margin-top: 10px;
            display: inline-block;
            text-decoration: none;
        }
        .button-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Login Page</h2>
    <form method="post" action="login.php">
        <label>Username:</label><br>
        <input type="text" name="username" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br>
        <div class="button-container">
            <input type="submit" value="Login">
        </div>
    </form>
    <br>
    <a href="sign_up.php" class="signup-button">Sign Up</a>
<?php ob_end_flush(); ?>
</body>
</html>
