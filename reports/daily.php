<?php
session_start();
include "../config/db.php";
include "../includes/header.php";

$today = date('Y-m-d');
$generatedAt = date('Y-m-d H:i:s');

// Today's appointments
$appointmentsResult = mysqli_query($conn, 
    "SELECT a.*, p.name as patient_name, d.name as doctor_name 
     FROM appointment a
     JOIN patient p ON a.patient_id = p.patient_id
     JOIN doctor d ON a.doctor_id = d.doctor_id
     WHERE a.appointment_date = '$today'");

$appointments = [];
while ($row = mysqli_fetch_assoc($appointmentsResult)) {
    $appointments[] = $row;
}

// Today's payments
$paymentsResult = mysqli_query($conn, 
    "SELECT p.*, pt.name as patient_name 
     FROM payment p
     JOIN invoice i ON p.invoice_id = i.invoice_id
     JOIN patient pt ON i.patient_id = pt.patient_id
     WHERE DATE(p.payment_date) = '$today'");

$payments = [];
while ($row = mysqli_fetch_assoc($paymentsResult)) {
    $payments[] = $row;
}

// Total payments
$total_payments = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(amount_paid), 0) as total 
     FROM payment 
     WHERE DATE(payment_date) = '$today'"))['total'];
?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<div class="main-content">

    <!-- Top Bar -->
    <div class="navbar-top no-print">
        <div>
            <h4><i class="bi bi-calendar-day"></i> Daily Report - <?= date('d M Y') ?></h4>
            <small class="text-muted">Generated on: <?= $generatedAt ?></small>
        </div>

        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <a href="index.php" class="btn btn-secondary">Back to Reports</a>
        </div>
    </div>

    <!-- Printable Header -->
    <div class="d-none d-print-block text-center mb-3">
        <h3>Daily Report</h3>
        <p>Date: <?= date('d M Y') ?></p>
        <p>Generated on: <?= $generatedAt ?></p>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <h5>Today's Appointments</h5>
                <h2><?= count($appointments) ?></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <h5>Today's Payments</h5>
                <h2 class="text-success">Ksh <?= number_format($total_payments, 2) ?></h2>
            </div>
        </div>
    </div>

    <!-- Appointments -->
    <div class="table-card mb-4">
        <h5>Today's Appointments</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($appointments) > 0): ?>
                    <?php foreach($appointments as $row): ?>
                    <tr>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><?= $row['patient_name'] ?></td>
                        <td><?= $row['doctor_name'] ?></td>
                        <td>
                            <span class="badge bg-<?= $row['status'] == 'completed' ? 'success' : 'warning' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No appointments today</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Payments -->
    <div class="table-card">
        <h5>Today's Payments</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Amount</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($payments) > 0): ?>
                    <?php foreach($payments as $row): ?>
                    <tr>
                        <td><?= date('H:i', strtotime($row['payment_date'])) ?></td>
                        <td><?= $row['patient_name'] ?></td>
                        <td>Ksh <?= number_format($row['amount_paid'], 2) ?></td>
                        <td><?= $row['payment_method'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No payments today</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include "../includes/footer.php"; ?>