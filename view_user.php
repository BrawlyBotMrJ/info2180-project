<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

// Database connection
$host = 'localhost';
$dbname = 'dolphin_crm';
$username = 'project_user';
$password = 'password';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($user_role == 'Admin') {
    $stmt = $conn->prepare("SELECT id, firstname, lastname, email, user_role, created_at FROM Users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_role = $_POST['user_role'];

    $stmt = $conn->prepare("SELECT id FROM Users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $error = "Email address is already in use.";
    } else {
       
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO Users (firstname, lastname, email, passwrd, user_role, created_at) 
                                VALUES (:firstname, :lastname, :email, :passwrd, :user_role, NOW())");
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':passwrd', $hashed_password);
        $stmt->bindParam(':user_role', $user_role);

        if ($stmt->execute()) {
            $success = "User created successfully!";
            header("Location: view_user.php");
            exit();
        } else {
            $error = "Error creating user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .new-user-form {
            display: none;
            margin-bottom: 20px;
        }
        .new-user-form.active {
            display: block;
        }
        .user-table {
            display: block;
        }
        .user-table.hidden {
            display: none;
        }
        .toggle-buttons {
            margin-bottom: 20px;
        }
        #cancelBtn {
            display: none;
        }
        .error-message {
            color: red;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Dolphin CRM</h2>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="create_contact.php">New Contact</a></li>
                <li><a href="view_user.php">Users</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <?php if ($user_role == 'Admin'): ?>
                <h2>Users</h2>

                <div class="toggle-buttons">
                    <button id="newUserBtn" onclick="toggleNewUserForm()">Create New User</button>
                    <button id="cancelBtn" onclick="cancelNewUserForm()">Cancel</button>
                </div>

                <div class="new-user-form" id="newUserForm">
                    <form id="newUserFormElement" method="POST" action="">
                        <input type="hidden" name="create_user" value="1">
                        <label for="firstname">First Name</label>
                        <input type="text" name="firstname" id="firstname" required>

                        <label for="lastname">Last Name</label>
                        <input type="text" name="lastname" id="lastname" required>

                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
                        <div id="emailError" class="error-message"></div>

                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                        <span id="passwordError" style="color: red; display: none;">Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, and one number.</span>

                        <label for="user_role">Role</label>
                        <select name="user_role" id="user_role">
                            <option value="Admin">Admin</option>
                            <option value="Member">Member</option>
                        </select>

                        <button type="submit">Create User</button>
                    </form>
                </div>

                <?php if (isset($success)): ?>
                    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <div class="user-table <?php echo isset($success) ? 'hidden' : ''; ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['firstname']) . " " . htmlspecialchars($user['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['user_role']); ?></td>
                                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;

        document.getElementById('newUserFormElement').addEventListener('submit', function (e) {
            const passwordField = document.getElementById('password');
            const passwordError = document.getElementById('passwordError');
            let formValid = true;

            passwordError.style.display = 'none';

            if (!passwordRegex.test(passwordField.value)) {
                passwordError.style.display = 'block';
                passwordField.focus();
                formValid = false;
            }

            if (!formValid) {
                e.preventDefault();
            }
        });

        function toggleNewUserForm() {
            const form = document.getElementById('newUserForm');
            const table = document.querySelector('.user-table');
            const newUserBtn = document.getElementById('newUserBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            
            form.classList.toggle('active');
            table.classList.toggle('hidden');
            newUserBtn.style.display = 'none';
            cancelBtn.style.display = 'inline-block';
        }

        function cancelNewUserForm() {
            const form = document.getElementById('newUserForm');
            const table = document.querySelector('.user-table');
            const newUserBtn = document.getElementById('newUserBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            
            form.classList.remove('active');
            table.classList.remove('hidden');
            newUserBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'none';
        }
    </script>
</body>
</html>
