<?php
session_start();
include "../config/db.php";

// Handle form submission
if(isset($_POST['generate'])) {
    $patient_id = $_POST['patient_id'];
    $consultation_fee = floatval($_POST['consultation_fee']);
    $medication_total = floatval($_POST['medication_total']);
    $total_amount = $consultation_fee + $medication_total;

    $query = "INSERT INTO invoice (patient_id, total_amount, status) 
              VALUES (?, ?, 'unpaid')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("id", $patient_id, $total_amount);

    if($stmt->execute()) {
        $invoice_id = $stmt->insert_id;
        header("Location: ../payment/make.php?invoice_id=$invoice_id");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
}

// Fetch all patients for dropdown
$patients = $conn->query("SELECT patient_id, name FROM patient ORDER BY name ASC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Generate Invoice</h4>
                </div>
                <div class="card-body">

                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label>Patient</label>
                            <select name="patient_id" class="form-control" required>
                                <option value="">-- Select Patient --</option>
                                <?php while($p = $patients->fetch_assoc()): ?>
                                    <option value="<?= $p['patient_id'] ?>">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Consultation Fee (Ksh)</label>
                            <input type="number" name="consultation_fee" class="form-control" 
                                   placeholder="Enter consultation fee" required min="0" step="0.01">
                        </div>

                        <div class="mb-3">
                            <label>Medication Total (Ksh)</label>
                            <input type="number" name="medication_total" class="form-control" 
                                   placeholder="Enter medication total" required min="0" step="0.01">
                        </div>

                        <button type="submit" name="generate" class="btn btn-primary w-100">
                            Generate Invoice
                        </button>

                        <a href="../invoice/list.php" class="btn btn-secondary w-100 mt-2">
                            Back to Invoice List
                        </a>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>