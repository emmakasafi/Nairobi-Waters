<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex min-h-screen flex-col md:flex-row">

    <!-- Sidebar -->
    <div class="bg-blue-800 text-white p-4 fixed top-0 left-0 z-10 w-64 h-screen overflow-y-auto">
        <button class="text-white absolute top-4 right-4 focus:outline-none">
            <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24">
                <path d="M4 5h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2h16zM4 11h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2h16zM10 17h4a1 1 0 0 1 0 2h-4a1 1 0 1 1 0-2h4z"></path>
            </svg>
        </button>
        <ul class="mt-6">
            <li class="mb-2"><a href="#" class="block text-white hover:bg-blue-700 p-2 rounded">Dashboard</a></li>
            <li class="mb-2"><a href="#" class="block text-white hover:bg-blue-700 p-2 rounded">Users</a></li>
            <li class="mb-2"><a href="#" class="block text-white hover:bg-blue-700 p-2 rounded">Complaints</a></li>
            <li class="mb-2"><a href="#" class="block text-white hover:bg-blue-700 p-2 rounded">Reports</a></li>
            <li class="mb-2"><a href="#" class="block text-white hover:bg-blue-700 p-2 rounded">Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 relative z-0 flex flex-col md:flex-row md:ml-64">
        <!-- Top Right Logout Button -->
        <div class="top-right-logout">
            <a href="{{ route('admin.logout') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded fixed top-4 right-4">Logout</a>
        </div>

        <!-- Main Content Area -->
        <div class="main-content p-4 pt-6 md:p-4 md:ml-8 w-full">
            <h1 class="text-2xl font-semibold mb-3">Welcome, Admin!</h1>
            <p class="mb-3">You have access to all administrative features.</p>

            <!-- Flex Container for Recent Complaints and User Statistics -->
            <div class="flex space-x-4 mb-4">
                <!-- Recent Complaints Section -->
                <div class="card bg-white rounded shadow p-3 w-1/2">
                    <h3 class="text-xl font-semibold mb-2">Recent Complaints</h3>
                    <ul class="list-disc list-inside">
                        <li>Complaint 1: Water pressure issue</li>
                        <li>Complaint 2: Pipe leakage</li>
                        <li>Complaint 3: Water quality concern</li>
                    </ul>
                    <!-- View All Complaints Button -->
                    <a href="{{ route('water_sentiments') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded mt-4 inline-block">View All Complaints</a>
                </div>

                <!-- User Statistics Section -->
                <div class="card bg-white rounded shadow p-3 w-1/2">
                    <h3 class="text-lg font-semibold mb-2">User Statistics</h3>
                    <p>Total Users: 120</p>
                    <p>New Users Today: 5</p>
                </div>
            </div>

            <!-- Additional Content Below -->
            <div class="card bg-white rounded shadow p-3">
                <h3 class="text-lg font-semibold mb-2">More Information</h3>
                <p>Some more content can go here to provide additional information or visual elements.</p>
            </div>
        </div>
    </div>
</body>
</html>
