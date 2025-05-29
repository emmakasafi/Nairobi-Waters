@extends('layouts.adminlte-nosidebar')

@section('title', 'Officer Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tachometer-alt mr-2"></i> Officer Dashboard</h1>
        <div class="d-flex align-items-center">
            <div class="position-relative mr-3">
                <button id="notification-bell" class="btn btn-outline-primary" data-toggle="modal" data-target="#notificationsModal">
                    <i class="fas fa-bell"></i> Notifications
                    <span id="notification-count" class="badge badge-danger position-absolute" style="top: -10px; right: -10px; display: none;">0</span>
                </button>
            </div>
            <div class="text-right">
                <span class="text-muted">Welcome, {{ auth()->user()->name }}</span>
                <br>
                <small class="text-muted">Last updated: {{ now()->format('M d, Y - H:i') }}</small>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span>×</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span>×</span>
                </button>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-white">Total Complaints</h5>
                                <h3 class="font-weight-bold">{{ $stats['total'] }}</h3>
                            </div>
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-white">Pending</h5>
                                <h3 class="font-weight-bold">{{ $stats['pending'] }}</h3>
                            </div>
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-white">In Progress</h5>
                                <h3 class="font-weight-bold">{{ $stats['in_progress'] }}</h3>
                            </div>
                            <i class="fas fa-bolt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-white">Resolved</h5>
                                <h3 class="font-weight-bold">{{ $stats['resolved'] }}</h3>
                            </div>
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">Filter Complaints</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('officer.officer.index') }}" class="form-row align-items-end">
                    <div class="col-md-2 form-group">
                        <label class="font-weight-bold">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="font-weight-bold">Sentiment</label>
                        <select name="sentiment" class="form-control">
                            <option value="">All Sentiments</option>
                            <option value="positive" {{ request('sentiment') == 'positive' ? 'selected' : '' }}>Positive</option>
                            <option value="neutral" {{ request('sentiment') == 'neutral' ? 'selected' : '' }}>Neutral</option>
                            <option value="negative" {{ request('sentiment') == 'negative' ? 'selected' : '' }}>Negative</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="font-weight-bold">From Date</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="font-weight-bold">To Date</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>
                    <div class="col-md-2 form-group">
                        <button type="submit" class="btn btn-primary btn-block">Filter</button>
                    </div>
                    <div class="col-md-2 form-group">
                        <a href="{{ route('officer.officer.index') }}" class="btn btn-secondary btn-block">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Complaints Table -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">Your Assigned Complaints</h3>
                <div class="card-tools">
                    <span class="badge badge-light">{{ $waterSentiments->total() }} total complaints</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($waterSentiments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Complaint</th>
                                    <th>Customer</th>
                                    <th>Location</th>
                                    <th>Category</th>
                                    <th>Sentiment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($waterSentiments as $complaint)
                                    <tr>
                                        <td>
                                            <div>{{ $complaint->timestamp->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $complaint->timestamp->format('H:i A') }}</small>
                                        </td>
                                        <td>
                                            <div>{{ Str::limit($complaint->processed_caption ?? $complaint->original_caption, 60) }}</div>
                                            <span class="badge badge-secondary">{{ ucfirst($complaint->source) }}</span>
                                        </td>
                                        <td>
                                            <div>{{ $complaint->user_name ?? 'Anonymous' }}</div>
                                            @if($complaint->user_phone)
                                                <small class="text-muted">{{ $complaint->user_phone }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $complaint->subcounty }}</div>
                                            <small class="text-muted">{{ $complaint->ward }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $complaint->complaint_category }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $sentimentColors = [
                                                    'positive' => 'badge-success',
                                                    'neutral' => 'badge-secondary',
                                                    'negative' => 'badge-danger'
                                                ];
                                            @endphp
                                            <span class="badge {{ $sentimentColors[$complaint->overall_sentiment] ?? 'badge-secondary' }}">{{ ucfirst($complaint->overall_sentiment) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'badge-warning',
                                                    'in_progress' => 'badge-primary',
                                                    'resolved' => 'badge-success',
                                                    'closed' => 'badge-secondary'
                                                ];
                                            @endphp
                                            <span class="badge {{ $statusColors[$complaint->status] ?? 'badge-secondary' }}">{{ ucfirst(str_replace('_', ' ', $complaint->status)) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('officer.officer.show', $complaint->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye mr-1"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $waterSentiments->links() }}
                    </div>
                @else
                    <div class="text-center p-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h3 class="text-muted">No complaints found</h3>
                        <p class="text-muted">No complaints have been assigned to you yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notifications Modal -->
        <div class="modal fade" id="notificationsModal" tabindex="-1" role="dialog" aria-labelledby="notificationsModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="notificationsModalLabel">Notifications</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="notifications-list">
                            <div class="text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p>Loading notifications...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('officer.officer.notifications.index') }}" class="btn btn-primary">View All Notifications</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card { border-radius: 10px; }
        .card-header { border-radius: 10px 10px 0 0; }
        .badge { font-size: 0.85rem; padding: 0.5em 0.75em; }
        .notification-message { color: #2c3e50; font-weight: 500; }
        .table th, .table td { vertical-align: middle; }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-dismiss alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Fetch notification count
            function updateNotificationCount() {
                console.log('Fetching notification count...');
                $.ajax({
                    url: '{{ route("officer.officer.notifications.count") }}',
                    method: 'GET',
                    success: function(response) {
                        console.log('Notification count response:', response);
                        const count = response.unread || 0;
                        $('#notification-count').text(count);
                        $('#notification-count').toggle(count > 0);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching notification count:', status, error);
                    }
                });
            }

            // Load notifications in modal
            $('#notificationsModal').on('show.bs.modal', function() {
                console.log('Loading notifications...');
                $.ajax({
                    url: '{{ route("officer.officer.notifications.list") }}',
                    method: 'GET',
                    success: function(response) {
                        console.log('Notifications list response:', response);
                        $('#notifications-list').html(response.html || '<p>No notifications found.</p>');
                        updateNotificationCount();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading notifications:', status, error);
                        $('#notifications-list').html(`
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                <p>Failed to load notifications.</p>
                            </div>
                        `);
                    }
                });
            });

            // Initial fetch
            updateNotificationCount();

            // Poll every 30 seconds
            setInterval(updateNotificationCount, 30000);
        });
    </script>
@stop