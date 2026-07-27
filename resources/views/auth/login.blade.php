<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f4f5f7;
        }

        .admin-login-card {
            width: 100%;
            max-width: 380px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 32px;
        }

        .admin-login-card h1 {
            font-size: 22px;
            text-align: center;
            margin-bottom: 24px;
            color: #222;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4a6cf7;
        }

        .alert-danger {
            background: #fdecea;
            color: #b42318;
            border: 1px solid #f5c2c0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 16px;
            font-size: 13px;
        }

        .btn-submit {
            width: 100%;
            padding: 11px;
            border: none;
            border-radius: 6px;
            background-color: #4a6cf7;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #3a5ce0;
        }
    </style>
</head>
<body>
    <div class="admin-login-card">
        <h1>Admin Login</h1>

        @if ($errors->any())
            <div class="alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="post">
            @csrf
            <div class="form-group">
                <label for="email">Username</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn-submit">Login</button>
        </form>
    </div>
</body>
</html>
