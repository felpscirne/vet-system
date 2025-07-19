<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];
$appointmentId = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            DATE_FORMAT(a.appointment_date, '%d/%m/%Y') as formatted_date,
            TIME_FORMAT(a.appointment_time, '%H:%i') as formatted_time
        FROM appointments a
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$appointmentId, $userId]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        $_SESSION['error'] = 'Appointment not found or you do not have permission to delete it.';
        header('Location: appointments.php');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Delete appointment error: " . $e->getMessage());
    $_SESSION['error'] = 'Error loading appointment.';
    header('Location: appointments.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = $_POST['confirm'] ?? '';
    
    if ($confirm === 'yes') {
        try {
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
            
            if ($stmt->execute([$appointmentId, $userId])) {
                $_SESSION['success'] = 'Appointment deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete appointment.';
            }
            
        } catch (PDOException $e) {
            error_log("Delete appointment error: " . $e->getMessage());
            $_SESSION['error'] = 'Internal error deleting appointment.';
        }
    }
    
    header('Location: appointments.php');
    exit;
}

$pageTitle = 'Delete Appointment - Veterinary System';
?>

<?php include '../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                </h4>
            </div>
            
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-warning me-2"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                
                <p class="mb-4">
                    Are you sure you want to delete the appointment below?
                </p>
                
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-paw me-2 text-primary"></i>
                            <?php echo htmlspecialchars($appointment['animal_name']); ?>
                        </h5>
                        
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
                        
                        <div class="mb-2">
                            <small class="text-muted d-block">Animal Age</small>
                            <span><?php echo $appointment['animal_age']; ?> years</span>
                        </div>
                        
                        <div>
                            <small class="text-muted d-block">Reason</small>
                            <p class="mb-0">
                                <?php echo htmlspecialchars($appointment['reason']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <form method="POST">
                    <div class="d-flex gap-3 justify-content-end">
                        <a href="appointments.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" 
                                name="confirm" 
                                value="yes" 
                                class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.')">
                            <i class="fas fa-trash me-1"></i>Yes, Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body text-center">
                <h6 class="text-muted">
                    <i class="fas fa-info-circle me-2"></i>Other Options
                </h6>
                <p class="text-muted small mb-3">
                    If you just want to change the date or time, 
                    consider editing the appointment instead of deleting it.
                </p>
                <a href="edit_appointment.php?id=<?php echo $appointment['id']; ?>" 
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit Appointment
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const deleteButton = document.querySelector('button[name="confirm"]');
    
    let counter = 5;
    deleteButton.disabled = true;
    deleteButton.innerHTML = '<i class="fas fa-clock me-1"></i>Wait ' + counter + 's';
    
    const interval = setInterval(function() {
        counter--;
        if (counter > 0) {
            deleteButton.innerHTML = '<i class="fas fa-clock me-1"></i>Wait ' + counter + 's';
        } else {
            deleteButton.disabled = false;
            deleteButton.innerHTML = '<i class="fas fa-trash me-1"></i>Yes, Delete';
            clearInterval(interval);
        }
    }, 1000);
    
    form.addEventListener('submit', function(e) {
        const animalName = '<?php echo addslashes($appointment['animal_name']); ?>';
        const appointmentDate = '<?php echo addslashes($appointment['formatted_date']); ?>';
        
        const confirmation = confirm(
            `FINAL CONFIRMATION:\n\n` +
            `Delete ${animalName}'s appointment on ${appointmentDate}?\n\n` +
            `This action is IRREVERSIBLE!`
        );
        
        if (!confirmation) {
            e.preventDefault();
        }
    });
});
</script>