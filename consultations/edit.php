<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin can edit
if($_SESSION['role'] != 'admin') {
    header("Location: list.php");
    exit();
}

if(!isset($_GET['id'])) {
    die("Invalid request");
}

$id = $_GET['id'];

/* FETCH EXISTING DATA */
$stmt = $conn->prepare("SELECT * FROM consultation WHERE consultation_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Consultation not found");
}

$data = $result->fetch_assoc();

/* HANDLE UPDATE */
if(isset($_POST['update'])){

    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];

    $update = $conn->prepare("
        UPDATE consultation 
        SET diagnosis=?, treatment=? 
        WHERE consultation_id=?
    ");
    $update->bind_param("ssi", $diagnosis, $treatment, $id);

    if($update->execute()){
        header("Location: list.php?success=1");
        exit();
    } else {
        $error = "Update failed!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Consultation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Edit Consultation</h3>

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST" class="card p-4" style="max-width:600px;">

    <div class="mb-3">
        <label class="form-label">Diagnosis</label>
        <textarea name="diagnosis" class="form-control" required><?= $data['diagnosis']; ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Treatment / Prescription</label>
        <textarea name="treatment" class="form-control" required><?= $data['treatment']; ?></textarea>
    </div>

    <button type="submit" name="update" class="btn btn-success">
        Update
    </button>

    <a href="list.php" class="btn btn-danger">Cancel</a>

</form>

</body>
</html>