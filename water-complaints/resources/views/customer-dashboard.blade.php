@extends('adminlte::page')

@section('title', 'Customer Dashboard')

@section('content_header')
    <h1>Dashboard Overview</h1>
@stop

@section('content')

    {{-- Flash Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" action="{{ route('complaints.index') }}" class="form-inline">
            <label for="status" class="mr-2">Filter by Status:</label>
            <select name="status" id="status" onchange="this.form.submit()" class="form-control">
                <option value="">-- All --</option>
                <option value="pending">Pending</option>
                <option value="resolved">Resolved</option>
                <option value="assigned">Assigned</option>
            </select>
        </form>

        <form method="GET" action="{{ route('complaints.index') }}" class="form-inline">
            <input type="text" name="query" class="form-control mr-2" placeholder="Search complaints...">
            <button type="submit" class="btn btn-info">Search</button>
        </form>

        <a href="{{ route('complaints.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Submit New Complaint
        </a>
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
                        <div class="col-md-3">
                            <input type="text" placeholder="Search Complaint" class="form-control form-control-sm" id="filter_complaint" />
                        </div>
                        <div class="col-md-3">
                            <select class="form-control form-control-sm" id="filter_status">
                                <option value="">All</option>
                                <option value="Pending">Pending</option>
                                <option value="Resolved">Resolved</option>
                                <option value="Assigned">Assigned</option>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($waterSentiments as $waterSentiment)
                            <tr>
                                <td>{{ $waterSentiment->original_caption }}</td>
                                <td>{{ ucfirst($waterSentiment->status) }}</td>
                                <td>{{ $waterSentiment->assignedOfficer->name ?? 'N/A' }}</td>
                                <td>{{ optional($waterSentiment->timestamp)->format('Y-m-d H:i') ?? 'N/A' }}</td>
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
        <strong>&copy; 2025 <a href="#">Nairobi Water Complaints Analysis</a>.</strong> All rights reserved.
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
                    // Filter each column based on input/select
                    $('#filter_complaint').on('keyup', function () {
                        table.column(0).search(this.value).draw();
                    });

                    $('#filter_status').on('change', function () {
                        table.column(1).search(this.value).draw();
                    });

                    $('#filter_officer').on('change', function () {
                        table.column(2).search(this.value).draw();
                    });

                    $('#filter_timestamp').on('change', function () {
                        table.column(3).search(this.value).draw();
                    });
                }
            });
        });
    </script>

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

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@stop