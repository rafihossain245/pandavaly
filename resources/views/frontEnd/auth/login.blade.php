@extends('frontEnd.layouts.master')

@section('css')
    <style>
        .signin-wrap {
            padding: 40px 0 60px;
        }

        .signin-card {
            max-width: 780px;
            margin: 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 40px rgba(16, 24, 40, .08);
            padding: 34px 36px 30px;
        }

        .signin-head {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin-bottom: 26px;
        }

        .signin-head .icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .signin-head h1 {
            font-size: 21px;
            font-weight: 700;
            color: #101828;
            margin: 0 0 2px;
        }

        .signin-head p {
            font-size: 13px;
            color: #667085;
            margin: 0;
        }

        /* Two panels with a vertical rule and an OR chip between them. */
        .signin-panels {
            display: grid;
            grid-template-columns: 1fr 60px 1fr;
            align-items: stretch;
        }

        .signin-panel {
            background: #f6f7f9;
            border-radius: 10px;
            padding: 20px 20px 22px;
        }

        .signin-panel h2 {
            font-size: 14px;
            font-weight: 700;
            color: #101828;
            margin: 0 0 14px;
        }

        .signin-or {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signin-or::before {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            width: 1px;
            background: #e4e7ec;
        }

        .signin-or span {
            position: relative;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid #e4e7ec;
            background: #fff;
            color: #667085;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signin-field {
            position: relative;
            margin-bottom: 12px;
        }

        .signin-field .fi {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #98a2b3;
            font-size: 14px;
            pointer-events: none;
        }

        .signin-field input[type="text"],
        .signin-field input[type="tel"],
        .signin-field input[type="email"],
        .signin-field input[type="password"] {
            width: 100%;
            height: 44px;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            background: #fff;
            padding: 0 40px 0 38px;
            font-size: 14px;
            color: #101828;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .signin-field input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(245, 124, 31, .12);
        }

        .signin-field input.is-invalid {
            border-color: #dc3545;
        }

        .signin-field .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: 0;
            color: #98a2b3;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
        }

        .signin-btn {
            width: 100%;
            height: 44px;
            border: 0;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: filter .15s;
        }

        .signin-btn:hover {
            filter: brightness(.95);
        }

        .signin-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 4px 0 14px;
            font-size: 13px;
        }

        .signin-meta label {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #475467;
            margin: 0;
            cursor: pointer;
        }

        .signin-meta a {
            color: var(--primary);
            text-decoration: underline;
        }

        .signin-divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 26px 0 16px;
            color: #98a2b3;
            font-size: 13px;
        }

        .signin-divider::before,
        .signin-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e4e7ec;
        }

        .signin-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            max-width: 260px;
            margin: 0 auto;
            height: 44px;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            background: #fff;
            color: #344054;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background .15s, border-color .15s;
        }

        .signin-google:hover {
            background: #f9fafb;
            border-color: #d0d5dd;
            color: #344054;
        }

        .signin-google svg {
            width: 18px;
            height: 18px;
        }

        .signin-foot {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #475467;
        }

        .signin-foot a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: underline;
        }

        .signin-error {
            color: #dc3545;
            font-size: 12.5px;
            margin: -6px 0 10px;
        }

        .signin-note {
            font-size: 12.5px;
            color: #475467;
            margin: 0 0 12px;
        }

        @media (max-width: 720px) {
            .signin-card {
                padding: 26px 20px 24px;
            }

            .signin-panels {
                grid-template-columns: 1fr;
            }

            .signin-or {
                height: 54px;
            }

            .signin-or::before {
                top: 50%;
                bottom: auto;
                left: 0;
                right: 0;
                width: auto;
                height: 1px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        // Set by OtpAuthController@send — when present the OTP panel switches
        // from "enter your number" to "enter the code we sent".
        $otpPhone = session('otp_phone');
    @endphp

    <div class="signin-wrap">
        <div class="container">
            <div class="signin-card">

                <div class="signin-head">
                    <div class="icon"><i class="fas fa-user-plus"></i></div>
                    <div>
                        <h1>Signin</h1>
                        <p>Access your account securely</p>
                    </div>
                </div>

                @if (session('error'))
                    <p class="signin-error text-center">{{ session('error') }}</p>
                @endif
                @if (session('info'))
                    <p class="signin-note text-center">{{ session('info') }}</p>
                @endif

                <div class="signin-panels">

                    {{-- Mobile / OTP --}}
                    <div class="signin-panel" id="otp-panel">
                        <h2>Login With Mobile Number</h2>

                        @if ($otpPhone)
                            <form method="POST" action="{{ route('buyer.login.otp.verify') }}">
                                @csrf
                                <input type="hidden" name="phone" value="{{ $otpPhone }}">
                                <p class="signin-note">
                                    Enter the 6 digit code sent to <strong>{{ $otpPhone }}</strong>.
                                </p>
                                <div class="signin-field">
                                    <i class="fas fa-key fi"></i>
                                    <input type="text" name="code" inputmode="numeric" maxlength="6"
                                        autocomplete="one-time-code" placeholder="6 digit code"
                                        class="@error('code') is-invalid @enderror" required autofocus>
                                </div>
                                @error('code')
                                    <p class="signin-error">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="signin-btn">Verify &amp; Sign In</button>
                            </form>

                            <form method="POST" action="{{ route('buyer.login.otp.send') }}" class="mt-2">
                                @csrf
                                <input type="hidden" name="phone" value="{{ $otpPhone }}">
                                <button type="submit"
                                    style="background:none;border:0;color:#667085;font-size:12.5px;text-decoration:underline;cursor:pointer;padding:6px 0">
                                    Send a new code
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('buyer.login.otp.send') }}">
                                @csrf
                                <div class="signin-field">
                                    <i class="fas fa-mobile-screen fi"></i>
                                    <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="11"
                                        inputmode="numeric" placeholder="01*********"
                                        class="@error('phone') is-invalid @enderror" required>
                                </div>
                                @error('phone')
                                    <p class="signin-error">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="signin-btn">Send OTP</button>
                            </form>
                        @endif
                    </div>

                    <div class="signin-or"><span>OR</span></div>

                    {{-- Email / password --}}
                    <div class="signin-panel">
                        <h2>Login With Credentials</h2>

                        <form method="POST" action="{{ route('buyer.login.attempt') }}">
                            @csrf
                            <div class="signin-field">
                                <i class="fas fa-user fi"></i>
                                <input type="text" name="login" value="{{ old('login') }}"
                                    placeholder="Email or phone number"
                                    class="@error('login') is-invalid @enderror" required>
                            </div>
                            @error('login')
                                <p class="signin-error">{{ $message }}</p>
                            @enderror

                            <div class="signin-field">
                                <i class="fas fa-lock fi"></i>
                                <input type="password" name="password" id="signin_password" placeholder="Password"
                                    class="@error('password') is-invalid @enderror" required>
                                <button type="button" class="toggle-pw" aria-label="Show password"
                                    data-target="signin_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="signin-error">{{ $message }}</p>
                            @enderror

                            <div class="signin-meta">
                                <label>
                                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                                    Remember me
                                </label>
                                {{-- There is no password-reset flow yet, so send anyone who has
                                     forgotten theirs to the OTP panel, which signs them in without
                                     a password at all. --}}
                                <a href="#otp-panel" class="js-use-otp">Forgotten password?</a>
                            </div>

                            <button type="submit" class="signin-btn">Login</button>
                        </form>
                    </div>
                </div>

                <div class="signin-divider">or signin with</div>

                <a href="{{ route('auth.google.redirect') }}" class="signin-google">
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335"
                            d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                        <path fill="#4285F4"
                            d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                        <path fill="#FBBC05"
                            d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                        <path fill="#34A853"
                            d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                    </svg>
                    <span>Continue with Google</span>
                </a>

                <p class="signin-foot">
                    Don't have any account? <a href="{{ route('buyer.register') }}">Register account</a>
                </p>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.toggle-pw').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.target);
                if (!input) return;
                var hidden = input.type === 'password';
                input.type = hidden ? 'text' : 'password';
                btn.querySelector('i').className = hidden ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        });

        // "Forgotten password?" scrolls to the OTP panel and focuses it.
        document.querySelectorAll('.js-use-otp').forEach(function (link) {
            link.addEventListener('click', function () {
                var field = document.querySelector('#otp-panel input[name="phone"]');
                if (field) setTimeout(function () { field.focus(); }, 250);
            });
        });

        // Keep the mobile field to digits only so the 01XXXXXXXXX rule is not
        // tripped by spaces or dashes the shopper pastes in.
        document.querySelectorAll('input[name="phone"], input[name="code"]').forEach(function (el) {
            el.addEventListener('input', function () {
                el.value = el.value.replace(/\D/g, '');
            });
        });
    </script>
@endsection
