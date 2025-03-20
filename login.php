<?php
session_start();
include 'config.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $errors[] = "Both fields are required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $full_name, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION["user_id"] = $user_id;
                $_SESSION["full_name"] = $full_name;
                header("Location: profile.php");
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <style>
        /* General styling */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #fdeee7; /* Light peach background */
            font-family: 'Arial', sans-serif;
            margin: 0;
        }

        /* Main container */
        .container {
            display: flex;
            width: 800px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 100%;
            
        }

        /* Left section */
        /* .left-section {
            flex: 1;
            width: 50%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            
            position: relative;
        } */

        

        .left-section {
    flex: 1; /* Equal division */
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    box-sizing: border-box;
}

/* Right section */
.right-section {
    flex: 1; /* Equal division */
    background-color: #f6d2b6; /* Light brown shade */
    display: flex;
    justify-content: center;
    align-items: center;
    box-sizing: border-box;
}

        .right-section img {
            max-width: 80%;
            height: auto;
        }

        h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* Input fields */
        input {
            width: 90%; /* Increase the length */
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
        }

        /* Login button */
        button {
            width: 100%;
            padding: 14px;
            background-color: #f0a774;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            color: #fff;
            cursor: pointer;
            margin-top: 15px;
        }

        button:hover {
            background-color: #e2955b;
        }

        /* Links */
        a {
            text-decoration: none;
            color: #f0a774;
            font-weight: bold;
        }

        a:hover {
            color: #e2955b;
        }

        /* Error messages */
        ul {
            list-style-type: none;
            padding: 0;
            color: red;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                width: 90%;
            }

            .left-section,
            .right-section {
                width: 100%;
            }

            .right-section {
                display: none; /* Hide image on small screens */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h2>Welcome Back!!</h2>
            <?php
            if (!empty($errors)) {
                echo "<ul>";
                foreach ($errors as $error) {
                    echo "<li>$error</li>";
                }
                echo "</ul>";
            }
            ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <a href="#">Forgot Password?</a>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
        <div class="right-section">
            <img src="images/man.svg" alt="Illustration">
        </div>
    </div>
</body>
</html>
