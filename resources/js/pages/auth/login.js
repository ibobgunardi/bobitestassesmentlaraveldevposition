// export function initLoginPage() {
//     const loginForm = document.getElementById('login-form');
//     const loginBtn = document.getElementById('login-btn');
//     const loadingIndicator = document.getElementById('loading-indicator');
//     const loginAlert = document.getElementById('login-alert');

//     if (!loginForm) return;

//     // Form submission handler
//     loginForm.addEventListener('submit', function(e) {
//         e.preventDefault();

//         // Clear previous errors
//         clearErrors();

//         // Show loading indicator, hide login button
//         loginBtn.classList.add('hidden');
//         loadingIndicator.classList.remove('hidden');

//         // Get form data
//         const formData = new FormData(loginForm);
//         const email = formData.get('email');
//         const password = formData.get('password');
//         const remember = formData.get('remember') ? true : false;

//         // Get the device name for token creation (or use the user agent)
//         const deviceName = navigator.userAgent || 'Web Browser';

//         // Prepare request data
//         const requestData = {
//             email: email,
//             password: password,
//             remember: remember,
//             device_name: deviceName
//         };

//         // Make Ajax request to the API endpoint
//         fetch('/api/auth/login', {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-TOKEN': formData.get('_token'),
//                 'Accept': 'application/json',
//                 'X-Requested-With': 'XMLHttpRequest'
//             },
//             body: JSON.stringify(requestData),
//             credentials: 'same-origin' // Include cookies for CSRF protection
//         })
//         .then(response => {
//             // Check if the response is JSON
//             const contentType = response.headers.get('content-type');
//             if (contentType && contentType.indexOf('application/json') !== -1) {
//                 return response.json().then(data => {
//                     return { status: response.status, body: data };
//                 });
//             } else {
//                 return response.text().then(text => {
//                     return {
//                         status: response.status,
//                         body: { success: false, message: 'Invalid response format' }
//                     };
//                 });
//             }
//         })
//         .then(({ status, body }) => {
//             // Hide loading indicator
//             loadingIndicator.classList.add('hidden');
//             loginBtn.classList.remove('hidden');

//             if (body.success) {
//                 // Success - store token and user data
//                 if (body.token) {
//                     // Using our Auth helper from app.js
//                     if (window.Auth) {
//                         window.Auth.setToken(body.token);
//                     } else {
//                         localStorage.setItem('auth_token', body.token);
//                     }
//                 }

//                 // Store user data if provided
//                 if (body.user) {
//                     if (window.Auth) {
//                         window.Auth.setUser(body.user);
//                     } else {
//                         localStorage.setItem('user', JSON.stringify(body.user));
//                     }
//                 }

//                 // Show success message
//                 showAlert('success', body.message || 'Login successful! Redirecting...');

//                 // Redirect to dashboard after a short delay
//                 setTimeout(() => {
//                     window.location.href = '/dashboard';
//                 }, 1000);
//             } else {
//                 // Error handling
//                 let errorMessage = body.message || 'Failed to login. Please try again.';

//                 // Check for specific error codes
//                 if (status === 401) {
//                     errorMessage = 'Invalid email or password.';
//                 } else if (status === 422) {
//                     errorMessage = 'Please check your input.';
//                 } else if (status === 429) {
//                     errorMessage = 'Too many login attempts. Please try again later.';
//                 } else if (status >= 500) {
//                     errorMessage = 'Server error. Please try again later.';
//                 }

//                 showAlert('error', errorMessage);

//                 // Show validation errors if any
//                 if (body.errors) {
//                     handleValidationErrors(body.errors);
//                 }
//             }
//         })
//         .catch(error => {
//             // Hide loading indicator
//             loadingIndicator.classList.add('hidden');
//             loginBtn.classList.remove('hidden');

//             // Show error message
//             showAlert('error', 'Network error occurred. Please check your connection and try again.');
//             console.error('Login error:', error);
//         });
//     });

//     // Helper function to show alerts
//     function showAlert(type, message) {
//         loginAlert.classList.remove('hidden', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');

//         if (type === 'success') {
//             loginAlert.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-200');
//         } else {
//             loginAlert.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-200');
//         }

//         loginAlert.textContent = message;
//     }

//     // Helper function to clear error messages
//     function clearErrors() {
//         document.getElementById('email-error').classList.add('hidden');
//         document.getElementById('email-error').textContent = '';
//         document.getElementById('password-error').classList.add('hidden');
//         document.getElementById('password-error').textContent = '';
//         loginAlert.classList.add('hidden');
//     }

//     // Helper function to handle validation errors
//     function handleValidationErrors(errors) {
//         if (errors.email) {
//             const emailError = document.getElementById('email-error');
//             emailError.textContent = Array.isArray(errors.email) ? errors.email[0] : errors.email;
//             emailError.classList.remove('hidden');
//         }

//         if (errors.password) {
//             const passwordError = document.getElementById('password-error');
//             passwordError.textContent = Array.isArray(errors.password) ? errors.password[0] : errors.password;
//             passwordError.classList.remove('hidden');
//         }
//     }
// }
