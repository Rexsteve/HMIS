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

$patient_id = $_GET['id'];

/* Fetch patient data */
$result = $conn->query("SELECT * FROM patient WHERE patient_id=$patient_id");

if($result->num_rows == 0){
    echo "Patient not found";
    exit();
}

$patient = $result->fetch_assoc();

/* Update patient */
if(isset($_POST['update'])){

    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    $conn->query("
        UPDATE patient 
        SET 
        name='$name',
        gender='$gender',
        dob='$dob',
        contact='$contact',
        address='$address'
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

<option value="Male" 
<?= $patient['gender']=='Male' ? 'selected':'' ?>>Male</option>

<option value="Female" 
<?= $patient['gender']=='Female' ? 'selected':'' ?>>Female</option>

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

<button type="submit" name="update" class="btn btn-primary">
Update Patient
</button>

</form>

</body>
</html>