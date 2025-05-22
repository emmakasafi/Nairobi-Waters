{{-- resources/views/admin/water_sentiments.blade.php --}}

@extends('adminlte::page')

@section('title', 'Water Sentiments')

@section('content_header')
    <h1 class="text-2xl font-semibold text-gray-800">Water Sentiments</h1>
@stop

@section('content')
    <div class="bg-white rounded shadow p-4">
        <table id="sentimentsTable" class="table table-bordered table-hover">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>User Name</th>
                    <th>User Email</th>
                    <th>User Phone</th>
                    <th>Original Caption</th>
                    <th>Processed Caption</th>
                    <th>Timestamp</th>
                    <th>Overall Sentiment</th>
                    <th>Complaint Category</th>
                    <th>Source</th>
                    <th>Subcounty</th>
                    <th>Ward</th>
                    <th>Status</th>
                    <th>Entity Type</th>
                    <th>Entity Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($water_sentiments as $water_sentiment)
                    <tr>
                        <td>{{ $water_sentiment->id }}</td>
                        <td>{{ $water_sentiment->user_id }}</td>
                        <td>{{ $water_sentiment->user_name }}</td>
                        <td>{{ $water_sentiment->user_email }}</td>
                        <td>{{ $water_sentiment->user_phone }}</td>
                        <td>{{ $water_sentiment->original_caption }}</td>
                        <td>{{ $water_sentiment->processed_caption }}</td>
                        <td>{{ $water_sentiment->timestamp }}</td>
                        <td>{{ $water_sentiment->overall_sentiment }}</td>
                        <td>{{ $water_sentiment->complaint_category }}</td>
                        <td>{{ $water_sentiment->source }}</td>
                        <td>{{ $water_sentiment->subcounty }}</td>
                        <td>{{ $water_sentiment->ward }}</td>
                        <td>{{ $water_sentiment->status }}</td>
                        <td>{{ $water_sentiment->entity_type }}</td>
                        <td>{{ $water_sentiment->entity_name }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('water_sentiments.show', $water_sentiment->id) }}" class="btn btn-success btn-sm">View</a>
                            <a href="{{ route('water_sentiments.edit', $water_sentiment->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('water_sentiments.destroy', $water_sentiment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <style>
        .dataTables_wrapper .dataTables_length select {
            padding: 0.375rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
        }

        .dataTables_wrapper .dataTables_filter input {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
        }

        .paginate_button.current {
            background-color: #0077B6 !important;
            color: white !important;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#sentimentsTable').DataTable({
                order: [[7, 'desc']],
                pageLength: 10,
                responsive: true
            });
        });
    </script>
@stop
