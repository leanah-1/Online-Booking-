<?php
session_start();
require_once '../include/db.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$error_message = '';

// Fetch all statistics first
try {
    // Get doctor information
    $doctor_query = "SELECT name, specialization, contactinfo FROM doctors WHERE user_id = ?";
    $doctor_stmt = $conn->prepare($doctor_query);
    $doctor_stmt->execute([$doctor_id]);
    $doctor_info = $doctor_stmt->fetch(PDO::FETCH_ASSOC);

    // Get appointment statistics
    $stats_query = "SELECT 
        COUNT(*) as total_appointments,
        SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed
        FROM appointments 
        WHERE doctor_id = ?";
    
    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->execute([$doctor_id]);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

    // Get monthly data for chart
    $monthly_query = "SELECT 
        DATE_FORMAT(appointment_date, '%Y-%m') as month,
        COUNT(*) as count
        FROM appointments 
        WHERE doctor_id = ?
        AND appointment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(appointment_date, '%Y-%m')
        ORDER BY month ASC";
    
    $monthly_stmt = $conn->prepare($monthly_query);
    $monthly_stmt->execute([$doctor_id]);
    $monthly_data = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent patients with their details
    $patients_query = "SELECT 
        p.patient_id,
        p.name as patient_name,
        COUNT(a.appointment_id) as visit_count,
        MAX(a.appointment_date) as last_visit,
        GROUP_CONCAT(DISTINCT m.diagnosis SEPARATOR ', ') as diagnoses
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        LEFT JOIN medical_records m ON a.patient_id = m.patient_id
        WHERE a.doctor_id = ?
        GROUP BY p.patient_id
        ORDER BY last_visit DESC
        LIMIT 10";
    
    $patients_stmt = $conn->prepare($patients_query);
    $patients_stmt->execute([$doctor_id]);
    $recent_patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error fetching data: " . $e->getMessage();
    // Initialize empty arrays/objects
    $doctor_info = ['name' => '', 'specialization' => '', 'contactinfo' => ''];
    $stats = ['total_appointments' => 0, 'confirmed' => 0, 'cancelled' => 0, 'pending' => 0, 'completed' => 0];
    $monthly_data = [];
    $recent_patients = [];
}

// Function to generate detailed report
function generateReport($doctor_id, $conn, $doctor_info) {
    try {
        // Create filename with timestamp
        $filename = 'doctor_report_' . date('Y-m-d_H-i-s') . '.txt';
        $filepath = 'reports/' . $filename;

        // Create reports directory if it doesn't exist
        if (!file_exists('reports')) {
            mkdir('reports', 0777, true);
        }

        // Open file for writing
        $file = fopen($filepath, 'w') or die("Unable to open file!");

        // Write report header
        fwrite($file, str_repeat("=", 50) . "\n");
        fwrite($file, str_pad("DOCTOR ACTIVITY REPORT", 50, " ", STR_PAD_BOTH) . "\n");
        fwrite($file, str_pad(date('F d, Y H:i:s'), 50, " ", STR_PAD_BOTH) . "\n");
        fwrite($file, str_repeat("=", 50) . "\n\n");

        // Write doctor information
        fwrite($file, "DOCTOR INFORMATION\n");
        fwrite($file, str_repeat("-", 20) . "\n");
        fwrite($file, "Name: " . $doctor_info['name'] . "\n");
        fwrite($file, "Specialization: " . $doctor_info['specialization'] . "\n");
        fwrite($file, "Contact: " . $doctor_info['contactinfo'] . "\n\n");

        // Get and write detailed statistics
        $detailed_stats = getDetailedStats($conn, $doctor_id);
        foreach ($detailed_stats as $section => $data) {
            fwrite($file, strtoupper($section) . "\n");
            fwrite($file, str_repeat("-", strlen($section)) . "\n");
            foreach ($data as $key => $value) {
                fwrite($file, $key . ": " . $value . "\n");
            }
            fwrite($file, "\n");
        }

        // Close the file
        fclose($file);
        return $filename;

    } catch (Exception $e) {
        error_log("Error generating report: " . $e->getMessage());
        return false;
    }
}

function getDetailedStats($conn, $doctor_id) {
    $stats = [];
    
    try {
        // Appointment Statistics
        $appointment_query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
            FROM appointments 
            WHERE doctor_id = ?";
        
        $stmt = $conn->prepare($appointment_query);
        $stmt->execute([$doctor_id]);
        $appointment_result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['Appointment Statistics'] = [
            'Total Appointments' => $appointment_result['total'],
            'Confirmed' => $appointment_result['confirmed'],
            'Completed' => $appointment_result['completed'],
            'Cancelled' => $appointment_result['cancelled'],
            'Pending' => $appointment_result['pending']
        ];

        // Patient Demographics
        $patient_query = "SELECT 
            COUNT(DISTINCT p.patient_id) as total_patients,
            COUNT(DISTINCT CASE 
                WHEN a.appointment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) 
                THEN p.patient_id 
                END) as new_patients,
            ROUND(COUNT(a.appointment_id) / COUNT(DISTINCT p.patient_id), 2) as avg_visits
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE a.doctor_id = ?";
        
        $stmt = $conn->prepare($patient_query);
        $stmt->execute([$doctor_id]);
        $patient_result = $stmt->fetch(PDO::FETCH_ASSOC);

        $stats['Patient Demographics'] = [
            'Total Patients' => $patient_result['total_patients'],
            'New Patients (Last 30 Days)' => $patient_result['new_patients'],
            'Average Visits per Patient' => $patient_result['avg_visits']
        ];

        // Medical Records
        $records_query = "SELECT 
            COUNT(*) as total_records,
            COUNT(CASE 
                WHEN record_date >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01') 
                THEN 1 
                END) as records_this_month,
            GROUP_CONCAT(DISTINCT diagnosis SEPARATOR ', ') as common_diagnoses
            FROM medical_records
            WHERE doctor_id = ?";
        
        $stmt = $conn->prepare($records_query);
        $stmt->execute([$doctor_id]);
        $records_result = $stmt->fetch(PDO::FETCH_ASSOC);

        $stats['Medical Records'] = [
            'Total Records' => $records_result['total_records'],
            'Records This Month' => $records_result['records_this_month'],
            'Common Diagnoses' => $records_result['common_diagnoses'] ?: 'None recorded'
        ];

    } catch (PDOException $e) {
        error_log("Error getting detailed stats: " . $e->getMessage());
        // Initialize with default values if query fails
        $stats['Appointment Statistics'] = [
            'Total Appointments' => 0,
            'Confirmed' => 0,
            'Completed' => 0,
            'Cancelled' => 0,
            'Pending' => 0
        ];
        $stats['Patient Demographics'] = [
            'Total Patients' => 0,
            'New Patients (Last 30 Days)' => 0,
            'Average Visits per Patient' => 0
        ];
        $stats['Medical Records'] = [
            'Total Records' => 0,
            'Records This Month' => 0,
            'Common Diagnoses' => 'None recorded'
        ];
    }

    return $stats;
}

// Handle report download
if (isset($_POST['download_report'])) {
    $filename = generateReport($doctor_id, $conn, $doctor_info);
    
    if ($filename) {
        $filepath = 'reports/' . $filename;
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        unlink($filepath);
        exit;
    } else {
        $error_message = "Error generating report. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Reports - <?php echo htmlspecialchars($doctor_info['name']); ?></title>
    <link rel="stylesheet" href="../crazy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Activity Report Dashboard</h1>
                <p class="hero-subtitle">Dr. <?php echo htmlspecialchars($doctor_info['name']); ?> - <?php echo htmlspecialchars($doctor_info['specialization']); ?></p>
            </div>
        </section>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <section class="report-actions">
            <form method="POST" class="download-form">
                <button type="submit" name="download_report" class="download-btn">
                    <i class="fas fa-download"></i> Download Detailed Report
                </button>
            </form>
        </section>

        <section class="stats-section">
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3><?php echo htmlspecialchars($stats['total_appointments'] ?? 0); ?></h3>
                <p>Total Appointments</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h3><?php echo htmlspecialchars($stats['confirmed'] ?? 0); ?></h3>
                <p>Confirmed</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-times-circle"></i>
                <h3><?php echo htmlspecialchars($stats['cancelled'] ?? 0); ?></h3>
                <p>Cancelled</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h3><?php echo htmlspecialchars($stats['pending'] ?? 0); ?></h3>
                <p>Pending</p>
            </div>
        </section>

        <section class="chart-section">
            <div class="chart-container">
                <h2>Monthly Appointments Trend</h2>
                <canvas id="appointmentsChart"></canvas>
            </div>
        </section>

        <section class="patients-section">
            <h2>Recent Patient Activity</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Total Visits</th>
                            <th>Last Visit</th>
                            <th>Diagnoses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_patients as $patient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['visit_count']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($patient['last_visit'])); ?></td>
                                <td><?php echo htmlspecialchars($patient['diagnoses'] ?: 'None recorded'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <?php include '../include/footer.php'; ?>

    <script>
        // Initialize Monthly Appointments Chart
        const ctx = document.getElementById('appointmentsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
                datasets: [{
                    label: 'Appointments',
                    data: <?php echo json_encode(array_column($monthly_data, 'count')); ?>,
                    borderColor: '#007bff',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>