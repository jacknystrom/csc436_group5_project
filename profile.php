<?php
require 'includes/database-connection.php'; // Include the database connection file

session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Logout functionality
if (isset($_POST['logout'])) {
    header("Location: logout.php");
    exit();
}

// Return to homepage functionality
if (isset($_POST['return_home'])) {
    header("Location: home.php");
    exit();
}

$userID = $_SESSION['userID'] ?? null;
if (!$userID || !is_numeric($userID)) {
    die("Invalid userID. Please log in again.");
}

// Handle review deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $reviewType = filter_input(INPUT_POST, 'review_type', FILTER_SANITIZE_SPECIAL_CHARS);
    $reviewID = filter_input(INPUT_POST, 'review_id', FILTER_SANITIZE_NUMBER_INT);

    if ($reviewType === 'episode' && $reviewID) {
        // Delete from watched_episode
        $deleteStmt = $pdo->prepare("DELETE FROM watched_episode WHERE userID = :userID AND episodeID = :reviewID");
        if ($deleteStmt->execute(['userID' => $userID, 'reviewID' => $reviewID])) {
            echo "<p>Episode review deleted successfully.</p>";
        } else {
            echo "<p>Error deleting episode review.</p>";
        }
    } elseif ($reviewType === 'movie' && $reviewID) {
        // Delete from watched_movie
        $deleteStmt = $pdo->prepare("DELETE FROM watched_movie WHERE userID = :userID AND movieID = :reviewID");
        if ($deleteStmt->execute(['userID' => $userID, 'reviewID' => $reviewID])) {
            echo "<p>Movie review deleted successfully.</p>";
        } else {
            echo "<p>Error deleting movie review.</p>";
        }
    }
}

// Fetch user profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE userID = :userID");
$stmt->execute(['userID' => $userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found. Please log in again.");
}

// Fetch episode reviews
$episodeStmt = $pdo->prepare("SELECT * FROM watched_episode WHERE userID = :userID");
$episodeStmt->execute(['userID' => $userID]);
$episodeReviews = $episodeStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch movie reviews
$movieStmt = $pdo->prepare("SELECT * FROM watched_movie WHERE userID = :userID");
$movieStmt->execute(['userID' => $userID]);
$movieReviews = $movieStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .profile, .reviews {
            margin-bottom: 30px;
        }
        .reviews h3 {
            margin-top: 20px;
        }
        .review-item {
            margin-bottom: 15px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .delete-button {
            padding: 10px 15px;
            font-size: 14px;
            background-color: #FF0000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: #CC0000;
        }
        .top-buttons {
            margin-bottom: 20px;
            text-align: center;
        }
        .top-buttons a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .top-buttons a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <!-- Navigation Buttons -->
    <div class="top-buttons">
        <a href="home.php" aria-label="Return to Home">Return to Home</a>
        <a href="profile.php" aria-label="Go to Profile">Go to Profile</a>
        <a href="logout.php" aria-label="Log Out">Log Out</a>
    </div>

    <!-- User Profile Section -->
    <div class="profile">
        <h1>User Profile</h1>
        <p><strong>First Name:</strong> <?= htmlspecialchars($user['fname']) ?></p>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($user['lname']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    </div>

    <!-- Episode Reviews Section -->
    <div class="reviews">
        <h2>Episode Reviews</h2>
        <?php if (count($episodeReviews) > 0): ?>
            <?php foreach ($episodeReviews as $review): ?>
                <div class="review-item">
                    <p><strong>Episode ID:</strong> <?= htmlspecialchars($review['episodeID']) ?></p>
                    <p><strong>Review:</strong> <?= htmlspecialchars($review['review']) ?></p>
                    <p><strong>Rating:</strong> <?= htmlspecialchars($review['rating'] ?? 'N/A') ?></p>
                    <p><strong>Date Watched:</strong> <?= htmlspecialchars($review['date_watched'] ?? 'N/A') ?></p>
                    <form method="post" action="" style="margin-top: 10px;">
                        <input type="hidden" name="review_type" value="episode">
                        <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['episodeID']) ?>">
                        <button type="submit" name="delete_review" class="delete-button" onclick="return confirm('Are you sure you want to delete this review?');">Delete Review</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No episode reviews found.</p>
        <?php endif; ?>
    </div>

    <!-- Movie Reviews Section -->
    <div class="reviews">
        <h2>Movie Reviews</h2>
        <?php if (count($movieReviews) > 0): ?>
            <?php foreach ($movieReviews as $review): ?>
                <div class="review-item">
                    <p><strong>Movie ID:</strong> <?= htmlspecialchars($review['movieID']) ?></p>
                    <p><strong>Review:</strong> <?= htmlspecialchars($review['review']) ?></p>
                    <p><strong>Rating:</strong> <?= htmlspecialchars($review['rating'] ?? 'N/A') ?></p>
                    <p><strong>Date Watched:</strong> <?= htmlspecialchars($review['date_watched'] ?? 'N/A') ?></p>
                    <form method="post" action="" style="margin-top: 10px;">
                        <input type="hidden" name="review_type" value="movie">
                        <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['movieID']) ?>">
                        <button type="submit" name="delete_review" class="delete-button" onclick="return confirm('Are you sure you want to delete this review?');">Delete Review</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No movie reviews found.</p>
        <?php endif; ?>
    </div>
</body>
</html>