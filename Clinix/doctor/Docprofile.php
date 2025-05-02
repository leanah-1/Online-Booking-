<?php
require_once '../session.php';
require_once '../include/db.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = null;
$success_message = null;

// Fetch current doctor data
try {
    $query = "SELECT * FROM doctors WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $doctor_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor_data) {
        $error_message = "Doctor record not found.";
    }
} catch (PDOException $e) {
    $error_message = "Error fetching doctor data: " . $e->getMessage();
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $contactinfo = filter_input(INPUT_POST, 'contactinfo', FILTER_SANITIZE_STRING);
    $specialization = filter_input(INPUT_POST, 'specialization', FILTER_SANITIZE_STRING);

    try {
        $update_query = "UPDATE doctors 
                        SET name = :name, 
                            contactinfo = :contactinfo, 
                            specialization = :specialization 
                        WHERE user_id = :user_id";
        
        $update_stmt = $conn->prepare($update_query);
        $result = $update_stmt->execute([
            ':name' => $name,
            ':contactinfo' => $contactinfo,
            ':specialization' => $specialization,
            ':user_id' => $user_id
        ]);

        if ($result) {
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Failed to update profile.";
        }
    } catch (PDOException $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile - Clinic Management System</title>
    <link rel="stylesheet" href="../crazy.css">
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Doctor Profile</h1>
                <p class="hero-subtitle">Update Your Professional Information</p>
            </div>
        </section>

        <section class="profile-section">
            <div class="form-container">
                <div class="form-card">
                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($doctor_data): ?>
                        <form method="POST" class="profile-form">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user-md"></i> Full Name
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       required
                                       value="<?php echo htmlspecialchars($doctor_data['name']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="contactinfo">
                                    <i class="fas fa-phone"></i> Contact Information
                                </label>
                                <input type="text" 
                                       id="contactinfo" 
                                       name="contactinfo" 
                                       required
                                       value="<?php echo htmlspecialchars($doctor_data['contactinfo']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="specialization">
                                    <i class="fas fa-stethoscope"></i> Specialization
                                </label>
                                <input type="text" 
                                       id="specialization" 
                                       name="specialization" 
                                       required
                                       value="<?php echo htmlspecialchars($doctor_data['specialization']); ?>">
                            </div>

                            <button type="submit" name="update_profile" class="submit-button">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include '../include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>