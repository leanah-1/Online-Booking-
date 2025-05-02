<?php
require_once '../session.php';
require_once '../include/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $phone = htmlspecialchars(strip_tags($_POST['phone']));
    $address = htmlspecialchars(strip_tags($_POST['address']));

    try {
        // Insert patient record
        $query = "INSERT INTO patients (user_id, name, phone, address) 
                 VALUES (:user_id, :name, :phone, :address)";
        $stmt = $conn->prepare($query);
        
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);

        if ($stmt->execute()) {
            // Get the newly inserted patient's ID
            $patient_id = $conn->lastInsertId();
            $_SESSION['role_id'] = $patient_id;
            
            // Redirect to dashboard after successful registration
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Failed to register patient. Please try again.";
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Patient - Clinic Management System</title>
    <link rel="stylesheet" href="../crazy.css">
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Register Patient</h1>
                <p class="hero-subtitle">Fill in the details below to create a new patient record</p>
            </div>
        </section>

        <!-- Registration Form Section -->
        <section class="register-section">
            <div class="form-container">
                <div class="form-card">
                    <h2><i class="fas fa-user-plus"></i> Patient Information</h2>

                    <!-- Display success or error messages -->
                    <?php if (isset($success_message)) : ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $success_message; ?>
                        </div>
                    <?php elseif (isset($error_message)) : ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="Register.php" method="POST" class="register-form">
    <div class="form-group">
        <label for="name">
            <i class="fas fa-user"></i> Name
        </label>
        <input type="text" 
               id="name" 
               name="name" 
               required 
               placeholder="Enter patient name">
    </div>

    <div class="form-group">
        <label for="phone">
            <i class="fas fa-phone"></i> Phone
        </label>
        <input type="text" 
               id="phone" 
               name="phone" 
               required 
               placeholder="Enter phone number">
    </div>

    <div class="form-group">
        <label for="address">
            <i class="fas fa-home"></i> Address
        </label>
        <input type="text" 
               id="address" 
               name="address" 
               required 
               placeholder="Enter address">
    </div>

    <button type="submit" class="submit-button">
        <i class="fas fa-save"></i> Register
    </button>
</form>
                </div>
            </div>
        </section>
    </main>

    <?php include '../include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>
