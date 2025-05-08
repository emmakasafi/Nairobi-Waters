@extends('adminlte::page')

@section('title', 'HOD Dashboard')

@section('content_header')
    <h1 class="mb-3">Unassigned Water Sentiments</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Unassigned Sentiments --}}
    <div class="card">
        <div class="card-body p-0">
            @if($unassignedComplaints->isEmpty())
                <div class="alert alert-info m-3">No unassigned water sentiments found.</div>
            @else
                <table class="table table-hover table-bordered table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Issue</th>
                            <th>Summary</th>
                            <th>Assign to Officer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unassignedComplaints as $complaint)
                            <tr>
                                <td>{{ $complaint->id }}</td>
                                <td>{{ $complaint->user->name ?? 'Unknown' }}</td>
                                <td>{{ $complaint->entity_name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($complaint->processed_caption, 50) }}</td>
                                <td>
                                    <form action="{{ route('hod.assign', $complaint->id) }}" method="POST">
                                        @csrf
                                        <div class="input-group input-group-sm">
                                            <select name="officer_id" class="form-control" required>
                                                <option value="">Select Officer</option>
                                                @foreach($officers as $officer)
                                                    <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button class="btn btn-success" type="submit">Assign</button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Officer Workload Overview --}}
    <hr>
    <h2 class="mt-4">Officer Workload Overview</h2>

    @forelse($officers as $officer)
        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                <strong>{{ $officer->name }}</strong> ({{ $officer->email }})
            </div>
            <div class="card-body p-0">
                @if($officer->assignedSentiments->isEmpty())
                    <p class="p-3 text-muted">No assigned water sentiments.</p>
                @else
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Issue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($officer->assignedSentiments as $complaint)
                                <tr>
                                    <td>{{ $complaint->id }}</td>
                                    <td>{{ $complaint->user->name ?? 'Unknown' }}</td>
                                    <td>{{ $complaint->entity_name ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($complaint->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-info mt-3">No officers found in your department.</div>
    @endforelse
@stop
