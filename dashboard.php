<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include "config/db.php";

$role = $_SESSION['role'];
$username = $_SESSION['username'];

function getValue($conn, $query, $field) {
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result)[$field];
    }
    return 0;
}

$total_patients = 0;
$total_doctors = 0;
$today_appointments = 0;
$low_stock = 0;
$today_payments = 0;

if($role == 'admin' || $role == 'receptionist') {
    $total_patients = getValue($conn, "SELECT COUNT(*) as count FROM patient", "count");
}

if($role == 'admin') {
    $total_doctors = getValue($conn, "SELECT COUNT(*) as count FROM doctor", "count");
}

if($role == 'admin' || $role == 'doctor') {
    $today_appointments = getValue($conn, 
        "SELECT COUNT(*) as count FROM appointment WHERE appointment_date = CURDATE()", "count");
}

if($role == 'admin' || $role == 'pharmacist') {
    $low_stock = getValue($conn, 
        "SELECT COUNT(*) as count FROM drug WHERE quantity < 10", "count");
}

if($role == 'admin' || $role == 'cashier') {
    $today_payments = getValue($conn, 
        "SELECT COALESCE(SUM(amount_paid),0) as total 
         FROM payment 
         WHERE DATE(payment_date) = CURDATE()", "total");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - HMIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <style>
        body{background:#f4f6f9;}

        .sidebar{
            position:fixed;
            width:240px;
            height:100vh;
            background:#2f3542;
            padding-top:20px;

            display:flex;
            flex-direction:column;
            overflow-y:auto; /* ✅ allows scrolling */
        }

        .sidebar a{
            color:#dfe4ea;
            padding:12px 20px;
            display:block;
            text-decoration:none;
        }

        .sidebar a:hover,
        .sidebar .active{
            background:#57606f;
            color:#fff;
        }

        .main-content{
            margin-left:240px;
            padding:20px;
        }

        .card-stat{
            border-left:5px solid #007bff;
        }

        .sidebar-footer{
            margin-top:auto; /* ✅ pushes logout to bottom */
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">

    <h4 class="text-center text-white mb-4">
        <i class="bi bi-hospital"></i> HMIS
    </h4>

    <div class="flex-grow-1">

        <a href="dashboard.php" class="active">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <?php if($role == 'admin' || $role == 'receptionist'): ?>
            <a href="patients/list.php"><i class="bi bi-people"></i> Patients</a>
            <a href="appointments/list.php"><i class="bi bi-calendar-check"></i> Appointments</a>
        <?php endif; ?>

        <?php if($role == 'admin' || $role == 'doctor'): ?>
            <a href="consultations/list.php"><i class="bi bi-chat-dots"></i> Consultations</a>
        <?php endif; ?>

        <?php if($role == 'admin' || $role == 'pharmacist'): ?>
            <a href="pharmacy/list.php"><i class="bi bi-capsule"></i> Pharmacy</a>
        <?php endif; ?>

        <?php if($role == 'admin' || $role == 'cashier'): ?>
            <a href="billing/list.php"><i class="bi bi-receipt"></i> Billing</a>
            <a href="invoice/list.php"><i class="bi bi-file-earmark-text"></i> Invoices</a>
            <a href="invoice/generate.php"><i class="bi bi-plus-square"></i> Generate Invoice</a>
            <a href="payment/list.php"><i class="bi bi-credit-card"></i> Payments</a>
        <?php endif; ?>

        <?php if($role == 'admin'): ?>
            <a href="doctors/list.php"><i class="bi bi-person-badge"></i> Doctors</a>
            <a href="reports/index.php"><i class="bi bi-bar-chart"></i> Reports</a>
        <?php endif; ?>

    </div>

    <!-- Logout pinned at bottom -->
    <div class="sidebar-footer">
        <hr class="text-white">
        <a href="auth/logout.php" class="text-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>

</div>

<!-- Main Content -->
<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Dashboard</h4>
        <div>
            Welcome, <strong><?= $username ?></strong>
            (<?= ucfirst($role) ?>)
        </div>
    </div>

    <div class="row">

        <?php if($role == 'admin' || $role == 'receptionist'): ?>
        <div class="col-md-3">
            <div class="card p-3 card-stat">
                <h6>Total Patients</h6>
                <h3><?= $total_patients ?></h3>
            </div>
        </div>
        <?php endif; ?>

        <?php if($role == 'admin'): ?>
        <div class="col-md-3">
            <div class="card p-3 card-stat">
                <h6>Total Doctors</h6>
                <h3><?= $total_doctors ?></h3>
            </div>
        </div>
        <?php endif; ?>

        <?php if($role == 'admin' || $role == 'doctor'): ?>
        <div class="col-md-3">
            <div class="card p-3 card-stat">
                <h6>Today's Appointments</h6>
                <h3><?= $today_appointments ?></h3>
            </div>
        </div>
        <?php endif; ?>

        <?php if($role == 'admin' || $role == 'pharmacist'): ?>
        <div class="col-md-3">
            <div class="card p-3 card-stat">
                <h6>Low Stock Drugs</h6>
                <h3><?= $low_stock ?></h3>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <?php if($role == 'admin' || $role == 'cashier'): ?>
    <div class="card mt-4 p-4">
        <h5>Today's Payments</h5>
        <h2 class="text-success">
            Ksh <?= number_format($today_payments, 2) ?>
        </h2>
    </div>
    <?php endif; ?>
    <?php if($role == 'admin' || $role == 'cashier'): ?>
<div class="card mt-4 p-4">
    <h5>Recent Unpaid Invoices</h5>

    <?php
    $recent_invoices = mysqli_query($conn,
        "SELECT i.*, p.name as patient_name
         FROM invoice i
         JOIN patient p ON i.patient_id = p.patient_id
         WHERE i.status = 'unpaid'
         ORDER BY i.created_at DESC
         LIMIT 5");
    ?>

    <?php if($recent_invoices && mysqli_num_rows($recent_invoices) > 0): ?>
        <table class="table table-bordered">
            <tr>
                <th>#</th>
                <th>Patient</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($recent_invoices)): ?>
            <tr>
                <td><?= $row['invoice_id'] ?></td>
                <td><?= $row['patient_name'] ?></td>
                <td>Ksh <?= number_format($row['total_amount'],2) ?></td>
                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
            <p class="text-muted">No unpaid invoices.</p>
        <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="text-center mt-5 text-muted">
        <small>© 2026 Hospital Management Information System</small>
    </div>

</div>

</body>
</html>