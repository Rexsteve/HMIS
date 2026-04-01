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

$id = intval($_GET['id']);

$sql = "
SELECT consultation.*,
       patient.patient_id,
       patient.name AS patient_name,
       patient.dob,
       patient.gender,
       patient.weight,
       patient.height,
       patient.blood_group,
       patient.allergies,
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

/* Calculate Age */
$age = null;
if(!empty($data['dob'])) {
    $age = date_diff(date_create($data['dob']), date_create('today'))->y;
}

/* Calculate BMI */
$bmi = null;
if(!empty($data['weight']) && !empty($data['height'])) {
    $h = $data['height'] / 100;
    if($h > 0) {
        $bmi = $data['weight'] / ($h * $h);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Consultation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 10px;
            border-radius: 5px;
        }

        .danger-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
        }

        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>

<body class="p-4">

<h3>Consultation Details</h3>

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<div class="row">

    <!-- LEFT: CLINICAL SNAPSHOT -->
    <div class="col-md-4">

        <div class="card p-3 mb-3">
            <h5>Patient Clinical Snapshot</h5>

            <p><b>Name:</b> <?= $data['patient_name']; ?></p>
            <p><b>Gender:</b> <?= $data['gender']; ?></p>
            <p><b>Age:</b> <?= $age ?? 'N/A'; ?> years</p>

            <hr>

            <p><b>Weight:</b> <?= $data['weight'] ? $data['weight'].' kg' : 'N/A'; ?></p>
            <p><b>Height:</b> <?= $data['height'] ? $data['height'].' cm' : 'N/A'; ?></p>
            <p><b>BMI:</b> <?= $bmi ? number_format($bmi,1) : 'N/A'; ?></p>

            <p><b>Blood Group:</b> <?= $data['blood_group'] ?? 'N/A'; ?></p>

            <!-- ALLERGY ALERT -->
            <?php if(!empty($data['allergies'])): ?>
                <div class="danger-box mt-2">
                    <b>⚠ Allergies:</b><br>
                    <?= nl2br($data['allergies']); ?>
                </div>
            <?php else: ?>
                <div class="info-box mt-2">
                    No known allergies recorded
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- RIGHT: CONSULTATION -->
    <div class="col-md-8">

        <div class="card p-4">

            <p><b>Doctor:</b> <?= $data['doctor_name']; ?></p>

            <hr>

            <h5>Diagnosis</h5>
            <p><?= nl2br($data['diagnosis']); ?></p>

            <h5>Treatment / Prescription</h5>
            <p><?= nl2br($data['treatment']); ?></p>

            <hr>

            <p><b>Date:</b> <?= date('d M Y H:i', strtotime($data['created_at'])); ?></p>

        </div>

    </div>

</div>

</body>
</html>