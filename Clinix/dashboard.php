<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once 'include/db.php';
require_once __DIR__ . '/notifications.php';  // Using absolute path

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle marking notifications as read
if (isset($_POST['mark_read'])) {
    $notification_id = $_POST['notification_id'];
    markNotificationAsRead($notification_id);
}

// At the top of the file, after session_start()
$user_id = $_SESSION['role_id'] ?? $_SESSION['user_id']; // Use role_id for patients
$role = $_SESSION['role'];

// Debug information
error_log("User ID: " . $user_id);
error_log("Role: " . $role);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clinic Management System</title>
    <link rel="stylesheet" href="crazy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'include/header.php'; ?>
<?php if ($_SESSION['role'] === 'patient'): ?>
    <?php 
        $unreadCount = getUnreadNotificationCount($user_id, $role);
        $notifications = getNotifications($user_id, $role);
    ?>
    <div class="notification-bell">
        <i class="fas fa-bell"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="notification-count"><?php echo $unreadCount; ?></span>
        <?php endif; ?>
    </div>

    <div class="notifications-panel">
        <h3>Notifications</h3>
        <?php if (empty($notifications)): ?>
            <p class="no-notifications">No notifications</p>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?php echo $notification['status'] === 'unread' ? 'unread' : ''; ?>">
                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                    <small><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                    <?php if ($notification['status'] === 'unread'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                            <button type="submit" name="mark_read" class="mark-read-btn">
                                Mark as read
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
    
    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Welcome, 
                    <?php 
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'doctor') {
                        echo 'Dr. ' . htmlspecialchars($_SESSION['username']);
                    } else {
                        echo htmlspecialchars($_SESSION['username']);
                    }
                    ?>
                </h1>
                <p class="hero-subtitle">Manage Your Healthcare Journey</p>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="dashboard-grid">
            <?php if ($_SESSION['role'] === 'doctor'): ?>
    <?php
    // Check if doctor is already registered
    $user_id = $_SESSION['user_id'];
    $check_query = "SELECT doctor_id FROM doctors WHERE user_id = :user_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([':user_id' => $user_id]);
    $is_registered = $check_stmt->fetch();
    ?>

    <?php if (!$is_registered): ?>
        <div class="dashboard-card animate">
            <div class="card-icon">
                <i class="fas fa-notes-medical"></i>
            </div>
            <h3>Registration</h3>
            <p>Register as a doctor</p>
            <a href="doctor/DocReg.php" class="dashboard-link">Register</a>
        </div>
    <?php endif; ?>

    <?php if ($is_registered): ?>
        <div class="dashboard-card animate">
            <div class="card-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3>Appointments</h3>
            <p>View and manage your patient appointments</p>
            <a href="doctor/DocEdit.php" class="dashboard-link">View Appointments</a>
        </div>

        <div class="dashboard-card animate">
            <div class="card-icon">
                <i class="fas fa-notes-medical"></i>
            </div>
            <h3>Update</h3>
            <p>Update your medical records</p>
            <a href="doctor/DocUpdate.php" class="dashboard-link">Update Records</a>
        </div>
        <div class="dashboard-card animate">
            <div class="card-icon">
                <i class="fas fa-notes-medical"></i>
            </div>
            <h3>Update</h3>
            <p>Update your profile</p>
            <a href="doctor/DocProfile.php" class="dashboard-link">Update Profile</a>
        </div>
        <div class="dashboard-card animate">
            <div class="card-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3>Reports</h3>
            <p>View and manage patient reports</p>
            <a href="doctor/DocReport.php" class="dashboard-link">Reports</a>
        </div>
    
<?php endif; ?>
                   
                    <?php elseif ($_SESSION['role'] === 'patient'): ?>
    <?php
    // Check if patient is already registered
    $user_id = $_SESSION['user_id'];
    $check_query = "SELECT patient_id FROM patients WHERE user_id = :user_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([':user_id' => $user_id]);
    $is_registered = $check_stmt->fetch();
    ?>

    <div class="dashboard-card animate">
        <div class="card-icon">
            <i class="fas fa-calendar-plus"></i>
        </div>
        <h3>Book Appointment</h3>
        <p>Schedule a new appointment with our doctors</p>
        <a href="patient/book.php" class="dashboard-link">Book Now</a>
    </div>

    <div class="dashboard-card animate">
        <div class="card-icon">
            <i class="fas fa-file-medical"></i>
        </div>
        <h3>Medical Records</h3>
        <p>Add and view your medical records</p>
        <a href="patient/viewMedical.php" class="dashboard-link">View Records</a>
    </div>

    <?php if (!$is_registered): ?>
        <div class="dashboard-card animate">
            <div class="card-icon">
                <i class="fas fa-file-medical"></i>
            </div>
            <h3>Register Here</h3>
            <p>Register as a new patient</p>
            <a href="patient/Register.php" class="dashboard-link">Register</a>
        </div>
    <?php endif; ?>

    <?php if ($is_registered): ?>
        <div class="dashboard-card animate">
            <div class="card-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <h3>Update Information</h3>
            <p>Update your personal information</p>
            <a href="patient/update.php" class="dashboard-link">Update Details</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="dashboard-card animate">
    <div class="card-icon">
        <i class="fas fa-sign-out-alt"></i>
    </div>
    <h3>Logout</h3>
    <p>Securely log out from your account</p>
    <a href="logout.php" class="dashboard-link">Logout</a>
</div>
        </section>
    </main>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBell = document.querySelector('.notification-bell');
    const notificationsPanel = document.querySelector('.notifications-panel');
    
    // Toggle notifications panel
    notificationBell.addEventListener('click', function() {
        notificationsPanel.style.display = 
            notificationsPanel.style.display === 'none' ? 'block' : 'none';
    });

    // Close panel when clicking outside
    document.addEventListener('click', function(event) {
        if (!notificationBell.contains(event.target) && 
            !notificationsPanel.contains(event.target)) {
            notificationsPanel.style.display = 'none';
        }
    });

    // Handle mark as read
    const markReadButtons = document.querySelectorAll('.mark-read-btn');
    markReadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const notificationItem = this.closest('.notification-item');
            notificationItem.classList.remove('unread');
            notificationItem.classList.add('read');
        });
    });
});
</script>

    <?php include 'include/footer.php'; ?>
</body>
</html>
