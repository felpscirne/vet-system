<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeString($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif (!validateEmail($email)) {
        $error = 'Enter a valid email.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Internal error. Please try again.';
        }
    }
}

$pageTitle = 'Login - Veterinary System';
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
                <i class="fas fa-paw fa-3x mb-3"></i>
                <h1>Login</h1>
                <p class="mb-0">Access your account</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
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
                               placeholder="your@email.com">
                        <div class="invalid-feedback">
                            Enter a valid email.
                        </div>
                    </div>
                    
                    <div class="mb-4">
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
                                   placeholder="Your password">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Password is required.
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <p class="mb-0">
                        Don't have an account? 
                        <a href="register.php" class="text-decoration-none">
                            <i class="fas fa-user-plus me-1"></i>Register
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
            
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
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
            });
        });
        
        document.getElementById('email').focus();
    </script>
</body>
</html>