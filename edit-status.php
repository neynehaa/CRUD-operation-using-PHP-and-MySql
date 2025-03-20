<?php
session_start();
require 'config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check if a status ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid status ID.";
    exit();
}

$status_id = $_GET['id'];

// Fetch the status from the database
$stmt = $conn->prepare("SELECT status_text FROM create_statuses WHERE id = ?");
$stmt->bind_param("i", $status_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Status not found.";
    exit();
}

$status = $result->fetch_assoc();
$stmt->close();

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_status_text = trim($_POST["status_text"]);

    if (!empty($new_status_text)) {
        $stmt = $conn->prepare("UPDATE create_statuses SET status_text = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status_text, $status_id);
        $stmt->execute();
        $stmt->close();
        header("Location: homepage.php");
        exit();
    } else {
        echo "Status text cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Status</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0d6c2; /* Same as homepage background color */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            width: 40%; /* Smaller width than homepage */
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
    <h2>Edit Status</h2>
    <form method="POST">
        <div class="form-group">
            <label for="status_text">Update your status:</label>
            <textarea name="status_text" class="form-control" rows="3" required><?php echo htmlspecialchars($status['status_text']); ?></textarea>
        </div>
        <button type="submit" class="btn">Update Status</button>
        <a href="homepage.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<footer>
    <p>&copy; 2025 Your Website Name. All Rights Reserved.</p>
</footer>

</body>
</html>
