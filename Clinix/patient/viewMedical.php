<?php
session_start();
require_once '../include/db.php';

// Check if user is logged in and is a patient
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$patient_id = $_SESSION['role_id'];
$error_message = '';

try {
    // Fetch patient's medical records with doctor information
    $records_query = "SELECT mr.*, 
                            d.name as doctor_name,
                            d.specialization as doctor_specialization,
                            mr.record_date
                     FROM medical_records mr
                     JOIN doctors d ON mr.doctor_id = d.doctor_id
                     WHERE mr.patient_id = :patient_id
                     ORDER BY mr.record_date DESC";
    
    $records_stmt = $conn->prepare($records_query);
    $records_stmt->execute([':patient_id' => $patient_id]);
    $medical_records = $records_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching medical records: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Medical Records</title>
    <link rel="stylesheet" href="../crazy.css">
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>My Medical Records</h1>
                <p class="hero-subtitle">View • Track • Monitor</p>
            </div>
        </section>

        <section class="records-section">
            <div class="records-container">
                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($medical_records)): ?>
                    <div class="no-records">
                        <i class="fas fa-folder-open"></i>
                        <p>No medical records found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($medical_records as $record): ?>
                        <div class="record-card">
                            <div class="record-header">
                                <div class="doctor-info">
                                    <i class="fas fa-user-md"></i>
                                    <h3>Dr. <?php echo htmlspecialchars($record['doctor_name']); ?></h3>
                                    <p><?php echo htmlspecialchars($record['doctor_specialization']); ?></p>
                                </div>
                                <div class="record-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date('F d, Y', strtotime($record['record_date'])); ?>
                                </div>
                            </div>

                            <div class="record-details">
                                <div class="record-item">
                                    <i class="fas fa-stethoscope"></i>
                                    <div>
                                        <h4>Diagnosis</h4>
                                        <p><?php echo htmlspecialchars($record['diagnosis']); ?></p>
                                    </div>
                                </div>

                                <div class="record-item">
                                    <i class="fas fa-procedures"></i>
                                    <div>
                                        <h4>Treatment</h4>
                                        <p><?php echo htmlspecialchars($record['treatment']); ?></p>
                                    </div>
                                </div>

                                <div class="record-item">
                                    <i class="fas fa-prescription-bottle-alt"></i>
                                    <div>
                                        <h4>Prescription</h4>
                                        <p><?php echo htmlspecialchars($record['prescription']); ?></p>
                                    </div>
                                </div>

                                <?php if (!empty($record['notes'])): ?>
                                    <div class="record-item">
                                        <i class="fas fa-notes-medical"></i>
                                        <div>
                                            <h4>Additional Notes</h4>
                                            <p><?php echo htmlspecialchars($record['notes']); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include '../include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>