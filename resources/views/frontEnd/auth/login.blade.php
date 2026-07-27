@extends('frontEnd.layouts.master')

@section('css')
<style>
    .buyer-auth-page .auth-container {
        height: auto;
        min-height: 70vh;
        overflow: visible;
        align-items: stretch;
    }

    .buyer-auth-page .auth-container .auth-section {
        padding: 50px 40px;
    }

    .buyer-auth-page .auth-container .form-input.is-invalid {
        border-color: #dc3545;
    }

    .buyer-auth-page .auth-container .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 13px;
        margin-top: 5px;
    }

    @media (max-width: 768px) {
        .buyer-auth-page .auth-container {
            height: auto;
        }
    }

    @php
        $showRegister = old('business_name') || $errors->hasAny(['business_name', 'phone', 'category', 'tin', 'trade_license_no', 'trade_license_expiry', 'trade_license_document', 'password_confirmation']);
    @endphp

    .buyer-auth-page .auth-container .login-section {
        display: {{ $showRegister ? 'none' : 'flex' }};
    }

    .buyer-auth-page .auth-container .register-section {
        display: {{ $showRegister ? 'flex' : 'none' }};
    }
</style>
@endsection

@section('content')
<div class="buyer-auth-page">
    <div class="container-fluid px-0">
        @include('frontEnd.buyer.partials.alerts')

        <div class="auth-container">
            {{-- Login Section --}}
            <div class="auth-section login-section">
                <form class="auth-form" action="{{ route('buyer.login.attempt') }}" method="post">
                    @csrf
                    <h2 class="form-title">Login to Your Account</h2>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input class="form-input @error('email') is-invalid @enderror" type="email" name="email"
                            id="email" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input @error('password') is-invalid @enderror" type="password"
                            name="password" id="password" placeholder="Enter your password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 400;">
                            <input type="checkbox" name="remember" value="1"> Keep me signed in
                        </label>
                    </div>

                    <div class="form-group">
                        <button class="form-button" type="submit">Sign In</button>
                    </div>

                    <div class="form-footer">
                        New buyer? <a class="form-link" id="goToRegister">Create an account</a>
                    </div>
                </form>
            </div>

            {{-- Registration Section --}}
            <div class="auth-section register-section">
                <form class="auth-form" action="{{ route('buyer.register.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <h2 class="form-title">Create New Account</h2>

                    <div class="form-group">
                        <label class="form-label" for="business_name">Name or Business Name</label>
                        <input class="form-input @error('business_name') is-invalid @enderror" type="text"
                            name="business_name" id="business_name" placeholder="Enter your full name"
                            value="{{ old('business_name') }}" required>
                        @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="register_email">Email Address</label>
                        <input class="form-input @error('email') is-invalid @enderror" type="email" name="email"
                            id="register_email" placeholder="Enter your email" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone</label>
                        <input class="form-input @error('phone') is-invalid @enderror" type="text" name="phone"
                            id="phone" placeholder="Enter your phone number" value="{{ old('phone') }}" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="category">Buyer Category</label>
                        <input class="form-input @error('category') is-invalid @enderror" type="text" name="category"
                            id="category" placeholder="Retail, Contractor, IT Shop" value="{{ old('category', 'Retail') }}">
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="register_password">Password</label>
                        <input class="form-input @error('password') is-invalid @enderror" type="password"
                            name="password" id="register_password" placeholder="Create a password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input class="form-input" type="password" name="password_confirmation"
                            id="password_confirmation" placeholder="Confirm your password" required>
                    </div>

                    <hr>
                    <h2 class="form-title" style="font-size: 20px; margin-bottom: 15px;">Business Verification (KYC)</h2>
                    <p class="form-subtitle" style="font-size: 14px; margin-bottom: 15px;">
                        Optional now &mdash; you can also complete this later from your profile.
                    </p>

                    <div class="form-group">
                        <label class="form-label" for="tin">TIN (Tax Identification Number)</label>
                        <input class="form-input @error('tin') is-invalid @enderror" type="text" name="tin"
                            id="tin" placeholder="Enter your TIN" value="{{ old('tin') }}">
                        @error('tin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="trade_license_no">Trade License No.</label>
                        <input class="form-input @error('trade_license_no') is-invalid @enderror" type="text"
                            name="trade_license_no" id="trade_license_no" placeholder="Enter your trade license number"
                            value="{{ old('trade_license_no') }}">
                        @error('trade_license_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="trade_license_expiry">Trade License Expiry Date</label>
                        <input class="form-input @error('trade_license_expiry') is-invalid @enderror" type="date"
                            name="trade_license_expiry" id="trade_license_expiry" value="{{ old('trade_license_expiry') }}">
                        @error('trade_license_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="trade_license_document">Trade License Document (PDF/Image)</label>
                        <input class="form-input @error('trade_license_document') is-invalid @enderror" type="file"
                            name="trade_license_document" id="trade_license_document" accept=".pdf,.jpg,.jpeg,.png">
                        @error('trade_license_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <button class="form-button" type="submit">Create Account</button>
                    </div>

                    <div class="terms-text">
                        By creating an account, you agree to our <a class="form-link" href="#">Terms of Service</a> and
                        <a class="form-link" href="#">Privacy Policy</a>.
                    </div>

                    <div class="form-footer">
                        Already have an account? <a class="form-link" id="goToLogin">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var loginSection = document.querySelector('.buyer-auth-page .login-section');
        var registerSection = document.querySelector('.buyer-auth-page .register-section');
        var goToRegister = document.getElementById('goToRegister');
        var goToLogin = document.getElementById('goToLogin');

        if (goToRegister) {
            goToRegister.addEventListener('click', function () {
                loginSection.style.display = 'none';
                registerSection.style.display = 'flex';
            });
        }

        if (goToLogin) {
            goToLogin.addEventListener('click', function () {
                registerSection.style.display = 'none';
                loginSection.style.display = 'flex';
            });
        }
    });
</script>
@endsection
