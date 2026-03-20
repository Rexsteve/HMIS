<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'cashier') {
    header("Location: ../dashboard.php");
    exit();
}

$payments = mysqli_query($conn, 
    "SELECT p.*, i.invoice_id, i.total_amount, pt.name as patient_name 
     FROM payment p
     JOIN invoice i ON p.invoice_id = i.invoice_id
     JOIN patient pt ON i.patient_id = pt.patient_id
     ORDER BY p.payment_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Payment History</h3>

<a href="../dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
<a href="make.php" class="btn btn-primary mb-3">New Payment</a>

<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <th>Invoice</th>
        <th>Patient</th>
        <th>Amount</th>
        <th>Method</th>
        <th>Date</th>
        <th>Action</th>
    </tr>

    <?php if(mysqli_num_rows($payments) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($payments)): ?>
        <tr>
            <td>#<?= $row['payment_id'] ?></td>
            <td>#<?= $row['invoice_id'] ?></td>
            <td><?= $row['patient_name'] ?></td>
            <td>Ksh <?= number_format($row['amount_paid'], 2) ?></td>
            <td><?= $row['payment_method'] ?></td>
            <td><?= date('d M Y H:i', strtotime($row['payment_date'])) ?></td>
            <td>
                <a href="view.php?payment_id=<?= $row['payment_id'] ?>" 
                class="btn btn-sm btn-info">View</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center">No payments found</td>
        </tr>
    <?php endif; ?>

</table>

</body>
</html>