<?php
session_start();
include "../config/db.php";

// Auth & role check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Check if ID is provided
if(!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$doctor_id = intval($_GET['id']);

// Fetch doctor data
$stmt = $conn->prepare("SELECT * FROM doctor WHERE doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    header("Location: list.php");
    exit();
}

$doctor = $result->fetch_assoc();

// Handle form submission
if(isset($_POST['update'])) {
    $name = trim($_POST['name']);
    $specialization = trim($_POST['specialization']);
    $contact = trim($_POST['contact']);

    $update = $conn->prepare("UPDATE doctor SET name = ?, specialization = ?, contact = ? WHERE doctor_id = ?");
    $update->bind_param("sssi", $name, $specialization, $contact, $doctor_id);
    $update->execute();

    header("Location: list.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Edit Doctor</h3>

<!-- Navigation -->
<div class="mb-3">
    <a href="list.php" class="btn btn-secondary">← Back to List</a>
</div>

<div class="card p-4">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($doctor['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Specialization</label>
            <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($doctor['specialization']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contact</label>
            <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($doctor['contact']); ?>" required>
        </div>

        <button type="submit" name="update" class="btn btn-success">Update Doctor</button>
    </form>
</div>

</body>
</html>