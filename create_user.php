<?php
session_start();

// Check if the user is logged in, if not, redirect to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'Admin') {
    header('Location: login.php');
    exit;
}

// Database connection
$host = 'localhost:3308';
$dbname = 'dolphin_crm';
$username = 'project_user';
$password = 'password';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_role = $_POST['user_role'];

    // Hash the password before storing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO Users (firstname, lastname, email, passwrd, user_role) 
                            VALUES (:firstname, :lastname, :email, :passwrd, :user_role)");
    $stmt->bindParam(':firstname', $firstname);
    $stmt->bindParam(':lastname', $lastname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':passwrd', $hashed_password);
    $stmt->bindParam(':user_role', $user_role);

    if ($stmt->execute()) {
        $success = "User created successfully!";
    } else {
        $error = "Error creating user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New User</title>
</head>
<body>
    <h1>Create New User</h1>
    
    <?php if (isset($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <label for="firstname">First Name:</label><br>
        <input type="text" name="firstname" id="firstname" required><br><br>

        <label for="lastname">Last Name:</label><br>
        <input type="text" name="lastname" id="lastname" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" name="email" id="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required><br><br>

        <label for="user_role">Role:</label><br>
        <select name="user_role" id="user_role">
            <option value="Admin">Admin</option>
            <option value="Member">Member</option>
        </select><br><br>

        <button type="submit">Create User</button>
    </form>
    
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
