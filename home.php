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

// Fetch the first name of the logged-in user
$stmt = $conn->prepare("SELECT firstname FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $firstname = $user['firstname'];
} else {
    $firstname = 'Guest'; // Fallback in case no user is found
}

$stmt = $conn->prepare("SELECT id, firstname, lastname, email, company, contact_type, assigned_to FROM contacts");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
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

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars($firstname); ?>!</h1>
            </div>
            <div>
                <h2>Dashboard</h2>
                <button onclick="toggleNewUserForm()">+ Add Contact</button>
            </div>
            <div class="filter">
                <button onclick="filterTable('all')">All</button>
                <button onclick="filterTable('Sales Lead')">Sales Leads</button>
                <button onclick="filterTable('Support')">Support</button>
                <button onclick="filterTable('Assigned-to-me')">Assigned to me</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="contactTable">
                    <?php foreach ($users as $contact): ?>
                    <tr class="contact-row" data-type="<?= htmlspecialchars($contact['contact_type']) ?>" data-assigned-to="<?= htmlspecialchars($contact['assigned_to']) ?>">
                        <td><?= htmlspecialchars($contact['firstname'] . ' ' . $contact['lastname']) ?></td>
                        <td><?= htmlspecialchars($contact['email']) ?></td>
                        <td><?= htmlspecialchars($contact['company']) ?></td>
                        <td>
                            <span class="badge <?= strtolower(str_replace(' ', '-', $contact['contact_type'])) ?>">
                                <?= htmlspecialchars($contact['contact_type']) ?>
                            </span>
                        </td>
                        <td><a href="contact_details.php?id=<?= $contact['id'] ?>" class="view">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterTable(type) {
            const rows = document.querySelectorAll('.contact-row');
            rows.forEach(row => {
                if (type === 'all') {
                    row.style.display = '';
                } else if (type === 'Assigned-to-me') {
                    if (row.dataset.assignedTo == <?= json_encode($user_id) ?>) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                } else {
                    // Filter by contact type
                    if (row.dataset.type === type) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        }

        function toggleNewUserForm() {
            window.location.href = "create_contact.php";
        }

        // Update contact list dynamically (AJAX)
        function updateContactList(newContact) {
            const contactTable = document.getElementById('contactTable');
            const newRow = document.createElement('tr');
            newRow.classList.add('contact-row');
            newRow.dataset.type = newContact.contact_type;
            newRow.dataset.assignedTo = newContact.assigned_to;

            newRow.innerHTML = `
                <td>${newContact.firstname} ${newContact.lastname}</td>
                <td>${newContact.email}</td>
                <td>${newContact.company}</td>
                <td><span class="badge ${newContact.contact_type.toLowerCase().replace(' ', '-')}" >${newContact.contact_type}</span></td>
                <td><a href="contact_details.php?id=${newContact.id}" class="view">View</a></td>
            `;

            contactTable.appendChild(newRow);
        }

        
    </script>
</body>
</html>





