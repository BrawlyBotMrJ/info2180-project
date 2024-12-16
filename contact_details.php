<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
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

// Fetch contact details
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $contact_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT c.*, CONCAT(u.firstname, ' ', u.lastname) AS created_by FROM contacts c
                            JOIN users u ON c.created_by = u.id WHERE c.id = ?");
    $stmt->execute([$contact_id]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$contact) {
        die("Error: Contact not found.");
    }

    // Fetch notes for this contact
    $notes_stmt = $conn->prepare("SELECT n.*, u.firstname, u.lastname 
                             FROM notes n 
                             JOIN users u ON n.created_by = u.id
                             WHERE n.contact_id = ?");
    $notes_stmt->execute([$contact_id]);
    $notes = $notes_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add new note
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_note'])) {
    $note = trim($_POST['new_note']);
    
    // Use logged-in user ID as the creator
    $created_by = $user_id;

    // Insert new note
    $stmt = $conn->prepare("INSERT INTO notes (contact_id, message, created_by, created_at) 
                            VALUES (?, ?, ?, NOW())");
    $stmt->execute([$contact_id, $note, $created_by]);
    
    // Redirect to avoid form re-submission
    header("Location: contact_details.php?id=$contact_id");
    exit;
}

// Update contact type
if (isset($_POST['switch_type'])) {
    $new_type = $contact['contact_type'] === 'Sales Lead' ? 'Support' : 'Sales Lead';
    $stmt = $conn->prepare("UPDATE contacts SET contact_type = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_type, $contact_id]);
    header("Location: contact_details.php?id=$contact_id");
    exit;
}

// Assign contact to self
if (isset($_POST['assign_to_me'])) {
    $stmt = $conn->prepare("UPDATE contacts SET assigned_to = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$user_id, $contact_id]);
    header("Location: contact_details.php?id=$contact_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Details</title>
    <link rel="stylesheet" href="contact_details.css">
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($contact['title'] . ' ' . $contact['firstname'] . ' ' . $contact['lastname']) ?></h1>
        <p><strong>Email:</strong> <?= htmlspecialchars($contact['email']) ?></p>
        <p><strong>Company:</strong> <?= htmlspecialchars($contact['company']) ?></p>
        <p><strong>Telephone:</strong> <?= htmlspecialchars($contact['telephone']) ?></p>
        <p><strong>Created By:</strong> <?= htmlspecialchars($contact['created_by']) ?></p>
        <p><strong>Date Last Updated:</strong> <?= htmlspecialchars($contact['updated_at']) ?></p>
        <p><strong>Assigned To:</strong> <?= htmlspecialchars($contact['assigned_to']) ?></p>

        <!-- Buttons for actions -->
        <form method="POST">
            <button type="submit" name="assign_to_me">Assign to Me</button>
            <button type="submit" name="switch_type">
                Switch to <?= $contact['contact_type'] === 'Sales Lead' ? 'Support' : 'Sales Lead' ?>
            </button>
        </form>

        <h2>Notes</h2>
        <?php if (!empty($notes)): ?>
            <?php foreach ($notes as $note): ?>
                <div>
                    <p><strong><?= htmlspecialchars($note['firstname'] . ' ' . $note['lastname']) ?></strong></p>
                    <p><?= htmlspecialchars($note['message']) ?></p>
                    <p><small><?= htmlspecialchars($note['created_at']) ?></small></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No notes available for this contact.</p>
        <?php endif; ?>

        <h3>Add a Note</h3>
        <form method="POST">
            <textarea name="new_note" placeholder="Enter details here" required></textarea><br>
            <button type="submit">Add Note</button>
        </form>
    </div>
</body>
</html>
