<?php
session_start();
require 'includes/database-connection.php'; // Include the database connection file

// Check if the user is logged in
$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    header("Location: login.php");
    exit();
}

// Get the episodeID from the query string
$episodeID = filter_input(INPUT_GET, 'episodeID', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($episodeID)) {
    die("No episodeID provided.");
}

// Query to fetch episode details along with the show name
$sql = "
    SELECT e.*, s.title AS show_name
    FROM episode e
    INNER JOIN shows s ON e.showID = s.showID
    WHERE e.episodeID = :episodeID
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':episodeID', $episodeID, PDO::PARAM_STR);

if (!$stmt->execute()) {
    die("Error fetching episode details.");
}

if ($stmt->rowCount() > 0) {
    $episode = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    die("<p>No details found for the provided episodeID.</p>");
}

// Section to add a review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review'])) {
    $review = filter_input(INPUT_POST, 'review', FILTER_SANITIZE_SPECIAL_CHARS);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);
    $dateWatched = filter_input(INPUT_POST, 'date_watched', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!empty($review) && $rating !== false && !empty($dateWatched)) {
        // Check if the user has already submitted a review for this episode
        $checkSql = "SELECT EXISTS(SELECT 1 FROM watched_episode WHERE userID = :userID AND episodeID = :episodeID)";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':userID', $userID, PDO::PARAM_STR);
        $checkStmt->bindValue(':episodeID', $episodeID, PDO::PARAM_STR);
        $checkStmt->execute();
        $reviewExists = $checkStmt->fetchColumn();

        if ($reviewExists) {
            echo "<p>You have already submitted a review for this episode.</p>";
        } else {
            // Insert the new review
            $insertSql = "INSERT INTO watched_episode (userID, episodeID, review, rating, date_watched) 
                          VALUES (:userID, :episodeID, :review, :rating, :dateWatched)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->bindValue(':userID', $userID, PDO::PARAM_STR);
            $insertStmt->bindValue(':episodeID', $episodeID, PDO::PARAM_STR);
            $insertStmt->bindValue(':review', $review, PDO::PARAM_STR);
            $insertStmt->bindValue(':rating', $rating, PDO::PARAM_STR);
            $insertStmt->bindValue(':dateWatched', $dateWatched, PDO::PARAM_STR);

            if ($insertStmt->execute()) {
                echo "<p>Review added successfully!</p>";
            } else {
                echo "<p>Error adding review.</p>";
            }
        }
    } else {
        echo "<p>Please fill in all fields correctly.</p>";
    }
}

// Section to display all reviews for the episode
$reviewSql = "SELECT * FROM watched_episode WHERE episodeID = :episodeID ORDER BY date_watched DESC";
$reviewStmt = $pdo->prepare($reviewSql);
$reviewStmt->bindValue(':episodeID', $episodeID, PDO::PARAM_STR);
$reviewStmt->execute();

$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Episode Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .top-buttons {
            margin-bottom: 20px;
            text-align: center;
        }
        .top-buttons button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .top-buttons button:hover {
            background-color: #0056b3;
        }
        .review-item {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <!-- Navigation Buttons -->
    <div class="top-buttons">
        <button type="button" onclick="location.href='home.php'" aria-label="Return to Home">Return to Home</button>
        <button type="button" onclick="location.href='profile.php'" aria-label="Go to Profile">Go to Profile</button>
        <button type="button" onclick="location.href='logout.php'" aria-label="Log Out">Log Out</button>
    </div>

    <!-- Episode Details -->
    <h1>Episode: <?= htmlspecialchars($episode['title']) ?></h1>
    <p><strong>Episode Number:</strong> <?= htmlspecialchars($episode['episode_num']) ?></p>
    <p><strong>Season:</strong> <?= htmlspecialchars($episode['season']) ?></p>
    <p><strong>Run Time:</strong> <?= htmlspecialchars($episode['run_time']) ?></p>
    <p><strong>Release Date:</strong> <?= htmlspecialchars($episode['release_date']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($episode['description'] ?? 'No description available.')) ?></p>
    <p><strong>Show:</strong> <a href="show.php?showID=<?= urlencode($episode['showID']) ?>"><?= htmlspecialchars($episode['show_name']) ?></a></p>

    <!-- Reviews Section -->
    <h2>Reviews for this Episode</h2>
    <?php if (count($reviews) > 0): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review-item">
                <p><strong>User ID:</strong> <?= htmlspecialchars($review['userID']) ?></p>
                <p><strong>Review:</strong> <?= nl2br(htmlspecialchars($review['review'])) ?></p>
                <p><strong>Rating:</strong> <?= htmlspecialchars($review['rating']) ?></p>
                <p><strong>Date Watched:</strong> <?= htmlspecialchars($review['date_watched']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No reviews available for this episode.</p>
    <?php endif; ?>

    <!-- Form to Add a Review -->
    <h2>Add a Review</h2>
    <form method="POST">
        <label for="review">Review:</label><br>
        <textarea id="review" name="review" required></textarea><br><br>

        <label for="rating">Rating (0-10):</label><br>
        <input type="number" id="rating" name="rating" step="0.1" min="0" max="10" required><br><br>

        <label for="date_watched">Date Watched:</label><br>
        <input type="date" id="date_watched" name="date_watched" required><br><br>

        <button type="submit">Submit Review</button>
    </form>
</body>
</html>