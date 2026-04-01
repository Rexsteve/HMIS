<?php
session_start();
include "../config/db.php";
include "../includes/header.php";

$generatedAt = date('Y-m-d H:i:s');

/* =========================
   WEEK RANGE
========================= */
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));

if (isset($_GET['week_start'])) {
    $week_start = $_GET['week_start'];
    $week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));
}

$prev_week = date('Y-m-d', strtotime($week_start . ' -7 days'));
$next_week = date('Y-m-d', strtotime($week_start . ' +7 days'));

/* =========================
   APPOINTMENTS (SAFE)
========================= */
$appointmentsResult = mysqli_query($conn,
    "SELECT a.*, p.name as patient_name, d.name as doctor_name
     FROM appointment a
     JOIN patient p ON a.patient_id = p.patient_id
     JOIN doctor d ON a.doctor_id = d.doctor_id
     WHERE a.appointment_date BETWEEN '$week_start' AND '$week_end'
     ORDER BY a.appointment_date, a.appointment_time");

$appointments = [];
while ($row = mysqli_fetch_assoc($appointmentsResult)) {
    $appointments[] = $row;
}

/* =========================
   PAYMENTS (SAFE)
========================= */
$paymentsResult = mysqli_query($conn,
    "SELECT p.*, pt.name as patient_name
     FROM payment p
     JOIN invoice i ON p.invoice_id = i.invoice_id
     JOIN patient pt ON i.patient_id = pt.patient_id
     WHERE DATE(p.payment_date) BETWEEN '$week_start' AND '$week_end'
     ORDER BY p.payment_date");

$payments = [];
$total_payments = 0;

while ($row = mysqli_fetch_assoc($paymentsResult)) {
    $payments[] = $row;
    $total_payments += $row['amount_paid'];
}

$payment_count = count($payments);
$total_appointments = count($appointments);

/* =========================
   DAILY BREAKDOWN
========================= */
$daily_totals = [];

for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime($week_start . " +$i days"));
    $day_name = date('D', strtotime($date));

    $total = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COALESCE(SUM(amount_paid),0) as total
         FROM payment
         WHERE DATE(payment_date) = '$date'"
    ))['total'];

    $daily_totals[] = [
        'day' => $day_name,
        'date' => $date,
        'total' => $total
    ];
}
?>

<style>
@media print {
    .no-print { display: none !important; }
}
</style>

<div class="main-content">

    <!-- TOP BAR -->
    <div class="navbar-top no-print">
        <div>
            <h4><i class="bi bi-calendar-week"></i> Weekly Report</h4>
            <small>Generated on: <?= $generatedAt ?></small>
        </div>

        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Print
            </button>

            <a href="weekly.php?week_start=<?= $prev_week ?>" class="btn btn-outline-primary btn-sm">← Prev</a>
            <a href="weekly.php" class="btn btn-outline-secondary btn-sm">Current</a>
            <a href="weekly.php?week_start=<?= $next_week ?>" class="btn btn-outline-primary btn-sm">Next →</a>
            <a href="index.php" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>

    <!-- PRINT HEADER -->
    <div class="d-none d-print-block text-center mb-3">
        <h3>Weekly Report</h3>
        <p><?= date('d M Y', strtotime($week_start)) ?> - <?= date('d M Y', strtotime($week_end)) ?></p>
        <p>Generated on: <?= $generatedAt ?></p>
    </div>

    <!-- RANGE -->
    <div class="alert alert-info">
        <strong><?= date('d M Y', strtotime($week_start)) ?> - <?= date('d M Y', strtotime($week_end)) ?></strong>
    </div>

    <!-- SUMMARY -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <h5>Appointments</h5>
                <h2><?= $total_appointments ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <h5>Payments</h5>
                <h2 class="text-success">Ksh <?= number_format($total_payments,2) ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <h5>Transactions</h5>
                <h2><?= $payment_count ?></h2>
            </div>
        </div>
    </div>

    <!-- DAILY BREAKDOWN -->
    <div class="table-card mb-4">
        <h5>Daily Breakdown</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Date</th>
                    <th>Payments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_totals as $day): ?>
                <tr>
                    <td><strong><?= $day['day'] ?></strong></td>
                    <td><?= date('d M Y', strtotime($day['date'])) ?></td>
                    <td>Ksh <?= number_format($day['total'],2) ?></td>
                </tr>
                <?php endforeach; ?>

                <tr class="fw-bold">
                    <td colspan="2" class="text-end">Total</td>
                    <td>Ksh <?= number_format($total_payments,2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- APPOINTMENTS -->
    <div class="table-card mb-4">
        <h5>Appointments</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_appointments > 0): ?>
                    <?php foreach ($appointments as $row): ?>
                    <tr>
                        <td><?= date('D', strtotime($row['appointment_date'])) ?></td>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><?= $row['patient_name'] ?></td>
                        <td><?= $row['doctor_name'] ?></td>
                        <td>
                            <span class="badge bg-<?= $row['status']=='completed'?'success':'warning' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No appointments this week</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAYMENTS -->
    <div class="table-card">
        <h5>Payments</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Amount</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payment_count > 0): ?>
                    <?php foreach ($payments as $row): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['payment_date'])) ?></td>
                        <td><?= $row['patient_name'] ?></td>
                        <td>Ksh <?= number_format($row['amount_paid'],2) ?></td>
                        <td><?= $row['payment_method'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No payments this week</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include "../includes/footer.php"; ?>