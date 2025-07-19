<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeString($_POST['name'] ?? '');
    $email = sanitizeString($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required.';
    } elseif (strlen($name) < 3) {
        $error = 'Name must be at least 3 characters.';
    } elseif (!validateEmail($email)) {
        $error = 'Enter a valid email.';
    } elseif (!validatePassword($password)) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'This email is already registered.';
            } else {
                $passwordHash = hashPassword($password);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$name, $email, $passwordHash])) {
                    $success = 'Registration successful! You can now login.';
                    $name = $email = '';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = 'Internal error. Please try again.';
        }
    }
}

$pageTitle = 'Register - Veterinary System';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-user-plus fa-3x mb-3"></i>
                <h1>Register</h1>
                <p class="mb-0">Create your account</p>
            </div>
            
            <div class="auth-body">
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
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-user me-1"></i>Full Name
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="name" 
                               name="name" 
                               value="<?php echo htmlspecialchars($name ?? ''); ?>"
                               required 
                               minlength="3"
                               maxlength="100"
                               placeholder="Your full name">
                        <div class="invalid-feedback">
                            Name must be between 3 and 100 characters.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               required 
                               maxlength="150"
                               placeholder="your@email.com">
                        <div class="invalid-feedback">
                            Enter a valid email.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Password
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="6"
                                   placeholder="Minimum 6 characters">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Password must be at least 6 characters.
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Confirm Password
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required 
                               minlength="6"
                               placeholder="Repeat password">
                        <div class="invalid-feedback">
                            Passwords must match.
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <p class="mb-0">
                        Already have an account? 
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </p>
                    <hr class="my-3">
                    <a href="../index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.needs-validation');
            
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
            
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
            nameInput.addEventListener('input', function() {
                if (this.value && this.value.length < 3) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else if (this.value) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
            
            emailInput.addEventListener('input', function() {
                if (this.value && !this.checkValidity()) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else if (this.value) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
            
            passwordInput.addEventListener('input', function() {
                if (this.value && this.value.length < 6) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else if (this.value) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
                
                validatePasswordConfirmation();
            });
            
            confirmPasswordInput.addEventListener('input', function() {
                validatePasswordConfirmation();
            });
            
            function validatePasswordConfirmation() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    confirmPasswordInput.classList.add('is-invalid');
                    confirmPasswordInput.classList.remove('is-valid');
                } else if (confirmPassword) {
                    confirmPasswordInput.classList.add('is-valid');
                    confirmPasswordInput.classList.remove('is-invalid');
                }
            }
        });
        
        document.getElementById('name').focus();
    </script>
</body>
</html>