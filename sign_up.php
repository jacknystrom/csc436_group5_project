<?php
require 'includes/database-connection.php';
$user = $_POST['username'];
$pass = $_POST['password'];
$f=$_POST['fname'];
$l=$_POST['lname'];
$e=$_POST['email'];
$confirm_password = $_POST['confirm_password'];

if ($pass !== $confirm_password) {
    die("Passwords do not match.");
}

// Use prepared statement to prevent SQL injection
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? and password = ?");
$stmt->bind_param("ss", $user, $pass);
$stmt->execute();

$result = $stmt->fetch();

if ($result) {
    die("Username already exists.");
} else {
    // Insert new user into the database
    $stmt = $pdo->prepare("INSERT INTO users (username, SHA2(pass,256), fname, lname, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $user, $pass, $f, $l, $e);
    if ($stmt->execute()) {
        $_SESSION['username'] = $user; // Store user in session
        header("Location: home.php"); // Redirect to a protected page
        exit();
    } else {
        echo "Error: " . $stmt->error;
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
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <form action="process_signup.php" method="POST">
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