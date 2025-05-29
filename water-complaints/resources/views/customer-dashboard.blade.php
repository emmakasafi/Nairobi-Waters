@extends('adminlte::page')

@section('title', 'Customer Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tachometer-alt mr-2"></i> Dashboard Overview</h1>
        <div class="notification-bell" id="notificationBell">
            <a href="{{ route('customer.notifications.index') }}" class="text-decoration-none">
                <i class="fas fa-bell"></i>
                <span class="badge badge-danger notification-count" id="notificationCount" style="display: none;">0</span>
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Pending Confirmations Alert --}}
    @if(isset($pendingConfirmations) && $pendingConfirmations > 0)
        <div class="alert alert-warning alert-dismissible fade show">
            <h5><i class="fas fa-exclamation-triangle mr-2"></i> Action Required!</h5>
            You have {{ $pendingConfirmations }} complaint(s) awaiting your confirmation.
            <a href="{{ route('customer.notifications.index') }}" class="alert-link">View Notifications</a>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <form method="GET" action="{{ route('complaints.index') }}" class="form-inline mb-2">
            <label for="status" class="mr-2">Filter by Status:</label>
            <select name="status" id="status" onchange="this.form.submit()" class="form-control">
                <option value="">-- All --</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
            </select>
        </form>

        <form method="GET" action="{{ route('complaints.index') }}" class="form-inline mb-2">
            <input type="text" name="query" class="form-control mr-2" placeholder="Search complaints..." value="{{ request('query') }}">
            <button type="submit" class="btn btn-info">Search</button>
        </form>

        <div class="btn-group mb-2">
            <a href="{{ route('customer.notifications.index') }}" class="btn btn-warning">
                <i class="fas fa-bell mr-1"></i> Notifications
                @if(isset($pendingConfirmations) && $pendingConfirmations > 0)
                    <span class="badge badge-light">{{ $pendingConfirmations }}</span>
                @endif
            </a>
            <a href="{{ route('complaints.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-1"></i> Submit New Complaint
            </a>
        </div>
    </div>

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
            <a href="{{ route('complaints.index', ['status' => 'assigned']) }}" style="text-decoration: none; color: inherit;">
                <div class="small-box bg-purple" style="cursor: pointer;">
                    <div class="inner">
                        <h3>{{ $assignedComplaints }}</h3>
                        <p>Assigned</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <input type="text" placeholder="Search Complaint" class="form-control form-control-sm" id="filter_complaint" />
                        </div>
                        <div class="col-md-3 mb-2">
                            <select class="form-control form-control-sm" id="filter_status">
                                <option value="">All</option>
                                <option value="Pending">Pending</option>
                                <option value="Resolved">Resolved</option>
                                <option value="Assigned">Assigned</option>
                                <option value="Awaiting Confirmation">Awaiting Confirmation</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Complaints with DataTable --}}
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Recent Complaints</h3>
        </div>
        <div class="card-body">
            @if($waterSentiments->count())
                <table id="complaintsTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Complaint</th>
                            <th>Status</th>
                            <th>Assigned Officer</th>
                            <th>Timestamp</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($waterSentiments as $waterSentiment)
                            <tr class="{{ $waterSentiment->awaiting_confirmation ? 'table-warning' : '' }}">
                                <td>{{ Str::limit($waterSentiment->original_caption, 50) }}</td>
                                <td>
                                    @if($waterSentiment->awaiting_confirmation)
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock mr-1"></i> Awaiting Confirmation
                                        </span>
                                        <small class="d-block text-muted">
                                            Pending: {{ ucfirst(str_replace('_', ' ', $waterSentiment->pending_status)) }}
                                        </small>
                                    @else
                                        <span class="badge badge-{{ $waterSentiment->status === 'resolved' ? 'success' : ($waterSentiment->status === 'pending' ? 'warning' : 'info') }}">
                                            {{ ucfirst($waterSentiment->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $waterSentiment->assignedOfficer->name ?? 'N/A' }}</td>
                                <td>{{ optional($waterSentiment->timestamp)->format('Y-m-d H:i') ?? 'N/A' }}</td>
                                <td>
                                    @if($waterSentiment->awaiting_confirmation)
                                        <a href="{{ route('customer.notifications.index') }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-bell mr-1"></i> Confirm
                                        </a>
                                    @else
                                        <a href="{{ route('complaints.index', $waterSentiment->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No recent complaints.</p>
            @endif
        </div>
    </div>
@stop

@section('footer')
    <div class="text-center">
        <strong>© 2025 <a href="#">Nairobi Water Complaints Analysis</a>.</strong> All rights reserved.
    </div>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            let table = $('#complaintsTable').DataTable({
                responsive: true,
                pageLength: 10,
                ordering: true,
                initComplete: function () {
                    $('#filter_complaint').on('keyup', function () {
                        table.column(0).search(this.value).draw();
                    });

                    $('#filter_status').on('change', function () {
                        table.column(1).search(this.value).draw();
                    });
                }
            });

            // Update notification count
            function updateNotificationCount() {
                $('#notificationCount').html('<i class="fas fa-spinner fa-spin"></i>').show();
                $.ajax({
                    url: '{{ route("customer.notifications.count") }}',
                    method: 'GET',
                    success: function(data) {
                        const totalCount = data.pending_confirmations + data.unread;
                        $('#notificationCount').text(totalCount > 0 ? totalCount : '').toggle(totalCount > 0);
                        $('#notificationBell').toggleClass('has-notifications', totalCount > 0);
                        console.log('Notification count updated:', data);
                    },
                    error: function(xhr) {
                        console.error('Failed to fetch notification count:', xhr);
                        $('#notificationCount').text('!').show();
                        $('#notificationBell').addClass('has-notifications');
                    }
                });
            }

            // Initial fetch and periodic update
            updateNotificationCount();
            setInterval(updateNotificationCount, 30000);

            // Auto-dismiss alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            function confirmLogout(event) {
                event.preventDefault();
                if (confirm('Are you sure you want to log out?')) {
                    document.getElementById('logout-form').submit();
                }
            }
        });
    </script>

    <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .notification-bell {
            position: relative;
            cursor: pointer;
            font-size: 1.5rem;
            color: #6c757d;
            transition: color 0.3s ease;
        }

        .notification-bell.has-notifications {
            color: #ffc107;
            animation: ring 2s infinite;
        }

        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.75rem;
            min-width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            border-radius: 50%;
        }

        @keyframes ring {
            0%, 20%, 50%, 80%, 100% { transform: rotate(0deg); }
            10% { transform: rotate(10deg); }
            30% { transform: rotate(-10deg); }
            60% { transform: rotate(10deg); }
            90% { transform: rotate(-10deg); }
        }

        .table-warning {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .action-buttons .btn {
            margin-left: 5px;
        }

        @media (max-width: 768px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start;
            }
            .form-inline, .btn-group {
                width: 100%;
                margin-bottom: 10px;
            }
            .form-inline .form-control, .btn-group .btn {
                width: 100%;
            }
        }
    </style>
@stop