<?php
require 'includes/database-connection.php';
$user = $_POST['username'];
$pass = $_POST['password'];


// Use prepared statement to prevent SQL injection
$stmt = $pdo->prepare("SELECT  userID
FROM `users` 
WHERE username = ? and password = SHA2(?, 256)
");
$stmt->bind_param("ss", $user, $pass);
$stmt->execute();

$result = $stmt->fetch();

if ($result->num_rows === 1) {
    if($result.['userID'])<4{
        $_SESSION['username'] = $user; // Store user in session
        header("Location: home.php"); // Redirect to a protected page
        exit();
    }else{
        $_SESSION['username'] = $user; // Store user in session
        header("Location: admin_home.php"); // Redirect to a protected page
        exit();
    }
} else {
    echo "Invalid username or password.";
}

?>

<!DOCTYPE>
<html>
<head>
<head>
		<meta charset="UTF-8">
  		<meta name="viewport" content="width=device-width, initial-scale=1.0">
  		<title>Login</title>
  		<link rel="stylesheet" href="css/style.css">
  		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
	</head>

</head>
<body>
    <h2>Login Page</h2>
    <form method="post" action="login.php">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>
