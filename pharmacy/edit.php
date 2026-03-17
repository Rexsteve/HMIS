<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin & pharmacist allowed
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'pharmacist') {
    header("Location: ../dashboard.php");
    exit();
}

if(!isset($_GET['id'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);

/* FETCH EXISTING DRUG */
$stmt = $conn->prepare("SELECT * FROM drug WHERE drug_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Drug not found");
}

$drug = $result->fetch_assoc();

/* HANDLE UPDATE */
if(isset($_POST['update'])){

    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $expiry = $_POST['expiry_date'];

    // Optional: prevent negative stock
    if($quantity < 0){
        $error = "Quantity cannot be negative!";
    } else {

        $update = $conn->prepare("
            UPDATE drug 
            SET name=?, quantity=?, price=?, expiry_date=? 
            WHERE drug_id=?
        ");
        $update->bind_param("sidsi", $name, $quantity, $price, $expiry, $id);

        if($update->execute()){
            header("Location: list.php?success=2");
            exit();
        } else {
            $error = "Update failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Drug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Edit Drug</h3>

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST" class="card p-4" style="max-width:600px;">

    <div class="mb-3">
        <label class="form-label">Drug Name</label>
        <input type="text" name="name" class="form-control"
               value="<?= $drug['name']; ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" class="form-control"
               value="<?= $drug['quantity']; ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Price (Ksh)</label>
        <input type="number" step="0.01" name="price" class="form-control"
               value="<?= $drug['price']; ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control"
               value="<?= $drug['expiry_date']; ?>" required>
    </div>

    <button type="submit" name="update" class="btn btn-success">
        Update Drug
    </button>

    <a href="list.php" class="btn btn-danger">Cancel</a>

</form>

</body>
</html>