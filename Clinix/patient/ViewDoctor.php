<?php
require_once '../session.php';
require_once '../include/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

try {
    // Fetch all doctors
    $query = "SELECT * FROM doctors ORDER BY name ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching doctors: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - Clinic Management System</title>
    <link rel="stylesheet" href="../crazy.css">
    <style>
        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .doctor-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
        }

        .doctor-info {
            margin-top: 15px;
        }

        .doctor-info h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .doctor-info p {
            color: #666;
            margin: 5px 0;
        }

        .doctor-icon {
            font-size: 2em;
            color: #3498db;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include '../include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Our Doctors</h1>
                <p class="hero-subtitle">Meet Our Professional Medical Team</p>
            </div>
        </section>

        <section class="doctors-section">
            <div class="doctors-grid">
                <?php if (!empty($doctors)): ?>
                    <?php foreach ($doctors as $doctor): ?>
                        <div class="doctor-card animate">
                            <div class="doctor-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="doctor-info">
                                <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                                <p>
                                    <i class="fas fa-stethoscope"></i>
                                    Specialization: <?php echo htmlspecialchars($doctor['specialization']); ?>
                                </p>
                                <p>
                                    <i class="fas fa-phone"></i>
                                    Contact: <?php echo htmlspecialchars($doctor['contactinfo']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-doctors">No doctors available at the moment.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include '../include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>