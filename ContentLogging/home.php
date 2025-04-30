<?php
session_start();
require 'includes/database-connection.php'; // Include the database connection file

$conn = $pdo; // Use the PDO connection from the included file

// Redirect to profile page
if (isset($_POST['go_to_profile'])) {
    header("Location: profile.php");
    exit();
}

// Return to homepage functionality
if (isset($_POST['return_home'])) {
    header("Location: home.php");
    exit();
}

// Logout functionality
if (isset($_POST['logout'])) {
    header("Location: logout.php");
    exit();
}

// Search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$searchQuery = "";
if (!empty($searchTerm)) {
    $searchQuery = "WHERE title LIKE :searchTerm";
}

// Fetch shows
$showsQuery = "SELECT * FROM shows $searchQuery";
$stmt = $conn->prepare($showsQuery);
if (!empty($searchTerm)) {
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
}
$stmt->execute();
$showsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch movies
$moviesQuery = "SELECT * FROM movie $searchQuery";
$stmt = $conn->prepare($moviesQuery);
if (!empty($searchTerm)) {
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
}
$stmt->execute();
$moviesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Content Logging Application - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
        }
        .search-bar button {
            padding: 8px 16px;
        }
    </style>
</head>
<body>

    <h1>Welcome to the Content Logging Application</h1>

    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search for a show or movie..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

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
                            <a href="show.php?showID=<?php echo urlencode($row['showID']); ?>">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['episode_count']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_seasons']); ?></td>
                        <td><?php echo htmlspecialchars($row['genre']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['studio_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No shows available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

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
                            <a href="movie.php?movieID=<?php echo urlencode($row['movieID']); ?>">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['run_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['release_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['studio_name']); ?></td>
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