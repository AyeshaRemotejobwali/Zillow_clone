<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Clear all session data
$_SESSION = [];

// Destroy the session
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Zillow Clone</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .message-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        h2 {
            color: #333;
        }
        p {
            color: #007bff;
            font-size: 1.1em;
        }
        @media (max-width: 480px) {
            .message-container {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h2>Zillow Clone</h2>
        <p>Logged out successfully! Redirecting to homepage...</p>
    </div>
    <script>
        // Redirect to index.php after 1 second
        setTimeout(() => {
            redirectTo('index.php');
        }, 1000);

        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
