<?php
/**
 * Landing Page
 * Hospital Management System
 */

require_once __DIR__ . '/../config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/landing.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-hospital"></i>
                <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-login" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            Empowering Healthcare with 
                            <span class="text-primary">Digital Excellence</span>
                        </h1>
                        <p class="hero-description">
                            Streamline your hospital operations with our comprehensive management system. 
                            Manage patients, appointments, admissions, and more with ease and efficiency.
                        </p>
                        <div class="hero-buttons">
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Access System
                            </a>
                            <a href="#features" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-info-circle"></i> Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <div class="hero-card">
                            <i class="fas fa-hospital-alt"></i>
                            <h3>Hospital Management</h3>
                            <p>Complete digital solution for modern healthcare facilities</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">Why Choose Our System?</h2>
                    <p class="section-description">
                        Experience comprehensive healthcare management delivered by our expert team, 
                        supported by modern technology and round-the-clock system availability.
                    </p>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Patient Management</h4>
                        <p>Comprehensive patient records, medical history, and treatment tracking in one centralized system.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4>Appointment Scheduling</h4>
                        <p>Efficient appointment booking, scheduling, and management with automated reminders.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bed"></i>
                        </div>
                        <h4>Admission Control</h4>
                        <p>Streamlined admission processes with bed management and patient flow optimization.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Analytics & Reports</h4>
                        <p>Comprehensive reporting and analytics to track performance and make informed decisions.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Secure & Compliant</h4>
                        <p>Advanced security measures and compliance with healthcare data protection standards.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Mobile Responsive</h4>
                        <p>Access your hospital management system from any device, anywhere, anytime.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">System Modules</h2>
                    <p class="section-description">
                        Our comprehensive hospital management system includes specialized modules 
                        designed to streamline every aspect of healthcare operations.
                    </p>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-lg-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="service-content">
                            <h4>Doctor Dashboard</h4>
                            <p>Comprehensive doctor interface with patient management, appointment scheduling, and treatment planning tools.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <div class="service-content">
                            <h4>Staff Management</h4>
                            <p>Complete staff portal with admissions, appointments, billing, lab, pharmacy, and reporting capabilities.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="service-content">
                            <h4>Laboratory Management</h4>
                            <p>Advanced lab test management with result tracking, reporting, and integration with patient records.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="service-content">
                            <h4>Pharmacy Management</h4>
                            <p>Complete pharmacy operations including prescription management, inventory control, and medication tracking.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="service-content">
                            <h4>Billing & Payments</h4>
                            <p>Streamlined billing processes with payment tracking, insurance management, and financial reporting.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="service-content">
                            <h4>Reports & Analytics</h4>
                            <p>Comprehensive reporting system with analytics dashboard for performance monitoring and decision making.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="features-content">
                        <h2 class="section-title">Advanced Features</h2>
                        <div class="feature-list">
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <h5>Real-time Data Sync</h5>
                                    <p>All data synchronized in real-time across all modules and users</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <h5>Role-based Access</h5>
                                    <p>Secure access control with different permission levels for doctors, staff, and administrators</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <h5>Automated Workflows</h5>
                                    <p>Streamlined processes with automated notifications and workflow management</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <h5>Integration Ready</h5>
                                    <p>Easy integration with existing hospital systems and third-party applications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="features-image">
                        <div class="dashboard-preview">
                            <div class="preview-header">
                                <div class="preview-dots">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <span class="preview-title">Dashboard Preview</span>
                            </div>
                            <div class="preview-content">
                                <div class="preview-card">
                                    <i class="fas fa-users"></i>
                                    <span>Patients</span>
                                </div>
                                <div class="preview-card">
                                    <i class="fas fa-calendar"></i>
                                    <span>Appointments</span>
                                </div>
                                <div class="preview-card">
                                    <i class="fas fa-bed"></i>
                                    <span>Admissions</span>
                                </div>
                                <div class="preview-card">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Reports</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">Get Started Today</h2>
                    <p class="section-description">
                        Ready to transform your hospital operations? Access our management system 
                        and experience the future of healthcare administration.
                    </p>
                    <div class="contact-buttons">
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Access System
                        </a>
                        <a href="mailto:admin@hospital.com" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-envelope"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="footer-brand">
                        <i class="fas fa-hospital"></i>
                        <h4><?php echo APP_NAME; ?></h4>
                        <p>Quality Care, Digital Excellence</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="footer-links">
                        <h5>Quick Links</h5>
                        <ul>
                            <li><a href="#about">About</a></li>
                            <li><a href="#services">Services</a></li>
                            <li><a href="#features">Features</a></li>
                            <li><a href="login.php">Login</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="footer-contact">
                        <h5>Contact Information</h5>
                        <p><i class="fas fa-envelope"></i> admin@hospital.com</p>
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-map-marker-alt"></i> Hospital Management System</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="footer-bottom">
                        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/landing.js"></script>
</body>
</html>
