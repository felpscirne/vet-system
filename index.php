<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit;
}

$pageTitle = 'Veterinary Appointment System';
?>

<?php include 'includes/header.php'; ?>

<div class="hero-section py-5 mb-5">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <div class="fade-in">
                <h1 class="display-4 fw-bold text-primary mb-4">
                    <i class="fas fa-paw text-veterinary me-3"></i>
                    Veterinary System
                </h1>
                <p class="lead text-muted mb-4">
                    Manage veterinary appointments easily and efficiently. 
                    Schedule, view and control all your pet appointments.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="pages/register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </a>
                    <a href="pages/login.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6 text-center">
            <div class="fade-in">
                <i class="fas fa-heart text-danger" style="font-size: 12rem; opacity: 0.1;"></i>
                <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    <i class="fas fa-dog text-primary" style="font-size: 6rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-12">
        <h2 class="text-center mb-5">
            <i class="fas fa-star text-warning me-2"></i>
            Key Features
        </h2>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card h-100 text-center dashboard-card fade-in">
            <div class="card-body">
                <div class="dashboard-icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <h5 class="card-title">Easy Scheduling</h5>
                <p class="card-text text-muted">
                    Schedule appointments for your animals quickly and intuitively, 
                    with automatic date and time validation.
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card h-100 text-center dashboard-card fade-in">
            <div class="card-body">
                <div class="dashboard-icon">
                    <i class="fas fa-list-alt"></i>
                </div>
                <h5 class="card-title">Complete Control</h5>
                <p class="card-text text-muted">
                    View, edit and manage all your scheduled appointments 
                    in a centralized and organized panel.
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card h-100 text-center dashboard-card fade-in">
            <div class="card-body">
                <div class="dashboard-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h5 class="card-title">Security</h5>
                <p class="card-text text-muted">
                    System developed with the best security practices, 
                    protecting your data and personal information.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    How It Works
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">
                            <i class="fas fa-check-circle me-2"></i>What you can do:
                        </h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-arrow-right text-success me-2"></i>View all scheduled appointments</li>
                            <li><i class="fas fa-arrow-right text-success me-2"></i>Schedule new appointments</li>
                            <li><i class="fas fa-arrow-right text-success me-2"></i>Edit your own appointments</li>
                            <li><i class="fas fa-arrow-right text-success me-2"></i>Cancel appointments you scheduled</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">
                            <i class="fas fa-clock me-2"></i>Scheduling Rules:
                        </h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-arrow-right text-info me-2"></i>Hours: 08:00 to 18:00</li>
                            <li><i class="fas fa-arrow-right text-info me-2"></i>Weekdays only (Mon-Fri)</li>
                            <li><i class="fas fa-arrow-right text-info me-2"></i>Future dates only</li>
                            <li><i class="fas fa-arrow-right text-info me-2"></i>Reason required (min. 10 chars)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body text-center py-5">
                <h3 class="mb-4">Professional Pet Care</h3>
                <p class="lead mb-0">
                    Complete solution for managing your pet's health appointments
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.fade-in');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        }, index * 200);
    });
});
</script>