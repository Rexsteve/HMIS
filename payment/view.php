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

if(!isset($_GET['payment_id'])) {
    die("Payment ID not provided.");
}

$payment_id = $_GET['payment_id'];

// Fetch payment + invoice + patient
$query = mysqli_query($conn, "
    SELECT p.*, 
           i.invoice_id, i.total_amount, i.status, i.created_at AS invoice_date,
           pt.name AS patient_name, pt.gender, pt.dob, pt.contact, pt.address
    FROM payment p
    JOIN invoice i ON p.invoice_id = i.invoice_id
    JOIN patient pt ON i.patient_id = pt.patient_id
    WHERE p.payment_id = '$payment_id'
");

if(mysqli_num_rows($query) == 0) {
    die("Payment not found.");
}

$data = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
    <h3 class="mb-4">Payment Details</h3>

    <a href="list.php" class="btn btn-secondary mb-3">Back to Payments</a>

    <div class="card">
        <div class="card-body">

            <h5>Patient Information</h5>
            <p><strong>Name:</strong> <?= $data['patient_name'] ?></p>
            <p><strong>Gender:</strong> <?= $data['gender'] ?></p>
            <p><strong>Date of Birth:</strong> <?= $data['dob'] ?></p>
            <p><strong>Contact:</strong> <?= $data['contact'] ?></p>
            <p><strong>Address:</strong> <?= $data['address'] ?></p>

            <hr>

            <h5>Invoice Information</h5>
            <p><strong>Invoice ID:</strong> #<?= $data['invoice_id'] ?></p>
            <p><strong>Status:</strong> <?= ucfirst($data['status']) ?></p>
            <p><strong>Invoice Date:</strong> <?= date('d M Y H:i', strtotime($data['invoice_date'])) ?></p>
            <p><strong>Total Amount:</strong> Ksh <?= number_format($data['total_amount'], 2) ?></p>

            <hr>

            <h5>Payment Information</h5>
            <p><strong>Payment ID:</strong> #<?= $data['payment_id'] ?></p>
            <p><strong>Amount Paid:</strong> Ksh <?= number_format($data['amount_paid'], 2) ?></p>
            <p><strong>Method:</strong> <?= $data['payment_method'] ?></p>
            <p><strong>Date:</strong> <?= date('d M Y H:i', strtotime($data['payment_date'])) ?></p>

        </div>
    </div>
</div>

</body>
</html>