<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- AdminLTE & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="btn btn-danger" href="{{ route('admin.logout') }}">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="#" class="brand-link">
            <span class="brand-text font-weight-light">Admin Panel</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item">
                        <a href="#" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->is('admin/users') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link {{ request()->is('complaints') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-comments"></i>
                            <p>Complaints</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link {{ request()->is('reports') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('departments.index') }}" class="nav-link {{ request()->is('departments') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>Departments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link {{ request()->is('settings') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Settings</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper p-4">
        <section class="content-header">
            <h1>Welcome, Admin!</h1>
        </section>

        <section class="content">
            <!-- Filter Section -->
            <div class="card">
                <div class="card-body">
                    <form class="row g-2">
                        <div class="col-md">
                            <select class="form-control" name="category">
                                <option>All Categories</option>
                                @foreach ($categories as $category)
                                    <option>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md">
                            <select class="form-control" name="subcounty">
                                <option>All Subcounties</option>
                                @foreach ($subcounties as $subcounty)
                                    <option>{{ $subcounty }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md">
                            <select class="form-control" name="ward">
                                <option>All Wards</option>
                                @foreach ($wards as $ward)
                                    <option>{{ $ward }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md">
                            <select class="form-control" name="sentiment">
                                <option>All Sentiments</option>
                                @foreach ($sentiments as $sentiment)
                                    <option>{{ $sentiment }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md">
                            <select class="form-control" name="source">
                                <option>All Sources</option>
                                @foreach ($sources as $source)
                                    <option>{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md">
                            <input type="date" class="form-control" name="start_date">
                        </div>
                        <div class="col-md">
                            <input type="date" class="form-control" name="end_date">
                        </div>
                        <div class="col-md">
                            <button type="submit" class="btn btn-primary w-100">Apply</button>
                        </div>
                        <div class="col-md">
                            <button type="reset" class="btn btn-secondary w-100">Clear</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cards & Charts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $totalComplaints }}</h3>
                            <p>Total Complaints</p>
                        </div>
                        <a href="{{ route('water_sentiments') }}" class="small-box-footer">
                            View All <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Recent Complaints</h3>
                        </div>
                        <div class="card-body">
                            @forelse ($recentComplaints->take(3) as $complaint)
                                <div class="callout callout-warning">
                                    <h5>{{ $complaint->processed_caption ?? $complaint->original_caption }}</h5>
                                    <p><strong>Time:</strong> {{ $complaint->timestamp ? $complaint->timestamp->format('M d, Y h:i A') : 'N/A' }}</p>
                                    <p><strong>Sentiment:</strong> {{ $complaint->overall_sentiment }}</p>
                                    <p><strong>Category:</strong> {{ $complaint->complaint_category }}</p>
                                    <p><strong>Location:</strong> {{ $complaint->subcounty }}, {{ $complaint->ward }}</p>
                                    <p><strong>Source:</strong> {{ $complaint->source }}</p>
                                </div>
                            @empty
                                <div class="alert alert-info">No recent complaints found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">Sentiment Distribution</h3></div>
                        <div class="card-body">
                            <canvas id="sentimentChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header"><h3 class="card-title">Sentiment Trend</h3></div>
                        <div class="card-body">
                            <canvas id="sentimentTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- More Charts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header"><h3 class="card-title">Complaints Per Subcounty</h3></div>
                        <div class="card-body"><canvas id="complaintsPerSubcountyChart"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header"><h3 class="card-title">Complaints Per Ward</h3></div>
                        <div class="card-body"><canvas id="complaintsPerWardChart"></canvas></div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card card-info">
                        <div class="card-header"><h3 class="card-title">Complaints Per Category</h3></div>
                        <div class="card-body"><canvas id="complaintsPerCategoryChart"></canvas></div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
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