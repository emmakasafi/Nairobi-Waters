<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding: 20px;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.5rem;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li a {
            display: block;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            font-size: 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: #495057;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 1rem;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 10px;
        }
        .logout {
            position: absolute;
            top: 10px;
            right: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border-radius: 10px;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-body {
            padding: 1.5rem;
        }
        canvas {
            max-height: 400px;
            max-width: 100%;
        }
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .filters select, .filters input[type="date"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            flex: 1 1 calc(20% - 10px);
        }
        .filters button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .filters button:hover {
            background-color: #0056b3;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            margin-bottom: 0;
        }
        .alert {
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }
            .content {
                margin-left: 60px;
            }
            .sidebar ul li a {
                text-align: center;
                padding: 10px;
            }
            .sidebar h2 {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ request()->is('users*') ? 'active' : '' }}">Users</a></li>
            <li><a href="{{ request()->is('complaints*') ? 'active' : '' }}">Complaints</a></li>
            <li><a href="{{ request()->is('reports*') ? 'active' : '' }}">Reports</a></li>
            <li><a href="{{ request()->is('settings*') ? 'active' : '' }}">Settings</a></li>
            <li><a href="{{ route('departments.index') }}" class="{{ request()->is('departments*') ? 'active' : '' }}">Departments</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <h1>Welcome, Admin!</h1>
            <a href="{{ route('admin.logout') }}" class="btn btn-danger logout">Logout</a>
        </div>
        <div class="filters">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
            <select name="subcounty" class="form-select">
                <option value="">All Subcounties</option>
                @foreach ($subcounties as $subcounty)
                    <option value="{{ $subcounty }}">{{ $subcounty }}</option>
                @endforeach
            </select>
            <select name="ward" class="form-select">
                <option value="">All Wards</option>
                @foreach ($wards as $ward)
                    <option value="{{ $ward }}">{{ $ward }}</option>
                @endforeach
            </select>
            <select name="sentiment" class="form-select">
                <option value="">All Sentiments</option>
                @foreach ($sentiments as $sentiment)
                    <option value="{{ $sentiment }}">{{ $sentiment }}</option>
                @endforeach
            </select>
            <select name="source" class="form-select">
                <option value="">All Sources</option>
                @foreach ($sources as $source)
                    <option value="{{ $source }}">{{ $source }}</option>
                @endforeach
            </select>
            <input type="date" name="start_date" class="form-control">
            <input type="date" name="end_date" class="form-control">
            <button onclick="applyFilters()" class="btn btn-primary">Apply Filters</button>
            <button onclick="clearFilters()" class="btn btn-secondary">Clear Filters</button>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Complaints</h5>
                        <h1>{{ $totalComplaints }}</h1>
                        <a href="{{ route('water_sentiments') }}" class="btn btn-primary">View All Complaints</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Complaints</h5>
                        @forelse ($recentComplaints->take(3) as $complaint)
                            <div class="alert alert-warning" role="alert">
                                <strong>{{ $complaint->processed_caption ?? $complaint->original_caption }}</strong>
                                <p>Timestamp: {{ $complaint->timestamp ? $complaint->timestamp->format('M d, Y h:i A') : 'No timestamp available' }}</p>
                                <p>Sentiment: {{ $complaint->overall_sentiment }}</p>
                                <p>Category: {{ $complaint->complaint_category }}</p>
                                <p>Subcounty: {{ $complaint->subcounty }}</p>
                                <p>Ward: {{ $complaint->ward }}</p>
                                <p>Source: {{ $complaint->source }}</p>
                            </div>
                        @empty
                            <div class="alert alert-info" role="alert">
                                No recent complaints found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sentiment Analysis</h5>
                        <canvas id="sentimentChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sentiment Trend Over Time</h5>
                        <canvas id="sentimentTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Complaints Per Subcounty</h5>
                        <canvas id="complaintsPerSubcountyChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Complaints Per Ward</h5>
                        <canvas id="complaintsPerWardChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Complaints Per Category</h5>
                        <canvas id="complaintsPerCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sentiment Chart
            const ctx1 = document.getElementById('sentimentChart').getContext('2d');
            const sentimentData = @json($sentimentData);
            const labels1 = sentimentData.map(item => item.overall_sentiment);
            const data1 = sentimentData.map(item => item.count);
            new Chart(ctx1, {
                type: 'pie',
                data: {
                    labels: labels1,
                    datasets: [{
                        data: data1,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Sentiment Distribution'
                        }
                    }
                }
            });

            // Sentiment Trend Chart
            const ctx2 = document.getElementById('sentimentTrendChart').getContext('2d');
            const sentimentTrendData = @json($sentimentTrendData);
            const labels2 = sentimentTrendData.map(item => item.date);
            const datasets2 = sentimentTrendData.reduce((acc, item) => {
                if (!acc.find(d => d.label === item.overall_sentiment)) {
                    acc.push({ label: item.overall_sentiment, data: [], borderColor: getRandomColor(), fill: false });
                }
                acc.find(d => d.label === item.overall_sentiment).data.push(item.count);
                return acc;
            }, []);
            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: labels2,
                    datasets: datasets2
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Sentiment Trend Over Time'
                        }
                    }
                }
            });

            // Complaints Per Subcounty Chart
            const ctx3 = document.getElementById('complaintsPerSubcountyChart').getContext('2d');
            const complaintsPerSubcountyData = @json($complaintsPerSubcounty);
            const labels3 = complaintsPerSubcountyData.map(item => item.subcounty);
            const data3 = complaintsPerSubcountyData.map(item => item.count);
            new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: labels3,
                    datasets: [{
                        label: 'Complaints',
                        data: data3,
                        backgroundColor: '#3498db',
                        borderColor: '#2980b9',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Complaints Per Subcounty'
                        }
                    }
                }
            });

            // Complaints Per Ward Chart
            const ctx4 = document.getElementById('complaintsPerWardChart').getContext('2d');
            const complaintsPerWardData = @json($complaintsPerWard);
            const labels4 = complaintsPerWardData.map(item => item.ward);
            const data4 = complaintsPerWardData.map(item => item.count);
            new Chart(ctx4, {
                type: 'bar',
                data: {
                    labels: labels4,
                    datasets: [{
                        label: 'Complaints',
                        data: data4,
                        backgroundColor: '#3498db',
                        borderColor: '#2980b9',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Complaints Per Ward'
                        }
                    }
                }
            });

            // Complaints Per Category Chart
            const ctx5 = document.getElementById('complaintsPerCategoryChart').getContext('2d');
            const complaintsPerCategoryData = @json($complaintsPerCategory);
            const labels5 = complaintsPerCategoryData.map(item => item.complaint_category);
            const data5 = complaintsPerCategoryData.map(item => item.count);
            new Chart(ctx5, {
                type: 'bar',
                data: {
                    labels: labels5,
                    datasets: [{
                        label: 'Complaints',
                        data: data5,
                        backgroundColor: '#3498db',
                        borderColor: '#2980b9',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Complaints Per Category'
                        }
                    }
                }
            });

            function getRandomColor() {
                const letters = '0123456789ABCDEF';
                let color = '#';
                for (let i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }

            function clearFilters() {
                document.querySelectorAll('.filters select, .filters input[type="date"]').forEach(input => {
                    input.value = '';
                });
                applyFilters();
            }
        });
    </script>
</body>
</html>