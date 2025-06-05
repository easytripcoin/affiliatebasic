// script.js

document.addEventListener('DOMContentLoaded', function () {
    // Initialize all components
    initBootstrapComponents();
    initFormValidation();
    initPasswordStrength();
    initPasswordVisibilityToggle();
});

function initBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.forEach(function (popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl);
    });
}

function initFormValidation() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                const hasStrengthIndicator = input.closest('.mb-3')?.querySelector('.password-strength-bar');
                if (input.name === 'password' && hasStrengthIndicator) {
                    updatePasswordStrength(input);
                } else if (input.name === 'confirm_password') {
                    validateConfirmPassword(input);
                } else {
                    // Standard validation for all other fields (including login password)
                    if (!input.checkValidity()) {
                        input.classList.add('is-invalid');
                        input.classList.remove('is-valid');
                        const feedbackDiv = input.closest('.mb-3')?.querySelector('.invalid-feedback') ||
                            input.parentElement.querySelector('.invalid-feedback');
                        if (feedbackDiv) feedbackDiv.style.display = 'block';
                    } else {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                        const feedbackDiv = input.closest('.mb-3')?.querySelector('.invalid-feedback') ||
                            input.parentElement.querySelector('.invalid-feedback');
                        if (feedbackDiv) feedbackDiv.style.display = 'none';
                    }
                }
            });

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', function () {
                const hasStrengthIndicator = input.closest('.mb-3')?.querySelector('.password-strength-bar');

                // Skip general validation for password fields with strength indicators
                // and confirm_password fields - they have their own specialized validation
                if ((input.name === 'password' && hasStrengthIndicator) || input.name === 'confirm_password') {
                    if (input.name === 'password' && hasStrengthIndicator) {
                        updatePasswordStrength(input);
                    } else if (input.name === 'confirm_password') {
                        validateConfirmPassword(input);
                    }
                    return; // Skip the general validation below
                }

                // General validation for all other fields (including login password without strength indicator)
                if (form.classList.contains('was-validated') || input.value.length > 0) {
                    if (input.checkValidity()) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                        const feedbackDiv = input.closest('.mb-3')?.querySelector('.invalid-feedback') ||
                            input.parentElement.querySelector('.invalid-feedback');
                        if (feedbackDiv) feedbackDiv.style.display = 'none';
                    } else {
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                        const feedbackDiv = input.closest('.mb-3')?.querySelector('.invalid-feedback') ||
                            input.parentElement.querySelector('.invalid-feedback');
                        if (feedbackDiv) feedbackDiv.style.display = 'block';
                    }
                }
            });
        });
    });
}

function initPasswordStrength() {
    document.querySelectorAll('input[type="password"]').forEach(input => {
        // Only apply password strength validation if the form has a password strength bar
        // This ensures the login form (which lacks a strength bar) is excluded
        const hasStrengthIndicator = input.closest('.mb-3')?.querySelector('.password-strength-bar');
        if (input.name !== 'current_password' && hasStrengthIndicator) {
            input.addEventListener('input', function () {
                if (input.name === 'password' || input.name === 'new_password') {
                    updatePasswordStrength(this);
                    validatePasswordMatch(this);
                }
            });
        }
        // Listener for confirm_password remains separate
        if (input.name === 'confirm_password') {
            input.addEventListener('input', function () {
                validateConfirmPassword(this);
            });
        }
    });
}

function updatePasswordStrength(input) {
    const container = input.closest('.mb-3');
    if (!container) return;

    const strengthBadge = container.querySelector('.password-strength-text');
    const strengthBar = container.querySelector('.password-strength-bar');
    const feedback = container.querySelector('#password-feedback') || container.querySelector('.invalid-feedback');

    if (!strengthBadge || !strengthBar || !feedback) return;

    const password = input.value;
    const strengthLevels = [
        { text: "Very Weak", class: "danger", width: "20%" },
        { text: "Weak", class: "danger", width: "40%" },
        { text: "Medium", class: "warning", width: "60%" },
        { text: "Strong", class: "info", width: "80%" },
        { text: "Very Strong", class: "success", width: "100%" }
    ];

    let score = 0;
    if (password.length > 0) score = 1;
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    score = Math.min(score, strengthLevels.length - 1);

    strengthBadge.textContent = strengthLevels[score].text;
    strengthBadge.className = `fw-bold text-${strengthLevels[score].class} password-strength-text`;
    strengthBar.className = `progress-bar bg-${strengthLevels[score].class} password-strength-bar`;
    strengthBar.style.width = strengthLevels[score].width;

    // Validate password requirements
    if (password.length === 0) {
        input.setCustomValidity("Please provide a password.");
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        feedback.textContent = "Please provide a password.";
        feedback.style.display = 'block';
    } else if (password.length < 8) {
        input.setCustomValidity("Password must be at least 8 characters long.");
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        feedback.textContent = "Password must be at least 8 characters long.";
        feedback.style.display = 'block';
    } else if (!/[A-Za-z]/.test(password) || !/\d/.test(password)) {
        input.setCustomValidity("Password must contain at least one letter and one number.");
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        feedback.textContent = "Password must contain at least one letter and one number.";
        feedback.style.display = 'block';
    } else {
        // Password is valid: clear feedback, hide the feedback div, and update classes
        input.setCustomValidity("");
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        feedback.textContent = ""; // Clear the feedback text
        feedback.style.display = 'none'; // Hide the feedback div
    }
}

function initPasswordVisibilityToggle() {
    document.querySelectorAll('[data-role="togglepassword"]').forEach(toggle => {
        toggle.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.querySelector(targetId);
            const icon = this.querySelector('i');

            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                this.setAttribute('title', 'Hide password');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                this.setAttribute('title', 'Show password');
            }
            input.focus();
        });
    });
}

function validatePasswordMatch(passwordInput) {
    const form = passwordInput.closest('form');
    if (!form) return;

    const confirmPasswordInput = form.querySelector('input[name="confirm_password"]');
    if (!confirmPasswordInput) return;

    // Trigger validation on confirm password as well
    validateConfirmPassword(confirmPasswordInput);
}

function validateConfirmPassword(confirmPasswordInput) {
    const form = confirmPasswordInput.closest('form');
    if (!form) return;

    const passwordInput = form.querySelector('input[name="password"], input[name="new_password"]'); // Handles register and change password
    const feedback = confirmPasswordInput.closest('.mb-3').querySelector('#confirm-password-feedback') || confirmPasswordInput.closest('.mb-3').querySelector('.invalid-feedback');

    if (!passwordInput || !feedback) return;

    const confirmPassword = confirmPasswordInput.value;
    const mainPassword = passwordInput.value;

    if (confirmPassword.length === 0 && mainPassword.length > 0) { // Only show error if main password has been typed
        confirmPasswordInput.setCustomValidity("Please confirm your password.");
        confirmPasswordInput.classList.add('is-invalid');
        confirmPasswordInput.classList.remove('is-valid');
        feedback.textContent = "Please confirm your password.";
        feedback.style.display = 'block'; // Show feedback
    } else if (confirmPassword.length > 0 && confirmPassword !== mainPassword) {
        confirmPasswordInput.setCustomValidity("Passwords do not match.");
        confirmPasswordInput.classList.add('is-invalid');
        confirmPasswordInput.classList.remove('is-valid');
        feedback.textContent = "Passwords do not match.";
        feedback.style.display = 'block'; // Show feedback
    } else if (confirmPassword.length > 0 && confirmPassword === mainPassword) {
        // Also check complexity for confirm_password if it's being actively typed and matches
        if (confirmPassword.length < 8) {
            confirmPasswordInput.setCustomValidity("Confirm password must be at least 8 characters long.");
            confirmPasswordInput.classList.add('is-invalid');
            confirmPasswordInput.classList.remove('is-valid');
            feedback.textContent = "Confirm password must be at least 8 characters long.";
            feedback.style.display = 'block';
        } else if (!/[A-Za-z]/.test(confirmPassword) || !/\d/.test(confirmPassword)) {
            confirmPasswordInput.setCustomValidity("Confirm password must contain at least one letter and one number.");
            confirmPasswordInput.classList.add('is-invalid');
            confirmPasswordInput.classList.remove('is-valid');
            feedback.textContent = "Confirm password must contain at least one letter and one number.";
            feedback.style.display = 'block';
        } else {
            confirmPasswordInput.setCustomValidity("");
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.add('is-valid');
            feedback.textContent = ""; // Clear feedback when valid
            feedback.style.display = 'none'; // Hide feedback
        }
    } else { // Handles case where confirm password is empty and main password is also empty or not yet focused
        confirmPasswordInput.setCustomValidity(""); // No error if both are empty or confirm is empty and main is not yet touched much
        confirmPasswordInput.classList.remove('is-invalid');
        confirmPasswordInput.classList.remove('is-valid'); // Not necessarily valid if empty
        feedback.textContent = "Please confirm your password."; // Default message if required
        feedback.style.display = 'none'; // Hide feedback if no active error
        // If the field is required and empty, Bootstrap's .was-validated will handle showing the default message on submit
    }
}