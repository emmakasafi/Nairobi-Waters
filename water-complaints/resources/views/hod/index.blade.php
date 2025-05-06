@extends('adminlte::page')

@section('title', 'HOD Dashboard')

@section('content_header')
    <h1>HOD Dashboard - Assign Complaints</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h4>Unassigned Complaints</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Title</th>
                <th>Description</th>
                <th>Assign To</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unassignedComplaints as $complaint)
                <tr>
                    <td>{{ $complaint->id }}</td>
                    <td>{{ $complaint->user->name ?? 'Unknown' }}</td>
                    <td>{{ $complaint->title }}</td>
                    <td>{{ $complaint->description }}</td>
                    <td>
                        <form method="POST" action="{{ route('hod.assign', $complaint->id) }}">
                            @csrf
                            <select name="officer_id" class="form-control" required>
                                <option value="">Select Officer</option>
                                @foreach($officers as $officer)
                                    <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                @endforeach
                            </select>
                    </td>
                    <td>
                            <button class="btn btn-primary btn-sm">Assign</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
