<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_appointments,
            COUNT(CASE WHEN appointment_date >= CURDATE() THEN 1 END) as future_appointments,
            COUNT(CASE WHEN appointment_date < CURDATE() THEN 1 END) as past_appointments,
            COUNT(CASE WHEN appointment_date = CURDATE() THEN 1 END) as today_appointments
        FROM appointments 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch();
    
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            DATE_FORMAT(a.appointment_date, '%d/%m/%Y') as formatted_date,
            TIME_FORMAT(a.appointment_time, '%H:%i') as formatted_time,
            DATEDIFF(a.appointment_date, CURDATE()) as days_remaining
        FROM appointments a
        WHERE a.user_id = ? 
        AND a.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $upcomingAppointments = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            DATE_FORMAT(a.appointment_date, '%d/%m/%Y') as formatted_date,
            TIME_FORMAT(a.appointment_time, '%H:%i') as formatted_time
        FROM appointments a
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recentAppointments = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = ['total_appointments' => 0, 'future_appointments' => 0, 'past_appointments' => 0, 'today_appointments' => 0];
    $upcomingAppointments = [];
    $recentAppointments = [];
}

$pageTitle = 'Dashboard - Veterinary System';
?>

<?php include '../includes/header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-hand-holding-heart me-2"></i>
                            Hello, <?php echo htmlspecialchars($userName); ?>!
                        </h2>
                        <p class="mb-0 opacity-75">
                            Welcome to your veterinary control panel
                        </p>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-paw fa-4x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-calendar-alt text-primary"></i>
                </div>
                <h3 class="fw-bold text-primary"><?php echo $stats['total_appointments']; ?></h3>
                <p class="card-text text-muted mb-0">Total Appointments</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-clock text-info"></i>
                </div>
                <h3 class="fw-bold text-info"><?php echo $stats['future_appointments']; ?></h3>
                <p class="card-text text-muted mb-0">Future Appointments</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-calendar-day text-warning"></i>
                </div>
                <h3 class="fw-bold text-warning"><?php echo $stats['today_appointments']; ?></h3>
                <p class="card-text text-muted mb-0">Today</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-history text-success"></i>
                </div>
                <h3 class="fw-bold text-success"><?php echo $stats['past_appointments']; ?></h3>
                <p class="card-text text-muted mb-0">Completed</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <a href="new_appointment.php" class="btn btn-primary w-100 py-3">
                            <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                            <strong>New Appointment</strong>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <a href="appointments.php" class="btn btn-info w-100 py-3">
                            <i class="fas fa-list fa-2x d-block mb-2"></i>
                            <strong>View All</strong>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <a href="appointments.php?filter=today" class="btn btn-warning w-100 py-3">
                            <i class="fas fa-calendar-day fa-2x d-block mb-2"></i>
                            <strong>Today</strong>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <a href="profile.php" class="btn btn-secondary w-100 py-3">
                            <i class="fas fa-user-cog fa-2x d-block mb-2"></i>
                            <strong>Profile</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-week me-2"></i>Upcoming Appointments
                </h5>
                <small class="text-muted">Next 7 days</small>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingAppointments)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No appointments scheduled for the next 7 days.</p>
                        <a href="new_appointment.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Schedule Appointment
                        </a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcomingAppointments as $appointment): ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <i class="fas fa-paw me-1 text-primary"></i>
                                            <?php echo htmlspecialchars($appointment['animal_name']); ?>
                                        </h6>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo $appointment['formatted_date']; ?> at <?php echo $appointment['formatted_time']; ?>
                                        </p>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($appointment['reason'], 0, 50)) . (strlen($appointment['reason']) > 50 ? '...' : ''); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($appointment['days_remaining'] == 0): ?>
                                            <span class="badge bg-warning">Today</span>
                                        <?php elseif ($appointment['days_remaining'] == 1): ?>
                                            <span class="badge bg-info">Tomorrow</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo $appointment['days_remaining']; ?> days</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="appointments.php?filter=future" class="btn btn-outline-primary btn-sm">
                            View all upcoming appointments
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Recent Appointments
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentAppointments)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No appointments scheduled yet.</p>
                        <a href="new_appointment.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>First Appointment
                        </a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentAppointments as $appointment): ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <i class="fas fa-paw me-1 text-veterinary"></i>
                                            <?php echo htmlspecialchars($appointment['animal_name']); ?>
                                            <small class="text-muted">(<?php echo $appointment['animal_age']; ?> years)</small>
                                        </h6>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo $appointment['formatted_date']; ?> at <?php echo $appointment['formatted_time']; ?>
                                        </p>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($appointment['reason'], 0, 50)) . (strlen($appointment['reason']) > 50 ? '...' : ''); ?>
                                        </small>
                                    </div>
                                    <div>
                                        <a href="edit_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="appointments.php" class="btn btn-outline-primary btn-sm">
                            View all appointments
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
setTimeout(function() {
    location.reload();
}, 300000);

document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const hour = now.getHours();
    const greetingEl = document.querySelector('.card-body h2');
    
    let greeting = 'Hello';
    if (hour < 12) {
        greeting = 'Good morning';
    } else if (hour < 18) {
        greeting = 'Good afternoon';
    } else {
        greeting = 'Good evening';
    }
    
    greetingEl.innerHTML = greetingEl.innerHTML.replace('Hello', greeting);
});
</script>