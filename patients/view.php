<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['id'])) {
    echo "Patient ID not provided.";
    exit();
}

$patient_id = intval($_GET['id']);

/* Get Patient Details */
$patient = $conn->query("
    SELECT * 
    FROM patient 
    WHERE patient_id=$patient_id
")->fetch_assoc();

/* Calculate BMI (if data exists) */
$bmi = null;
if(!empty($patient['weight']) && !empty($patient['height'])) {
    $height_m = $patient['height'] / 100; // assuming height in cm
    if($height_m > 0) {
        $bmi = $patient['weight'] / ($height_m * $height_m);
    }
}

/* Get Medical History (Consultations) */
$history = $conn->query("
SELECT 
    c.consultation_id,
    c.diagnosis,
    c.treatment,
    c.notes,
    c.consultation_date
FROM consultation c
JOIN appointment a ON c.appointment_id = a.appointment_id
WHERE a.patient_id = $patient_id
ORDER BY c.consultation_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Patient Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<h3>Patient Information</h3>

<table class="table table-bordered">
<tr>
<td><b>Name</b></td>
<td><?= $patient['name']; ?></td>
</tr>

<tr>
<td><b>Gender</b></td>
<td><?= $patient['gender']; ?></td>
</tr>

<tr>
<td><b>Date of Birth</b></td>
<td><?= $patient['dob']; ?></td>
</tr>

<tr>
<td><b>Contact</b></td>
<td><?= $patient['contact']; ?></td>
</tr>

<tr>
<td><b>Address</b></td>
<td><?= $patient['address']; ?></td>
</tr>

<!-- NEW CLINICAL FIELDS -->
<tr>
<td><b>Weight</b></td>
<td>
    <?= !empty($patient['weight']) ? $patient['weight'] . " kg" : "Not recorded"; ?>
</td>
</tr>

<tr>
<td><b>Height</b></td>
<td>
    <?= !empty($patient['height']) ? $patient['height'] . " cm" : "Not recorded"; ?>
</td>
</tr>

<tr>
<td><b>BMI</b></td>
<td>
    <?= $bmi ? number_format($bmi, 1) : "N/A"; ?>
</td>
</tr>

<tr>
<td><b>Blood Group</b></td>
<td>
    <?= !empty($patient['blood_group']) ? $patient['blood_group'] : "Not recorded"; ?>
</td>
</tr>

<tr>
<td><b>Allergies</b></td>
<td>
    <?= !empty($patient['allergies']) ? $patient['allergies'] : "None recorded"; ?>
</td>
</tr>

</table>

<h3 class="mt-4">Medical History</h3>

<table class="table table-bordered">
<tr>
<th>Date</th>
<th>Diagnosis</th>
<th>Treatment</th>
<th>Notes</th>
</tr>

<?php if($history->num_rows > 0): ?>

    <?php while($row = $history->fetch_assoc()): ?>
    <tr>
        <td><?= $row['consultation_date']; ?></td>
        <td><?= $row['diagnosis']; ?></td>
        <td><?= $row['treatment']; ?></td>
        <td><?= $row['notes']; ?></td>
    </tr>
    <?php endwhile; ?>

<?php else: ?>
<tr>
    <td colspan="4" class="text-center">No medical history found</td>
</tr>
<?php endif; ?>

</table>

</body>
</html>