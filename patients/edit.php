<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['id'])) {
    echo "Patient ID not provided";
    exit();
}

$patient_id = intval($_GET['id']);

/* Fetch patient data */
$result = $conn->query("SELECT * FROM patient WHERE patient_id=$patient_id");

if($result->num_rows == 0){
    echo "Patient not found";
    exit();
}

$patient = $result->fetch_assoc();

/* Update patient */
if(isset($_POST['update'])){

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = $_POST['dob'];
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    /* NEW CLINICAL FIELDS */
    $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : null;
    $height = !empty($_POST['height']) ? floatval($_POST['height']) : null;
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);
    $allergies = mysqli_real_escape_string($conn, $_POST['allergies']);

    $conn->query("
        UPDATE patient 
        SET 
            name='$name',
            gender='$gender',
            dob='$dob',
            contact='$contact',
            address='$address',
            weight=" . ($weight !== null ? $weight : "NULL") . ",
            height=" . ($height !== null ? $height : "NULL") . ",
            blood_group='$blood_group',
            allergies='$allergies'
        WHERE patient_id=$patient_id
    ");

    header("Location: list.php?success=updated");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Patient</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h3>Edit Patient</h3>

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<form method="POST">

<div class="mb-3">
<label>Name</label>
<input type="text" name="name" class="form-control"
value="<?= $patient['name']; ?>" required>
</div>

<div class="mb-3">
<label>Gender</label>
<select name="gender" class="form-control">
    <option value="Male" <?= $patient['gender']=='Male' ? 'selected':'' ?>>Male</option>
    <option value="Female" <?= $patient['gender']=='Female' ? 'selected':'' ?>>Female</option>
</select>
</div>

<div class="mb-3">
<label>Date of Birth</label>
<input type="date" name="dob" class="form-control"
value="<?= $patient['dob']; ?>">
</div>

<div class="mb-3">
<label>Contact</label>
<input type="text" name="contact" class="form-control"
value="<?= $patient['contact']; ?>">
</div>

<div class="mb-3">
<label>Address</label>
<textarea name="address" class="form-control"><?= $patient['address']; ?></textarea>
</div>

<!-- NEW CLINICAL FIELDS -->

<div class="row">
    <div class="col-md-4 mb-3">
        <label>Weight (kg)</label>
        <input type="number" step="0.1" name="weight" class="form-control"
        value="<?= $patient['weight'] ?? '' ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label>Height (cm)</label>
        <input type="number" step="0.1" name="height" class="form-control"
        value="<?= $patient['height'] ?? '' ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label>Blood Group</label>
        <select name="blood_group" class="form-control">
            <option value="">-- Select --</option>
            <?php
            $groups = ["A+","A-","B+","B-","AB+","AB-","O+","O-"];
            foreach($groups as $g){
                $selected = ($patient['blood_group'] == $g) ? "selected" : "";
                echo "<option value='$g' $selected>$g</option>";
            }
            ?>
        </select>
    </div>
</div>

<div class="mb-3">
<label>Allergies</label>
<textarea name="allergies" class="form-control" placeholder="e.g. Penicillin, peanuts"><?= $patient['allergies'] ?? '' ?></textarea>
</div>

<button type="submit" name="update" class="btn btn-primary">
Update Patient
</button>

</form>

</body>
</html>