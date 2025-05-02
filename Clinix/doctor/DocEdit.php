<?php
session_start();
require_once '../include/db.php';
require_once '../notifications.php';  

// Check if user is logged in and is a doctor
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$doctor_id = $_SESSION['role_id'];
$success_message = $error_message = '';

// Modify the appointment fetching query (around line 15)
try {
    // Add a status filter parameter
    $show_cancelled = isset($_GET['show_cancelled']) ? true : false;
    
    $query = "SELECT 
                a.*,
                p.name as patient_name,
                p.phone as patient_phone
              FROM 
                appointments a
                JOIN patients p ON a.patient_id = p.patient_id
              WHERE 
                a.doctor_id = :doctor_id
                " . (!$show_cancelled ? "AND a.status != 'Cancelled'" : "") . "
              ORDER BY 
                CASE 
                    WHEN a.status = 'Pending' THEN 1
                    WHEN a.status = 'Confirmed' THEN 2
                    WHEN a.status = 'Completed' THEN 3
                    WHEN a.status = 'Cancelled' THEN 4
                END,
                a.appointment_date ASC, 
                a.appointment_time ASC";

    $stmt = $conn->prepare($query);
    $stmt->execute([':doctor_id' => $doctor_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching appointments: " . $e->getMessage();
}
// Update the status update handling section
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    try {
        $appointment_id = $_POST['appointment_id'];
        $new_status = $_POST['status'];
        $doctor_notes = $_POST['doctor_notes'];

        // First, get the current appointment details
        $get_appointment = "SELECT patient_id, status, appointment_date, appointment_time 
                          FROM appointments 
                          WHERE appointment_id = :appointment_id";
        $stmt = $conn->prepare($get_appointment);
        $stmt->execute([':appointment_id' => $appointment_id]);
        $current_appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        // Only proceed if the status is actually changing
        if ($current_appointment && $current_appointment['status'] !== $new_status) {
            // Update the appointment
            $update_query = "UPDATE appointments 
                           SET status = :status,
                               doctor_notes = :doctor_notes
                           WHERE appointment_id = :appointment_id 
                           AND doctor_id = :doctor_id";
            
            $update_stmt = $conn->prepare($update_query);
            $result = $update_stmt->execute([
                ':status' => $new_status,
                ':doctor_notes' => $doctor_notes,
                ':appointment_id' => $appointment_id,
                ':doctor_id' => $doctor_id
            ]);

            if ($result) {
                // Debug information
                error_log("Starting notification process");
                error_log("Patient ID: " . $current_appointment['patient_id']);
                error_log("New Status: " . $new_status);

                // Send notification to patient
                $notification_sent = notifyPatientAboutAppointment(
                    $current_appointment['patient_id'],
                    $new_status,
                    $current_appointment['appointment_date'],
                    $current_appointment['appointment_time']
                );

                error_log("Notification sent: " . ($notification_sent ? "Success" : "Failed"));

                $_SESSION['success_message'] = "Appointment status updated successfully!" . 
                    ($notification_sent ? " Patient has been notified." : "");
                
                // Use JavaScript to show a modal/alert before redirecting
                echo "<script>
                    alert('Appointment status updated to {$new_status}. " . 
                    ($notification_sent ? "Patient notification sent." : "Failed to send notification.") . "');
                    window.location.href = 'DocEdit.php';
                </script>";
                exit();
            } else {
                $error_message = "Failed to update appointment status.";
            }
        } else {
            $_SESSION['info_message'] = "No changes were made to the appointment status.";
            header("Location: DocEdit.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error in appointment update process: " . $e->getMessage());
        $error_message = "Error updating appointment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Appointment Management</title>
    <link rel="stylesheet" href="../crazy.css">
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Appointment Management</h1>
                <p class="hero-subtitle">Manage • Update • Track</p>
            </div>
        </section>
<section class="filter-section">
    <div class="container">
        <div class="filter-controls">
            <?php if (!isset($_GET['show_cancelled'])): ?>
                <a href="?show_cancelled=1" class="filter-btn">
                    <i class="fas fa-eye"></i> Show Cancelled Appointments
                </a>
            <?php else: ?>
                <a href="?" class="filter-btn active">
                    <i class="fas fa-eye-slash"></i> Hide Cancelled Appointments
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

        <section class="appointments-section">
            <div class="container">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($appointments)): ?>
                    <div class="no-appointments">
                        <i class="fas fa-calendar-times"></i>
                        <p>No appointments found.</p>
                    </div>
                <?php else: ?>
                    <div class="appointments-grid">
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-card">
                                <div class="appointment-header">
                                    <h3>
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($appointment['patient_name']); ?>
                                    </h3>
                                    <span class="status <?php echo strtolower($appointment['status']); ?>">
                                        <?php echo htmlspecialchars($appointment['status']); ?>
                                    </span>
                                </div>

                                <div class="appointment-details">
                                    <p>
                                        <i class="fas fa-calendar"></i>
                                        Date: <?php echo date('F d, Y', strtotime($appointment['appointment_date'])); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-clock"></i>
                                        Time: <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-phone"></i>
                                        Phone: <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-map-marker-alt"></i>
                                        Location: <?php echo htmlspecialchars($appointment['location']); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-comment-medical"></i>
                                        Reason: <?php echo htmlspecialchars($appointment['reason']); ?>
                                    </p>
                                </div>

                                <form method="POST" class="status-update-form">
                                    <input type="hidden" name="appointment_id" 
                                           value="<?php echo $appointment['appointment_id']; ?>">
                                    
                                    <div class="form-group">
                                        <label for="status_<?php echo $appointment['appointment_id']; ?>">
                                            <i class="fas fa-tasks"></i> Update Status
                                        </label>
                                        <select name="status" 
                                                id="status_<?php echo $appointment['appointment_id']; ?>" 
                                                required>
                                            <option value="Pending" <?php echo $appointment['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Confirmed" <?php echo $appointment['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="Cancelled" <?php echo $appointment['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="Completed" <?php echo $appointment['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="notes_<?php echo $appointment['appointment_id']; ?>">
                                            <i class="fas fa-notes-medical"></i> Doctor Notes
                                        </label>
                                        <textarea name="doctor_notes" 
                                                  id="notes_<?php echo $appointment['appointment_id']; ?>"
                                                  placeholder="Add notes about the appointment"><?php echo htmlspecialchars($appointment['doctor_notes'] ?? ''); ?></textarea>
                                    </div>

                                    <button type="submit" name="update_status" class="submit-button">
                                        <i class="fas fa-save"></i> Update Appointment
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include '../include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>