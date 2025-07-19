<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

$filter = $_GET['filter'] ?? 'all';
$search = sanitizeString($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'date_desc';

$whereClause = "WHERE a.user_id = ?";
$params = [$userId];

switch ($filter) {
    case 'today':
        $whereClause .= " AND a.appointment_date = CURDATE()";
        break;
    case 'future':
        $whereClause .= " AND a.appointment_date >= CURDATE()";
        break;
    case 'past':
        $whereClause .= " AND a.appointment_date < CURDATE()";
        break;
    case 'this_week':
        $whereClause .= " AND a.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        break;
}

if (!empty($search)) {
    $whereClause .= " AND (a.animal_name LIKE ? OR a.reason LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$orderClause = "ORDER BY ";
switch ($sort) {
    case 'date_asc':
        $orderClause .= "a.appointment_date ASC, a.appointment_time ASC";
        break;
    case 'date_desc':
        $orderClause .= "a.appointment_date DESC, a.appointment_time DESC";
        break;
    case 'animal':
        $orderClause .= "a.animal_name ASC";
        break;
    case 'recent':
        $orderClause .= "a.created_at DESC";
        break;
}

try {
    $sql = "
        SELECT 
            a.*,
            DATE_FORMAT(a.appointment_date, '%d/%m/%Y') as formatted_date,
            TIME_FORMAT(a.appointment_time, '%H:%i') as formatted_time,
            DATE_FORMAT(a.created_at, '%d/%m/%Y at %H:%i') as created_formatted,
            CASE 
                WHEN a.appointment_date < CURDATE() THEN 'past'
                WHEN a.appointment_date = CURDATE() THEN 'today'
                ELSE 'future'
            END as appointment_status,
            DATEDIFF(a.appointment_date, CURDATE()) as days_remaining
        FROM appointments a
        $whereClause
        $orderClause
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
    
    $sqlCount = "SELECT COUNT(*) as total FROM appointments a $whereClause";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $totalAppointments = $stmtCount->fetch()['total'];
    
} catch (PDOException $e) {
    error_log("Appointments list error: " . $e->getMessage());
    $appointments = [];
    $totalAppointments = 0;
    $error = 'Error loading appointments.';
}

$pageTitle = 'My Appointments - Veterinary System';
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>
                    <i class="fas fa-calendar-alt me-2"></i>My Appointments
                </h2>
                <p class="text-muted mb-0">
                    <?php echo $totalAppointments; ?> appointment(s) found
                </p>
            </div>
            <div>
                <a href="new_appointment.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>New Appointment
                </a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="filter" class="form-label">Filter by:</label>
                        <select class="form-select" id="filter" name="filter">
                            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
                            <option value="this_week" <?php echo $filter === 'this_week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="future" <?php echo $filter === 'future' ? 'selected' : ''; ?>>Future</option>
                            <option value="past" <?php echo $filter === 'past' ? 'selected' : ''; ?>>Past</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="sort" class="form-label">Sort by:</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="date_desc" <?php echo $sort === 'date_desc' ? 'selected' : ''; ?>>Date (newest)</option>
                            <option value="date_asc" <?php echo $sort === 'date_asc' ? 'selected' : ''; ?>>Date (oldest)</option>
                            <option value="animal" <?php echo $sort === 'animal' ? 'selected' : ''; ?>>Animal Name</option>
                            <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>>Recently Added</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search:</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Animal name or reason...">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="appointments.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (empty($appointments)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No appointments found</h4>
                    <p class="text-muted mb-4">
                        <?php if (!empty($search) || $filter !== 'all'): ?>
                            Try adjusting the filters or search terms.
                        <?php else: ?>
                            You haven't scheduled any appointments yet.
                        <?php endif; ?>
                    </p>
                    <a href="new_appointment.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Schedule First Appointment
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($appointments as $appointment): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 <?php echo $appointment['appointment_status'] === 'today' ? 'border-warning' : ''; ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-paw me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($appointment['animal_name']); ?>
                                </h5>
                                <div>
                                    <?php if ($appointment['appointment_status'] === 'today'): ?>
                                        <span class="badge bg-warning">Today</span>
                                    <?php elseif ($appointment['appointment_status'] === 'future'): ?>
                                        <?php if ($appointment['days_remaining'] == 1): ?>
                                            <span class="badge bg-info">Tomorrow</span>
                                        <?php elseif ($appointment['days_remaining'] <= 7): ?>
                                            <span class="badge bg-primary"><?php echo $appointment['days_remaining']; ?> days</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Future</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Date</small>
                                        <strong>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo $appointment['formatted_date']; ?>
                                        </strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Time</small>
                                        <strong>
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo $appointment['formatted_time']; ?>
                                        </strong>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted d-block">Animal Age</small>
                                    <span><?php echo $appointment['animal_age']; ?> years</span>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted d-block">Reason</small>
                                    <p class="mb-0">
                                        <?php echo htmlspecialchars($appointment['reason']); ?>
                                    </p>
                                </div>
                                
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Scheduled on <?php echo $appointment['created_formatted']; ?>
                                </small>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="d-flex gap-2 justify-content-end">
                                    <?php if ($appointment['appointment_status'] !== 'past'): ?>
                                        <a href="edit_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <a href="delete_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-outline-danger btn-sm btn-delete"
                                           data-item="this appointment">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-check-circle me-1"></i>Appointment completed
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('filter');
    const sortSelect = document.getElementById('sort');
    
    filterSelect.addEventListener('change', function() {
        this.form.submit();
    });
    
    sortSelect.addEventListener('change', function() {
        this.form.submit();
    });
    
    const searchTerm = '<?php echo addslashes($search); ?>';
    if (searchTerm) {
        const elements = document.querySelectorAll('.card-body');
        elements.forEach(element => {
            const text = element.innerHTML;
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            element.innerHTML = text.replace(regex, '<mark>$1</mark>');
        });
    }
});
</script>