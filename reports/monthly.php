<?php
session_start();
include "../config/db.php";
include "../includes/header.php";

$selected_month = $_GET['month'] ?? date('m');
$selected_year = $_GET['year'] ?? date('Y');

$generatedAt = date('Y-m-d H:i:s');

$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

$month_start = "$selected_year-$selected_month-01";
$month_end = date('Y-m-t', strtotime($month_start));

$prev_month = $selected_month == 1 ? 12 : $selected_month - 1;
$prev_year = $selected_month == 1 ? $selected_year - 1 : $selected_year;
$next_month = $selected_month == 12 ? 1 : $selected_month + 1;
$next_year = $selected_month == 12 ? $selected_year + 1 : $selected_year;

/* =========================
   APPOINTMENTS (SAFE ARRAY)
========================= */
$appointmentsResult = mysqli_query($conn, 
    "SELECT a.*, p.name as patient_name, d.name as doctor_name
     FROM appointment a
     JOIN patient p ON a.patient_id = p.patient_id
     JOIN doctor d ON a.doctor_id = d.doctor_id
     WHERE a.appointment_date BETWEEN '$month_start' AND '$month_end'
     ORDER BY a.appointment_date, a.appointment_time");

$appointments = [];
while ($row = mysqli_fetch_assoc($appointmentsResult)) {
    $appointments[] = $row;
}

/* =========================
   PAYMENTS (SAFE ARRAY)
========================= */
$paymentsResult = mysqli_query($conn, 
    "SELECT p.*, pt.name as patient_name
     FROM payment p
     JOIN invoice i ON p.invoice_id = i.invoice_id
     JOIN patient pt ON i.patient_id = pt.patient_id
     WHERE DATE(p.payment_date) BETWEEN '$month_start' AND '$month_end'
     ORDER BY p.payment_date");

$payments = [];
$total_payments = 0;

while ($row = mysqli_fetch_assoc($paymentsResult)) {
    $payments[] = $row;
    $total_payments += $row['amount_paid'];
}

/* =========================
   INVOICES (SAFE ARRAY)
========================= */
$invoicesResult = mysqli_query($conn, 
    "SELECT i.*, p.name as patient_name
     FROM invoice i
     JOIN patient p ON i.patient_id = p.patient_id
     WHERE DATE(i.created_at) BETWEEN '$month_start' AND '$month_end'
     ORDER BY i.created_at");

$invoices = [];
$total_invoiced = 0;
$paid_invoices = 0;
$unpaid_invoices = 0;

while ($row = mysqli_fetch_assoc($invoicesResult)) {
    $invoices[] = $row;
    $total_invoiced += $row['total_amount'];

    if ($row['status'] == 'paid') $paid_invoices++;
    else $unpaid_invoices++;
}

$payment_count = count($payments);
$total_appointments = count($appointments);
$invoice_count = count($invoices);

/* =========================
   WEEKLY BREAKDOWN
========================= */
$weekly_data = [];
$week_start = $month_start;

while (strtotime($week_start) <= strtotime($month_end)) {
    $week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));
    if (strtotime($week_end) > strtotime($month_end)) {
        $week_end = $month_end;
    }

    $week_payments = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COALESCE(SUM(amount_paid),0) as total
         FROM payment
         WHERE DATE(payment_date) BETWEEN '$week_start' AND '$week_end'"
    ))['total'];

    $weekly_data[] = [
        'start' => $week_start,
        'end' => $week_end,
        'total' => $week_payments
    ];

    $week_start = date('Y-m-d', strtotime($week_start . ' +7 days'));
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
            <h4><i class="bi bi-calendar-month"></i>
                Monthly Report - <?= $months[(int)$selected_month] ?> <?= $selected_year ?>
            </h4>
            <small>Generated on: <?= $generatedAt ?></small>
        </div>

        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Print
            </button>

            <a href="monthly.php?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="btn btn-outline-primary btn-sm">← Prev</a>
            <a href="monthly.php" class="btn btn-outline-secondary btn-sm">Current</a>
            <a href="monthly.php?month=<?= $next_month ?>&year=<?= $next_year ?>" class="btn btn-outline-primary btn-sm">Next →</a>
            <a href="index.php" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>

    <!-- PRINT HEADER -->
    <div class="d-none d-print-block text-center mb-3">
        <h3>Monthly Report</h3>
        <p><?= $months[(int)$selected_month] ?> <?= $selected_year ?></p>
        <p>Generated on: <?= $generatedAt ?></p>
    </div>

    <!-- MONTH RANGE -->
    <div class="alert alert-info">
        <strong><?= date('d M Y', strtotime($month_start)) ?> - <?= date('d M Y', strtotime($month_end)) ?></strong>
    </div>

    <!-- SUMMARY -->
    <div class="row mb-4">
        <div class="col-md-3"><div class="stat-card"><h6>Appointments</h6><h2><?= $total_appointments ?></h2></div></div>
        <div class="col-md-3"><div class="stat-card"><h6>Payments</h6><h2>Ksh <?= number_format($total_payments,2) ?></h2></div></div>
        <div class="col-md-3"><div class="stat-card"><h6>Invoices</h6><h2><?= $invoice_count ?></h2></div></div>
        <div class="col-md-3">
            <div class="stat-card">
                <h6>Collection Rate</h6>
                <h2>
                    <?= $total_invoiced > 0 ? round(($total_payments/$total_invoiced)*100,1) : 0 ?>%
                </h2>
            </div>
        </div>
    </div>

    <!-- WEEKLY -->
    <div class="table-card mb-4">
        <h5>Weekly Breakdown</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Week</th>
                    <th>Period</th>
                    <th>Payments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($weekly_data as $i => $week): ?>
                <tr>
                    <td>Week <?= $i+1 ?></td>
                    <td><?= date('d M',strtotime($week['start'])) ?> - <?= date('d M',strtotime($week['end'])) ?></td>
                    <td>Ksh <?= number_format($week['total'],2) ?></td>
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
                <tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if($total_appointments > 0): ?>
                    <?php foreach($appointments as $row): ?>
                    <tr>
                        <td><?= date('d M Y',strtotime($row['appointment_date'])) ?></td>
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
                    <tr><td colspan="5" class="text-center">No appointments</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAYMENTS -->
    <div class="table-card">
        <h5>Payments</h5>
        <table class="table">
            <thead>
                <tr><th>Date</th><th>Patient</th><th>Amount</th><th>Method</th></tr>
            </thead>
            <tbody>
                <?php if($payment_count > 0): ?>
                    <?php foreach($payments as $row): ?>
                    <tr>
                        <td><?= date('d M Y',strtotime($row['payment_date'])) ?></td>
                        <td><?= $row['patient_name'] ?></td>
                        <td>Ksh <?= number_format($row['amount_paid'],2) ?></td>
                        <td><?= $row['payment_method'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No payments</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include "../includes/footer.php"; ?>