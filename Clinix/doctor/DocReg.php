<?php
session_start();
require_once '../include/db.php';

$success_message = $error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate input data
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $specialization = filter_input(INPUT_POST, 'specialization', FILTER_SANITIZE_STRING);
        $contact_info = filter_input(INPUT_POST, 'contact_info', FILTER_SANITIZE_STRING);

        if (!$name || !$specialization || !$contact_info) {
            throw new Exception("All fields are required");
        }

        // Start transaction
        $conn->beginTransaction();

        try {
            // Insert new doctor with all fields
            $insert_query = "INSERT INTO doctors (user_id, name, contactinfo, specialization) 
                            VALUES (:user_id, :name, :contactinfo, :specialization)";
            $insert_stmt = $conn->prepare($insert_query);
            
            if (!$insert_stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':name' => $name,
                ':contactinfo' => $contact_info,
                ':specialization' => $specialization
            ])) {
                throw new Exception("Failed to register doctor");
            }

            // Get the newly inserted doctor's ID
            $doctor_id = $conn->lastInsertId();
            $_SESSION['role_id'] = $doctor_id;

            // Commit transaction
            $conn->commit();

            // Redirect to doctor dashboard
            header("Location: doctor/DocEdit.php");
            exit();

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Error in doctor/DocReg.php: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Registration</title>
    <link rel="stylesheet" href="../crazy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Join Our Medical Team</h1>
                <p class="hero-subtitle">Become part of our healthcare revolution</p>
            </div>
        </section>

        <section class="registration-section">
            <div class="container">
                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <h2><i class="fas fa-user-md"></i> Doctor Registration</h2>
                    <form action="DocReg.php" method="POST" class="registration-form">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required 
                                   placeholder="Enter your full name">
                        </div>

                        <div class="form-group">
                            <label for="specialization">
                                <i class="fas fa-stethoscope"></i> Specialization
                            </label>
                            <select id="specialization" name="specialization" required>
                                <option value="">Select Specialization</option>
                                <option value="General Medicine">General Medicine</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="Cardiology">Cardiology</option>
                                <option value="Dermatology">Dermatology</option>
                                <option value="Orthopedics">Orthopedics</option>
                                <option value="Neurology">Neurology</option>
                                <option value="Gynecology">Gynecology</option>
                                <option value="Ophthalmology">Ophthalmology</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="contact_info">
                                <i class="fas fa-address-card"></i> Contact Information
                            </label>
                            <textarea 
                                id="contact_info" 
                                name="contact_info" 
                                required 
                                placeholder="Enter your contact information (email, phone, address)"></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="submit-button">
                                <i class="fas fa-user-plus"></i> Register
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include '../include/footer.php'; ?>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>