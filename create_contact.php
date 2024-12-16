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

// Handle form submission via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    $title = $_POST['title'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $company = $_POST['company'];
    $contact_type = $_POST['contact_type'];
    $assigned_to = $_POST['assigned_to'];
    $created_by = $_POST['created_by'];

    // Insert the new contact into the database
    $stmt = $conn->prepare("INSERT INTO contacts (title, firstname, lastname, email, telephone, company, contact_type, assigned_to, created_by, created_at) 
                            VALUES (:title, :firstname, :lastname, :email, :telephone, :company, :contact_type, :assigned_to, :created_by, NOW())");

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':firstname', $firstname);
    $stmt->bindParam(':lastname', $lastname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telephone', $telephone);
    $stmt->bindParam(':company', $company);
    $stmt->bindParam(':contact_type', $contact_type);
    $stmt->bindParam(':assigned_to', $assigned_to);
    $stmt->bindParam(':created_by', $created_by);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Contact added successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding contact.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Contact</title>
    <link rel="stylesheet" href="create_contact.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

        <div class="sidebar">
            <h2>Dolphin CRM</h2>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="create_contact.php">New Contact</a></li>
                <?php if ($user_role !== 'Member'): ?>
                    <li><a href="view_user.php">Users</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    
        <h1>Add New Contact</h1>

    <div id="message"></div>

    <form id="contactForm">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" required><br>

        <label for="firstname">First Name</label>
        <input type="text" name="firstname" id="firstname" required><br>

        <label for="lastname">Last Name</label>
        <input type="text" name="lastname" id="lastname" required><br>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required><br>

        <label for="telephone">Telephone</label>
        <input type="text" name="telephone" id="telephone" required><br>

        <label for="company">Company</label>
        <input type="text" name="company" id="company"><br>

        <label for="contact_type">Contact Type</label>
        <select name="contact_type" id="contact_type" required>
            <option value="Sales Lead">Sales Lead</option>
            <option value="Support">Support</option>
        </select><br>

        <label for="assigned_to">Assigned To</label>
        <input type="text" name="assigned_to" id="assigned_to" required><br>

        <label for="created_by">Created By</label>
        <input type="text" name="created_by" id="created_by" required><br>

        <button type="submit">Add Contact</button>
    </form>

    <script>
        $(document).ready(function() {
            $('#contactForm').on('submit', function(e) {
                e.preventDefault(); // Prevent form submission

                // Show loading message
                $('#message').html('Adding contact...');

                $.ajax({
                    type: 'POST',
                    url: 'create_contact.php',
                    data: $(this).serialize() + '&ajax=true', // Include the ajax flag
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#message').html('<span style="color: green;">' + response.message + '</span>');
                            $('#contactForm')[0].reset(); // Reset the form after success
                        } else {
                            $('#message').html('<span style="color: red;">' + response.message + '</span>');
                        }
                    },
                    error: function() {
                        $('#message').html('<span style="color: red;">There was an error processing your request. Please try again later.</span>');
                    }
                });
            });
        });
    </script>
</body>
</html>

