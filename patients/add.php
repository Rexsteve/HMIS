<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin & receptionist allowed
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'receptionist') {
    header("Location: ../dashboard.php");
    exit();
}

if(isset($_POST['submit'])) {

    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    /* NEW CLINICAL FIELDS */
    $weight = !empty($_POST['weight']) ? $_POST['weight'] : null;
    $height = !empty($_POST['height']) ? $_POST['height'] : null;
    $blood_group = $_POST['blood_group'];
    $allergies = $_POST['allergies'];

    $sql = "INSERT INTO patient 
        (name, gender, dob, contact, address, weight, height, blood_group, allergies)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sssssddss",
        $name,
        $gender,
        $dob,
        $contact,
        $address,
        $weight,
        $height,
        $blood_group,
        $allergies
    );

    if($stmt->execute()) {
        header("Location: list.php?success=1");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Add Patient</h3>

<!-- Navigation Buttons -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    <a href="list.php" class="btn btn-dark">← Back to Patient List</a>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
<?php endif; ?>

<form method="POST" class="card p-4" style="max-width:700px;">

    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="dob" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Contact</label>
        <input type="text" name="contact" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control">
    </div>

    <!-- NEW CLINICAL VITALS -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Weight (kg)</label>
            <input type="number" step="0.1" name="weight" class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Height (cm)</label>
            <input type="number" step="0.1" name="height" class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Blood Group</label>
            <select name="blood_group" class="form-select">
                <option value="">-- Select --</option>
                <option>A+</option>
                <option>A-</option>
                <option>B+</option>
                <option>B-</option>
                <option>AB+</option>
                <option>AB-</option>
                <option>O+</option>
                <option>O-</option>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Allergies</label>
        <textarea name="allergies" class="form-control" placeholder="e.g. Penicillin, peanuts"></textarea>
    </div>

    <button type="submit" name="submit" class="btn btn-success">
        Save Patient
    </button>

    <a href="list.php" class="btn btn-danger">
        Cancel
    </a>

</form>

</body>
</html>