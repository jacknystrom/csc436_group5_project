<?php
session_start();
require 'includes/database-connection.php'; // Include the database connection file

$conn = $pdo; // Use the PDO connection from the included file

// Check if the user is logged in
$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    header("Location: login.php");
    exit();
}

// Search functionality
$searchTerm = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) ?? ""; // Default to an empty string
$searchQuery = "";
if (!empty($searchTerm) && strlen($searchTerm) >= 3) {
    $searchQuery = "WHERE title LIKE :searchTerm";
} elseif (!empty($searchTerm)) {
    $error = "Search term must be at least 3 characters long.";
}

// Fetch shows
$showsQuery = "SELECT * FROM shows $searchQuery";
$stmt = $conn->prepare($showsQuery);
if (!empty($searchTerm) && strlen($searchTerm) >= 3) {
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
}
if (!$stmt->execute()) {
    die("Error fetching shows.");
}
$showsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch movies
$moviesQuery = "SELECT * FROM movie $searchQuery";
$stmt = $conn->prepare($moviesQuery);
if (!empty($searchTerm) && strlen($searchTerm) >= 3) {
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
}
if (!$stmt->execute()) {
    die("Error fetching movies.");
}
$moviesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Logging Application - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
        }
        .search-bar {
            margin-bottom: 20px;
            text-align: center;
        }
        .search-bar input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-bar button {
            padding: 8px 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
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

    <h1>Welcome to the Content Logging Application</h1>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search for a show or movie..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Shows Section -->
    <h2>Available Shows</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Episodes</th>
                <th>Seasons</th>
                <th>Genre</th>
                <th>Description</th>
                <th>Studio</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($showsResult) > 0): ?>
                <?php foreach ($showsResult as $row): ?>
                    <tr>
                        <td>
                            <a href="show.php?showID=<?= urlencode($row['showID']) ?>">
                                <?= htmlspecialchars($row['title']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($row['episode_count']) ?></td>
                        <td><?= htmlspecialchars($row['total_seasons']) ?></td>
                        <td><?= htmlspecialchars($row['genre']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['studio_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No shows available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Movies Section -->
    <h2>Available Movies</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Run Time</th>
                <th>Release Date</th>
                <th>Description</th>
                <th>Studio</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($moviesResult) > 0): ?>
                <?php foreach ($moviesResult as $row): ?>
                    <tr>
                        <td>
                            <a href="movie.php?movieID=<?= urlencode($row['movieID']) ?>">
                                <?= htmlspecialchars($row['title']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($row['run_time']) ?></td>
                        <td><?= htmlspecialchars($row['release_date']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['studio_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No movies available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>