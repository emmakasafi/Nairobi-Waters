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
    
    <style>
        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .filter-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .chart-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
        }
        
        .chart-card:hover {
            transform: translateY(-2px);
        }
        
        .small-box {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .small-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), transparent);
            pointer-events: none;
        }
        
        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0,0.2);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3f3;
            border-top: 5px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .complaint-item {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        
        .complaint-item:hover {
            transform: translateX(5px);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .content-wrapper {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        @media (max-width: 768px) {
            .col-md {
                margin-bottom: 10px;
            }
            
            .small-box {
                margin-bottom: 20px;
            }
            
            .chart-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="btn btn-danger" href="#">
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
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="color: #2c3e50; font-weight: 700;">
                            <i class="fas fa-chart-line text-primary"></i> Analytics Dashboard
                        </h1>
                        <p class="text-muted">Real-time water complaints monitoring and analysis</p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <!-- Filter Section -->
            <div class="card filter-card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i> Advanced Filters
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" id="categoryFilter">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label">Subcounty</label>
                            <select class="form-select" name="subcounty" id="subcountyFilter">
                                <option value="">All Subcounties</option>
                                @foreach ($subcounties as $subcounty)
                                    <option value="{{ $subcounty }}">{{ $subcounty }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label">Ward</label>
                            <select class="form-select" name="ward" id="wardFilter">
                                <option value="">All Wards</option>
                                @foreach ($wards as $ward)
                                    <option value="{{ $ward }}">{{ $ward }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label">Sentiment</label>
                            <select class="form-select" name="sentiment" id="sentimentFilter">
                                <option value="">All Sentiments</option>
                                @foreach ($sentiments as $sentiment)
                                    <option value="{{ $sentiment }}">{{ $sentiment }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label">Source</label>
                            <select class="form-select" name="source" id="sourceFilter">
                                <option value="">All Sources</option>
                                @foreach ($sources as $source)
                                    <option value="{{ $source }}">{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="startDateFilter">
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="endDateFilter">
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="fas fa-search"></i> Apply
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 d-flex align-items-end">
                            <button type="button" class="btn btn-secondary w-100" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="small-box bg-info stats-card">
                        <div class="inner">
                            <h3 id="totalComplaintsCount">{{ $totalComplaints }}</h3>
                            <p>Total Complaints</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <a href="{{ route('water_sentiments') }}" class="small-box-footer">
                            View All <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="small-box bg-success stats-card">
                        <div class="inner">
                            <h3 id="resolvedCount">0</h3>
                            <p>Resolved</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="small-box bg-warning stats-card">
                        <div class="inner">
                            <h3 id="pendingCount">0</h3>
                            <p>Pending</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="small-box bg-danger stats-card">
                        <div class="inner">
                            <h3 id="criticalCount">0</h3>
                            <p>Critical</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card chart-card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie"></i> Complaint Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card chart-card">
                        <div class="card-header bg-info text-white">
                            <h3 class="card-title">
                                <i class="fas fa-heart"></i> Sentiment Distribution
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="sentimentChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="card chart-card">
                        <div class="card-header bg-warning text-white">
                            <h3 class="card-title">
                                <i class="fas fa-newspaper"></i> Recent Complaints
                            </h3>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div id="recentComplaintsList">
                                @forelse ($recentComplaints->take(3) as $complaint)
                                    <div class="complaint-item">
                                        <h6 class="mb-2">{{ $complaint->processed_caption ?? $complaint->original_caption }}</h6>
                                        <div class="row text-sm">
                                            <div class="col-6">
                                                <p class="mb-1"><i class="fas fa-calendar"></i> {{ $complaint->timestamp ? $complaint->timestamp->format('M d, Y') : 'N/A' }}</p>
                                                <p class="mb-1"><i class="fas fa-tag"></i> {{ $complaint->complaint_category }}</p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1"><i class="fas fa-map-marker-alt"></i> {{ $complaint->subcounty }}</p>
                                                <p class="mb-1"><i class="fas fa-smile"></i> {{ $complaint->overall_sentiment }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No recent complaints found.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row mb-4">
                <div class="col-lg-6 col-md-12">
                    <div class="card chart-card">
                        <div class="card-header bg-success text-white">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line"></i> Sentiment Trend
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="sentimentTrendChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="card chart-card">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="card-title">
                                <i class="fas fa-map"></i> Complaints by Location
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="complaintsPerSubcountyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 3 -->
            <div class="row mb-4">
                <div class="col-lg-6 col-md-12">
                    <div class="card chart-card">
                        <div class="card-header bg-dark text-white">
                            <h3 class="card-title">
                                <i class="fas fa-home"></i> Complaints by Ward
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="complaintsPerWardChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="card chart-card">
                        <div class="card-header bg-gradient-primary text-white">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i> Complaints by Category
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="complaintsPerCategoryChart"></canvas>
                        </div>
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
    let chartInstances = {};
    
    const originalData = {
        sentimentData: @json($sentimentData),
        sentimentTrendData: @json($sentimentTrendData),
        complaintsPerSubcounty: @json($complaintsPerSubcounty),
        complaintsPerWard: @json($complaintsPerWard),
        complaintsPerCategory: @json($complaintsPerCategory),
        complaintStatuses: @json($complaintStatuses),
        recentComplaints: @json($recentComplaints),
        totalComplaints: {{ $totalComplaints }}
    };

    const filters = {
        category: '',
        subcounty: '',
        ward: '',
        sentiment: '',
        source: '',
        startDate: null,
        endDate: null
    };

    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        updateStatistics();
    });

    function initializeCharts() {
        createSentimentChart();
        createSentimentTrendChart();
        createSubcountyChart();
        createWardChart();
        createCategoryChart();
        createStatusChart();
    }

    function createSentimentChart() {
        const ctx = document.getElementById('sentimentChart').getContext('2d');
        if (chartInstances.sentimentChart) {
            chartInstances.sentimentChart.destroy();
        }
        
        const data = getFilteredData(originalData.sentimentData);
        
        chartInstances.sentimentChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.overall_sentiment),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    function createSentimentTrendChart() {
        const ctx = document.getElementById('sentimentTrendChart').getContext('2d');
        if (chartInstances.sentimentTrendChart) {
            chartInstances.sentimentTrendChart.destroy();
        }
        
        const data = getFilteredData(originalData.sentimentTrendData);
        
        const datasets = [...new Set(data.map(item => item.overall_sentiment))].map(sentiment => {
            const color = getColorForSentiment(sentiment);
            return {
                label: sentiment,
                data: data.map(item => item.overall_sentiment === sentiment ? item.count : 0),
                borderColor: color,
                backgroundColor: color + '20',
                fill: true,
                tension: 0.4
            };
        });
        
        chartInstances.sentimentTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.date),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function createSubcountyChart() {
        const ctx = document.getElementById('complaintsPerSubcountyChart').getContext('2d');
        if (chartInstances.subcountyChart) {
            chartInstances.subcountyChart.destroy();
        }
        
        const data = getFilteredData(originalData.complaintsPerSubcounty);
        
        chartInstances.subcountyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.subcounty),
                datasets: [{
                    label: 'Complaints',
                    data: data.map(item => item.count),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function createWardChart() {
        const ctx = document.getElementById('complaintsPerWardChart').getContext('2d');
        if (chartInstances.wardChart) {
            chartInstances.wardChart.destroy();
        }
        
        const data = getFilteredData(originalData.complaintsPerWard);
        
        chartInstances.wardChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.ward),
                datasets: [{
                    label: 'Complaints',
                    data: data.map(item => item.count),
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function createCategoryChart() {
        const ctx = document.getElementById('complaintsPerCategoryChart').getContext('2d');
        if (chartInstances.categoryChart) {
            chartInstances.categoryChart.destroy();
        }
        
        const data = getFilteredData(originalData.complaintsPerCategory);
        
        chartInstances.categoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.complaint_category),
                datasets: [{
                    label: 'Complaints',
                    data: data.map(item => item.count),
                    backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function createStatusChart() {
        const ctx = document.getElementById('statusChart').getContext('2d');
        if (chartInstances.statusChart) {
            chartInstances.statusChart.destroy();
        }
        
        const data = getFilteredData(originalData.complaintStatuses);
        
        chartInstances.statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.status),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    function updateStatistics() {
        const statusData = originalData.complaintStatuses;
        const resolvedCount = statusData.find(item => item.status === 'Resolved')?.count || 0;
        const pendingCount = statusData.find(item => item.status === 'Pending')?.count || 0;
        const criticalCount = statusData.find(item => item.status === 'Critical')?.count || 0;
        
        document.getElementById('resolvedCount').textContent = resolvedCount;
        document.getElementById('pendingCount').textContent = pendingCount;
        document.getElementById('criticalCount').textContent = criticalCount;
    }

    function applyFilters() {
        filters.category = document.getElementById('categoryFilter').value || '';
        filters.subcounty = document.getElementById('subcountyFilter').value || '';
        filters.ward = document.getElementById('wardFilter').value || '';
        filters.sentiment = document.getElementById('sentimentFilter').value || '';
        filters.source = document.getElementById('sourceFilter').value || '';
        filters.startDate = document.getElementById('startDateFilter').value ? new Date(document.getElementById('startDateFilter').value) : null;
        filters.endDate = document.getElementById('endDateFilter').value ? new Date(document.getElementById('endDateFilter').value) : null;

        updateCharts();
    }

    function clearFilters() {
        document.getElementById('categoryFilter').value = '';
        document.getElementById('subcountyFilter').value = '';
        document.getElementById('wardFilter').value = '';
        document.getElementById('sentimentFilter').value = '';
        document.getElementById('sourceFilter').value = '';
        document.getElementById('startDateFilter').value = '';
        document.getElementById('endDateFilter').value = '';

        filters.category = '';
        filters.subcounty = '';
        filters.ward = '';
        filters.sentiment = '';
        filters.source = '';
        filters.startDate = null;
        filters.endDate = null;

        updateCharts();
    }

    function getFilteredData(data) {
        let filteredData = [...data];

        if (filters.category) {
            filteredData = filteredData.filter(item => item.category === filters.category);
        }
        if (filters.subcounty) {
            filteredData = filteredData.filter(item => item.subcounty === filters.subcounty);
        }
        if (filters.ward) {
            filteredData = filteredData.filter(item => item.ward === filters.ward);
        }
        if (filters.sentiment) {
            filteredData = filteredData.filter(item => item.overall_sentiment === filters.sentiment);
        }
        if (filters.source) {
            filteredData = filteredData.filter(item => item.source === filters.source);
        }
        if (filters.startDate && filters.endDate) {
            const startDate = new Date(filters.startDate);
            const endDate = new Date(filters.endDate);
            filteredData = filteredData.filter(item => {
                const date = new Date(item.date);
                return date >= startDate && date <= endDate;
            });
        }

        return filteredData;
    }

    function updateCharts() {
        createSentimentChart();
        createSentimentTrendChart();
        createSubcountyChart();
        createWardChart();
        createCategoryChart();
        createStatusChart();
    }

    function getColorForSentiment(sentiment) {
        const colors = {
            'Positive': '#28a745',
            'Neutral': '#ffc107',
            'Negative': '#dc3545'
        };
        return colors[sentiment] || '#6c757d';
    }
</script>
</body>
</html>