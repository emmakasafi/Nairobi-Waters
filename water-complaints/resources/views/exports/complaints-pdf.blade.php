<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complaints Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h1 { text-align: center; }
        .header { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Water Complaints Report</h1>
        <p>Generated on {{ now()->format('M d, Y H:i:s') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Caption</th>
                <th>Processed Caption</th>
                <th>Date</th>
                <th>Category</th>
                <th>Subcounty</th>
                <th>Ward</th>
                <th>Sentiment</th>
                <th>Source</th>
                <th>Status</th>
                <th>User Name</th>
                <th>User Email</th>
                <th>User Phone</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($complaints as $complaint)
                <tr>
                    <td>{{ $complaint->original_caption ?? 'N/A' }}</td>
                    <td>{{ $complaint->processed_caption ?? 'N/A' }}</td>
                    <td>{{ $complaint->timestamp ? $complaint->timestamp->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    <td>{{ $complaint->complaint_category ?? 'Unknown' }}</td>
                    <td>{{ $complaint->subcounty ?? 'Unknown' }}</td>
                    <td>{{ $complaint->ward ?? 'Unknown' }}</td>
                    <td>{{ $complaint->overall_sentiment ?? 'Unknown' }}</td>
                    <td>{{ $complaint->source ?? 'Unknown' }}</td>
                    <td>{{ $complaint->status ?? 'Unknown' }}</td>
                    <td>{{ $complaint->user_name ?? 'N/A' }}</td>
                    <td>{{ $complaint->user_email ?? 'N/A' }}</td>
                    <td>{{ $complaint->user_phone ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center;">No complaints found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>