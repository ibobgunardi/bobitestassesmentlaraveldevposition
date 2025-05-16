@extends('layouts.app')
@section('title', 'Login')
<link href="{{asset('assets/css/pages/login.css')}}" rel="stylesheet">
@section('hide-navbar', true)
@section('full-width', true)

@section('content')

<div class="login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="card login-transparent-card">
                    <div class="card-body">
                        <h2 class="text-center mb-4 text-shadow">Demo Login</h2>
                        <p class="text-center text-dark mb-4">Select one to auto fill</p>

                        <!-- Autofill Buttons  -->
                        <div class="autofill-buttons">
                            <button type="button" class="btn btn-outline-secondary" onclick="fillEmployee()">
                                <i class="fas fa-user me-1"></i>
                                Employee
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="fillManager()">
                                <i class="fas fa-user-tie me-1"></i>
                                Manager
                            </button>
                        </div>
               

                        <!-- Login Form -->
                        <form id="login-form">


                            <!-- Email -->
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                    </div>
                                    <input id="email" type="email" name="email" required autocomplete="email" autofocus
                                        class="form-control" placeholder="Email Address">
                                </div>
                                <div id="email-error" class="invalid-feedback d-none"></div>
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                    <input id="password" type="password" name="password" required autocomplete="current-password"
                                        class="form-control" placeholder="Password">
                                </div>
                                <div id="password-error" class="invalid-feedback d-none"></div>
                            </div>


                            <!-- Login Button -->
                            <div class="form-group">
                                <button type="submit" id="login-btn" class="btn btn-primary w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Log in
                                </button>
                            </div>

                            <!-- Loading Indicator - Hidden by default -->
                            <div id="loading-indicator" class="d-flex justify-content-center  align-items-center d-none mt-3">
                                <div class="spinner-border text-white" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2 text-white font-bold">Logging in...</span>
                            </div>
                        </form>
                        <!-- Alert Messages - Hidden by default -->
                        <div id="login-alert" class="alert alert-danger d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    //Auto fill
    function fillEmployee() {
        $('#email').val('user@coalitiontechnologies.com');
        $('#password').val('password');
    }

    function fillManager() {
        $('#email').val('manager@coalitiontechnologies.com');
        $('#password').val('password');
    }
    // On form submit, use AJAX for login
    $('#login-form').on('submit', function(event) {
        event.preventDefault();

        var email = $('#email').val();
        var password = $('#password').val();
        var remember = $('#remember_me').is(':checked');

        // Show loading indicator
        $('#loading-indicator').removeClass('d-none');
        $('#login-btn').attr('disabled', true);

        // Clear previous errors
        $.ajax({
            url: '/api/auth/login',
            method: 'POST',
            data: {
                email: email,
                password: password,
                _token: $('meta[name="csrf-token"]').attr('content') // Ensure CSRF token is sent
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect_url;
                } else {
                    $('#login-alert').removeClass('d-none').addClass('alert-danger')
                        .html('Invalid credentials. Please try again.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 419) {
                    window.location.reload(); // CSRF token mismatch
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    if (errors.email) {
                        $('#email-error').removeClass('d-none').text(errors.email[0]);
                    }
                    if (errors.password) {
                        $('#password-error').removeClass('d-none').text(errors.password[0]);
                    }
                } else {
                    $('#login-alert').removeClass('d-none')
                        .addClass('alert-danger')
                        .text(xhr.responseJSON?.message || 'An error occurred during login.');
                }
            },
            complete: function() {
                $('#loading-indicator').addClass('d-none');
                $('#login-btn').attr('disabled', false);
            }
        });

    });
</script>
@endpush