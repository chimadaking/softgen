// public/js/script.js

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages after 5 seconds
    const flashMessage = document.getElementById('msg-flash');
    if (flashMessage) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(flashMessage);
            bsAlert.close();
        }, 5000);
    }
});
