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

// Update the patients query
try {
    $patients_query = "SELECT DISTINCT 
                        p.patient_id, 
                        p.name as patient_name,
                        p.phone,  -- Changed from u.phone to p.phone
                        a.appointment_date,
                        a.appointment_time
                      FROM patients p
                      JOIN appointments a ON p.patient_id = a.patient_id
                      WHERE a.doctor_id = :doctor_id
                      AND a.status = 'Confirmed'
                      ORDER BY a.appointment_date ASC, a.appointment_time ASC";
    
    $patients_stmt = $conn->prepare($patients_query);
    $patients_stmt->execute([':doctor_id' => $doctor_id]);
    $patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug information
    error_log("Found " . count($patients) . " patients with confirmed appointments");
} catch (PDOException $e) {
    $error_message = "Error fetching patients: " . $e->getMessage();
    error_log($error_message);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get form data
        $patient_id = filter_input(INPUT_POST, 'patient_id', FILTER_SANITIZE_NUMBER_INT);
        $diagnosis = filter_input(INPUT_POST, 'diagnosis', FILTER_SANITIZE_STRING);
        $treatment = filter_input(INPUT_POST, 'treatment', FILTER_SANITIZE_STRING);
        $prescription = filter_input(INPUT_POST, 'prescription', FILTER_SANITIZE_STRING);
        $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

        // Insert medical record
        $insert_query = "INSERT INTO medical_records 
                        (patient_id, doctor_id, diagnosis, treatment, prescription, notes) 
                        VALUES (:patient_id, :doctor_id, :diagnosis, :treatment, :prescription, :notes)";
        
        $insert_stmt = $conn->prepare($insert_query);
        $result = $insert_stmt->execute([
            ':patient_id' => $patient_id,
            ':doctor_id' => $doctor_id,
            ':diagnosis' => $diagnosis,
            ':treatment' => $treatment,
            ':prescription' => $prescription,
            ':notes' => $notes
        ]);

        if ($result) {
            // Update appointment status to Completed
            $update_query = "UPDATE appointments 
                           SET status = 'Completed' 
                           WHERE patient_id = :patient_id 
                           AND doctor_id = :doctor_id 
                           AND status = 'Confirmed'";
            
            $update_stmt = $conn->prepare($update_query);
            $update_result = $update_stmt->execute([
                ':patient_id' => $patient_id,
                ':doctor_id' => $doctor_id
            ]);

            if ($update_result) {
                // Send notification to patient
                $message = "Your medical record has been updated with new diagnosis and prescription. Please check your records.";
                $notification_sent = createPatientNotification($patient_id, $message);

                // Show success message and redirect
                echo "<script>
                    alert('Medical record saved successfully! Patient has been notified.');
                    window.location.href = 'DocEdit.php';
                </script>";
                exit();
            } else {
                $error_message = "Failed to update appointment status.";
            }
        } else {
            $error_message = "Failed to save medical record.";
        }
    } catch (PDOException $e) {
        error_log("Error in medical record update: " . $e->getMessage());
        $error_message = "Error adding medical record: " . $e->getMessage();
    }
}?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Medical Records</title>
    <link rel="stylesheet" href="../crazy.css">
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Medical Records Update</h1>
                <p class="hero-subtitle">Document • Track • Monitor</p>
            </div>
        </section>

        <section class="medical-records-section">
            <div class="form-container">
                <div class="form-card">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="medical-form">
                        <!-- Update the patient selection in the form -->
<div class="form-group">
    <label for="patient_id">
        <i class="fas fa-user"></i> Select Patient
    </label>
    <select id="patient_id" name="patient_id" required>
        <option value="">Choose a patient</option>
        <?php foreach ($patients as $patient): ?>
            <option value="<?php echo $patient['patient_id']; ?>">
                <?php echo htmlspecialchars($patient['patient_name']); ?> - 
                <?php echo date('M d, Y h:i A', strtotime($patient['appointment_date'] . ' ' . $patient['appointment_time'])); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

                        <div class="form-group">
                            <label for="diagnosis">
                                <i class="fas fa-stethoscope"></i> Diagnosis
                            </label>
                            <textarea id="diagnosis" name="diagnosis" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="treatment">
                                <i class="fas fa-procedures"></i> Treatment
                            </label>
                            <textarea id="treatment" name="treatment" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="prescription">
                                <i class="fas fa-prescription"></i> Prescription
                            </label>
                            <textarea id="prescription" name="prescription" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">
                                <i class="fas fa-notes-medical"></i> Additional Notes
                            </label>
                            <textarea id="notes" name="notes"></textarea>
                        </div>

                        <button type="submit" class="submit-button">
                            <i class="fas fa-save"></i> Save Medical Record
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include '../include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.medical-form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to save this medical record? This will mark the appointment as completed.')) {
            this.submit();
        }
    });

    // Add patient details display when selected
    const patientSelect = document.getElementById('patient_id');
    const patientDetails = document.createElement('div');
    patientDetails.className = 'patient-details';
    patientSelect.parentNode.appendChild(patientDetails);

    patientSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const patientData = <?php echo json_encode($patients); ?>;
            const patient = patientData.find(p => p.patient_id === selectedOption.value);
            if (patient) {
                patientDetails.innerHTML = `
                    <p><strong>Phone:</strong> ${patient.phone}</p>
                    <p><strong>Appointment:</strong> ${new Date(patient.appointment_date + ' ' + patient.appointment_time).toLocaleString()}</p>
                `;
            }
        } else {
            patientDetails.innerHTML = '';
        }
    });
});
</script>
</body>
</html>