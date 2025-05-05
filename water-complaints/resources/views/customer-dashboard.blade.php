<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <!-- Navbar -->
    <nav class="bg-white shadow fixed w-full z-10 top-0">
        <div class="w-full px-6 py-4 flex justify-between items-center">
            <div class="text-xl font-bold text-blue-800">Customer Dashboard</div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">Hello, {{ Auth::user()->name }}</span>
                <button onclick="confirmLogout(event)" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </nav>

    <div class="flex pt-16">

        <!-- Sidebar -->
        <aside class="w-64 bg-blue-800 text-white min-h-screen fixed">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Menu</h2>
                <nav class="space-y-2">
                    <a href="{{ route('complaints.create') }}" class="flex items-center py-2 px-4 hover:bg-blue-700 rounded">
                        <i class="fas fa-plus-circle mr-3"></i> Submit Complaint
                    </a>
                    <a href="{{ route('complaints.index') }}" class="flex items-center py-2 px-4 hover:bg-blue-700 rounded">
                        <i class="fas fa-eye mr-3"></i> View Complaints
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center py-2 px-4 hover:bg-blue-700 rounded">
                        <i class="fas fa-user-edit mr-3"></i> Edit Profile
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 w-full p-8">
            <div class="text-2xl font-bold text-gray-800 mb-6">Dashboard Overview</div>

            <!-- Cards Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center">
                        <div class="text-blue-600 text-3xl mr-4"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <p class="text-gray-600">Total Complaints</p>
                            <h3 class="text-xl font-bold">0</h3>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center">
                        <div class="text-green-600 text-3xl mr-4"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <p class="text-gray-600">Resolved</p>
                            <h3 class="text-xl font-bold">0</h3>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center">
                        <div class="text-yellow-600 text-3xl mr-4"><i class="fas fa-clock"></i></div>
                        <div>
                            <p class="text-gray-600">Pending</p>
                            <h3 class="text-xl font-bold">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Complaints -->
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">Recent Complaints</h3>
                <p class="text-gray-600">No recent complaints.</p>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-800 text-white text-center py-4 mt-12">
        <p>&copy; 2025 Nairobi Water Complaints Analysis. All rights reserved.</p>
    </footer>

    <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm('Are you sure you want to log out?')) {
                document.getElementById('logout-form').submit();
            }
        }
    </script>
</body>
</html>
