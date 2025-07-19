<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];
$appointmentId = (int)($_GET['id'] ?? 0);
$error = '';

try {
    $stmt = $pdo->prepare("
        SELECT * FROM appointments 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$appointmentId, $userId]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        $_SESSION['error'] = 'Appointment not found or you do not have permission to edit it.';
        header('Location: appointments.php');
        exit;
    }
    
    if ($appointment['appointment_date'] < date('Y-m-d')) {
        $_SESSION['error'] = 'Cannot edit past appointments.';
        header('Location: appointments.php');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Edit appointment error: " . $e->getMessage());
    $_SESSION['error'] = 'Error loading appointment.';
    header('Location: appointments.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animalName = sanitizeString($_POST['animal_name'] ?? '');
    $animalAge = (int)($_POST['animal_age'] ?? 0);
    $appointmentDate = $_POST['appointment_date'] ?? '';
    $appointmentTime = $_POST['appointment_time'] ?? '';
    $reason = sanitizeString($_POST['reason'] ?? '');
    
    if (empty($animalName) || empty($appointmentDate) || empty($appointmentTime) || empty($reason)) {
        $error = 'All fields are required.';
    } elseif (strlen($animalName) < 2) {
        $error = 'Animal name must be at least 2 characters.';
    } elseif ($animalAge <= 0 || $animalAge > 50) {
        $error = 'Age must be between 1 and 50 years.';
    } elseif (!validateFutureDate($appointmentDate)) {
        $error = 'Appointment date must be in the future.';
    } elseif (!validateWeekday($appointmentDate)) {
        $error = 'Appointments can only be scheduled Monday to Friday.';
    } elseif (!validateBusinessHours($appointmentTime)) {
        $error = 'Business hours: 08:00 to 18:00.';
    } elseif (!validateReason($reason)) {
        $error = 'Reason must be at least 10 characters.';
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT id FROM appointments 
                WHERE appointment_date = ? AND appointment_time = ? AND user_id = ? AND id != ?
            ");
            $stmt->execute([$appointmentDate, $appointmentTime, $userId, $appointmentId]);
            
            if ($stmt->fetch()) {
                $error = 'You already have an appointment at this time.';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE appointments 
                    SET animal_name = ?, animal_age = ?, appointment_date = ?, appointment_time = ?, reason = ?
                    WHERE id = ? AND user_id = ?
                ");
                
                if ($stmt->execute([$animalName, $animalAge, $appointmentDate, $appointmentTime, $reason, $appointmentId, $userId])) {
                    $_SESSION['success'] = 'Appointment updated successfully!';
                    header('Location: appointments.php');
                    exit;
                } else {
                    $error = 'Failed to update appointment. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log("Update appointment error: " . $e->getMessage());
            $error = 'Internal error. Please try again.';
        }
    }
    
    $appointment['animal_name'] = $animalName;
    $appointment['animal_age'] = $animalAge;
    $appointment['appointment_date'] = $appointmentDate;
    $appointment['appointment_time'] = $appointmentTime;
    $appointment['reason'] = $reason;
}

$pageTitle = 'Edit Appointment - Veterinary System';
?>

<?php include '../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Edit Appointment
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">
                    Update information for <strong><?php echo htmlspecialchars($appointment['animal_name']); ?></strong>'s appointment.
                </p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="animal_name" class="form-label">
                                <i class="fas fa-paw me-1"></i>Animal Name *
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="animal_name" 
                                   name="animal_name" 
                                   value="<?php echo htmlspecialchars($appointment['animal_name']); ?>"
                                   required 
                                   minlength="2"
                                   maxlength="100"
                                   placeholder="e.g. Rex, Fluffy, Buddy...">
                            <div class="invalid-feedback">
                                Animal name is required (min. 2 characters).
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="animal_age" class="form-label">
                                <i class="fas fa-birthday-cake me-1"></i>Age (years) *
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="animal_age" 
                                   name="animal_age" 
                                   value="<?php echo htmlspecialchars($appointment['animal_age']); ?>"
                                   required 
                                   min="1" 
                                   max="50"
                                   placeholder="1">
                            <div class="invalid-feedback">
                                Age between 1 and 50 years.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="appointment_date" class="form-label">
                                <i class="fas fa-calendar me-1"></i>Appointment Date *
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="appointment_date" 
                                   name="appointment_date" 
                                   value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>"
                                   required 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            <div class="invalid-feedback">
                                Select a future date (Mon-Fri).
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Service Monday to Friday
                            </small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="appointment_time" class="form-label">
                                <i class="fas fa-clock me-1"></i>Time *
                            </label>
                            <select class="form-select" 
                                    id="appointment_time" 
                                    name="appointment_time" 
                                    required>
                                <option value="">Select time</option>
                                <?php
                                for ($h = 8; $h <= 18; $h++) {
                                    for ($m = 0; $m < 60; $m += 30) {
                                        if ($h == 18 && $m > 0) break;
                                        
                                        $time = sprintf('%02d:%02d', $h, $m);
                                        $selected = ($appointment['appointment_time'] === $time) ? 'selected' : '';
                                        echo "<option value=\"$time\" $selected>$time</option>";
                                    }
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">
                                Select a time.
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Business hours: 08:00 to 18:00
                            </small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="reason" class="form-label">
                            <i class="fas fa-notes-medical me-1"></i>Reason for Appointment *
                        </label>
                        <textarea class="form-control" 
                                  id="reason" 
                                  name="reason" 
                                  rows="4" 
                                  required 
                                  minlength="10"
                                  maxlength="500"
                                  placeholder="Describe the reason for the appointment (symptoms, behavior, etc.)"><?php echo htmlspecialchars($appointment['reason']); ?></textarea>
                        <div class="invalid-feedback">
                            Reason must be at least 10 characters.
                        </div>
                        <small class="form-text text-muted">
                            <span id="char-counter">0</span>/500 characters
                        </small>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="appointments.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-muted">
                    <i class="fas fa-history me-2"></i>Original Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Original Date</small>
                        <p><?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Scheduled on</small>
                        <p><?php echo date('d/m/Y \a\t H:i', strtotime($appointment['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    const dateInput = document.getElementById('appointment_date');
    const reasonTextarea = document.getElementById('reason');
    const charCounter = document.getElementById('char-counter');
    
    function updateCounter() {
        const length = reasonTextarea.value.length;
        charCounter.textContent = length;
        
        if (length < 10) {
            charCounter.className = 'text-danger';
        } else if (length > 450) {
            charCounter.className = 'text-warning';
        } else {
            charCounter.className = 'text-success';
        }
    }
    
    reasonTextarea.addEventListener('input', updateCounter);
    updateCounter();
    
    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const dayOfWeek = selectedDate.getDay();
        
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
            
            let feedback = this.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = 'Appointments only Monday to Friday.';
            }
        } else {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
        }
    });
    
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    });
    
    const ageInput = document.getElementById('animal_age');
    ageInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    
    let formModified = false;
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            formModified = true;
        });
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formModified) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Do you really want to leave?';
        }
    });
    
    form.addEventListener('submit', function() {
        formModified = false;
    });
    
    document.getElementById('animal_name').focus();
});
</script>