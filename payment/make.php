<?php
session_start();
include "../config/db.php";

$invoice_id = $_GET['invoice_id'] ?? 0;

// Get invoice details
$invoice = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT i.*, p.name as patient_name,
            COALESCE(SUM(pmt.amount_paid), 0) as total_paid
     FROM invoice i
     JOIN patient p ON i.patient_id = p.patient_id
     LEFT JOIN payment pmt ON i.invoice_id = pmt.invoice_id
     WHERE i.invoice_id = $invoice_id
     GROUP BY i.invoice_id"));

// Calculate remaining balance
$remaining = $invoice['total_amount'] - $invoice['total_paid'];

// Process payment
if(isset($_POST['pay'])) {
    $invoice_id = $_POST['invoice_id'];
    $amount_paid = floatval($_POST['amount_paid']);
    $payment_method = $_POST['payment_method'];

    if($amount_paid <= 0 || $amount_paid > $remaining) {
        $error = "Invalid payment amount. Remaining balance: Ksh $remaining";
    } else {
        // Insert payment record
        $query = "INSERT INTO payment (invoice_id, amount_paid, payment_method) 
                  VALUES ('$invoice_id', '$amount_paid', '$payment_method')";

        if(mysqli_query($conn, $query)) {
            // Check if fully paid now
            $total_paid = mysqli_fetch_assoc(mysqli_query($conn, 
                "SELECT COALESCE(SUM(amount_paid),0) as total FROM payment WHERE invoice_id=$invoice_id"))['total'];
            
            if($total_paid >= $invoice['total_amount']) {
                mysqli_query($conn, "UPDATE invoice SET status='paid' WHERE invoice_id=$invoice_id");
            }

            $success = "Payment of Ksh $amount_paid recorded successfully! Remaining: Ksh " . ($invoice['total_amount'] - $total_paid);
            $remaining = $invoice['total_amount'] - $total_paid; // update for display
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Make Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4>Process Payment</h4>
                </div>
                <div class="card-body">

                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                        <a href="../invoice/list.php" class="btn btn-primary w-100">View All Invoices</a>
                    <?php elseif(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label>Invoice #<?= $invoice_id ?></label>
                        <input type="text" class="form-control" value="Patient: <?= $invoice['patient_name'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Total Amount</label>
                        <input type="text" class="form-control fw-bold" 
                               value="Ksh <?= number_format($invoice['total_amount'], 2) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Total Paid So Far</label>
                        <input type="text" class="form-control fw-bold" 
                               value="Ksh <?= number_format($invoice['total_paid'], 2) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Remaining Balance</label>
                        <input type="text" class="form-control fw-bold text-danger" 
                               value="Ksh <?= number_format($remaining, 2) ?>" readonly>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">

                        <div class="mb-3">
                            <label>Amount to Pay</label>
                            <input type="number" step="0.01" max="<?= $remaining ?>" name="amount_paid" 
                                   class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="">Select Payment Method</option>
                                <option value="Cash">Cash</option>
                                <option value="M-PESA">M-PESA</option>
                                <option value="Card">Card</option>
                                <option value="Insurance">Insurance</option>
                            </select>
                        </div>

                        <button type="submit" name="pay" class="btn btn-success w-100">Confirm Payment</button>
                        <a href="../invoice/list.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>