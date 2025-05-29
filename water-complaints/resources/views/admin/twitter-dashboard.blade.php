<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Waters - Twitter Data Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        header { text-align: center; margin-bottom: 20px; }
        nav ul { list-style: none; padding: 0; }
        nav ul li { display: inline; margin-right: 20px; }
        nav ul li a { text-decoration: none; color: #007bff; }
        section { margin-bottom: 40px; }
        h2, h3 { color: #333; }
        .filters { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; }
        .filters div { display: flex; flex-direction: column; }
        .filters select, .filters input { padding: 8px; }
        .filters button { padding: 8px 16px; background: #007bff; color: white; border: none; cursor: pointer; }
        .filters a { padding: 8px 16px; color: #007bff; text-decoration: none; }
        .charts { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .charts div { border: 1px solid #ddd; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <header>
        <h1>Nairobi Waters</h1>
        <h2>Twitter Data Dashboard</h2>
        <nav>
            <ul>
                <li><a href="{{ route('admin.dashboard') }}">Main Dashboard</a></li>
                <li><a href="{{ route('admin.twitter.dashboard') }}">Twitter Data</a></li>
                <li><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li><a href="{{ route('logout') }}">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Twitter Analytics Dashboard</h2>
            <p>Real-time Twitter water complaints monitoring and insights</p>
            <div>
                <h3>{{ $totalComplaints }}</h3>
                <p>Total Twitter Complaints</p>
            </div>
        </section>

        <section>
            <h2>Filters</h2>
            <form method="GET" action="{{ route('admin.twitter.dashboard') }}">
                @csrf
                <div class="filters">
                    <div>
                        <label for="category">Category</label>
                        <select name="category" id="category">
                            <option value="All Categories">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sentiment">Sentiment</label>
                        <select name="sentiment" id="sentiment">
                            <option value="All Sentiments">All Sentiments</option>
                            @foreach ($sentiments as $sentiment)
                                <option value="{{ $sentiment }}" {{ request('sentiment') == $sentiment ? 'selected' : '' }}>{{ $sentiment }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div>
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div>
                        <button type="submit">Apply Filters</button>
                        <a href="{{ route('admin.twitter.dashboard') }}">Clear</a>
                    </div>
                </div>
            </form>
        </section>

        <section class="charts">
            <div>
                <h3>Sentiment Analysis</h3>
                <canvas id="sentimentChart"></canvas>
            </div>
            <div>
                <h3>Sentiment Trends Over Time</h3>
                <canvas id="sentimentTrendChart"></canvas>
            </div>
            <div>
                <h3>Complaints by Category</h3>
                <canvas id="categoryChart"></canvas>
            </div>
        </section>

        <section>
            <h2>Recent Twitter Complaints</h2>
            <table>
                <thead>
                    <tr>
                        <th>Caption</th>
                        <th>Timestamp</th>
                        <th>Category</th>
                        <th>Sentiment</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentComplaints as $complaint)
                        <tr>
                            <td>{{ $complaint->processed_caption ?? $complaint->original_caption ?? 'N/A' }}</td>
                            <td>{{ $complaint->timestamp ? $complaint->timestamp->format('Y-m-d H:i:s') : 'N/A' }}</td>
                            <td>{{ $complaint->complaint_category ?? 'Unknown' }}</td>
                            <td>{{ $complaint->overall_sentiment ?? 'Unknown' }}</td>
                            <td>{{ $complaint->source ?? 'Twitter' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No recent Twitter complaints found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>

    <script>
        // Sentiment Chart
        const sentimentCtx = document.getElementById('sentimentChart').getContext('2d');
        new Chart(sentimentCtx, {
            type: 'pie',
            data: {
                labels: @json($sentimentData->pluck('overall_sentiment')),
                datasets: [{
                    data: @json($sentimentData->pluck('count')),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Sentiment Distribution' }
                }
            }
        });

        // Sentiment Trend Chart
        const sentimentTrendCtx = document.getElementById('sentimentTrendChart').getContext('2d');
        const dates = @json($sentimentTrendData->pluck('date')->unique()->sort());
        const sentiments = @json($sentimentTrendData->pluck('overall_sentiment')->unique());
        const datasets = sentiments.map(sentiment => ({
            label: sentiment,
            data: dates.map(date => {
                const record = @json($sentimentTrendData)->find(item => item.date === date && item.overall_sentiment === sentiment);
                return record ? record.count : 0;
            }),
            borderColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'][sentiments.indexOf(sentiment) % 4],
            fill: false
        }));
        new Chart(sentimentTrendCtx, {
            type: 'line',
            data: { labels: dates, datasets },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Sentiment Trends Over Time' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: @json($complaintsPerCategory->pluck('complaint_category')),
                datasets: [{
                    label: 'Complaints',
                    data: @json($complaintsPerCategory->pluck('count')),
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Complaints by Category' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>