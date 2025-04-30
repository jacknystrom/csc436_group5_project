<?php
session_start();
require 'includes/database-connection.php'; // Include the database connection file

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

// Get studio name from query parameter
$studio_name = isset($_GET['studio_name']) ? $_GET['studio_name'] : '';

if (empty($studio_name)) {
    echo "Please provide a studio name.";
    exit;
}

// Fetch studio information
$studio_query = "SELECT * FROM studio WHERE name = :studio_name";
$stmt = $pdo->prepare($studio_query);
$stmt->bindValue(':studio_name', $studio_name, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $studio = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    echo "<p>Studio not found.</p>";
    exit;
}

// Fetch shows created by the studio
$shows_query = "
    SELECT s.showID, s.title, s.episode_count, s.total_seasons
    FROM shows s
    WHERE s.studio_name = :studio_name
    ORDER BY s.showID
";
$stmt = $pdo->prepare($shows_query);
$stmt->bindValue(':studio_name', $studio_name, PDO::PARAM_STR);
$stmt->execute();
$shows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch movies created by the studio
$movies_query = "
    SELECT m.movieID, m.title, m.run_time, m.release_date
    FROM movie m
    WHERE m.studio_name = :studio_name
    ORDER BY m.movieID
";
$stmt = $pdo->prepare($movies_query);
$stmt->bindValue(':studio_name', $studio_name, PDO::PARAM_STR);
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Studio Details</title>
</head>
<body>

    <!-- Studio Details -->
    <h1>Studio: <?= htmlspecialchars($studio['name']) ?></h1>
    <p><strong>Foundation Date:</strong> <?= htmlspecialchars($studio['foundation_date']) ?></p>
    <p><strong>Top Rated:</strong> <?= htmlspecialchars($studio['top_rated']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($studio['description'])) ?></p>

    <!-- Shows -->
    <h2>Shows</h2>
    <?php if (count($shows) > 0): ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Show ID</th>
                <th>Title</th>
                <th>Episode Count</th>
                <th>Total Seasons</th>
            </tr>
            <?php foreach ($shows as $show): ?>
                <tr>
                    <td><?= htmlspecialchars($show['showID']) ?></td>
                    <td><a href="show.php?showID=<?= urlencode($show['showID']) ?>"><?= htmlspecialchars($show['title']) ?></a></td>
                    <td><?= htmlspecialchars($show['episode_count']) ?></td>
                    <td><?= htmlspecialchars($show['total_seasons']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No shows found for this studio.</p>
    <?php endif; ?>

    <!-- Movies -->
    <h2>Movies</h2>
    <?php if (count($movies) > 0): ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Movie ID</th>
                <th>Title</th>
                <th>Run Time</th>
                <th>Release Date</th>
            </tr>
            <?php foreach ($movies as $movie): ?>
                <tr>
                    <td><?= htmlspecialchars($movie['movieID']) ?></td>
                    <td><a href="movie.php?movieID=<?= urlencode($movie['movieID']) ?>"><?= htmlspecialchars($movie['title']) ?></a></td>
                    <td><?= htmlspecialchars($movie['run_time']) ?></td>
                    <td><?= htmlspecialchars($movie['release_date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No movies found for this studio.</p>
    <?php endif; ?>
</body>
</html>