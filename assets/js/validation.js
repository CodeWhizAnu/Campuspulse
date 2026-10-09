// assets/js/validation.js
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Password Match Validation
    const formsWithPasswords = document.querySelectorAll('form');
    formsWithPasswords.forEach(form => {
        form.addEventListener('submit', function(e) {
            const pass = this.querySelector('input[name="password"]');
            const confPass = this.querySelector('input[name="confirm_password"]');
            
            if (pass && confPass) {
                if (pass.value !== confPass.value) {
                    e.preventDefault();
                    alert("Error: Passwords do not match!");
                    confPass.focus();
                }
            }
        });
    });

    // 2. File Size Validation (Max 5MB for complaints, 2MB for profile)
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileSize = this.files[0].size;
                const isProfile = this.name === 'profile_photo';
                const maxSize = isProfile ? (2 * 1024 * 1024) : (5 * 1024 * 1024);
                
                if (fileSize > maxSize) {
                    alert(`File is too large. Maximum allowed size is ${isProfile ? '2MB' : '5MB'}.`);
                    this.value = ''; // Clear the input
                }
            }
        });
    });
});