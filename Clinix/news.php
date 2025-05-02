<?php
require_once 'include/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Updates - Clinic Management System</title>
    <link rel="stylesheet" href="crazy.css">
    <style>
        .news-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .news-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .news-card:hover {
            transform: translateY(-5px);
        }

        .news-image {
            height: 200px;
            overflow: hidden;
        }

        .news-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .news-content {
            padding: 20px;
        }

        .news-date {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .news-tag {
            display: inline-block;
            padding: 5px 10px;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 15px;
            font-size: 0.8em;
            margin-bottom: 10px;
        }

        .news-title {
            font-size: 1.2em;
            margin-bottom: 10px;
            color: #333;
        }

        .news-excerpt {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        
        
        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .section-subtitle {
            color: #666;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <?php include 'include/header.php'; ?>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Latest Updates</h1>
                <p class="hero-subtitle">Stay Informed About Our Services and Community Health Initiatives</p>
            </div>
        </section>

        <div class="news-container">
            <div class="section-header">
                <h2 class="section-title">What's New at Our Clinic</h2>
                <p class="section-subtitle">Keeping You Updated on Healthcare Services and Community Wellness</p>
            </div>

            <div class="news-grid">
                <!-- New Services -->
                <div class="news-card animate">
                    <div class="news-image">
                        <img src="images/feature1.svg" alt="New Services">
                    </div>
                    <div class="news-content">
                        <div class="news-date">
                            <i class="far fa-calendar"></i> <?php echo date('F d, Y'); ?>
                        </div>
                        <span class="news-tag">New Services</span>
                        <h3 class="news-title">Extended Clinic Hours</h3>
                        <p class="news-excerpt">
                            We're now open longer to serve you better! Visit us from Monday to Saturday, 
                            9:00 AM to 5:00 PM. Emergency services available 24/7.
                        </p>
                    </div>
                </div>

                <!-- Community Care -->
                <div class="news-card animate">
                    <div class="news-image">
                        <img src="images/feature2.svg" alt="Community Care">
                    </div>
                    <div class="news-content">
                        <div class="news-date">
                            <i class="far fa-calendar"></i> <?php echo date('F d, Y'); ?>
                        </div>
                        <span class="news-tag">Community Care</span>
                        <h3 class="news-title">Free Health Screenings</h3>
                        <p class="news-excerpt">
                            Join us every last Saturday of the month for free health screenings. 
                            Services include blood pressure checks, BMI assessment, and basic health consultations.
                        </p>
                    </div>
                </div>

                <!-- Future Plans -->
                <div class="news-card animate">
                    <div class="news-image">
                        <img src="images/feature3.svg" alt="Future Plans">
                    </div>
                    <div class="news-content">
                        <div class="news-date">
                            <i class="far fa-calendar"></i> <?php echo date('F d, Y'); ?>
                        </div>
                        <span class="news-tag">Future Plans</span>
                        <h3 class="news-title">New Pediatric Wing Coming Soon</h3>
                        <p class="news-excerpt">
                            We're expanding our facilities to include a specialized pediatric wing, 
                            ensuring the best care for our youngest patients.
                        </p>
                    </div>
                </div>

                <!-- Patient Care -->
                <div class="news-card animate">
                    <div class="news-image">
                        <img src="images/feature4.svg" alt="Patient Care">
                    </div>
                    <div class="news-content">
                        <div class="news-date">
                            <i class="far fa-calendar"></i> <?php echo date('F d, Y'); ?>
                        </div>
                        <span class="news-tag">Patient Care</span>
                        <h3 class="news-title">New Patient Support Program</h3>
                        <p class="news-excerpt">
                            Introducing our patient support program offering guidance on medication, 
                            lifestyle changes, and continuous health monitoring.
                        </p>
                    </div>
                </div>

                <!-- Health Education -->
                <div class="news-card animate">
                    <div class="news-image">
                        <img src="images/feature5.svg" alt="Health Education">
                    </div>
                    <div class="news-content">
                        <div class="news-date">
                            <i class="far fa-calendar"></i> <?php echo date('F d, Y'); ?>
                        </div>
                        <span class="news-tag">Health Education</span>
                        <h3 class="news-title">Monthly Health Workshops</h3>
                        <p class="news-excerpt">
                            Join our free monthly workshops covering topics like diabetes management, 
                            nutrition, and mental health awareness.
                        </p>
                    </div>
                </div>

                <!-- Staff Updates -->
                <div class="news-card animate">
                    <div class="news-image">
                        <img src="images/feature1.svg" alt="Staff Updates">
                    </div>
                    <div class="news-content">
                        <div class="news-date">
                            <i class="far fa-calendar"></i> <?php echo date('F d, Y'); ?>
                        </div>
                        <span class="news-tag">Staff Updates</span>
                        <h3 class="news-title">Welcome New Specialists</h3>
                        <p class="news-excerpt">
                            We're proud to welcome new specialists to our team, expanding our 
                            expertise in cardiology and pediatric care.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>