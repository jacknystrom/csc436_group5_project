<?php
session_start();
require 'includes/database-connection.php'; // Include the database connection file

// Check if the user is logged in
$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    header("Location: login.php");
    exit();
}

// Redirect to profile page
if (isset($_POST['go_to_profile'])) {
    header("Location: profile.php");
    exit();
}

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

// Get the episodeID from the query string
$episodeID = isset($_GET['episodeID']) ? $_GET['episodeID'] : '';

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
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $episode = $stmt->fetch(PDO::FETCH_ASSOC);

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

    // Display episode details
    echo "<h1>Episode: " . htmlspecialchars($episode['title']) . "</h1>";
    echo "<p><strong>Episode Number:</strong> " . htmlspecialchars($episode['episode_num']) . "</p>";
    echo "<p><strong>Season:</strong> " . htmlspecialchars($episode['season']) . "</p>";
    echo "<p><strong>Run Time:</strong> " . htmlspecialchars($episode['run_time']) . "</p>";
    echo "<p><strong>Release Date:</strong> " . htmlspecialchars($episode['release_date']) . "</p>";
    echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($episode['description'])) . "</p>";
    echo "<p><strong>Show:</strong> <a href='show.php?showID=" . urlencode($episode['showID']) . "'>" . htmlspecialchars($episode['show_name']) . "</a></p>";
} else {
    echo "<p>No details found for the provided episodeID.</p>";
}

// Section to add a review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review'])) {
    $review = $_POST['review'] ?? '';
    $rating = $_POST['rating'] ?? null;
    $dateWatched = $_POST['date_watched'] ?? '';

    if (!empty($review) && $rating !== null && !empty($dateWatched)) {
        // Check if the user has already submitted a review for this episode
        $checkSql = "SELECT COUNT(*) FROM watched_episode WHERE userID = :userID AND episodeID = :episodeID";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':userID', $userID, PDO::PARAM_STR);
        $checkStmt->bindValue(':episodeID', $episodeID, PDO::PARAM_STR);
        $checkStmt->execute();
        $reviewExists = $checkStmt->fetchColumn();

        if ($reviewExists > 0) {
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
        echo "<p>Please fill in all fields.</p>";
    }
}

// Section to display all reviews for the episode
echo "<h2>Reviews for this Episode</h2>";
$reviewSql = "SELECT * FROM watched_episode WHERE episodeID = :episodeID ORDER BY date_watched DESC";
$reviewStmt = $pdo->prepare($reviewSql);
$reviewStmt->bindValue(':episodeID', $episodeID, PDO::PARAM_STR);
$reviewStmt->execute();

if ($reviewStmt->rowCount() > 0) {
    while ($reviewRow = $reviewStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;'>";
        echo "<p><strong>User ID:</strong> " . htmlspecialchars($reviewRow['userID']) . "</p>";
        echo "<p><strong>Review:</strong> " . nl2br(htmlspecialchars($reviewRow['review'])) . "</p>";
        echo "<p><strong>Rating:</strong> " . htmlspecialchars($reviewRow['rating']) . "</p>";
        echo "<p><strong>Date Watched:</strong> " . htmlspecialchars($reviewRow['date_watched']) . "</p>";
        echo "</div>";
    }
} else {
    echo "<p>No reviews available for this episode.</p>";
}
?>

<!-- Form to add a review -->
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