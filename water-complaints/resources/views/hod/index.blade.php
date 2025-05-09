@extends('adminlte::page')

@section('content_header')
    <div class="text-center mb-4">
        <h1 class="font-weight-bold">{{ $departmentName }} Department</h1>
        <p class="text-muted font-weight-bold">HOD Dashboard</p>
    </div>

    <h2 class="mb-3">Unassigned Water Sentiments</h2>

    @if(session('new_complaints'))
        <div class="alert alert-warning">
            You have <span class="badge badge-danger">{{ session('new_complaints') }}</span> new complaint(s) that need attention.
        </div>
    @endif
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

@section('adminlte::navbar')
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-bell"></i>
                @if($unassignedComplaints->count() > 0)
                    <span class="badge badge-warning navbar-badge">{{ $unassignedComplaints->count() }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ $unassignedComplaints->count() }} New Complaints</span>
                <div class="dropdown-divider"></div>
                @foreach($unassignedComplaints as $complaint)
                    <a href="#complaint-{{ $complaint->id }}" class="dropdown-item">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">
                                    {{ $complaint->entity_name ?? 'N/A' }}
                                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                </h3>
                                <p class="text-sm">{{ Str::limit($complaint->processed_caption, 50) }}</p>
                                <p class="text-sm text-muted">
                                <i class="far fa-clock mr-1"></i>
                                {{ $complaint->timestamp ? \Carbon\Carbon::parse($complaint->timestamp)->diffForHumans() : 'Unknown time' }}
                            </p>

                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                @endforeach
                <a href="#unassigned-complaints" class="dropdown-item dropdown-footer">See All Complaints</a>
            </div>
        </li>
    </ul>
@stop
