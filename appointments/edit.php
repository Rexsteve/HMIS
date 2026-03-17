<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin & receptionist can edit
if($_SESSION['role'] == 'doctor') {
    header("Location: list.php");
    exit();
}

if(!isset($_GET['id'])) {
    echo "Appointment ID not provided";
    exit();
}

$appointment_id = $_GET['id'];

/* Fetch appointment */
$result = $conn->query("SELECT * FROM appointment WHERE appointment_id=$appointment_id");

if($result->num_rows == 0){
    echo "Appointment not found";
    exit();
}

$appointment = $result->fetch_assoc();

/* Fetch patients */
$patients = $conn->query("SELECT * FROM patient");

/* Fetch doctors */
$doctors = $conn->query("SELECT * FROM doctor");

/* Update */
if(isset($_POST['update'])){

    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $status = $_POST['status'];

    $conn->query("
        UPDATE appointment 
        SET 
        patient_id='$patient_id',
        doctor_id='$doctor_id',
        appointment_date='$date',
        appointment_time='$time',
        status='$status'
        WHERE appointment_id=$appointment_id
    ");

    header("Location: list.php?success=updated");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Appointment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h3>Edit Appointment</h3>

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<form method="POST">

<div class="mb-3">
<label>Patient</label>
<select name="patient_id" class="form-control">
<?php while($p = $patients->fetch_assoc()): ?>
<option value="<?= $p['patient_id']; ?>" 
<?= $p['patient_id']==$appointment['patient_id'] ? 'selected':'' ?>>
<?= $p['name']; ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="mb-3">
<label>Doctor</label>
<select name="doctor_id" class="form-control">
<?php while($d = $doctors->fetch_assoc()): ?>
<option value="<?= $d['doctor_id']; ?>" 
<?= $d['doctor_id']==$appointment['doctor_id'] ? 'selected':'' ?>>
<?= $d['name']; ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="mb-3">
<label>Date</label>
<input type="date" name="appointment_date" class="form-control"
value="<?= $appointment['appointment_date']; ?>">
</div>

<div class="mb-3">
<label>Time</label>
<input type="time" name="appointment_time" class="form-control"
value="<?= $appointment['appointment_time']; ?>">
</div>

<div class="mb-3">
<label>Status</label>
<select name="status" class="form-control">
<option value="Pending" <?= $appointment['status']=='Pending'?'selected':'' ?>>Pending</option>
<option value="Completed" <?= $appointment['status']=='Completed'?'selected':'' ?>>Completed</option>
<option value="Cancelled" <?= $appointment['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
</select>
</div>

<button type="submit" name="update" class="btn btn-primary">
Update Appointment
</button>

</form>

</body>
</html>