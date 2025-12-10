<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Get role before destroying session
$role = isset($_SESSION['UserRole']) ? $_SESSION['UserRole'] : "Default";

// Clear sessions
session_unset();
session_destroy();

// Assign colors based on user role
switch ($role) {
    case "Administrator":
        $borderColor = "#FF7A00";
        $buttonColor = "#FF9A3C";
        $buttonHover = "#FF7F11";
        $textColor = "#773f00";
        $bgColor = "#FFF5EC";
        break;

    case "Student":
        $borderColor = "#1B98E0";
        $buttonColor = "#1B98E0";
        $buttonHover = "#1478B5";
        $textColor = "#0A3D62";
        $bgColor = "#EAF4FF";
        break;

    case "Security Staff":
        $borderColor = "#FFD700";   
        $buttonColor = "#F2C200";   
        $buttonHover = "#D4A017";   
        $textColor = "#7A5B00";     
        $bgColor = "#FFF9D6";       
        break;

    default: 
        $borderColor = "#999";
        $buttonColor = "#777";
        $buttonHover = "#555";
        $textColor = "#444";
        $bgColor = "#f6f6f6";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>

    <style>
        body {
            background: <?= $bgColor ?>;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .logout-box {
            background: white;
            padding: 35px;
            border-radius: 15px;
            text-align: center;
            width: 350px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-top: 6px solid <?= $borderColor ?>;
        }

        h2 {
            color: <?= $textColor ?>;
            margin-bottom: 10px;
        }

        p {
            color: <?= $textColor ?>;
        }

        .login-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background: <?= $buttonColor ?>;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
        }

        .login-btn:hover {
            background: <?= $buttonHover ?>;
        }

        .timer {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }
    </style>

    <script>
        let seconds = 3;
        function countdown() {
            document.getElementById("timer").innerHTML = seconds;
            seconds--;
            if (seconds < 0) {
                window.location = "../Module1/login.php";
            } else {
                setTimeout(countdown, 1000);
            }
        }
        window.onload = countdown;
    </script>

</head>

<body>

<div class="logout-box">
    <h2>You've Been Logged Out</h2>
    <p>You have successfully logged out of FKPark.</p>

    <a class="login-btn" href="../Module1/login.php">Return to Login</a>

    <p class="timer">Redirecting in <span id="timer">3</span> seconds...</p>
</div>
<script>
    // If the page was loaded from cache (e.g., user pressed Back)
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            // Force a full reload
            window.location.reload();
        }
    });
</script>
</body>
</html>
