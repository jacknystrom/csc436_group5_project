<?php
require 'includes/database-connection.php'; // Include the database connection file

session_start();

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

// Redirect to profile page (if needed)
if (isset($_POST['go_to_profile'])) {
    header("Location: profile.php");
    exit();
}

$userID = $_SESSION['userID'] ?? null;
if (!$userID || !is_numeric($userID)) {
    header("Location: login.php");
    exit();
}

// Handle review deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $reviewType = $_POST['review_type'] ?? '';
    $reviewID = $_POST['review_id'] ?? '';

    if ($reviewType === 'episode' && is_numeric($reviewID)) {
        // Delete from watched_episode
        $deleteStmt = $pdo->prepare("DELETE FROM watched_episode WHERE userID = :userID AND episodeID = :reviewID");
        $deleteStmt->execute(['userID' => $userID, 'reviewID' => $reviewID]);
    } elseif ($reviewType === 'movie' && is_numeric($reviewID)) {
        // Delete from watched_movie
        $deleteStmt = $pdo->prepare("DELETE FROM watched_movie WHERE userID = :userID AND movieID = :reviewID");
        $deleteStmt->execute(['userID' => $userID, 'reviewID' => $reviewID]);
    }
    // Redirect to refresh the page after deletion
    header("Location: profile.php");
    exit();
}

// Fetch user profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE userID = :userID");
$stmt->execute(['userID' => $userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: login.php");
    exit();
}

// Fetch episode reviews
$episodeStmt = $pdo->prepare("SELECT * FROM watched_episode WHERE userID = :userID");
$episodeStmt->execute(['userID' => $userID]);
$episodeReviews = $episodeStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch movie reviews
$movieStmt = $pdo->prepare("SELECT * FROM watched_movie WHERE userID = :userID");
$movieStmt->execute(['userID' => $userID]);
$movieReviews = $movieStmt->fetchAll(PDO::FETCH_ASSOC);

    // Display buttons
    echo "<div class='top-buttons' style='margin-bottom: 20px; text-align: center;'>";
    echo "<form method='post' action='' style='display: inline-block; margin: 0 10px;'>";
    echo "<button type='submit' name='return_home'>Return to Home</button>";
    echo "</form>";
    echo "<form method='post' action='' style='display: inline-block; margin: 0 10px;'>";
    echo "<button type='submit' name='go_to_profile'>Go to Profile</button>";
    echo "</form>";
    echo "<form method='post' action='' style='display: inline-block; margin: 0 10px;'>";
    echo "<button type='submit' name='logout'>Log Out</button>";
    echo "</form>";
    echo "</div>";
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
    </style>
</head>
<body>

    <div class="profile">
        <h1>User Profile</h1>
        <p><strong>First Name:</strong> <?= htmlspecialchars($user['fname']) ?></p>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($user['lname']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    </div>

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