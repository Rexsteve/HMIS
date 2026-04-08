<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Allowed roles
if($_SESSION['role'] != 'admin' && 
   $_SESSION['role'] != 'receptionist' && 
   $_SESSION['role'] != 'doctor') {
    header("Location: ../dashboard.php");
    exit();
}

$role = $_SESSION['role'];
$doctor_id = $_SESSION['doctor_id'] ?? 0;

/* =========================
   ROLE-BASED QUERY
========================= */
if($role == 'doctor') {

    $stmt = $conn->prepare("
        SELECT appointment.*, 
               patient.name AS patient_name, 
               doctor.name AS doctor_name
        FROM appointment
        JOIN patient ON appointment.patient_id = patient.patient_id
        JOIN doctor ON appointment.doctor_id = doctor.doctor_id
        WHERE appointment.doctor_id = ?
        ORDER BY appointment.appointment_date DESC, appointment.appointment_time DESC
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

} else {

    $sql = "SELECT appointment.*, 
                   patient.name AS patient_name, 
                   doctor.name AS doctor_name
            FROM appointment
            JOIN patient ON appointment.patient_id = patient.patient_id
            JOIN doctor ON appointment.doctor_id = doctor.doctor_id
            ORDER BY appointment.appointment_date DESC, appointment.appointment_time DESC";

    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Appointment List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Appointments</h3>

<!-- Navigation Buttons -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <?php if($role == 'admin' || $role == 'receptionist'): ?>
        <a href="add.php" class="btn btn-primary">
            + Book New Appointment
        </a>
    <?php endif; ?>
</div>

<!-- Success Message -->
<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Appointment saved successfully!
    </div>
<?php endif; ?>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>

    <?php if($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['appointment_id']; ?></td>
            <td><?= $row['patient_name']; ?></td>
            <td><?= $row['doctor_name']; ?></td>
            <td><?= $row['appointment_date']; ?></td>
            <td><?= $row['appointment_time']; ?></td>
            <td>
                <?php if($row['status'] == 'Completed'): ?>
                    <span class="badge bg-success"><?= $row['status']; ?></span>
                <?php elseif($row['status'] == 'Cancelled'): ?>
                    <span class="badge bg-danger"><?= $row['status']; ?></span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark"><?= $row['status']; ?></span>
                <?php endif; ?>
            </td>
            <td>
                <a href="view.php?id=<?= $row['appointment_id']; ?>" 
                   class="btn btn-sm btn-info">View</a>

                <?php if($role != 'doctor'): ?>
                    <a href="edit.php?id=<?= $row['appointment_id']; ?>" 
                       class="btn btn-sm btn-warning">Edit</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center">No appointments found</td>
        </tr>
    <?php endif; ?>

    </tbody>
</table>

</body>
</html>