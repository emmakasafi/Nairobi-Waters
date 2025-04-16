<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <div class="bg-blue-800 text-white w-64 fixed h-screen overflow-y-auto">
            <div class="px-4 py-6">
                <h2 class="text-2xl font-bold mb-4">Menu</h2>
                <a href="{{ route('complaints.create') }}" class="block py-2 px-4 hover:bg-blue-600">
                    <i class="fas fa-plus-circle mr-2"></i> Submit Complaint
                </a>
                <a href="{{ route('complaints.index') }}" class="block py-2 px-4 hover:bg-blue-600">
                    <i class="fas fa-eye mr-2"></i> View Complaints
                </a>
                <a href="{{ route('profile.edit') }}" class="block py-2 px-4 hover:bg-blue-600">
                    <i class="fas fa-user-edit mr-2"></i> Edit Profile
                </a>
                <a href="#" onclick="confirmLogout(event)" class="block py-2 px-4 hover:bg-red-600">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="ml-64 p-8">
            <h2 class="text-2xl font-bold mb-4">Welcome, {{ Auth::user()->name }}!</h2>
            <p class="text-gray-600">Manage your complaints here.</p>

            <div class="mt-8">
                <h3 class="text-xl font-bold mb-4">Recent Complaints</h3>
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <!-- List of recent complaints -->
                    <p class="text-gray-600">No recent complaints.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-blue-800 text-white text-center py-4 fixed bottom-0 w-full">
        <p>&copy; 2023 Nairobi Water Complaints Analysis. All rights reserved.</p>
    </footer>

    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm('Are you sure you want to log out?')) {
                document.getElementById('logout-form').submit();
            }
        }
    </script>

    <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</body>
</html>