<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'doctor') {
    header("Location: ../dashboard.php");
    exit();
}

$role = $_SESSION['role'];

/* ✅ Get doctor_id safely */
$doctor_id = isset($_SESSION['doctor_id']) ? intval($_SESSION['doctor_id']) : 0;

/* ✅ Base query (now uses consultation.doctor_id) */
$sql = "
SELECT consultation.*,
       patient.name AS patient_name,
       doctor.name AS doctor_name
FROM consultation
JOIN appointment ON consultation.appointment_id = appointment.appointment_id
JOIN patient ON appointment.patient_id = patient.patient_id
JOIN doctor ON consultation.doctor_id = doctor.doctor_id
";

/* ✅ Filter consultations for logged-in doctor */
if($role == 'doctor' && $doctor_id > 0) {
    $sql .= " WHERE consultation.doctor_id = $doctor_id ";
}

$sql .= " ORDER BY consultation.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Consultations</h3>

<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>

    <?php if($role == 'admin' || $role == 'doctor'): ?>
        <a href="add.php" class="btn btn-primary">+ Add Consultation</a>
    <?php endif; ?>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Consultation saved successfully!</div>
<?php endif; ?>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Diagnosis</th>
            <th>Treatment</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['consultation_id']; ?></td>
                <td><?= $row['patient_name']; ?></td>
                <td><?= $row['doctor_name']; ?></td>
                <td><?= $row['diagnosis']; ?></td>
                <td><?= $row['treatment']; ?></td>
                <td><?= date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                <td>
                    <a href="view.php?id=<?= $row['consultation_id']; ?>" class="btn btn-sm btn-info">View</a>
                    <a href="../prescription/add.php?consultation_id=<?= $row['consultation_id']; ?>" class="btn btn-sm btn-success">Prescribe</a>

                    <?php if($role == 'admin'): ?>
                        <a href="edit.php?id=<?= $row['consultation_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No consultations found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>