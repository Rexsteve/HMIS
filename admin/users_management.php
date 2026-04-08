<?php
session_start();
include "../config/db.php";

// Only admin access
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

// Fetch users by status
$pending = $conn->query("SELECT * FROM users WHERE status='pending' ORDER BY user_id DESC");
$approved = $conn->query("SELECT * FROM users WHERE status='approved' ORDER BY user_id DESC");
$rejected = $conn->query("SELECT * FROM users WHERE status='rejected' ORDER BY user_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin User Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .tab-btn {
            padding:10px 18px;
            border-radius:30px;
            border:none;
            margin-right:10px;
            font-weight:600;
            cursor:pointer;
        }

        .tab-active {
            background:#4f46e5;
            color:white;
        }

        .tab-section {
            display:none;
            margin-top:20px;
        }

        .table-box {
            background:white;
            padding:20px;
            border-radius:16px;
            box-shadow:0 10px 25px rgba(0,0,0,0.05);
        }

        table th {
            font-size:13px;
            text-transform:uppercase;
            color:#666;
        }

        table td {
            vertical-align:middle;
            font-size:14px;
        }

        .action-btn {
            padding:6px 10px;
            border-radius:8px;
            font-size:12px;
            font-weight:600;
            text-decoration:none;
            margin-right:5px;
        }

        .approve { background:#d1fae5; color:#065f46; }
        .reject { background:#fee2e2; color:#991b1b; }
        .restore { background:#e0e7ff; color:#3730a3; }
    </style>
</head>

<body>

<div class="container mt-4">

    <h2>🏥 Hospital User Management</h2>
    <p style="color:#666;">Control system access and approvals</p>
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <!-- Tabs -->
    <div class="mt-3">
        <button class="tab-btn tab-active" onclick="showTab('pending')">Pending</button>
        <button class="tab-btn" onclick="showTab('approved')">Approved</button>
        <button class="tab-btn" onclick="showTab('rejected')">Rejected</button>
    </div>

    <!-- PENDING -->
    <div id="pending" class="tab-section" style="display:block;">
        <div class="table-box">
            <h5>Pending Users</h5>
            <table class="table table-hover">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php while($u = $pending->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['user_id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td>
                        <span class="role-badge role-<?= $u['role'] ?>">
                            <?= strtoupper(substr($u['role'],0,1)) ?>
                        </span>
                        <?= $u['role'] ?>
                    </td>
                    <td><span class="status status-pending">Pending</span></td>
                    <td>
                        <a class="action-btn approve" href="user_actions.php?action=approve&id=<?= $u['user_id'] ?>">Approve</a>
                        <a class="action-btn reject" href="user_actions.php?action=reject&id=<?= $u['user_id'] ?>">Reject</a>
                    </td>
                </tr>
                <?php endwhile; ?>

            </table>
        </div>
    </div>

    <!-- APPROVED -->
    <div id="approved" class="tab-section">
        <div class="table-box">
            <h5>Approved Users</h5>
            <table class="table table-hover">

                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>

                <?php while($u = $approved->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['user_id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= $u['role'] ?></td>
                    <td><span class="status status-approved">Approved</span></td>
                </tr>
                <?php endwhile; ?>

            </table>
        </div>
    </div>

    <!-- REJECTED -->
    <div id="rejected" class="tab-section">
        <div class="table-box">
            <h5>Rejected Users</h5>
            <table class="table table-hover">

                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php while($u = $rejected->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['user_id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= $u['role'] ?></td>
                    <td><span class="status status-rejected">Rejected</span></td>
                    <td>
                        <a class="action-btn restore" href="user_actions.php?action=approve&id=<?= $u['user_id'] ?>">Restore</a>
                    </td>
                </tr>
                <?php endwhile; ?>

            </table>
        </div>
    </div>

</div>

<script>
function showTab(tab) {

    document.querySelectorAll('.tab-section').forEach(el => {
        el.style.display = 'none';
    });

    document.getElementById(tab).style.display = 'block';

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('tab-active');
    });

    event.target.classList.add('tab-active');
}
</script>

</body>
</html>