<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Allow admin & doctor
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'doctor') {
    header("Location: ../dashboard.php");
    exit();
}

if(!isset($_GET['id'])) {
    die("Invalid request");
}

$id = $_GET['id'];

$sql = "
SELECT consultation.*,
       patient.name AS patient_name,
       doctor.name AS doctor_name
FROM consultation
JOIN appointment ON consultation.appointment_id = appointment.appointment_id
JOIN patient ON appointment.patient_id = patient.patient_id
JOIN doctor ON appointment.doctor_id = doctor.doctor_id
WHERE consultation.consultation_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Consultation not found");
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Consultation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Consultation Details</h3>

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<div class="card p-4">

    <p><b>Patient:</b> <?= $data['patient_name']; ?></p>
    <p><b>Doctor:</b> <?= $data['doctor_name']; ?></p>

    <hr>

    <p><b>Diagnosis:</b><br><?= nl2br($data['diagnosis']); ?></p>

    <p><b>Treatment / Prescription:</b><br><?= nl2br($data['treatment']); ?></p>

    <hr>

    <p><b>Date:</b> <?= date('d M Y H:i', strtotime($data['created_at'])); ?></p>

</div>

</body>
</html>