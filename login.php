<?php
session_start();
include "db.php";

// if (isset($_SESSION["user_id"])) {
//     //header("Location: home.php");
//     exit();
// }

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        $error = "Enter username and password";
    } else {
        $username = $_POST["username"];
        $password = $_POST["password"];

        $sql = "SELECT TravelerID, Username, Password, isDeactivated, isSuspended, isBanned FROM traveler WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            $is_password_hashed = strlen($user["Password"]) > 60;

            if ($is_password_hashed) {
                if (password_verify($password, $user["Password"])) {
                    if ($user["isDeactivated"] || $user["isSuspended"] || $user["isBanned"]) {
                        $error = "Your account is not active.";
                    } else {
                        $_SESSION["user_id"] = $user["TravelerID"];
                        $_SESSION["username"] = $user["Username"];

                        //header("Location: home.php");
                        exit;
                    }
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                if ($password === $user["Password"]) {
                    if ($user["isDeactivated"] || $user["isSuspended"] || $user["isBanned"]) {
                        $error = "Your account is not active.";
                    } else {
                        $_SESSION["user_id"] = $user["TravelerID"];
                        $_SESSION["username"] = $user["Username"];

                        //header("Location: home.php");
                        exit;
                    }
                } else {
                    $error = "Invalid username or password.";
                }
            }
        } else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
            text-align: center;
            overflow-x: hidden;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
        }

        .login-page {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            max-width: 90%; /* Max width for mobile */
            width: 400px; /* Fixed width for mobile */
        }

        .header-shape {
            position: absolute;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 150px;
            background-color: #074D4C;
            border-radius: 50%;
            z-index: -1;
        }

        .login-welcome h1 {
            margin: 0;
            font-size: 1.5em;
            color: #074D4C;
        }

        .desc p {
            font-size: 0.9em;
            color: #666;
            margin: 10px 0 20px;
        }

        .input-box {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }

        .input-box i {
            color: #074D4C;
            margin-right: 10px;
        }

        .input-box input {
            border: none;
            outline: none;
            width: 100%;
            padding: 10px;
        }

        .login-btn {
            background-color:#074D4C;
            color: white;
            border: none;
            padding: 10px;
            font-size: 1em;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
        }

        .signup-link p {
            margin: 20px 0 0;
            font-size: 0.9em;
        }

        .signup-link a {
            color: #074D4C;
            text-decoration: none;
            font-weight: bold;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        /* Web view styles */
        @media (min-width: 769px) {
            .login-page {
                max-width: 600px; /* Increase width for larger screens */
                padding: 40px; /* More padding for larger screens */
            }

            .header-shape {
                width: 200px; /* Larger header shape for web view */
                height: 200px;
            }

            .login-welcome h1 {
                font-size: 2.5em;
            }

            .desc p {
                font-size: 1.2em;
            }

            .input-box {
                padding: 15px;
                font-size: 1em;
            }

            .login-btn {
                padding: 15px;
                font-size: 1.2em;
            }
        }
    </style>
    <script>
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("password");
            var toggleIcon = document.getElementById("togglePassword");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="login-page">
            <div class="header-shape"></div>
            <div class="login-welcome">
                <h1>Welcome Back!</h1>
            </div>
            <div class="desc">
                <p>Start your journey by one click, explore the beauty of the Philippines</p>
            </div>
            <div class="login-form">
                <!-- Display error message -->
                <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="input-box">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Enter Username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="input-box">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Enter Password" required>
                        <i class="fas fa-eye" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                    </div>
                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
            <div class="signup-link">
                <p>Don't have an account? <a href="index.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</body>
</html>
