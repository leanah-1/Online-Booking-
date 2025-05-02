<?php
session_start();
require_once 'include/db.php';

$error = isset($_GET['timeout']) ? "Your session has expired. Please log in again." : "";
$username = '';

// Add this section to process the login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Verify user credentials
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        // Check if doctor needs to complete registration
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
        
            // Check user role and registration status
            if ($user['role'] === 'doctor') {
                $role_query = "SELECT doctor_id FROM doctors WHERE user_id = ?";
                $role_stmt = $conn->prepare($role_query);
                $role_stmt->execute([$user['user_id']]);
                $role_info = $role_stmt->fetch(PDO::FETCH_ASSOC);
        
                if (!$role_info) {
                    // Doctor hasn't completed registration
                    header("Location: doctor/DocReg.php");
                    exit();
                } else {
                    $_SESSION['role_id'] = $role_info['doctor_id'];
                    header("Location: dashboard.php");
                    exit();
                }
            } else if ($user['role'] === 'patient') {
                $role_query = "SELECT patient_id FROM patients WHERE user_id = ?";
                $role_stmt = $conn->prepare($role_query);
                $role_stmt->execute([$user['user_id']]);
                $role_info = $role_stmt->fetch(PDO::FETCH_ASSOC);
        
                if (!$role_info) {
                    // Patient hasn't completed registration
                    header("Location: patient/Register.php");
                    exit();
                } else {
                    $_SESSION['role_id'] = $role_info['patient_id'];
                    header("Location: dashboard.php");
                    exit();
                }
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clinic Management System</title>
    <link rel="stylesheet" href="crazy.css">
</head>
<body>
    <?php include 'include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Welcome Back</h1>
                <p class="hero-subtitle">Access Your Healthcare Portal</p>
            </div>
        </section>

        <section class="auth-section">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <i class="fas fa-user-circle"></i>
                        <h2>Login</h2>
                        <p>Access your account</p>
                    </div>

                    <?php if (!empty($error)) : ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="auth-form">
                        <div class="form-group">
                            <label for="username"><i class="fas fa-user"></i> Username</label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="<?php echo htmlspecialchars($username); ?>"
                                required 
                            >
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                            >
                        </div>
                        <button type="submit" class="auth-button">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Don't have an account? <a href="sign.php">Sign up here</a></p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>
