<?php
require_once '../session.php';
require_once '../include/db.php';

// Authentication check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = null;
$success_message = null;

// Fetch current patient data
try {
    $query = "SELECT * FROM patients WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $patient_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient_data) {
        $error_message = "Patient record not found.";
    }
} catch (PDOException $e) {
    $error_message = "Error fetching patient data: " . $e->getMessage();
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);

    try {
        $update_query = "UPDATE patients 
                        SET name = :name, 
                            phone = :phone, 
                            address = :address, 
                            location = :location 
                        WHERE user_id = :user_id";
        
        $update_stmt = $conn->prepare($update_query);
        $result = $update_stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':address' => $address,
            ':location' => $location,
            ':user_id' => $user_id
        ]);

        if ($result) {
            echo "<script>
                alert('Information updated successfully!');
                window.location.href = '../dashboard.php';
            </script>";
            exit();
        } else {
            $error_message = "Failed to update information. Please try again.";
        }
    } catch (PDOException $e) {
        $error_message = "Error updating information: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Information - Clinic Management System</title>
    <link rel="stylesheet" href="../crazy.css">
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Update Personal Information</h1>
                <p class="hero-subtitle">Keep Your Details Current</p>
            </div>
        </section>

        <section class="update-section">
            <div class="form-container">
                <div class="form-card">
                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($patient_data): ?>
                        <h2><i class="fas fa-user-edit"></i> Update Information</h2>
                        <form action="update.php" method="POST" class="update-form">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user"></i>
                                    Full Name
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       required
                                       value="<?php echo htmlspecialchars($patient_data['name']); ?>"
                                       placeholder="Enter your full name">
                            </div>

                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone"></i>
                                    Phone Number
                                </label>
                                <input type="text" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo htmlspecialchars($patient_data['phone'] ?? ''); ?>"
                                       placeholder="Enter your phone number">
                            </div>

                            <div class="form-group">
                                <label for="address">
                                    <i class="fas fa-home"></i>
                                    Address
                                </label>
                                <input type="text" 
                                       id="address" 
                                       name="address" 
                                       value="<?php echo htmlspecialchars($patient_data['address'] ?? ''); ?>"
                                       placeholder="Enter your address">
                            </div>

                            <div class="form-group">
                                <label for="location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Location
                                </label>
                                <input type="text" 
                                       id="location" 
                                       name="location" 
                                       value="<?php echo htmlspecialchars($patient_data['location'] ?? ''); ?>"
                                       placeholder="Enter your location">
                            </div>

                            <button type="submit" name="update_info" class="submit-button">
                                <i class="fas fa-save"></i>
                                Update Information
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