// Error Handling with Toastr
export function showToastr(message, type = 'error') {
    if (typeof toastr !== 'undefined') {
        toastr[type](message, type, {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000,
            extendedTimeOut: 2000,
            preventDuplicates: true
        });
    } else {
        console.error('Error:', message);
        // Fallback to alert if toastr is not available
        alert('Error: ' + message);
    }
}