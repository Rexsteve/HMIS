<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin & pharmacist allowed
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'pharmacist') {
    header("Location: ../dashboard.php");
    exit();
}

/* Handle Delete Safely */
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM drug WHERE drug_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: list.php?deleted=1");
    exit();
}

$result = $conn->query("SELECT * FROM drug ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Drug Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Drug Inventory</h3>

<!-- Navigation -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <a href="add.php" class="btn btn-primary">
        + Add New Drug
    </a>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Drug added successfully!</div>
<?php endif; ?>

<?php if(isset($_GET['success']) && $_GET['success'] == 2): ?>
    <div class="alert alert-success">Drug updated successfully!</div>
<?php endif; ?>

<?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Drug deleted successfully!</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Drug Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Expiry Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['drug_id'] ?></td>
                    <td><?= $row['name'] ?></td>

                    <td class="<?= $row['quantity'] < 10 ? 'text-danger fw-bold' : '' ?>">
                        <?= $row['quantity'] ?>
                        <?php if($row['quantity'] < 10): ?>
                            <span class="badge bg-warning">Low Stock</span>
                        <?php endif; ?>
                    </td>

                    <td>Ksh <?= number_format($row['price'], 2) ?></td>

                    <td>
                        <?= date('d M Y', strtotime($row['expiry_date'])) ?>
                    </td>

                    <td>
                        <a href="edit.php?id=<?= $row['drug_id'] ?>" 
                           class="btn btn-sm btn-warning">Edit</a>

                        <a href="list.php?delete=<?= $row['drug_id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Are you sure you want to delete this drug?')">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No drugs found</td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>

    </div>
</div>

</body>
</html>