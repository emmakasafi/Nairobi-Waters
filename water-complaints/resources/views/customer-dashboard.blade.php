@extends('adminlte::page')

@section('title', 'Customer Dashboard')

@section('content_header')
    <h1>Dashboard Overview</h1>
@stop

@section('content')
    {{-- Dashboard Summary Boxes --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalComplaints }}</h3>
                    <p>Total Complaints</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $resolvedComplaints }}</h3>
                    <p>Resolved</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendingComplaints }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $assignedComplaints }}</h3>
                    <p>Assigned</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Complaints --}}
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Recent Complaints</h3>
        </div>
        <div class="card-body">
            @forelse ($waterSentiments as $waterSentiment)
                <div class="mb-4 border-bottom pb-3">
                    <p><strong>Complaint:</strong> {{ $waterSentiment->original_caption }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($waterSentiment->status) }}</p>
                    <p><strong>Assigned Officer:</strong> {{ $waterSentiment->assignedOfficer->name ?? 'N/A' }}</p>
                    <p><strong>Timestamp:</strong> {{ optional($waterSentiment->timestamp)->format('Y-m-d H:i') ?? 'N/A' }}</p>
                </div>
            @empty
                <p>No recent complaints.</p>
            @endforelse
        </div>
    </div>
@stop

@section('footer')
    <div class="text-center">
        <strong>&copy; 2025 <a href="#">Nairobi Water Complaints Analysis</a>.</strong> All rights reserved.
    </div>
@stop

@section('right-sidebar')
    {{-- Optional right sidebar content --}}
@stop

@section('js')
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm('Are you sure you want to log out?')) {
                document.getElementById('logout-form').submit();
            }
        }
    </script>
    <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
@stop
