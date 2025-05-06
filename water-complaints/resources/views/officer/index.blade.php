@extends('adminlte::page')

@section('title', 'Officer Dashboard')

@section('content_header')
    <h1>My Assigned Complaints</h1>
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
            @foreach($complaints as $complaint)
                <tr>
                    <td>{{ $complaint->id }}</td>
                    <td>{{ $complaint->user->name ?? 'Unknown' }}</td>
                    <td>{{ $complaint->title }}</td>
                    <td>{{ ucfirst($complaint->status) }}</td>
                    <td><a href="{{ route('officer.show', $complaint->id) }}" class="btn btn-info btn-sm">View</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
