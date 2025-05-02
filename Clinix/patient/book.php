<?php
require_once '../session.php';
require_once '../include/db.php';

// Authentication check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];
        $location = htmlspecialchars(strip_tags($_POST['location']));
        $reason = htmlspecialchars(strip_tags($_POST['reason']));

        // Date and time validation
        $current_date = date('Y-m-d');
        $current_time = date('H:i');

        if ($appointment_date < $current_date) {
            throw new Exception("Please select a future date.");
        }
        
        if ($appointment_date == $current_date && $appointment_time < $current_time) {
            throw new Exception("Please select a future time.");
        }

        // Check for existing appointments
        $check_query = "SELECT COUNT(*) FROM appointments 
                       WHERE appointment_date = :date 
                       AND appointment_time = :time 
                       AND status != 'Cancelled'";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':date', $appointment_date);
        $check_stmt->bindParam(':time', $appointment_time);
        $check_stmt->execute();

        if ($check_stmt->fetchColumn() > 0) {
            throw new Exception("This time slot is already booked. Please select another time.");
        }

        // Get a random available doctor
        $doctor_query = "SELECT doctor_id FROM doctors ORDER BY RAND() LIMIT 1";
        $doctor_stmt = $conn->query($doctor_query);
        $doctor = $doctor_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doctor) {
            throw new Exception("No doctors available at the moment. Please try again later.");
        }

        // Insert new appointment
        $query = "INSERT INTO appointments 
                 (patient_id, doctor_id, appointment_date, appointment_time, location, reason, status) 
                 VALUES 
                 (:patient_id, :doctor_id, :appointment_date, :appointment_time, :location, :reason, 'Pending')";
        
        $stmt = $conn->prepare($query);
        
        $stmt->execute([
            ':patient_id' => $_SESSION['role_id'],
            ':doctor_id' => $doctor['doctor_id'],
            ':appointment_date' => $appointment_date,
            ':appointment_time' => $appointment_time,
            ':location' => $location,
            ':reason' => $reason
        ]);

        if ($stmt->rowCount() > 0) {
            echo "<script>
                alert('Appointment booked successfully!');
                window.location.href = '../dashboard.php';
            </script>";
            exit();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Clinic Management System</title>
    <link rel="stylesheet" href="../crazy.css">
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Book Appointment</h1>
                <p class="hero-subtitle">Quick • Easy • Convenient</p>
            </div>
        </section>

        <section class="booking-section">
            <div class="form-container">
                <div class="form-card">
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <h2><i class="fas fa-calendar-plus"></i> Book Appointment</h2>
                    <form action="book.php" method="POST" class="booking-form">
                        <div class="form-group">
                            <label for="appointment_date">
                                <i class="fas fa-calendar"></i>
                                Appointment Date
                            </label>
                            <input type="date" 
                                   id="appointment_date" 
                                   name="appointment_date" 
                                   min="<?php echo date('Y-m-d'); ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="appointment_time">
                                <i class="fas fa-clock"></i>
                                Appointment Time
                            </label>
                            <input type="time" 
                                   id="appointment_time" 
                                   name="appointment_time" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="location">
                                <i class="fas fa-map-marker-alt"></i>
                                Location
                            </label>
                            <input type="text" 
                                   id="location" 
                                   name="location" 
                                   required
                                   placeholder="Enter appointment location">
                        </div>

                        <div class="form-group">
                            <label for="reason">
                                <i class="fas fa-comment-medical"></i>
                                Reason for Visit
                            </label>
                            <textarea id="reason" 
                                    name="reason" 
                                    required
                                    placeholder="Please describe your reason for visiting"></textarea>
                        </div>

                        <button type="submit" class="submit-button">
                            <i class="fas fa-check-circle"></i>
                            Book Appointment
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include '../include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <script>
        // Client-side validation for business hours (9 AM to 5 PM)
        document.getElementById('appointment_time').addEventListener('change', function() {
            const time = this.value;
            const [hours, minutes] = time.split(':');
            if (hours < 9 || hours >= 17) {
                alert('Please select a time between 9:00 AM and 5:00 PM');
                this.value = '';
            }
        });
    </script>
</body>
</html>