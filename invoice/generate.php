<?php
session_start();
include "../config/db.php";

// Fetch consultations
$consultations = $conn->query("
    SELECT c.consultation_id, p.patient_id, p.name AS patient_name
    FROM consultation c
    JOIN appointment a ON c.appointment_id = a.appointment_id
    JOIN patient p ON a.patient_id = p.patient_id
    ORDER BY c.created_at DESC
");

if(isset($_POST['generate'])) {

    $consultation_id = $_POST['consultation_id'];
    $consultation_fee = floatval($_POST['consultation_fee']);

    // Get patient from consultation
    $patient_query = $conn->query("
        SELECT p.patient_id 
        FROM consultation c
        JOIN appointment a ON c.appointment_id = a.appointment_id
        JOIN patient p ON a.patient_id = p.patient_id
        WHERE c.consultation_id = $consultation_id
    ");
    $patient = $patient_query->fetch_assoc();
    $patient_id = $patient['patient_id'];

    // Calculate medication total from prescriptions
    $med_query = $conn->query("
        SELECT pr.quantity, dr.price
        FROM prescription pr
        JOIN drug dr ON pr.drug_id = dr.drug_id
        WHERE pr.consultation_id = $consultation_id
    ");

    $medication_total = 0;

    while($row = $med_query->fetch_assoc()) {
        $medication_total += $row['quantity'] * $row['price'];
    }

    $total_amount = $consultation_fee + $medication_total;

    // Insert invoice
    $stmt = $conn->prepare("
        INSERT INTO invoice (patient_id, total_amount, status) 
        VALUES (?, ?, 'unpaid')
    ");
    $stmt->bind_param("id", $patient_id, $total_amount);

    if($stmt->execute()) {
        $invoice_id = $stmt->insert_id;

        header("Location: ../payment/make.php?invoice_id=$invoice_id");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
}
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
        <div class="col-md-6">

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
                            <label>Consultation</label>
                            <select name="consultation_id" class="form-control" required>
                                <option value="">-- Select Consultation --</option>
                                <?php while($c = $consultations->fetch_assoc()): ?>
                                    <option value="<?= $c['consultation_id'] ?>">
                                        <?= $c['patient_name'] ?> (ID: <?= $c['consultation_id'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Consultation Fee (Ksh)</label>
                            <input type="number" name="consultation_fee" class="form-control" required min="0" step="0.01">
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