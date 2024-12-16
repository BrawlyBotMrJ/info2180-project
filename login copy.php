<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
$host = 'localhost'; // Update with your database host
$dbname = 'dolphin_crm'; // Database name
$username = 'project_user'; // Database username
$password = 'password'; // Database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    // Query the Users table to find the user by email
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    

    if ($user && password_verify($password, $user['passwrd'])) {
        // Password matches, start a session
        echo "<script type='text/javascript'>
            console.log('working');
        </script>";
        $_SESSION['user_id'] = $user['id']; // Save user ID in session
        $_SESSION['user_email'] = $user['email']; // Save email in session
        $_SESSION['user_role'] = $user['user_role']; // Save role in session

        // Prevent further script execution after redirection
        header('Location: home.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <nav>
        <h1>Dolphin CRM</h1>
    </nav>

    <div class="login-container">
        <h1>Login</h1>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
        <input type="email" name="email" id="email" placeholder="Email address" required>
        <input type="password" name="password" id="password" placeholder="Password" required>
        <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>