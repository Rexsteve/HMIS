<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit();
}
include "../config/db.php";

$error = '';
$success = '';

if(isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Check passwords match
    if($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if username exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

        if(mysqli_num_rows($check) > 0) {
            $error = "Username already exists!";
        } else {

            // Insert into users
            $query = "INSERT INTO users (username, password, role) 
                      VALUES ('$username', '$hashed_password', '$role')";

            if(mysqli_query($conn, $query)) {

                $user_id = mysqli_insert_id($conn);

                // If role is doctor → add to doctor table
                if($role == 'doctor') {
                    $name = $username; // you can later improve this
                    $specialization = '';
                    $contact = '';

                    $doctor_query = "INSERT INTO doctor (name, specialization, contact)
                                     VALUES ('$name', '$specialization', '$contact')";
                    mysqli_query($conn, $doctor_query);
                }

                $success = "Registration successful! Please login.";

            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HMIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#667eea;">

<div class="bg-white p-4 rounded shadow" style="width:100%; max-width:450px;">

    <h3 class="text-center mb-3">🏥 HMIS Register</h3>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Register as</label>
            <select name="role" class="form-select" required>
                <option value="">Select role</option>
                <option value="admin">Admin</option>
                <option value="doctor">Doctor</option>
                <option value="pharmacist">Pharmacist</option>
                <option value="receptionist">Receptionist</option>
                <option value="cashier">Cashier</option>
            </select>
        </div>

        <button type="submit" name="register" class="btn btn-primary w-100">
            Register
        </button>

    </form>

    <div class="text-center mt-3">
        <a href="login.php">Already have an account? Login</a>
    </div>

</div>

</body>
</html>