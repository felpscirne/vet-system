document.addEventListener('DOMContentLoaded', function() {
    
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const item = this.getAttribute('data-item') || 'this item';
            
            if (confirm(`Are you sure you want to delete ${item}? This action cannot be undone.`)) {
                window.location.href = url;
            }
        });
    });
    
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
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
                validateField(this);
            });
        });
    });
    
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('input', function() {
            validateEmail(this);
        });
    });
    
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            validatePassword(this);
        });
    });
    
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            validateFutureDate(this);
            validateWeekday(this);
        });
    });
    
    const timeInputs = document.querySelectorAll('input[type="time"]');
    timeInputs.forEach(input => {
        input.addEventListener('change', function() {
            validateBusinessHours(this);
        });
    });
    
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
    
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (form && form.checkValidity()) {
                showLoadingState(this);
            }
        });
    });
});

function validateField(field) {
    const value = field.value.trim();
    
    if (field.hasAttribute('required') && !value) {
        setFieldInvalid(field, 'This field is required.');
        return false;
    }
    
    if (field.hasAttribute('minlength')) {
        const minLength = parseInt(field.getAttribute('minlength'));
        if (value.length < minLength) {
            setFieldInvalid(field, `Minimum ${minLength} characters.`);
            return false;
        }
    }
    
    setFieldValid(field);
    return true;
}

function validateEmail(field) {
    const email = field.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        setFieldInvalid(field, 'Enter a valid email.');
        return false;
    }
    
    return validateField(field);
}

function validatePassword(field) {
    const password = field.value;
    
    if (password && password.length < 6) {
        setFieldInvalid(field, 'Password must be at least 6 characters.');
        return false;
    }
    
    return validateField(field);
}

function validateFutureDate(field) {
    const selectedDate = new Date(field.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate <= today) {
        setFieldInvalid(field, 'Date must be in the future.');
        return false;
    }
    
    return validateField(field);
}

function validateWeekday(field) {
    const selectedDate = new Date(field.value);
    const dayOfWeek = selectedDate.getDay();
    
    if (dayOfWeek === 0 || dayOfWeek === 6) {
        setFieldInvalid(field, 'Appointments only on weekdays.');
        return false;
    }
    
    return validateField(field);
}

function validateBusinessHours(field) {
    const time = field.value;
    const [hours, minutes] = time.split(':').map(Number);
    const timeInMinutes = hours * 60 + minutes;
    
    const startTime = 8 * 60;
    const endTime = 18 * 60;
    
    if (timeInMinutes < startTime || timeInMinutes > endTime) {
        setFieldInvalid(field, 'Business hours: 08:00 to 18:00.');
        return false;
    }
    
    return validateField(field);
}

function setFieldInvalid(field, message) {
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    
    let feedback = field.parentNode.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
}

function setFieldValid(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.remove();
    }
}

function showLoadingState(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 3000);
}

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.getElementById('toast-container').appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}