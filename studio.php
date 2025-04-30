<?php
session_start();
require 'includes/database-connection.php'; // Include the database connection file

// Get studio name from query parameter
$studio_name = filter_input(INPUT_GET, 'studio_name', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($studio_name)) {
    die("Please provide a valid studio name.");
}

// Fetch studio information
$studio_query = "SELECT * FROM studio WHERE name = :studio_name";
$stmt = $pdo->prepare($studio_query);
$stmt->bindValue(':studio_name', $studio_name, PDO::PARAM_STR);

if (!$stmt->execute()) {
    die("Error fetching studio details.");
}

if ($stmt->rowCount() > 0) {
    $studio = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    die("<p>Studio not found.</p>");
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

if (!$stmt->execute()) {
    die("Error fetching shows for the studio.");
}

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

if (!$stmt->execute()) {
    die("Error fetching movies for the studio.");
}

$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Details</title>
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

    <!-- Studio Details -->
    <h1>Studio: <?= htmlspecialchars($studio['name']) ?></h1>
    <p><strong>Foundation Date:</strong> <?= htmlspecialchars($studio['foundation_date']) ?></p>
    <p><strong>Top Rated:</strong> <?= htmlspecialchars($studio['top_rated']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($studio['description'])) ?></p>

    <!-- Shows -->
    <h2>Shows</h2>
    <?php if (count($shows) > 0): ?>
        <table>
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
        <table>
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