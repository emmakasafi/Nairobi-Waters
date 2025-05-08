<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('path-to-your-background-image.jpg');
            background-size: cover;
            background-position: center;
            font-family: 'Arial', sans-serif;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .login-form {
            max-width: 400px;
            margin: 0 auto;
        }
        .login-title {
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            color: #0077B6;
        }
        .login-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: #555;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group input:focus {
            border-color: #0077B6;
            outline: none;
        }
        .form-group input[type="submit"] {
            background-color: #0077B6;
            color: white;
            border-color: #0077B6;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background-color: #005f7a;
        }
        .form-group a {
            display: block;
            text-align: right;
            text-decoration: none;
            color: #0077B6;
        }
        .form-group a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="login-container">
        <div class="login-form">
            <h1 class="login-title">Officer Login</h1>
            <p class="login-subtitle">Please log in as Officer to continue.</p>
            <form method="POST" action="{{ route('officer.login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="w-full border border-gray-300 rounded p-2" required autofocus autocomplete="username">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded p-2" required autocomplete="current-password">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="remember" class="form-checkbox text-blue-600">
                        <span class="ml-2 text-gray-700">Remember me</span>
                    </label>
                </div>
                <div class="form-group">
                    <input type="submit" value="Log in" class="w-full py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
