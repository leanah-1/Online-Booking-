<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Management System - Home</title>
    <link rel="stylesheet" href="crazy.css">
</head>
<body>
    <?php include 'include/header.php'; ?>

    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Clinic Revolution</h1>
                <p class="hero-subtitle">Access • Patient Friendly • Instant Notification</p>
                <a href="login.php" class="cta-button">Book Appointment</a>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="stat-card animate">
                <i class="fas fa-users"></i>
                <h3>10,000+</h3>
                <p>Happy Patients</p>
            </div>
            <div class="stat-card animate">
                <i class="fas fa-chart-line"></i>
                <h3>99%</h3>
                <p>Patient Satisfaction</p>
            </div>
            <div class="stat-card animate">
                <i class="fas fa-file-medical"></i>
                <h3>1,000+</h3>
                <p>Records Handled</p>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section">
            <h2 class="section-title">Your Questions Answered!</h2>
            <div class="faq-container">
                <details class="faq-item">
                    <summary>How do I book an appointment?</summary>
                    <div class="faq-content">
                        <p>You can easily book through our online portal or call our reception.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>What if I need to cancel?</summary>
                    <div class="faq-content">
                        <p>Cancellations can be made 24 hours before your appointment.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>Do you accept insurance?</summary>
                    <div class="faq-content">
                        <p>Yes, we work with most major insurance providers.</p>
                    </div>
                </details>
            </div>
        </section>

        <!-- Doctors Profile Section -->
<section class="doctors-section">
    <h2 class="section-title">Our Medical Team</h2>
    <div class="doctors-grid">
        <?php
        try {
            require_once 'include/db.php';
            // Fetch all active doctors
            $query = "SELECT name, specialization, contactinfo FROM doctors ORDER BY name ASC LIMIT 3";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($doctors)) {
                foreach ($doctors as $doctor) {
                    echo '<div class="doctor-card animate">
                            <div class="doctor-header">
                                <div class="doctor-avatar">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <h3>' . htmlspecialchars($doctor['name']) . '</h3>
                                <p class="doctor-title">' . htmlspecialchars($doctor['specialization']) . '</p>
                            </div>
                            <div class="doctor-info">
                                <ul>
                                    <li><i class="fas fa-stethoscope"></i> ' . htmlspecialchars($doctor['specialization']) . '</li>
                                    <li><i class="fas fa-phone"></i> ' . htmlspecialchars($doctor['contactinfo']) . '</li>
                                </ul>
                            </div>
                            <a href="sign.php" class="doctor-button">Book Appointment</a>
                        </div>';
                }
            } else {
                echo '<div class="no-doctors">
                        <p>No doctors available at the moment.</p>
                      </div>';
            }
        } catch (PDOException $e) {
            error_log("Error fetching doctors: " . $e->getMessage());
            echo '<div class="error-message">Unable to load doctors profiles.</div>';
        }
        ?>
    </div>
    
</section>


        <!-- Latest News Section -->
        <section class="latest-news">
            <h2 class="section-title">Latest Buzz</h2>
            <div class="news-grid">
                <div class="news-card">
                    <div class="news-image">
                        <img src="images/feature1.svg" alt="Health Tips">
                    </div>
                    <div class="news-content">
                        <h3>New Health Services Launched</h3>
                        <p>Expanding our care options for you</p>
                        <a href="news.php" class="read-more">Read More</a>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-image">
                        <img src="images/feature2.svg" alt="Medical Tips">
                    </div>
                    <div class="news-content">
                        <h3>Health Fair Coming Soon</h3>
                        <p>Join us for free health screenings</p>
                        <a href="news.php" class="read-more">Read More</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include 'include/footer.php'; ?>

    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>