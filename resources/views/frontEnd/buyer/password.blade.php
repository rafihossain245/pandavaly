@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')

    <div class="ba-panel">
        <div class="ba-panel-head">
            <h3 class="ba-panel-title">Change Password</h3>
        </div>
        <div class="ba-panel-body">

            @if($buyer->must_set_password)
                <div style="background:#fffbeb; border:1px solid #fcd34d; border-radius:8px; padding:13px 15px; margin-bottom:18px; font-size:13px; color:#92400e">
                    <i class="fas fa-circle-info me-1"></i>
                    Your account was created automatically when you placed an order, so it has no password yet.
                    Set one now and you will be able to log in with
                    <strong>{{ $buyer->email ?: $buyer->phone }}</strong>.
                </div>
            @endif

            <form action="{{ route('buyer.password.update') }}" method="POST" style="max-width:440px">
                @csrf
                @method('PUT')

                @unless($buyer->must_set_password)
                <div class="mb-3">
                    <label class="ba-label" for="current_password">Current password <span style="color:#dc2626">*</span></label>
                    <input type="password" id="current_password" name="current_password" class="ba-input" required>
                    @error('current_password')<div class="ba-err">{{ $message }}</div>@enderror
                </div>
                @endunless

                <div class="mb-3">
                    <label class="ba-label" for="password">New password <span style="color:#dc2626">*</span></label>
                    <input type="password" id="password" name="password" class="ba-input" required>
                    <div class="ba-help">At least 8 characters.</div>
                    @error('password')<div class="ba-err">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="ba-label" for="password_confirmation">Confirm new password <span style="color:#dc2626">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="ba-input" required>
                </div>

                <button type="submit" class="ba-btn-sm ba-btn-primary" style="padding:9px 22px">
                    {{ $buyer->must_set_password ? 'Set password' : 'Update password' }}
                </button>
            </form>
        </div>
    </div>

@include('frontEnd.buyer.partials.layout-end')
@endsection
