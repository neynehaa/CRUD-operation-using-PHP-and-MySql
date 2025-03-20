<?php
session_start();
require 'config.php'; // Database connection

// Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$full_name = $_SESSION["full_name"];

// Handle status submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["status_text"])) {
    $status_text = trim($_POST["status_text"]);

    if (!empty($status_text)) {
        $stmt = $conn->prepare("INSERT INTO create_statuses (username, status_text) VALUES (?, ?)");
        $stmt->bind_param("ss", $full_name, $status_text);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch statuses from the database
$sql = "SELECT * FROM create_statuses ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fdeee9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
            flex-grow: 1;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2b4857;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
            margin-bottom: 10px; /* Added margin to separate the textarea and button */
        }

        textarea:focus {
            border-color: #f4a261;
            outline: none;
        }

        .btn {
            background-color: #f4a261;
            color: white;
            padding: 14px;
            width: 100%;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s;
            margin-bottom: 15px; /* Add some space below the button */
        }

        .btn:hover {
            background-color: rgb(234, 139, 61);
            transform: translateY(-3px);
        }

        .status-card {
            background: #fff;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .status-text {
            font-size: 16px;
            color: #333;
        }

        .btn-edit, .btn-delete {
            font-size: 14px;
            margin-left: 5px;
        }

        /* Container for Edit and Delete buttons */
        .btn-container {
            display: inline-flex;
            gap: 10px; /* Adds space between buttons */
        }

        nav {
            background-color: rgb(234, 182, 140);
            padding: 25px 50px;
        }

        nav ul {
            list-style-type: none;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            display: inline-block;
            margin-left: 20px;
        }

        nav ul li a {
            color: black;
            text-decoration: none;
            font-size: 18px;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        footer {
            background-color: rgb(234, 182, 140);
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 50px;
            position: relative;
        }
    </style>
</head>
<body>

<nav>
    <ul>
        <li><a href="homepage.php">Home</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h2>
    <p>Create a new status:</p>

    <form method="POST">
        <textarea name="status_text" class="form-control" rows="3" placeholder="What's on your mind?" required></textarea>
        <button type="submit" class="btn">Post Status</button>
    </form>

    <h3 class="mt-4">Recent Status Updates</h3>
    <?php while ($status = $result->fetch_assoc()): ?>
        <div class="status-card">
            <p><strong><?php echo htmlspecialchars($status['username']); ?>:</strong></p>
            <p class="status-text"><?php echo nl2br(htmlspecialchars($status['status_text'])); ?></p>
            <small>Posted on: <?php echo $status['created_at']; ?></small>
            <br>
            <!-- Edit and Delete Buttons (side by side) -->
            <div class="btn-container">
                <a href="edit-status.php?id=<?php echo $status['id']; ?>" class="btn btn-sm btn-warning btn-edit">Edit</a>
                <a href="delete-status.php?id=<?php echo $status['id']; ?>" class="btn btn-sm btn-danger btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<footer>
    <p>&copy; 2025 Your Website Name. All Rights Reserved.</p>
</footer>

</body>
</html>
