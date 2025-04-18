{{-- resources/views/admin/water_sentiments.blade.php --}}

@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <style>
        body {
            font-family: 'ui-sans-serif', 'system-ui', 'sans-serif';
        }

        div.dataTables_length label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #374151; /* Tailwind gray-700 */
        }

        div.dataTables_length select {
            padding: 0.375rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db; /* Tailwind gray-300 */
            background-color: white;
            font-size: 0.875rem;
        }

        div.dataTables_filter input {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            font-size: 0.875rem;
        }

        div.dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin: 0 2px;
            border-radius: 0.5rem;
            background-color: #f3f4f6; /* Tailwind gray-100 */
            border: 1px solid #d1d5db;
            font-size: 0.875rem;
        }

        div.dataTables_paginate .paginate_button.current {
            background-color: #0077B6;
            color: white !important;
        }
    </style>
@endpush

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-semibold mb-4 text-gray-800">Water Sentiments</h1>

    <div class="overflow-x-auto bg-white rounded-lg shadow-xl p-4">
        <table id="sentimentsTable" class="min-w-full">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Original Caption</th>
                    <th class="px-4 py-2">Processed Caption</th>
                    <th class="px-4 py-2">Timestamp</th>
                    <th class="px-4 py-2">Overall Sentiment</th>
                    <th class="px-4 py-2">Complaint Category</th>
                    <th class="px-4 py-2">Source</th>
                    <th class="px-4 py-2">County</th>
                    <th class="px-4 py-2">Subcounty</th>
                    <th class="px-4 py-2">Ward</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($water_sentiments as $water_sentiment)
                    <tr>
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
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex space-x-2">
                                <a href="{{ route('water_sentiments.show', $water_sentiment->id) }}" class="px-3 py-1 bg-green-500 hover:bg-green-700 text-white rounded-lg text-sm">View</a>
                                <a href="{{ route('water_sentiments.edit', $water_sentiment->id) }}" class="px-3 py-1 bg-yellow-500 hover:bg-yellow-700 text-white rounded-lg text-sm">Edit</a>
                                <form action="{{ route('water_sentiments.destroy', $water_sentiment->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-500 hover:bg-red-700 text-white rounded-lg text-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#sentimentsTable').DataTable({
                order: [[3, 'desc']],
                pageLength: 10,
                responsive: true
            });
        });
    </script>
@endpush
