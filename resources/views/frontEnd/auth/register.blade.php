@extends('frontEnd.layouts.master')

@section('content')
<section class="buyer-auth py-5">
    <div class="container">
        <div class="buyer-auth-card">
            <div class="text-center mb-4">
                <h2>Create Buyer Account</h2>
                <p class="text-muted mb-0">Register once to track every purchase in one place.</p>
            </div>

            @include('frontEnd.buyer.partials.alerts')

            <form method="POST" action="{{ route('buyer.register.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="business_name">Name or business name</label>
                    <input class="form-control @error('business_name') is-invalid @enderror" id="business_name"
                        name="business_name" value="{{ old('business_name') }}" required autofocus>
                    @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email address</label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                            type="email" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Phone</label>
                        <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
                            value="{{ old('phone') }}" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="category">Buyer category</label>
                        <input class="form-control @error('category') is-invalid @enderror" id="category"
                            name="category" value="{{ old('category', 'Retail') }}" placeholder="Retail, Contractor, IT Shop">
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password" type="password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirm password</label>
                        <input class="form-control" id="password_confirmation" name="password_confirmation"
                            type="password" required>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-1">Business Verification (KYC)</h6>
                <p class="text-muted small mb-3">Optional now &mdash; you can also complete this later from your profile. Providing these details helps us verify your business faster.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="tin">TIN (Tax Identification Number)</label>
                        <input class="form-control @error('tin') is-invalid @enderror" id="tin" name="tin"
                            value="{{ old('tin') }}">
                        @error('tin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="trade_license_no">Trade License No.</label>
                        <input class="form-control @error('trade_license_no') is-invalid @enderror" id="trade_license_no"
                            name="trade_license_no" value="{{ old('trade_license_no') }}">
                        @error('trade_license_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="trade_license_expiry">Trade License Expiry Date</label>
                        <input class="form-control @error('trade_license_expiry') is-invalid @enderror" id="trade_license_expiry"
                            name="trade_license_expiry" type="date" value="{{ old('trade_license_expiry') }}">
                        @error('trade_license_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="trade_license_document">Trade License Document (PDF/Image)</label>
                        <input class="form-control @error('trade_license_document') is-invalid @enderror" id="trade_license_document"
                            name="trade_license_document" type="file" accept=".pdf,.jpg,.jpeg,.png">
                        @error('trade_license_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button class="btn btn-primary w-100 mt-4" type="submit">Create Account</button>
            </form>

            <p class="text-center mt-4 mb-0">
                Already registered? <a href="{{ route('buyer.login') }}">Sign in</a>
            </p>
        </div>
    </div>
</section>
@endsection
