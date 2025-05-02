<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Clinic Management System</title>
    <link rel="stylesheet" href="crazy.css">
</head>
<body>
    <?php include 'include/header.php'; ?>
    
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Contact Us</h1>
                <p class="hero-subtitle">We're Here to Help You</p>
            </div>
        </section>

        <!-- Contact Information Section -->
        <section class="contact-info-section">
            <div class="contact-grid">
                <div class="contact-card animate">
                    <i class="fas fa-phone"></i>
                    <h3>Phone</h3>
                    <p>+256 700 123 456</p>
                    <p>Emergency: +256 800 123 456</p>
                </div>
                <div class="contact-card animate">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p>info@clinic.com</p>
                    <p>support@clinic.com</p>
                </div>
                <div class="contact-card animate">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Location</h3>
                    <p>123 Health Street</p>
                    <p>Kampala, Uganda</p>
                </div>
                <div class="contact-card animate">
                    <i class="fas fa-clock"></i>
                    <h3>Working Hours</h3>
                    <p>Monday - Friday: 8:00 AM - 8:00 PM</p>
                    <p>Weekend: 9:00 AM - 6:00 PM</p>
                </div>
            </div>
        </section>
        <?php
// Include database configuration file
include 'include/db.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    try {
        // Prepare the insert query
        $query = "INSERT INTO messages (name, email, message, created_at) VALUES (:name, :email, :message, NOW())";
        $stmt = $conn->prepare($query);

        // Bind parameters using PDO::PARAM_STR for each
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);

        // Execute the query
        if ($stmt->execute()) {
            echo "<script>alert('Message sent successfully!'); window.location.href='contactus.php';</script>";
        } else {
            echo "<script>alert('Failed to send message. Please try again.'); window.location.href='contactus.php';</script>";
        }
    } catch (PDOException $e) {
        // Handle any PDO exceptions
        echo "<script>alert('Database error: " . $e->getMessage() . "'); window.location.href='contact.php';</script>";
    }

    // Close the connection
    $conn = null;
}
?>





        <!-- Contact Form Section -->
        <section class="contact-form-section">
            <h2 class="section-title">Send Us a Message</h2>
            <form action="contactus.php" method="POST" class="contact-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="6" required></textarea>
                    </div>
                    <div class="form-group full-width">
                        <button type="submit" class="submit-button">Send Message</button>
                    </div>
                </div>
            </form>
        </section>

        
        </section>
    </main>

    <?php include 'include/footer.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>