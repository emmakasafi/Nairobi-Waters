<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-96">
        <h2 class="text-2xl font-semibold mb-6">Admin Dashboard</h2>
        <p>Welcome, Admin! You have access to all administrative features.</p>
        <a href="{{ route('admin.logout') }}" class="bg-red-500 text-white p-2 rounded hover:bg-red-600">Logout</a>
    </div>
</body>
</html>