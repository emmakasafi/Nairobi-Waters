@extends('adminlte::page')

@section('title', 'Complaint Details')

@section('content_header')
    <h1>Complaint #{{ $complaint->id }} - {{ $complaint->title }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>Description:</strong> {{ $complaint->description }}</p>
            <p><strong>Status:</strong> {{ ucfirst($complaint->status) }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('officer.update', $complaint->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="status">Update Status</label>
            <select name="status" class="form-control" required>
                <option value="">-- Select Status --</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
            </select>
        </div>
        <div class="form-group">
            <label for="resolution_notes">Resolution Notes</label>
            <textarea name="resolution_notes" class="form-control" rows="4">{{ $complaint->resolution_notes }}</textarea>
        </div>
        <button class="btn btn-success">Update</button>
    </form>
@stop
