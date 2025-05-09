<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('{{ asset('images/bg.jpg') }}'); /* Replace with actual image path */
            background-size: cover;
            background-position: center;
            font-family: 'Arial', sans-serif;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="login-container w-full max-w-md">
        <div class="login-form">
            <h1 class="text-3xl font-bold text-center text-blue-700 mb-2">Officer Login</h1>
            <p class="text-center text-gray-600 mb-6">Please log in as Officer to continue.</p>

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <strong class="font-bold">Whoops!</strong>
                    <ul class="mt-2 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('officer.login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" required autofocus autocomplete="username">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-semibold mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" required autocomplete="current-password">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4 flex items-center justify-between">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="remember" class="form-checkbox text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Remember me</span>
                    </label>
                    <a href="{{ route('welcome') }}" class="text-sm text-blue-600 hover:underline">Back to Welcome Page</a>
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-200">
                        Log in
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
