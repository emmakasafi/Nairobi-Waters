{{-- resources/views/admin/water_sentiments.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-semibold mb-4">Water Sentiments</h1>
    <div class="mb-4 flex items-center">
        <input type="text" placeholder="Search..." class="px-4 py-2 border rounded-lg" id="searchInput">
        <button class="px-4 py-2 bg-blue-500 hover:bg-blue-700 text-white rounded-lg ml-2" onclick="searchSentiments()">Search</button>
    </div>

    <table class="min-w-full bg-white rounded-lg shadow-xl">
        <thead class="bg-gray-200 text-gray-700 text-left">
            <tr>
                <th scope="col" class="px-4 py-2">ID</th>
                <th scope="col" class="px-4 py-2">Original Caption</th>
                <th scope="col" class="px-4 py-2">Processed Caption</th>
                <th scope="col" class="px-4 py-2">Timestamp</th>
                <th scope="col" class="px-4 py-2">Overall Sentiment</th>
                <th scope="col" class="px-4 py-2">Complaint Category</th>
                <th scope="col" class="px-4 py-2">Source</th>
                <th scope="col" class="px-4 py-2">County</th>
                <th scope="col" class="px-4 py-2">Subcounty</th>
                <th scope="col" class="px-4 py-2">Ward</th>
                <th scope="col" class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody id="sentimentsTableBody">
        @foreach ($water_sentiments->sortByDesc('timestamp') as $water_sentiment)
            <tr class="{{ $loop->last ? 'bg-gray-100' : '' }}">
                <td class="px-4 py-2">{{ $water_sentiment->id }}</td>
                <td class="px-4 py-2">{{ $water_sentiment->original_caption }}</td>
                <td class="px-4 py-2">{{ $water_sentiment->processed_caption }}</td>
                <td class="px-4 py-2">{{ $water_sentiment->timestamp }}</td>
                <td class="px-4 py-2">{{ $water_sentiment->overall_sentiment }}</td>
                <td class="px-4 py-2">{{ $water_sentiment->complaint_category }}</td>
                <td class="px-4 py-2">{{ $water_sentiment->source }}</td>
                <td class="px-4 py-2">{{ $water_sentiment->county }}</td>
                <td class="px-4 py-2">{{ $water_sentiment->subcounty }}</td>
                <td class="px-4 py-2">{{ $water_sentiment->ward }}</td>
                <td class="px-4 py-2">
                    <a href="{{ route('water_sentiments.show', $water_sentiment->id) }}" class="px-4 py-2 bg-green-500 hover:bg-green-700 text-white rounded-lg">View</a>
                    <a href="{{ route('water_sentiments.edit', $water_sentiment->id) }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-700 text-white rounded-lg">Edit</a>
                    <form action="{{ route('water_sentiments.destroy', $water_sentiment->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-700 text-white rounded-lg">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.querySelector('button[onclick="searchSentiments()"]');
    const tableBody = document.getElementById('sentimentsTableBody');

    searchButton.addEventListener('click', function() {
        const query = searchInput.value.trim();
        if (query) {
            fetch(`/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = '';
                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.className = tableBody.rows.length % 2 ? 'bg-gray-100' : '';
                        row.innerHTML = `
                            <td class="px-4 py-2">${item.id}</td>
                            <td class="px-4 py-2">${item.original_caption}</td>
                            <td class="px-4 py-2">${item.processed_caption}</td>
                            <td class="px-4 py-2">${item.timestamp}</td>
                            <td class="px-4 py-2">${item.overall_sentiment}</td>
                            <td class="px-4 py-2">${item.complaint_category}</td>
                            <td class="px-4 py-2">${item.source}</td>
                            <td class="px-4 py-2">${item.county}</td>
                            <td class="px-4 py-2">${item.subcounty}</td>
                            <td class="px-4 py-2">${item.ward}</td>
                            <td class="px-4 py-2">
                                <a href="/admin/water_sentiments/${item.id}" class="px-4 py-2 bg-green-500 hover:bg-green-700 text-white rounded-lg">View</a>
                                <a href="/admin/water_sentiments/${item.id}/edit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-700 text-white rounded-lg">Edit</a>
                                <form action="/admin/water_sentiments/${item.id}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-700 text-white rounded-lg">Delete</button>
                                </form>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                });
        } else {
            window.location = '/admin/water_sentiments';
        }
    });
});
</script>
@endsection