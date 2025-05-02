<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Clinic Management System</title>
    <link rel="stylesheet" href="crazy.css">
</head>
<body>
    <?php include 'include/header.php'; ?>
    
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Our Services</h1>
                <p class="hero-subtitle">Comprehensive Healthcare Solutions</p>
            </div>
        </section>

        <!-- Services Grid Section -->
        <section class="services-grid-section">
            <div class="services-grid">
                <div class="service-card animate">
                    <div class="service-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3>General Consultations</h3>
                    <p>Comprehensive medical check-ups and consultations with our experienced doctors.</p>
                </div>

                <div class="service-card animate">
                    <div class="service-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3>Diagnostic Services</h3>
                    <p>Advanced diagnostic testing and medical imaging services.</p>
                </div>

                <div class="service-card animate">
                    <div class="service-icon">
                        <i class="fas fa-procedures"></i>
                    </div>
                    <h3>Specialized Treatments</h3>
                    <p>Specialized medical treatments and procedures by expert specialists.</p>
                </div>

                <div class="service-card animate">
                    <div class="service-icon">
                        <i class="fas fa-pills"></i>
                    </div>
                    <h3>Pharmacy Services</h3>
                    <p>Full-service pharmacy with prescription and over-the-counter medications.</p>
                </div>

                <div class="service-card animate">
                    <div class="service-icon">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <h3>Emergency Care</h3>
                    <p>24/7 emergency medical services with rapid response capabilities.</p>
                </div>

                <div class="service-card animate">
                    <div class="service-icon">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <h3>Preventive Care</h3>
                    <p>Comprehensive preventive care and wellness programs.</p>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <h2 class="section-title">Why Choose Us</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-clock"></i>
                    <h3>24/7 Availability</h3>
                    <p>Round-the-clock medical services</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-user-md"></i>
                    <h3>Expert Doctors</h3>
                    <p>Highly qualified medical professionals</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-hospital"></i>
                    <h3>Modern Facilities</h3>
                    <p>State-of-the-art medical equipment</p>
                </div>
            </div>
        </section>

        <!-- Appointment Section -->
        <section class="appointment-section">
            <div class="appointment-content">
                <h2>Need a Consultation?</h2>
                <p>Book an appointment with our specialists today</p>
                <a href="login.php" class="cta-button">Book Appointment</a>
            </div>
        </section>
    </main>

    <?php include 'include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>