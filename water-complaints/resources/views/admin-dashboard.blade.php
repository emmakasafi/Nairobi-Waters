<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #0077B6;
            color: white;
            padding: 20px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            border-bottom: 1px solid #333;
        }
        .sidebar ul li a:hover {
            background-color: #005f7a;
        }
        .main-content h2 {
            font-size: 1.5rem;
            color: #333;
        }
        .main-content p {
            color: #666;
        }
        .card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="dashboard">
        <div class="sidebar fixed top-0 left-0 h-screen">
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#">Users</a></li>
                <li><a href="#">Complaints</a></li>
                <li><a href="#">Reports</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>
        <div class="main-content ml-64">
            <h2>Welcome, Admin!</h2>
            <p>You have access to all administrative features.</p>

            <!-- Recent Complaints Section -->
            <div class="card">
                <h3 class="text-xl font-semibold mb-2">Recent Complaints</h3>
                <ul class="list-disc list-inside">
                    <li>Complaint 1: Water pressure issue</li>
                    <li>Complaint 2: Pipe leakage</li>
                    <li>Complaint 3: Water quality concern</li>
                </ul>
            </div>

            <!-- User Statistics Section -->
            <div class="card">
                <h3 class="text-xl font-semibold mb-2">User Statistics</h3>
                <p>Total Users: 120</p>
                <p>New Users Today: 5</p>
            </div>

            <!-- Logout Button -->
            <a href="{{ route('admin.logout') }}" class="bg-red-500 text-white p-2 rounded hover:bg-red-600">Logout</a>

            <!-- Complaints Link -->
            <a href="{{ route('water_sentiments') }}" class="text-blue-500 hover:text-blue-700">View All Complaints</a>
        </div>
    </div>
</body>
</html>