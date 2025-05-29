@extends('adminlte::page')

@section('title', 'Complaint Details')

@section('content_header')
    <div class="container-fluid d-flex align-items-center mb-4">
        <div>
            <h1 class="text-white mb-2 font-weight-bold">
                <i class="fas fa-file-alt mr-2"></i>
                Complaint #{{ $waterSentiment->id }}
            </h1>
            <p class="text-white-50 mb-0 h5">
                <i class="fas fa-clock mr-2"></i>
                Submitted {{ $waterSentiment->timestamp->diffForHumans() }}
            </p>
        </div>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-light btn-lg px-4">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show modern-alert" role="alert">
            <div class="d-flex align-items-center">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    <strong>Success!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
            <div class="d-flex align-items-center">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <strong>Error!</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="container-fluid row">
        <div class="col-md-8 col-lg-8">
            <div class="card modern-card gradient-primary mb-4">
                <div class="card-header">
                    <h3 class="header-title card-title text-white font-weight-bold">
                        <i class="fas fa-file-alt mr-2"></i> Complaint Details
                    </h3>
                </div>
                <div class="card-body">
                    <div class="status-banner status-{{ $waterSentiment->status }} mb-4">
                        <div class="d-flex align-items-center">
                            <div class="status-icon">
                                <i class="fas fa-{{ $waterSentiment->status === 'resolved' ? 'check-circle' : ($waterSentiment->status === 'in_progress' ? 'spinner' : ($waterSentiment->status === 'closed' ? 'lock' : ($waterSentiment->status === 'pending_customer_confirmation' ? 'clock' : 'clock'))) }}"></i>
                            </div>
                            <div class="status-content">
                                <h5 class="mb-1 font-weight-bold">Status: <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $waterSentiment->status)) }}</span></h5>
                                <p class="mb-0">
                                    @if($waterSentiment->status === 'resolved')
                                        Your complaint has been successfully resolved.
                                    @elseif($waterSentiment->status === 'in_progress')
                                        Your complaint is currently being worked on.
                                    @elseif($waterSentiment->status === 'closed')
                                        Your complaint has been closed.
                                    @elseif($waterSentiment->status === 'pending_customer_confirmation')
                                        Your complaint is pending your confirmation. Please check your notifications.
                                    @else
                                        Your complaint is pending review.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="content-section mb-4">
                        <h5 class="section-title">
                            <i class="fas fa-comment-alt mr-2"></i> Description
                        </h5>
                        <div class="content-box">
                            <p class="mb-0">{{ $waterSentiment->original_caption }}</p>
                        </div>
                    </div>

                    <div class="content-section mb-4">
                        <h5 class="section-title">
                            <i class="fas fa-map-marker-alt mr-2"></i> Location Information
                        </h5>
                        <div class="content-box">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt text-danger mr-3 fa-lg"></i>
                                <div>
                                    <strong class="d-block">{{ $waterSentiment->subcounty ?? 'Subcounty not specified' }}</strong>
                                    @if($waterSentiment->ward)
                                        <small class="text-muted">
                                            {{ $waterSentiment->ward }} Ward
                                            @if($waterSentiment->entity_type)
                                                | {{ $waterSentiment->entity_type }}
                                            @endif
                                            @if($waterSentiment->entity_name)
                                                | {{ $waterSentiment->entity_name }}
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($waterSentiment->officer_notes)
                        <div class="content-section mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-sticky-note mr-2"></i> Officer Notes
                            </h5>
                            <div class="content-box notes-box">
                                <p class="mb-0">{{ $waterSentiment->officer_notes }}</p>
                            </div>
                        </div>
                    @endif

                    @if($waterSentiment->statusUpdates?->count())
                        <div class="content-section">
                            <h5 class="section-title">
                                <i class="fas fa-history mr-2"></i> Status History
                            </h5>
                            <div class="content-box">
                                @foreach($waterSentiment->statusUpdates->sortByDesc('created_at') as $statusUpdate)
                                    <p class="mb-2">
                                        <strong>{{ ucfirst($statusUpdate->status) }} on {{ $statusUpdate->created_at->format('M d, Y \at g:i A') }}:</strong>
                                        @if($statusUpdate->status === 'rejected')
                                            Rejected: {{ $statusUpdate->customer_rejection_reason ?? 'No reason provided' }}
                                        @else
                                            New Status: {{ ucfirst(str_replace('_', ' ', $statusUpdate->new_status)) }}
                                            @if($statusUpdate->officer_notes)
                                                <br>Notes: {{ $statusUpdate->officer_notes }}
                                            @endif
                                        @endif
                                    </p>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card modern-card gradient-info mb-4">
                <div class="card-header">
                    <h3 class="card-title text-white font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i> Quick Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Category</div>
                        <div class="info-value">
                            <span class="badge badge-primary modern-badge">
                                <i class="fas fa-tag mr-1"></i>
                                {{ ucfirst($waterSentiment->complaint_category ?? 'General') }}
                            </span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Sentiment Analysis</div>
                        <div class="info-value">
                            <span class="badge badge-{{ $waterSentiment->overall_sentiment === 'positive' ? 'success' : ($waterSentiment->overall_sentiment === 'neutral' ? 'warning' : 'danger') }} modern-badge">
                                <i class="fas fa-{{ $waterSentiment->overall_sentiment === 'positive' ? 'smile' : ($waterSentiment->overall_sentiment === 'neutral' ? 'meh' : 'frown') }} mr-1"></i>
                                {{ ucfirst($waterSentiment->overall_sentiment) }}
                            </span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Submitted Date</div>
                        <div class="info-value info-box">
                            <i class="fas fa-calendar-alt text-primary mr-2"></i>
                            {{ $waterSentiment->timestamp->format('M d, Y \a\t g:i A') }}
                        </div>
                    </div>

                    @if($waterSentiment->updated_at && $waterSentiment->updated_at != $waterSentiment->timestamp)
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value info-box">
                                <i class="fas fa-clock text-warning mr-2"></i>
                                {{ $waterSentiment->updated_at->format('M d, Y \a\t g:i A') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($waterSentiment->assignedOfficer)
                <div class="card modern-card gradient-secondary mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-white font-weight-bold">
                            <i class="fas fa-user-shield mr-2"></i> Assigned Officer
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <div class="info-label">Name</div>
                            <div class="info-value info-box">
                                <i class="fas fa-user text-primary mr-2"></i>
                                {{ $waterSentiment->assignedOfficer->name }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($waterSentiment->department)
                <div class="card modern-card gradient-dark">
                    <div class="card-header">
                        <h3 class="card-title text-white font-weight-bold">
                            <i class="fas fa-building mr-2"></i> Department
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="department-info">
                            <i class="fas fa-building fa-3x text-white mb-3"></i>
                            <h5 class="text-white font-weight-bold mb-0">{{ $waterSentiment->department->name }}</h5>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            --secondary-gradient: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
            --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.37);
        }

        .content-wrapper {
            background: var(--primary-gradient);
            min-height: 100vh;
        }

        .modern-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            transition: all 0.4s ease;
        }

        .modern-card .card-header {
            background: transparent;
            border: none;
            padding: 1.5rem 2rem;
        }

        .modern-card .card-body {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 2rem;
            border-radius: 0 0 20px 20px;
        }

        .gradient-primary .card-header { background: var(--primary-gradient); }
        .gradient-success .card-header { background: var(--success-gradient); }
        .gradient-info .card-header { background: var(--info-gradient); }
        .gradient-secondary .card-header { background: var(--secondary-gradient); }
        .gradient-dark .card-header { background: var(--dark-gradient); }

        .status-banner {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            border-left: 5px solid;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .status-banner.status-resolved { border-left-color: #27ae60; }
        .status-banner.status-in_progress { border-left-color: #f39c12; }
        .status-banner.status-pending { border-left-color: #3498db; }
        .status-banner.status-closed { border-left-color: #95a5a6; }
        .status-banner.status-pending_customer_confirmation { border-left-color: #e67e22; }

        .status-icon {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .content-section { margin-bottom: 2rem; }
        .section-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .content-box {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .info-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 12px;
            text-transform: uppercase;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 4px;
            padding: 0.75rem;
            display: flex;
            align-items: center;
        }

        .modern-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .modern-alert {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 12px;
            border-left: 5px solid;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .alert-success { border-left-color: #28a745; }
        .alert-danger { border-left-color: #dc3545; }

        .alert-icon {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
@stop