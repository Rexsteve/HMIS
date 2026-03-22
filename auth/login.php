<?php
session_start();
include "../config/db.php";

$error = '';

if(isset($_POST['login'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    
    if(mysqli_num_rows($query) == 1) {

        $user = mysqli_fetch_assoc($query);

        if(password_verify($password, $user['password'])) {

            // Store basic session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // ✅ Default doctor_id (important)
            $_SESSION['doctor_id'] = 0;

            // ✅ If user is a doctor, get doctor_id
            if($user['role'] == 'doctor') {

                $stmt = $conn->prepare("SELECT doctor_id FROM doctor WHERE user_id = ?");
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                if($result && $result->num_rows > 0) {
                    $doctor = $result->fetch_assoc();
                    $_SESSION['doctor_id'] = (int)$doctor['doctor_id'];
                }
            }

            header("Location: ../dashboard.php");
            exit();

        } else {
            $error = "Invalid password!";
        }

    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - HMIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>HMIS Login</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
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
                            
                            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="register.php">Register here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>