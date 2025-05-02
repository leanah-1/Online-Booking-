<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Clinic Management System</title>
    <link rel="stylesheet" href="crazy.css">
</head>
<body>
    <?php include 'include/header.php'; ?>
    
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Create Account</h1>
                <p class="hero-subtitle">Join Our Healthcare Community</p>
            </div>
        </section>

        <!-- Signup Section -->
        <section class="auth-section">
            <div class="auth-container">
                <?php
                // Your existing PHP logic
                include 'include/db.php';

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $username = $_POST['username'];
                    $password = $_POST['password'];
                    $role = $_POST['role'];
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ["cost" => 10]);

                    try {
                        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':password', $hashedPassword);
                        $stmt->bindParam(':role', $role);
                        $stmt->execute();
                        header("Location: login.php");
                        exit;
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-error'><p>Error: " . $e->getMessage() . "</p></div>";
                    }
                }
                ?>

                <div class="auth-card">
                    <div class="auth-header">
                        <i class="fas fa-user-plus"></i>
                        <h2>Sign Up</h2>
                        <p>Create your account to get started</p>
                    </div>

                    <form method="post" action="sign.php" class="auth-form">
                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-user"></i>
                                Username
                            </label>
                            <input type="text" id="username" name="username" required>
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <input type="password" id="password" name="password" required>
                        </div>

                        <div class="form-group">
                            <label for="role">
                                <i class="fas fa-user-tag"></i>
                                Role
                            </label>
                            <select id="role" name="role" required>
                                <option value="patient">Patient</option>
                                <option value="doctor">Doctor</option>
                            </select>
                        </div>

                        <button type="submit" class="auth-button">
                            <i class="fas fa-sign-in-alt"></i>
                            Create Account
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>