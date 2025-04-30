<?php
session_start();
require 'includes/database-connection.php'; // Include the database connection file

// Get the showID from the query string
$showID = isset($_GET['showID']) ? $_GET['showID'] : '';

if (empty($showID)) {
    die("No showID provided.");
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

// Query to fetch show and episode details
$sql = "
    SELECT e.title AS episode_title, e.season, e.episode_num, e.episodeID, s.*
    FROM episode e
    INNER JOIN shows s 
    ON e.showID = s.showID
    WHERE s.showID = :showID
    ORDER BY e.season, e.episode_num
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':showID', $showID, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // Fetch show details (only once since it's the same for all episodes)
    $showDetails = null;
    $episodes = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!$showDetails) {
            $showDetails = [
                'title' => $row['title'],
                'episode_count' => $row['episode_count'],
                'total_seasons' => $row['total_seasons'],
                'genre' => $row['genre'],
                'description' => $row['description'],
                'studio_name' => $row['studio_name']
            ];
        }

        $episodes[] = [
            'episode_title' => $row['episode_title'],
            'season' => $row['season'],
            'episode_num' => $row['episode_num'],
            'episodeID' => $row['episodeID']
        ];
    }

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

    // Display show details
    echo "<h1>Show: " . htmlspecialchars($showDetails['title']) . "</h1>";
    echo "<p><strong>Genre:</strong> " . htmlspecialchars($showDetails['genre']) . "</p>";
    echo "<p><strong>Total Seasons:</strong> " . htmlspecialchars($showDetails['total_seasons']) . "</p>";
    echo "<p><strong>Episode Count:</strong> " . htmlspecialchars($showDetails['episode_count']) . "</p>";
    echo "<p><strong>Studio:</strong> <a href='studio.php?studio_name=" . urlencode($showDetails['studio_name']) . "'>" . htmlspecialchars($showDetails['studio_name']) . "</a></p>";
    echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($showDetails['description'])) . "</p>";

    // Display episodes
    echo "<h2>Episodes</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Season</th><th>Episode Number</th><th>Title</th><th>Episode ID</th></tr>";

    foreach ($episodes as $episode) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($episode['season']) . "</td>";
        echo "<td>" . htmlspecialchars($episode['episode_num']) . "</td>";
        echo "<td><a href='episode.php?episodeID=" . urlencode($episode['episodeID']) . "'>" . htmlspecialchars($episode['episode_title']) . "</a></td>";
        echo "<td>" . htmlspecialchars($episode['episodeID']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "<h1>No results found for the provided showID.</h1>";
}

?>