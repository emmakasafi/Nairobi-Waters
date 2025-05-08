@extends('adminlte::page')

@section('title', 'Officer Dashboard')

@section('content_header')
    <h1>My Assigned Water Sentiments</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Title</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @foreach($waterSentiments as $waterSentiment)
                <tr>
                    <td>{{ $waterSentiment->id }}</td>
                    <td>{{ $waterSentiment->user->name ?? 'Unknown' }}</td>
                    <td>{{ $waterSentiment->title }}</td>
                    <td>{{ ucfirst($waterSentiment->status) }}</td>
                    <td><a href="{{ route('officer.show', $waterSentiment->id) }}" class="btn btn-info btn-sm">View</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
