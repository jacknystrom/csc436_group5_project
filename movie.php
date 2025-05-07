<?php
session_start();
require 'includes/database-connection.php'; // Include the database connection file

// Check if the user is logged in
$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    header("Location: login.php");
    exit();
}

// Get the movieID from the query string
$movieID = filter_input(INPUT_GET, 'movieID', FILTER_SANITIZE_SPECIAL_CHARS);
if (empty($movieID)) {
    die("No movieID provided.");
}

// Query to fetch movie details
$sql = "SELECT * FROM movie WHERE movieID = :movieID";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':movieID', $movieID, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    // Display buttons
    echo "<div class='top-buttons'>";
    echo "<button type='button' onclick=\"location.href='home.php'\" aria-label='Return to Home'>Return to Home</button>";
    echo "<button type='button' onclick=\"location.href='profile.php'\" aria-label='Go to Profile'>Go to Profile</button>";
    echo "<button type='button' onclick=\"location.href='logout.php'\" aria-label='Log Out'>Log Out</button>";
    echo "</div>";

    // Display movie details
    echo "<h1>Movie: " . htmlspecialchars($movie['title']) . "</h1>";
    echo "<p><strong>Run Time:</strong> " . htmlspecialchars($movie['run_time']) . "</p>";
    echo "<p><strong>Release Date:</strong> " . htmlspecialchars($movie['release_date']) . "</p>";
    echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($movie['description'])) . "</p>";
    echo "<p><strong>Studio:</strong> <a href='studio.php?studio_name=" . urlencode($movie['studio_name']) . "'>" . htmlspecialchars($movie['studio_name']) . "</a></p>";
} else {
    echo "<p>No details found for the provided movieID.</p>";
}

// Section to add a review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review'])) {
    $review = filter_input(INPUT_POST, 'review', FILTER_SANITIZE_SPECIAL_CHARS);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);
    $dateWatched = filter_input(INPUT_POST, 'date_watched', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!empty($review) && $rating !== false && !empty($dateWatched)) {
        // Check if the user has already submitted a review for this movie
        $checkSql = "SELECT EXISTS(SELECT 1 FROM watched_movie WHERE userID = :userID AND movieID = :movieID)";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':userID', $userID, PDO::PARAM_STR);
        $checkStmt->bindValue(':movieID', $movieID, PDO::PARAM_STR);
        $checkStmt->execute();
        $reviewExists = $checkStmt->fetchColumn();

        if ($reviewExists) {
            echo "<p>You have already submitted a review for this movie.</p>";
        } else {
            // Insert the new review
            $insertSql = "INSERT INTO watched_movie (userID, movieID, review, rating, date_watched) 
                          VALUES (:userID, :movieID, :review, :rating, :dateWatched)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->bindValue(':userID', $userID, PDO::PARAM_STR);
            $insertStmt->bindValue(':movieID', $movieID, PDO::PARAM_STR);
            $insertStmt->bindValue(':review', $review, PDO::PARAM_STR);
            $insertStmt->bindValue(':rating', $rating, PDO::PARAM_STR);
            $insertStmt->bindValue(':dateWatched', $dateWatched, PDO::PARAM_STR);

            if ($insertStmt->execute()) {
                echo "<p>Review added successfully!</p>";
            } else {
                echo "<p>Error adding review. Please try again later.</p>";
            }
        }
    } else {
        echo "<p>Please fill in all fields correctly.</p>";
    }
}

// Section to display all reviews for the movie
echo "<h2>Reviews for this Movie</h2>";
$reviewSql = "SELECT * FROM watched_movie WHERE movieID = :movieID ORDER BY date_watched DESC";
$reviewStmt = $pdo->prepare($reviewSql);
$reviewStmt->bindValue(':movieID', $movieID, PDO::PARAM_STR);
$reviewStmt->execute();

if ($reviewStmt->rowCount() > 0) {
    while ($reviewRow = $reviewStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='review-item'>";
        echo "<p><strong>User ID:</strong> " . htmlspecialchars($reviewRow['userID']) . "</p>";
        echo "<p><strong>Review:</strong> " . nl2br(htmlspecialchars($reviewRow['review'])) . "</p>";
        echo "<p><strong>Rating:</strong> " . htmlspecialchars($reviewRow['rating']) . "</p>";
        echo "<p><strong>Date Watched:</strong> " . htmlspecialchars($reviewRow['date_watched']) . "</p>";
        echo "</div>";
    }
} else {
    echo "<p>No reviews available for this movie.</p>";
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

<style>
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