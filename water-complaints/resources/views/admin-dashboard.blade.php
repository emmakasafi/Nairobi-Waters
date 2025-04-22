<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f4fa8;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #3498db;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            transition: transform 0.3s ease;
            z-index: 1000;
            transform: translateX(-250px); /* Hide sidebar by default */
            overflow-x: hidden;
        }
        .sidebar.open {
            transform: translateX(0); /* Show sidebar when open */
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
            margin: 5px 0;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }
        .sidebar ul li a:hover {
            background-color: #2980b9;
        }
        .content {
            margin-left: 0;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .content.open {
            margin-left: 250px; /* Adjust margin to accommodate sidebar */
        }
        .header {
            background-color: #3498db;
            color: white;
            padding: 10px 0;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            padding: 10px 0;
        }
        .main {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 20px;
            padding: 20px;
        }
        .card {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: calc(50% - 20px); /* Adjust width for spacing */
            margin-right: 20px;
        }
        .card h3 {
            margin-top: 0;
        }
        .logout {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #e74c3c3;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            z-index: 1001;
        }
        .logout:hover {
            background-color: #c0392b;
        }
        .toggle-button {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #3498db;
            color: white;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
        }
        .view-all {
            display: inline-block;
            margin-top: 15px;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            border: 1px solid #2a6496;
            transition: background-color 0.3s, transform 0.3s;
        }
        .view-all:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .filters select, .filters input[type="date"] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .filters button {
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .filters button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Users</a></li>
            <li><a href="#">Complaints</a></li>
            <li><a href="#">Reports</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
    </div>
    <button class="toggle-button" onclick="toggleSidebar()">☰</button>
    <div class="content">
        <div class="header">
            <h1>Welcome, Admin!</h1>
        </div>
        <a href="{{ route('admin.logout') }}" class="logout">Logout</a>
        <div class="filters">
            <select name="category">
                <option value="">All Categories</option>
                <!-- Add options dynamically -->
            </select>
            <select name="subcounty">
                <option value="">All Subcounties</option>
                <!-- Add options dynamically -->
            </select>
            <select name="ward">
                <option value="">All Wards</option>
                <!-- Add options dynamically -->
            </select>
            <select name="sentiment">
                <option value="">All Sentiments</option>
                <!-- Add options dynamically -->
            </select>
            <select name="source">
                <option value="">All Sources</option>
                <!-- Add options dynamically -->
            </select>
            <input type="date" name="start_date">
            <input type="date" name="end_date">
            <button type="submit" onclick="applyFilters()">Apply Filters</button>
        </div>
        <div class="main">
            <div class="card">
                <h3>Recent Complaints</h3>
                <ul>
                    @forelse ($recentComplaints as $complaint)
                        <li class="text-gray-700">
                            {{ $complaint->processed_caption ?? $complaint->original_caption }}
                            <!-- Check if timestamp is not null before formatting -->
                            @if (!is_null($complaint->timestamp))
                                <small class="block text-gray-500 text-sm">({{ $complaint->timestamp->format('M d, Y h:i A') }})</small>
                            @else
                                <small class="block text-gray-500 text-sm">No timestamp available</small>
                            @endif
                        </li>
                    @empty
                        <li class="text-gray-500">No recent complaints with timestamps.</li>
                    @endforelse
                </ul>
                <a href="{{ route('water_sentiments') }}" class="view-all">View All Complaints</a>
            </div>
            <div class="card">
                <h3>User Statistics</h3>
                <div>Total Users: <span class="font-bold">{{ $totalUsers }}</span></div>
                <div>New Users Today: <span class="font-bold">{{ $newUsersToday }}</span></div>
            </div>
        </div>
        <div class="card">
            <h3>More Information</h3>
            <p>Some more content can go here to provide additional information or visual elements.</p>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            sidebar.classList.toggle('open');
            content.classList.toggle('open');
        }
        document.addEventListener('DOMContentLoaded', (event) => {
            const mobile = window.matchMedia("(max-width: 768px)");
            mobile.addListener((mq) => {
                if (mq.matches) {
                    toggleSidebar();
                }
            });
        });
        function applyFilters() {
            const formData = new FormData(document.querySelector('.filters'));
            fetch(`{{ route('admin.dashboard') }}?${new URLSearchParams(formData).toString()}`, {
                method: 'GET',
            })
            .then(response => response.text())
            .then(html => {
                document.querySelector('.main').innerHTML = html;
            });
        }
    </script>
</body>
</html>