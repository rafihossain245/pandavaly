<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

if (!function_exists('redirectToRoleDashboard')) {
    function redirectToRoleDashboard($user)
    {
        $role = Str::slug($user->getRoleNames()->first()); // e.g., "super-admin"
        return match (true) {
            $role  => redirect()->route('role.dashboard', ['role' => $role]),
            default => redirect()->route('admin.login')->withErrors('Unauthorized role.')
        };
    }
}
if (!function_exists('redirectToLogin')) {
    function redirectToLogin()
    {
        return Redirect::route('admin.login');
    }
}
if (!function_exists('redirectToLoginWithError')) {
    function redirectToLoginWithError($error)
    {
        return Redirect::route('admin.login')->withErrors($error);
    }
}
if (!function_exists('redirectToDashboard')) {
    function redirectToDashboard($user)
    {
        if (!$user) {
            return redirectToLogin();
        }

        return redirectToRoleDashboard($user);
    }
}

