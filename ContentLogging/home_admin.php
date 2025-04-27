<?php
session_start();
require 'includes/database-connection.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['userID']) || $_SESSION['userID'] >= 6) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_movie'])) {
        // Add a movie
        $movieID = str_pad($_POST['movieID'], 6, '0', STR_PAD_LEFT);
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $hours = $_POST['run_time_hours'];
        $minutes = $_POST['run_time_minutes'];
        $seconds = $_POST['run_time_seconds'];
        $run_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds); // Format runtime as HH:MM:SS
        $release_date = $_POST['release_date'];
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $studio_name = filter_input(INPUT_POST, 'studio_name', FILTER_SANITIZE_STRING);

        $stmt = $pdo->prepare("INSERT INTO movie (movieID, title, run_time, release_date, description, studio_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$movieID, $title, $run_time, $release_date, $description, $studio_name]);
    } elseif (isset($_POST['add_episode'])) {
        // Add an episode
        $episodeID = str_pad($_POST['episodeID'], 6, '0', STR_PAD_LEFT);
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $episode_num = $_POST['episode_num'];
        $hours = $_POST['run_time_hours'];
        $minutes = $_POST['run_time_minutes'];
        $seconds = $_POST['run_time_seconds'];
        $run_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds); // Format runtime as HH:MM:SS
        $release_date = $_POST['release_date'];
        $season = $_POST['season'];
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $showID = str_pad($_POST['showID'], 6, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare("INSERT INTO episode (episodeID, title, episode_num, run_time, release_date, season, description, showID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$episodeID, $title, $episode_num, $run_time, $release_date, $season, $description, $showID]);
    } elseif (isset($_POST['add_studio'])) {
        // Add a studio
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $foundation_date = $_POST['foundation_date'];
        $top_rated = filter_input(INPUT_POST, 'top_rated', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

        $stmt = $pdo->prepare("INSERT INTO studio (name, foundation_date, top_rated, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $foundation_date, $top_rated, $description]);
    } elseif (isset($_POST['logout'])) {
        // Handle logout
        header("Location: logout.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        form {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            max-width: 500px;
            margin: 20px auto;
        }
        form div {
            margin-bottom: 10px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .runtime-container {
            display: flex;
            gap: 10px;
        }
        .runtime-container input {
            width: calc(33.33% - 20px);
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color:rgb(0, 123, 255);
        }
        .logout-container {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .logout-container form {
            display: inline;
        }
    </style>
</head>
<body>
    <!-- Logout Button -->
    <div class="logout-container">
        <form method="POST">
            <button type="submit" name="logout">Log Out</button>
        </form>
    </div>

    <h1>Admin Panel</h1>

    <!-- Add Movie Form -->
    <form method="POST">
        <h2>Add Movie</h2>
        <div>
            <label for="movieID">Movie ID:</label>
            <input type="text" id="movieID" name="movieID" required>
        </div>
        <div>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div>
            <label>Run Time (HH:MM:SS):</label>
            <div class="runtime-container">
                <input type="number" name="run_time_hours" placeholder="Hours" min="0" required>
                <input type="number" name="run_time_minutes" placeholder="Minutes" min="0" max="59" required>
                <input type="number" name="run_time_seconds" placeholder="Seconds" min="0" max="59" required>
            </div>
        </div>
        <div>
            <label for="release_date">Release Date:</label>
            <input type="date" id="release_date" name="release_date">
        </div>
        <div>
            <label for="description">Description:</label>
            <textarea id="description" name="description"></textarea>
        </div>
        <div>
            <label for="studio_name">Studio Name:</label>
            <input type="text" id="studio_name" name="studio_name" required>
        </div>
        <button type="submit" name="add_movie">Add Movie</button>
    </form>

    <!-- Add Episode Form -->
    <form method="POST">
        <h2>Add Episode</h2>
        <div>
            <label for="episodeID">Episode ID:</label>
            <input type="text" id="episodeID" name="episodeID" required>
        </div>
        <div>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div>
            <label for="episode_num">Episode Number:</label>
            <input type="number" id="episode_num" name="episode_num" required>
        </div>
        <div>
            <label>Run Time (HH:MM:SS):</label>
            <div class="runtime-container">
                <input type="number" name="run_time_hours" placeholder="Hours" min="0" required>
                <input type="number" name="run_time_minutes" placeholder="Minutes" min="0" max="59" required>
                <input type="number" name="run_time_seconds" placeholder="Seconds" min="0" max="59" required>
            </div>
        </div>
        <div>
            <label for="release_date">Release Date:</label>
            <input type="date" id="release_date" name="release_date">
        </div>
        <div>
            <label for="season">Season:</label>
            <input type="number" id="season" name="season">
        </div>
        <div>
            <label for="description">Description:</label>
            <textarea id="description" name="description"></textarea>
        </div>
        <div>
            <label for="showID">Show ID:</label>
            <input type="text" id="showID" name="showID" required>
        </div>
        <button type="submit" name="add_episode">Add Episode</button>
    </form>

    <!-- Add Studio Form -->
    <form method="POST">
        <h2>Add Studio</h2>
        <div>
            <label for="name">Studio Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div>
            <label for="foundation_date">Foundation Date:</label>
            <input type="date" id="foundation_date" name="foundation_date">
        </div>
        <div>
            <label for="top_rated">Top Rated:</label>
            <input type="text" id="top_rated" name="top_rated">
        </div>
        <div>
            <label for="description">Description:</label>
            <textarea id="description" name="description"></textarea>
        </div>
        <button type="submit" name="add_studio">Add Studio</button>
    </form>
</body>
</html>