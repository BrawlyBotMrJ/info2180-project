<?php
session_start();

// Check if the user is logged in, if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user info from session
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

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

// Fetch users if the user is an admin
if ($user_role == 'Admin') {
    $stmt = $conn->prepare("SELECT id, firstname, lastname, email, user_role FROM Users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to handle null or empty values
function sanitizeField($field) {
    return empty($field) ? "" : $field;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome to the Dashboard</h1>
    
    <p>Hello, <?php echo htmlspecialchars($user_email); ?>! You are logged in as an <?php echo htmlspecialchars($user_role); ?>.</p>
    
    <!-- Display different content based on user role -->
    <?php if ($user_role == 'Admin'): ?>
        <h2>Admin Section</h2>
        <p>As an admin, you can manage users and settings.</p>
        
        <!-- Button to navigate to create new user page -->
        <a href="create_user.php">
            <button>Create New User</button>
        </a>

        <h3>View Users</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo sanitizeField($user['id']); ?></td>
                        <td><?php echo sanitizeField($user['firstname']); ?></td>
                        <td><?php echo sanitizeField($user['lastname']); ?></td>
                        <td><?php echo sanitizeField($user['email']); ?></td>
                        <td><?php echo sanitizeField($user['user_role']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <h2>Member Section</h2>
        <p>As a member, you can view your profile and contacts.</p>
        <a href="view_profile.php">View Profile</a>
    <?php endif; ?>
    
    <h2>Logout</h2>
    <p><a href="logout.php">Click here to logout</a></p>
</body>
</html>
