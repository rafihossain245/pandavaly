@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')
    <div class="buyer-panel">
        <h2 class="mb-1">Profile</h2>
        <p class="text-muted mb-4">Keep your account and default shipping details current.</p>
        <form method="POST" action="{{ route('buyer.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                @foreach([
                    ['business_name', 'Name or business name', 'text', 6],
                    ['category', 'Buyer category', 'text', 6],
                    ['email', 'Email address', 'email', 6],
                    ['phone', 'Phone', 'text', 6],
                    ['address', 'Address', 'text', 12],
                    ['city', 'City', 'text', 4],
                    ['state', 'State', 'text', 4],
                    ['postal_code', 'Postal code', 'text', 4],
                    ['country', 'Country', 'text', 6],
                ] as [$name, $label, $type, $width])
                    <div class="col-md-{{ $width }}">
                        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
                        @if($name === 'address')
                            <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" rows="3">{{ old($name, $buyer->{$name}) }}</textarea>
                        @else
                            <input class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}"
                                type="{{ $type }}" value="{{ old($name, $buyer->{$name}) }}" @required(in_array($name, ['business_name', 'email', 'phone']))>
                        @endif
                        @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
            <hr class="my-4">
            <h5>Business Verification (KYC)</h5>
            <p class="text-muted small">Keep your business verification details up to date.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="tin">TIN (Tax Identification Number)</label>
                    <input class="form-control @error('tin') is-invalid @enderror" id="tin" name="tin"
                        value="{{ old('tin', $buyer->tin) }}">
                    @error('tin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="trade_license_no">Trade License No.</label>
                    <input class="form-control @error('trade_license_no') is-invalid @enderror" id="trade_license_no"
                        name="trade_license_no" value="{{ old('trade_license_no', $buyer->trade_license_no) }}">
                    @error('trade_license_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="trade_license_expiry">Trade License Expiry Date</label>
                    <input class="form-control @error('trade_license_expiry') is-invalid @enderror" id="trade_license_expiry"
                        name="trade_license_expiry" type="date"
                        value="{{ old('trade_license_expiry', optional($buyer->trade_license_expiry)->format('Y-m-d')) }}">
                    @error('trade_license_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="trade_license_document">Trade License Document (PDF/Image)</label>
                    <input class="form-control @error('trade_license_document') is-invalid @enderror" id="trade_license_document"
                        name="trade_license_document" type="file" accept=".pdf,.jpg,.jpeg,.png">
                    @error('trade_license_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($tradeLicenseDocument && $tradeLicenseDocument->media)
                        <div class="form-text">
                            Current file: <a href="{{ asset($tradeLicenseDocument->media->path) }}" target="_blank">View uploaded document</a>
                        </div>
                    @endif
                </div>
            </div>

            <hr class="my-4">
            <h5>Change Password</h5>
            <p class="text-muted small">Leave these fields blank to keep your current password.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="current_password">Current password</label>
                    <input class="form-control @error('current_password') is-invalid @enderror" id="current_password"
                        name="current_password" type="password">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="password">New password</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="password_confirmation">Confirm new password</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password">
                </div>
            </div>
            <button class="btn btn-primary mt-4" type="submit">Save Changes</button>
        </form>
    </div>
@include('frontEnd.buyer.partials.layout-end')
@endsection
