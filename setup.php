<?php
// Database setup script
require_once 'includes/database.php';

echo "<h2>Setting up Sliding Puzzle Database...</h2>";

if (initializeDatabase()) {
    echo "<p style='color: green;'>✓ Database and tables created successfully!</p>";
    echo "<p>Default admin user created:</p>";
    echo "<ul>";
    echo "<li>Username: admin</li>";
    echo "<li>Password: admin123</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
} else {
    echo "<p style='color: red;'>✗ Database setup failed. Please check your database configuration.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h2 {
            color: #333;
        }
        p {
            margin: 10px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
</body>
</html>
