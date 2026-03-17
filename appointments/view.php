<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['id'])) {
    echo "Appointment ID not provided";
    exit();
}

$appointment_id = $_GET['id'];

/* Fetch appointment details */
$sql = "
SELECT appointment.*, 
       patient.name AS patient_name, 
       doctor.name AS doctor_name
FROM appointment
JOIN patient ON appointment.patient_id = patient.patient_id
JOIN doctor ON appointment.doctor_id = doctor.doctor_id
WHERE appointment.appointment_id = $appointment_id
";

$result = $conn->query($sql);

if($result->num_rows == 0){
    echo "Appointment not found";
    exit();
}

$appointment = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>View Appointment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h3>Appointment Details</h3>

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<table class="table table-bordered">

<tr>
<td><b>Patient</b></td>
<td><?= $appointment['patient_name']; ?></td>
</tr>

<tr>
<td><b>Doctor</b></td>
<td><?= $appointment['doctor_name']; ?></td>
</tr>

<tr>
<td><b>Date</b></td>
<td><?= $appointment['appointment_date']; ?></td>
</tr>

<tr>
<td><b>Time</b></td>
<td><?= $appointment['appointment_time']; ?></td>
</tr>

<tr>
<td><b>Status</b></td>
<td><?= $appointment['status']; ?></td>
</tr>

</table>

</body>
</html>