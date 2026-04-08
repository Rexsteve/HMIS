<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include "config/db.php";

$role = $_SESSION['role'];
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

/* Doctor ID */
$doctor_id = 0;
if($role === 'doctor') {
    $stmt = $conn->prepare("SELECT doctor_id FROM doctor WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()) {
        $doctor_id = $row['doctor_id'];
    }
}

/* Helper */
function getValue($conn, $query, $field) {
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result)[$field];
    }
    return 0;
}

/* Stats */
$total_patients = ($role=='admin' || $role=='receptionist') 
    ? getValue($conn,"SELECT COUNT(*) as count FROM patient","count") : 0;

$total_doctors = ($role=='admin') 
    ? getValue($conn,"SELECT COUNT(*) as count FROM doctor","count") : 0;

$today_appointments = 0;

if($role=='admin'){
    $today_appointments = getValue($conn,
        "SELECT COUNT(*) as count FROM appointment WHERE appointment_date=CURDATE()","count");
}
elseif($role=='doctor' && $doctor_id>0){
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment WHERE appointment_date=CURDATE() AND doctor_id=?");
    $stmt->bind_param("i",$doctor_id);
    $stmt->execute();
    $today_appointments = $stmt->get_result()->fetch_assoc()['count'];
}

$low_stock = ($role=='admin' || $role=='pharmacist')
    ? getValue($conn,"SELECT COUNT(*) as count FROM drug WHERE quantity<10","count") : 0;

$today_payments = ($role=='admin' || $role=='cashier')
    ? getValue($conn,"SELECT COALESCE(SUM(amount_paid),0) as total FROM payment WHERE DATE(payment_date)=CURDATE()","total") : 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>HMIS Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{background:#f1f5f9;font-family:'Segoe UI';}

/* Sidebar */
.sidebar{
    position:fixed;
    width:250px;
    height:100vh;
    background:#0f172a;
    padding:20px;
    color:white;
}

.sidebar h4{
    font-weight:700;
    margin-bottom:20px;
}

.sidebar a{
    display:block;
    padding:12px;
    color:#cbd5e1;
    text-decoration:none;
    border-radius:10px;
    margin-bottom:6px;
}

.sidebar a:hover{
    background:#1e293b;
    color:white;
}

/* Main */
.main{
    margin-left:250px;
    padding:25px;
}

/* Header */
.header{
    background:linear-gradient(90deg,#2563eb,#4f46e5);
    color:white;
    padding:20px;
    border-radius:16px;
    display:flex;
    justify-content:space-between;
}

/* Cards */
.cardx{
    background:white;
    padding:20px;
    border-radius:16px;
    box-shadow:0 8px 20px rgba(0,0,0,0.05);
}

.big{
    font-size:26px;
    font-weight:700;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>🏥 HMIS</h4>

    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>

    <?php if($role=='admin' || $role=='receptionist'): ?>
        <a href="patients/list.php"><i class="bi bi-people"></i> Patients</a>
        <a href="appointments/list.php"><i class="bi bi-calendar-check"></i> Appointments</a>
    <?php endif; ?>

    <?php if($role=='doctor'): ?>
        <a href="appointments/list.php"><i class="bi bi-calendar-check"></i> My Appointments</a>
        <a href="consultations/list.php"><i class="bi bi-chat-dots"></i> My Consultations</a>
    <?php endif; ?>

    <?php if($role=='pharmacist'): ?>
        <a href="pharmacy/list.php"><i class="bi bi-capsule"></i> Pharmacy</a>
    <?php endif; ?>

    <?php if($role=='cashier'): ?>
        <a href="billing/list.php"><i class="bi bi-receipt"></i> Billing</a>
        <a href="invoice/list.php"><i class="bi bi-file-earmark-text"></i> Invoices</a>
        <a href="payment/list.php"><i class="bi bi-credit-card"></i> Payments</a>
        <a href="reports/index.php"><i class="bi bi-bar-chart"></i> Reports</a>
    <?php endif; ?>

    <?php if($role=='admin'): ?>
        <hr style="border-color:#334155">

        <a href="doctors/list.php"><i class="bi bi-person-badge"></i> Doctors</a>
        <a href="reports/index.php"><i class="bi bi-bar-chart"></i> Reports</a>

        <a href="admin/users_all.php"><i class="bi bi-people-fill"></i> All Users</a>
        <a href="admin/users_management.php"><i class="bi bi-gear"></i> Manage Users</a>
    <?php endif; ?>

    <a href="auth/logout.php" style="color:#f87171;">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<!-- MAIN -->
<div class="main">

    <div class="header">
        <div>
            <h5>Welcome, <?= $username ?></h5>
            <small><?= ucfirst($role) ?> Panel</small>
        </div>
    </div>

    <br>

    <div class="row g-3">

        <?php if($total_patients): ?>
        <div class="col-md-3">
            <div class="cardx">
                <div class="big"><?= $total_patients ?></div>
                <small>Patients</small>
            </div>
        </div>
        <?php endif; ?>

        <?php if($total_doctors): ?>
        <div class="col-md-3">
            <div class="cardx">
                <div class="big"><?= $total_doctors ?></div>
                <small>Doctors</small>
            </div>
        </div>
        <?php endif; ?>

        <?php if($today_appointments): ?>
        <div class="col-md-3">
            <div class="cardx">
                <div class="big"><?= $today_appointments ?></div>
                <small>Today's Appointments</small>
            </div>
        </div>
        <?php endif; ?>

        <?php if($low_stock): ?>
        <div class="col-md-3">
            <div class="cardx">
                <div class="big"><?= $low_stock ?></div>
                <small>Low Stock</small>
            </div>
        </div>
        <?php endif; ?>

        <?php if($today_payments): ?>
        <div class="col-md-3">
            <div class="cardx">
                <div class="big">Ksh <?= number_format($today_payments) ?></div>
                <small>Today's Payments</small>
            </div>
        </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>