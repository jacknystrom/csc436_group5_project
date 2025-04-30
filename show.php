<?php
session_start();
require 'includes/database-connection.php'; // Include the database connection file

// Get the showID from the query string
$showID = filter_input(INPUT_GET, 'showID', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($showID)) {
    die("No showID provided.");
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

if (!$stmt->execute()) {
    die("Error fetching show details.");
}

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
} else {
    $showDetails = null;
    $episodes = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Details</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
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

    <?php if ($showDetails): ?>
        <!-- Show Details Section -->
        <h1>Show: <?= htmlspecialchars($showDetails['title']) ?></h1>
        <p><strong>Genre:</strong> <?= htmlspecialchars($showDetails['genre']) ?></p>
        <p><strong>Total Seasons:</strong> <?= htmlspecialchars($showDetails['total_seasons']) ?></p>
        <p><strong>Episode Count:</strong> <?= htmlspecialchars($showDetails['episode_count']) ?></p>
        <p><strong>Studio:</strong> <a href="studio.php?studio_name=<?= urlencode($showDetails['studio_name']) ?>"><?= htmlspecialchars($showDetails['studio_name']) ?></a></p>
        <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($showDetails['description'])) ?></p>

        <!-- Episodes Section -->
        <h2>Episodes</h2>
        <?php if (count($episodes) > 0): ?>
            <table>
                <tr>
                    <th>Season</th>
                    <th>Episode Number</th>
                    <th>Title</th>
                    <th>Episode ID</th>
                </tr>
                <?php foreach ($episodes as $episode): ?>
                    <tr>
                        <td><?= htmlspecialchars($episode['season']) ?></td>
                        <td><?= htmlspecialchars($episode['episode_num']) ?></td>
                        <td><a href="episode.php?episodeID=<?= urlencode($episode['episodeID']) ?>"><?= htmlspecialchars($episode['episode_title']) ?></a></td>
                        <td><?= htmlspecialchars($episode['episodeID']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No episodes available for this show.</p>
        <?php endif; ?>
    <?php else: ?>
        <h1>No results found for the provided showID.</h1>
    <?php endif; ?>

</body>
</html>