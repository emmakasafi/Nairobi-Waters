<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Waters - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            font-family: 'Poppins', sans-serif;
            color: #1e293b;
        }
        .card {
            background: linear-gradient(145deg, #ffffff, #f0f9ff);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-in-out;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }
        .sidebar {
            background: linear-gradient(180deg, #1e40af, #3b82f6);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar a {
            transition: background-color 0.3s ease, padding-left 0.3s ease;
        }
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 1.5rem;
        }
        .btn-primary {
            background: linear-gradient(90deg, #2563eb, #4f46e5);
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            background: linear-gradient(90deg, #1d4ed8, #4338ca);
        }
        .btn-secondary {
            background: linear-gradient(90deg, #6b7280, #4b5563);
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-secondary:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            background: linear-gradient(90deg, #4b5563, #374151);
        }
        .table-header {
            background: linear-gradient(90deg, #e0f2fe, #bae6fd);
        }
        table tr {
            transition: background-color 0.3s ease;
        }
        table tr:hover {
            background-color: #f0f9ff;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="sidebar w-64 p-6 text-white">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight">Nairobi Waters</h1>
            <p class="text-sm opacity-80">Admin Dashboard</p>
        </div>
        <nav>
            <ul class="space-y-4">
                <li><a href="{{ route('admin.dashboard') }}" class="block py-2 px-4 rounded hover:bg-blue-700 text-sm font-medium">Dashboard</a></li>
                <li><a href="{{ route('admin.users.index') }}" class="block py-2 px-4 rounded hover:bg-blue-700 text-sm font-medium">Users</a></li>
                <li><a href="{{ route('logout') }}" class="block py-2 px-4 rounded hover:bg-blue-700 text-sm font-medium">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-4xl font-semibold text-gray-900 tracking-tight">Analytics Dashboard</h2>
            <p class="text-gray-600 mt-2">Real-time water complaints monitoring and intelligent analysis</p>
            <nav class="text-sm text-gray-500 mt-2">
                <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a> / <span>Dashboard</span>
            </nav>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card p-6 text-center">
                <h3 class="text-3xl font-bold text-gray-800">{{ $totalComplaints }}</h3>
                <p class="text-gray-600 mt-2">Total Complaints</p>
                <a href="{{ route('water_sentiments') }}" class="text-blue-600 hover:underline text-sm mt-2 block">View Details</a>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-3xl font-bold text-green-600">{{ $complaintStatuses->where('status', 'Resolved')->first()->count ?? 0 }}</h3>
                <p class="text-gray-600 mt-2">Resolved</p>
                <a href="{{ route('water_sentiments') }}?status=Resolved" class="text-blue-600 hover:underline text-sm mt-2 block">View Details</a>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-3xl font-bold text-yellow-600">{{ $complaintStatuses->where('status', 'Pending')->first()->count ?? 0 }}</h3>
                <p class="text-gray-600 mt-2">Pending</p>
                <a href="{{ route('water_sentiments') }}?status=Pending" class="text-blue-600 hover:underline text-sm mt-2 block">View Details</a>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-3xl font-bold text-red-600">{{ $complaintStatuses->where('status', 'Critical')->first()->count ?? 0 }}</h3>
                <p class="text-gray-600 mt-2">Critical</p>
                <a href="{{ route('water_sentiments') }}?status=Critical" class="text-blue-600 hover:underline text-sm mt-2 block">View Details</a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card p-6 mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Advanced Filters</h3>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category" id="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="All Categories">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subcounty" class="block text-sm font-medium text-gray-700">Subcounty</label>
                    <select name="subcounty" id="subcounty" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="All Subcounties">All Subcounties</option>
                        @foreach ($subcounties as $subcounty)
                            <option value="{{ $subcounty }}" {{ request('subcounty') == $subcounty ? 'selected' : '' }}>{{ $subcounty }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ward" class="block text-sm font-medium text-gray-700">Ward</label>
                    <select name="ward" id="ward" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="All Wards">All Wards</option>
                        @foreach ($wards as $ward)
                            <option value="{{ $ward }}" {{ request('ward') == $ward ? 'selected' : '' }}>{{ $ward }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="sentiment" class="block text-sm font-medium text-gray-700">Sentiment</label>
                    <select name="sentiment" id="sentiment" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="All Sentiments">All Sentiments</option>
                        @foreach ($sentiments as $sentiment)
                            <option value="{{ $sentiment }}" {{ request('sentiment') == $sentiment ? 'selected' : '' }}>{{ $sentiment }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                    <select name="source" id="source" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="All Sources">All Sources</option>
                        @foreach ($sources as $source)
                            <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex space-x-4 items-end">
                    <button type="submit" class="btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Clear</a>
                </div>
            </form>
            <div class="mt-4 flex space-x-4">
                <a href="{{ route('admin.export.csv') }}?{{ http_build_query(request()->query()) }}" class="btn-primary">Export CSV</a>
                <a href="{{ route('admin.export.excel') }}?{{ http_build_query(request()->query()) }}" class="btn-primary">Export Excel</a>
                <a href="{{ route('admin.export.pdf') }}?{{ http_build_query(request()->query()) }}" class="btn-primary">Export PDF</a>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="card p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Complaint Status</h3>
                <canvas id="complaintStatusChart"></canvas>
            </div>
            <div class="card p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Sentiment Analysis</h3>
                <canvas id="sentimentChart"></canvas>
            </div>
            <div class="card p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Sentiment Trends Over Time</h3>
                <canvas id="sentimentTrendChart"></canvas>
            </div>
            <div class="card p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Complaints by Subcounty</h3>
                <canvas id="locationChart"></canvas>
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="card p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Recent Complaints</h3>
                <span class="text-sm text-blue-600 font-medium">Live Updates</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="table-header">
                            <th class="p-4 font-semibold">Caption</th>
                            <th class="p-4 font-semibold">Timestamp</th>
                            <th class="p-4 font-semibold">Category</th>
                            <th class="p-4 font-semibold">Location</th>
                            <th class="p-4 font-semibold">Sentiment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentComplaints as $complaint)
                            <tr class="border-b">
                                <td class="p-4">{{ $complaint->processed_caption ?? $complaint->original_caption ?? 'N/A' }}</td>
                                <td class="p-4">{{ $complaint->timestamp ? $complaint->timestamp->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                <td class="p-4">{{ $complaint->complaint_category ?? 'Unknown' }}</td>
                                <td class="p-4">{{ $complaint->subcounty ?? 'Unknown' }}</td>
                                <td class="p-4">{{ $complaint->overall_sentiment ?? 'Unknown' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-600">No recent complaints found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Complaints by Ward</h3>
                <canvas id="wardChart"></canvas>
            </div>
            <div class="card p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Complaints by Category</h3>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script>
        // Helper function to safely get chart data
        function getChartData(labels, data, defaultLabels = ['No Data'], defaultData = [0]) {
            return {
                labels: labels && labels.length ? labels : defaultLabels,
                data: data && data.length ? data : defaultData
            };
        }

        // Dynamic Ward Dropdown
        document.getElementById('subcounty').addEventListener('change', function () {
            const subcounty = this.value;
            const wardSelect = document.getElementById('ward');
            wardSelect.innerHTML = '<option value="All Wards">All Wards</option>';

            if (subcounty) {
                fetch(`{{ route('admin.wards.by.subcounty') }}?subcounty=${encodeURIComponent(subcounty)}`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(wards => {
                    wards.forEach(ward => {
                        const option = document.createElement('option');
                        option.value = ward;
                        option.textContent = ward;
                        if (ward === '{{ request('ward') }}') {
                            option.selected = true;
                        }
                        wardSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching wards:', error));
            } else {
                // Populate all wards if "All Subcounties" is selected
                const allWards = @json($wards);
                allWards.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward;
                    option.textContent = ward;
                    if (ward === '{{ request('ward') }}') {
                        option.selected = true;
                    }
                    wardSelect.appendChild(option);
                });
            }
        });

        // Trigger change event on page load to populate wards if subcounty is pre-selected
        document.getElementById('subcounty').dispatchEvent(new Event('change'));

        // Color palette for charts
        const colorPalette = [
            '#34d399', // Green (Positive/Resolved)
            '#ef4444', // Red (Negative/Critical)
            '#facc15', // Yellow (Neutral/Pending)
            '#3b82f6', // Blue
            '#a855f7', // Purple
            '#ec4899', // Pink
            '#14b8a6', // Teal
            '#f97316'  // Orange
        ];

        // Complaint Status Chart
        const complaintStatusData = getChartData(
            @json($complaintStatuses->pluck('status')),
            @json($complaintStatuses->pluck('count'))
        );
        const complaintStatusCtx = document.getElementById('complaintStatusChart').getContext('2d');
        new Chart(complaintStatusCtx, {
            type: 'pie',
            data: {
                labels: complaintStatusData.labels,
                datasets: [{
                    data: complaintStatusData.data,
                    backgroundColor: ['#34d399', '#facc15', '#ef4444'], // Resolved, Pending, Critical
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Sentiment Analysis Chart
        const sentimentDataChart = getChartData(
            @json($sentimentData->pluck('overall_sentiment')),
            @json($sentimentData->pluck('count'))
        );
        const sentimentCtx = document.getElementById('sentimentChart').getContext('2d');
        new Chart(sentimentCtx, {
            type: 'bar',
            data: {
                labels: sentimentDataChart.labels,
                datasets: [{
                    label: 'Sentiment Count',
                    data: sentimentDataChart.data,
                    backgroundColor: sentimentDataChart.labels.map(label => {
                        if (label === 'Positive') return '#34d399';
                        if (label === 'Negative') return '#ef4444';
                        if (label === 'Neutral') return '#facc15';
                        return '#60a5fa';
                    }),
                    borderColor: '#2563eb',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Number of Complaints' }
                    },
                    x: {
                        title: { display: true, text: 'Sentiment' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });

        // Sentiment Trends Chart
        const sentimentTrends = @json($sentimentTrendData);
        const dates = sentimentTrends && sentimentTrends.length ? [...new Set(sentimentTrends.map(item => item.date))] : ['No Data'];
        const sentiments = sentimentTrends && sentimentTrends.length ? [...new Set(sentimentTrends.map(item => item.overall_sentiment))] : ['No Data'];
        const datasets = sentiments.map((sentiment, index) => ({
            label: sentiment,
            data: dates.map(date => {
                const item = sentimentTrends.find(t => t.date === date && t.overall_sentiment === sentiment);
                return item ? item.count : 0;
            }),
            borderColor: sentiment === 'Positive' ? '#34d399' :
                        sentiment === 'Negative' ? '#ef4444' :
                        sentiment === 'Neutral' ? '#facc15' : colorPalette[index % colorPalette.length],
            backgroundColor: sentiment === 'Positive' ? 'rgba(52, 211, 153, 0.2)' :
                            sentiment === 'Negative' ? 'rgba(239, 68, 68, 0.2)' :
                            sentiment === 'Neutral' ? 'rgba(250, 204, 21, 0.2)' : `rgba(${colorPalette[index % colorPalette.length]}, 0.2)`,
            fill: true,
            tension: 0.4
        }));
        const sentimentTrendCtx = document.getElementById('sentimentTrendChart').getContext('2d');
        new Chart(sentimentTrendCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Number of Complaints' }
                    },
                    x: {
                        title: { display: true, text: 'Date' }
                    }
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });

        // Location Distribution Chart
        const locationData = getChartData(
            @json($complaintsPerSubcounty->pluck('subcounty')),
            @json($complaintsPerSubcounty->pluck('count'))
        );
        const locationCtx = document.getElementById('locationChart').getContext('2d');
        new Chart(locationCtx, {
            type: 'bar',
            data: {
                labels: locationData.labels,
                datasets: [{
                    label: 'Complaints by Subcounty',
                    data: locationData.data,
                    backgroundColor: locationData.labels.map((_, index) => colorPalette[index % colorPalette.length]),
                    borderColor: colorPalette.map(color => color.replace('0.2', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Number of Complaints' }
                    },
                    x: {
                        title: { display: true, text: 'Subcounty' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });

        // Complaints by Ward Chart
        const wardData = getChartData(
            @json($complaintsPerWard->pluck('ward')),
            @json($complaintsPerWard->pluck('count'))
        );
        const wardCtx = document.getElementById('wardChart').getContext('2d');
        new Chart(wardCtx, {
            type: 'bar',
            data: {
                labels: wardData.labels,
                datasets: [{
                    label: 'Complaints by Ward',
                    data: wardData.data,
                    backgroundColor: wardData.labels.map((_, index) => colorPalette[index % colorPalette.length]),
                    borderColor: colorPalette.map(color => color.replace('0.2', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Number of Complaints' }
                    },
                    x: {
                        title: { display: true, text: 'Ward' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });

        // Complaints by Category Chart
        const categoryData = getChartData(
            @json($complaintsPerCategory->pluck('complaint_category')),
            @json($complaintsPerCategory->pluck('count'))
        );
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: categoryData.labels,
                datasets: [{
                    label: 'Complaints by Category',
                    data: categoryData.data,
                    backgroundColor: categoryData.labels.map((_, index) => colorPalette[index % colorPalette.length]),
                    borderColor: colorPalette.map(color => color.replace('0.2', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Number of Complaints' }
                    },
                    x: {
                        title: { display: true, text: 'Category' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>