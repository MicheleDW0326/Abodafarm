/**
 * Alpine.js Auth Functions
 * Reusable Alpine.js data functions for authentication pages
 *
 * IMPORTANT: Include this script BEFORE Alpine.js initialization
 */

// Make functions globally available
window.authAlert = function() {
    return {
        showAlert: false,
        alertType: 'error',
        alertTitle: '',
        alertMessage: '',

        triggerAlert(type, title, message) {
            this.alertType = type;
            this.alertTitle = title;
            this.alertMessage = message;
            this.showAlert = true;
        },

        closeAlert() {
            this.showAlert = false;
        }
    }
};

window.authSuccessAlert = function() {
    return {
        showSuccessAlert: false,
        successTitle: '',
        successMessage: '',

        triggerSuccessAlert(title, message) {
            this.successTitle = title;
            this.successMessage = message;
            this.showSuccessAlert = true;

            setTimeout(() => {
                this.showSuccessAlert = false;
            }, 3000);
        },

        closeSuccessAlert() {
            this.showSuccessAlert = false;
        }
    }
};
