<?php
session_start();
include 'config.php';

$errors = [];
$success_message = "";

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Fetch the current user profile data
$stmt = $conn->prepare("SELECT full_name, email, phone_number, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_full_name, $current_email, $current_phone_number, $current_profile_photo);
$stmt->fetch();
$stmt->close();

// Set the default profile picture path
$photo_path = $current_profile_photo;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone_number = trim($_POST["phone_number"]);

    // Validate required fields
    if (empty($full_name) || empty($email) || empty($phone_number)) {
        $errors[] = "Full name, email, and phone number are required.";
    }

    // Check if the email is already in use (excluding the current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "This email is already in use by another user.";
    }
    $stmt->close();

    // Handle profile photo upload if a file is provided
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $photo = $_FILES['profile_photo'];
        $photo_name = basename($photo['name']);
        $photo_tmp_name = $photo['tmp_name'];
        $photo_extension = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($photo_extension, $allowed_extensions)) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Ensure the directory exists
            }

            $new_photo_name = "profile_" . $user_id . "_" . time() . "." . $photo_extension;
            $upload_path = $upload_dir . $new_photo_name;

            if (move_uploaded_file($photo_tmp_name, $upload_path)) {
                $photo_path = $upload_path;
            } else {
                $errors[] = "Failed to upload profile photo.";
            }
        } else {
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    // If no errors, update the database
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ?, profile_photo = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone_number, $photo_path, $user_id);

        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Update session data
            $current_full_name = $full_name;
            $current_email = $email;
            $current_phone_number = $phone_number;
            $current_profile_photo = $photo_path;
        } else {
            $errors[] = "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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

    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="file"] {
        width: 100%;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }

    input:focus {
        border-color: #f4a261;
        outline: none;
    }

    button {
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
    }

    button:hover {
        background-color: rgb(234, 139, 61);
        transform: translateY(-3px);
    }

    .success {
        color: green;
        font-size: 18px;
        text-align: center;
    }

    .error {
        color: red;
        font-size: 16px;
        text-align: center;
        margin-bottom: 15px;
    }

    .profile-photo {
        display: block;
        margin: 20px auto;
        max-width: 150px;
        max-height: 150px;
        border-radius: 50%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Navbar styling */
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
        margin-left: 20px
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
    <?php if (!empty($success_message)) { ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php } ?>

    <?php if (!empty($errors)) { ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?php echo $error; ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <?php if (!empty($current_profile_photo)) { ?>
        <img src="<?php echo htmlspecialchars($current_profile_photo); ?>" alt="Profile Photo" class="profile-photo">
    <?php } ?>

    <h2>Welcome, <?php echo htmlspecialchars($current_full_name); ?>!</h2>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Full Name:</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($current_full_name); ?>" required>
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required>
        </div>

        <div class="form-group">
            <label>Phone Number:</label>
            <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($current_phone_number); ?>" required>
        </div>

        <div class="form-group">
            <label>Profile Photo:</label>
            <input type="file" name="profile_photo" accept="image/*">
        </div>

        <button type="submit">Update Profile</button>
    </form>
</div>

<footer>
    <p>&copy; 2025 Your Website. All Rights Reserved.</p>
</footer>

</body>
</html>
