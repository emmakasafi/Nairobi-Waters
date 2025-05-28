@extends('adminlte::page')

@section('title', 'Water Sentiment Details')

@section('content_header')
    <div class="container-fluid d-flex align-items-center mb-4">
        <div>
            <h1 class="text-white mb-2 font-weight-bold">
                <i class="fas fa-file-alt mr-2"></i>
                Water Sentiment #{{ $waterSentiment->id }}
            </h1>
            <p class="text-white-50 mb-0 h5">
                <i class="fas fa-clock mr-2"></i>
                Submitted {{ $waterSentiment->timestamp->diffForHumans() }}
            </p>
        </div>
        <a href="{{ route('officer.officer.index') }}" class="btn btn-outline-light btn-lg px-4">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>
@stop

@section('content')
    @if(config('app.debugMode'))
        <div class="alert alert-info">
            <strong>Debug Info:</strong>
            <ul>
                <li>WaterSentiment ID: {{ $waterSentiment->id }}</li>
                <li>Current Status: {{ $waterSentiment->status ?? 'None' }}</li>
                <li>Status Options: {{ json_encode($statusOptions ?? []) }}</li>
                <li>Assigned To: {{ $waterSentiment->assigned_to ?? 'None' }}</li>
                <li>Auth User ID: {{ Auth::id() }}</li>
            </ul>
        </div>
    @endif

    <div id="alert-container"></div>

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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
            <div class="d-flex align-items-center">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <strong>Error!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                        <i class="fas fa-file-bold-alt mr-2"></i> Water Sentiment Details
                    </h3>
                </div>
                <div class="card-body">
                    <div class="status-banner status-{{ $waterSentiment->status }} mb-4" id="status-banner">
                        <div class="d-flex align-items-center">
                            <div class="status-icon">
                                <i class="fas fa-{{ $waterSentiment->status === 'resolved' ? 'check-circle' : ($waterSentiment->status === 'in_progress' ? 'spinner' : ($waterSentiment->status === 'closed' ? 'lock' : ($waterSentiment->status === 'pending_customer_confirmation' ? 'clock' : 'clock'))) }}" id="status-icon"></i>
                            </div>
                            <div class="status-content">
                                <h5 class="mb-1 font-weight-bold" id="status-title">Status: <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $waterSentiment->status)) }}</span></h5>
                                <p class="mb-0" id="status-message">
                                    @if($waterSentiment->status === 'resolved')
                                        This water sentiment has been successfully resolved.
                                    @elseif($waterSentiment->status === 'in_progress')
                                        This water sentiment is currently being worked on.
                                    @elseif($waterSentiment->status === 'closed')
                                        This water sentiment has been closed.
                                    @elseif($waterSentiment->status === 'pending_customer_confirmation')
                                        This water sentiment is pending customer confirmation.
                                    @else
                                        This water sentiment is pending review.
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

                    <div class="content-section" id="officer-notes-section" @if(!$waterSentiment->officer_notes) style="display: none;" @endif>
                        <h5 class="section-title">
                            <i class="fas fa-sticky-note mr-2"></i> Officer Notes
                        </h5>
                        <div class="content-box notes-box">
                            <p class="mb-0" id="officer-notes-display">{{ $waterSentiment->officer_notes ?? 'No notes provided.' }}</p>
                        </div>
                    </div>

                    @if($waterSentiment->statusUpdates?->where('status', 'rejected')->count())
                        <div class="content-section">
                            <h5 class="section-title">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Customer Rejection Reason
                            </h5>
                            <div class="content-box">
                                @foreach($waterSentiment->statusUpdates->where('status', 'rejected')->sortByDesc('created_at') as $statusUpdate)
                                    <p class="mb-2">
                                        <strong>Rejected on {{ $statusUpdate->customer_responded_at ? $statusUpdate->customer_responded_at->format('M d, Y \at g:i A') : 'N/A' }}:</strong>
                                        {{ $statusUpdate->customer_rejection_reason ?? 'No reason provided' }}
                                    </p>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card modern-card gradient-success">
                <div class="card-header">
                    <h3 class="card-title text-white font-weight-bold">
                        <i class="fas fa-edit mr-2"></i> Update Water Sentiment Status
                    </h3>
                </div>
                <div class="card-body">
                    <form id="updateStatusForm">
                        @csrf
                        <div class="form-group mb-4">
                            <label for="status" class="form-label">Update Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control modern-select @error('status') is-invalid @enderror" required>
                               @if(isset($statusOptions) && is_array($statusOptions) && !empty($statusOptions))
                              @foreach($statusOptions as $value => $label)
                              @if($value !== 'pending_customer_confirmation')
                              <option value="{{ $value }}" {{ $waterSentiment->status === $value ? 'selected class="current-status"' : '' }}>
                                {{ $label }}
                               </option>
                            @endif
                              @endforeach
                                 @else
                           <option value="pending" {{ $waterSentiment->status === 'pending' ? 'selected class="current-status"' : '' }}>📋 Pending</option>
                           <option value="in_progress" {{ $waterSentiment->status === 'in_progress' ? 'selected class="current-status"' : '' }}>⚡ In Progress</option>
                           <option value="resolved" {{ $waterSentiment->status === 'resolved' ? 'selected class="current-status"' : '' }}>✅ Resolved</option>
                          <option value="closed" {{ $waterSentiment->status === 'closed' ? 'selected class="current-status"' : '' }}>🔒 Closed</option>
                                @endif
                     </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Current status: <span id="current-status-text" class="badge badge-primary badge-lg">{{ ucfirst(str_replace('_', ' ', $waterSentiment->status ?? 'Pending')) }}</span>
                            </small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="notes" class="form-label" id="notes-label">Officer Notes</label>
                            <textarea name="notes" id="notes" class="form-control modern-textarea @error('notes') is-invalid @enderror" rows="6" placeholder="Add detailed notes about actions taken, findings, or next steps...">{{ old('notes', $waterSentiment->officer_notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span id="notes-help-text">Provide comprehensive notes about the current status, actions taken, or planned next steps.</span>
                            </small>
                        </div>

                        <div class="d-flex justify-content-between flex-wrap gap-3">
                            <button type="submit" class="btn btn-success btn-lg modern-btn" id="updateBtn">
                                <i class="fas fa-save mr-2"></i> Update Status
                            </button>
                            <a href="{{ route('officer.officer.index') }}" class="btn btn-outline-light btn-lg modern-btn">
                                <i class="fas fa-list mr-2"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
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

                    <div class="info-item" id="last-updated-section" @if(!$waterSentiment->updated_at || $waterSentiment->updated_at == $waterSentiment->timestamp) style="display: none;" @endif>
                        <div class="info-label">Last Updated</div>
                        <div class="info-value info-box">
                            <i class="fas fa-clock text-warning mr-2"></i>
                            <span id="last-updated-time">
                                @if($waterSentiment->updated_at && $waterSentiment->updated_at !== $waterSentiment->timestamp)
                                    {{ $waterSentiment->updated_at->format('M d, Y \a\t g:i A') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if($waterSentiment->user)
                <div class="card modern-card gradient-secondary mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-white font-weight-bold">
                            <i class="fas fa-user mr-2"></i> Complainant Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <div class="info-label">Name</div>
                            <div class="info-value info-box">
                                <i class="fas fa-user text-primary mr-2"></i>
                                {{ $waterSentiment->user->name }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value info-box">
                                <i class="fas fa-envelope text-primary mr-2"></i>
                                <a href="mailto:{{ $waterSentiment->user->email }}" class="text-decoration-none">
                                    {{ $waterSentiment->user->email }}
                                </a>
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

    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-3">Updating status and sending notification...</p>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="fas fa-check-circle mr-2"></i>Status Updated Successfully
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h4 class="text-success">Success!</h4>
                    </div>
                    <div id="successMessage" class="alert alert-success">
                    </div>
                    <div class="notification-info">
                        <h6 class="font-weight-bold mb-2">Notifications Sent:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-user text-primary mr-2"></i> Customer has been notified</li>
                            <li><i class="fas fa-bell text-info mr-2"></i> Status update recorded in system</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">
                        <i class="fas fa-thumbs-up mr-2"></i>Great!
                    </button>
                    <a href="{{ route('officer.officer.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
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
            transition: all 0.3s ease;
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
            transition: all 0.3s ease;
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

        .modern-select {
            background: transparent;
            border: 2px solid #3498db;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            color: #2c3e50;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            cursor: pointer;
        }

        .modern-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232c3e50' width='18px' height='18px'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.2rem;
        }

        .modern-select:focus {
            border-color: #1e90ff;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.5);
            outline: none;
        }

        .modern-select option {
            background: transparent;
            color: #2c3e50;
            font-size: 1rem;
            padding: 0.5rem;
            font-weight: 500;
        }

        .modern-select option.current-status {
            color: #2c3e50;
            font-weight: bold;
            background: transparent;
        }

        .modern-select option:hover {
            background: #e6f3ff;
        }

        .modern-textarea {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .modern-textarea:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: #3498db;
            box-shadow: 0 0 4px rgba(52, 152, 219, 0.3);
            outline: none;
        }

        .modern-btn {
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-success {
            background: var(--success-gradient);
            color: white;
        }

        .btn-outline-light {
            background: none;
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
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

        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
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
        .alert-warning { border-left-color: #ffc107; }

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

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .modern-modal .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .modern-modal .modal-header {
            border: none;
            padding: 1.5rem 2rem;
        }

        .modern-modal .modal-body {
            padding: 2rem;
        }

        .modern-modal .modal-footer {
            border-top: none;
            padding: 1rem 2rem 2rem;
        }

        .notification-info {
            background: rgba(52, 152, 219, 0.1);
            border-left: 4px solid #3498db;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .d-flex.justify-content-between.flex-wrap {
                flex-direction: column;
            }
            .d-flex.justify-content-between .btn {
                margin-bottom: 1rem;
            }
        }
    </style>
@stop

@section('js')
    <style>
        .modern-alert.alert {
            background-color: #dc3545 !important;
            color: #fff !important;
            box-shadow: none !important;
        }

        .modern-alert.alert-success {
            background-color: #28a745 !important;
            color: #fff !important;
        }

        .modern-alert.alert-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .modern-alert .alert-content {
            color: #fff !important;
        }

        .modern-alert .alert-icon i {
            color: #fff !important;
        }

        .modern-alert .btn-close {
            filter: invert(1);
        }

        .modern-alert {
            opacity: 1 !important;
            backdrop-filter: none !important;
        }
    </style>

    <script>
        $(document).ready(function() {
            var placeholders = {
                'resolved': 'Describe the resolution steps taken and final outcome. This will be sent to the customer for confirmation.',
                'closed': 'Provide reason for closing and any final notes. This will be sent to the customer for confirmation.',
                'in_progress': 'Detail current actions being taken and next steps...',
                'pending': 'Add any initial observations or assignment notes...',
                'pending_customer_confirmation': 'Add any additional notes while awaiting customer confirmation...'
            };

            var helpTexts = {
                'resolved': 'Required: Detailed notes are mandatory when marking as resolved. Customer will be asked to confirm this status.',
                'closed': 'Required: Detailed notes are mandatory when closing. Customer will be asked to confirm this status.',
                'in_progress': 'Optional: Provide updates on current progress and planned actions.',
                'pending': 'Optional: Add any initial observations or notes.',
                'pending_customer_confirmation': 'Optional: Add any additional notes while awaiting customer confirmation.'
            };

            $('#status').on('change', function() {
                var status = $(this).val();
                var notesField = $('#notes');
                var notesLabel = $('#notes-label');
                var helpText = $('#notes-help-text');

                if (status === 'resolved' || status === 'closed') {
                    notesLabel.html('Officer Notes <span class="text-danger">*</span>');
                    notesField.prop('required', true);
                    notesField.addClass('border-warning');
                } else {
                    notesLabel.text('Officer Notes');
                    notesField.prop('required', false);
                    notesField.removeClass('border-warning');
                }

                notesField.attr('placeholder', placeholders[status] || 'Add detailed notes about actions taken, findings, or next steps...');
                helpText.text(helpTexts[status] || 'Provide comprehensive notes about the current status, actions taken, or planned next steps.');
            });

            $('#status').trigger('change');

            $('#updateStatusForm').on('submit', function(e) {
                e.preventDefault();

                var status = $('#status').val();
                var notes = $('#notes').val().trim();

                if ((status === 'resolved' || status === 'closed') && !notes) {
                    showAlert('Officer notes are required when marking a water sentiment as ' + status.replace('_', ' ').toUpperCase() + '.', 'warning');
                    $('#notes').focus();
                    return;
                }

                $('#loadingOverlay').show();
                $('#updateBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Updating...');

                $.ajax({
                    url: '{{ route("officer.officer.updateStatus", ["complaint" => $waterSentiment->id]) }}',
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        status: status,
                        notes: notes
                    },
                    success: function(response) {
                        $('#loadingOverlay').hide();
                        $('#updateBtn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Update Status');

                        if (response.success) {
                            updateStatusDisplay(response.status, notes);
                            showSuccessModal(response.message, response.requires_confirmation);
                            $('.is-invalid').removeClass('is-invalid');
                        } else {
                            showAlert(response.message || 'Failed to update status.', 'danger');
                        }
                    },
                    error: function(xhr) {
    $('#loadingOverlay').hide();
    $('#updateBtn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Update Status');

    console.log('AJAX Error:', xhr); // Debug response

    if (xhr.responseJSON && xhr.responseJSON.errors) {
        let errors = xhr.responseJSON.errors;
        for (let field in errors) {
            let errorField = $('#' + field);
            errorField.addClass('is-invalid');
            errorField.next('.invalid-feedback').text(errors[field][0]);
        }
    } else {
        let message = xhr.responseJSON?.message || 'An error occurred while updating the status. Please try again.';
        showAlert(message, 'danger');
    }
}
                });
            });

            function showAlert(message, type) {
                let alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show modern-alert" role="alert">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon">
                                <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'danger' ? 'exclamation-triangle' : 'exclamation-circle')}"></i>
                            </div>
                            <div class="alert-content">
                                <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                `;
                $('#alert-container').html(alertHtml);
                if (type !== 'success') {
                    setTimeout(() => $('.alert.alert-dismissible').alert('close'), 5000);
                }
            }

            function updateStatusDisplay(status, notes) {
                $('#status-title').html(`Status: <span class="badge badge-primary">${ucfirst(status.replace('_', ' '))}</span>`);
                $('#status-message').text(getStatusMessage(status));
                $('#status-icon').removeClass().addClass(`fas fa-${getStatusIcon(status)}`);
                $('#status-banner').removeClass().addClass(`status-banner status-${status}`);
                $('#current-status-text').html(`<span class="badge badge-primary badge-lg">${ucfirst(status.replace('_', ' '))}</span>`);
                $('#officer-notes-display').text(notes || 'No notes provided.');
                $('#officer-notes-section').show(notes && notes.trim() !== '');

                $.ajax({
                    url: '{{ route("officer.officer.getStatusOptions", ["complaint" => $waterSentiment->id]) }}',
                    method: 'GET',
                    success: function(options) {
                        console.log('Reloaded Status Options:', options);
                        $('#status').empty();
                        $.each(options, function(value, label) {
                            $('#status').append(`<option value="${value}" ${value === status ? 'selected class="current-status"' : ''}>${label}</option>`);
                        });
                        $('#status').trigger('change');
                    },
                    error: function(xhr) {
                        console.error('Failed to reload status options:', xhr);
                        showAlert('Failed to reload status options. Using fallback options.', 'danger');
                        const fallbackOptions = {
                        'pending': '📋 Pending',
                        'in_progress': '⚡ In Progress',
                        'resolved': '✅ Resolved',
                        'closed': '🔒 Closed'
                       };
                        $('#status').empty();
                        $.each(fallbackOptions, function(value, label) {
                            $('#status').append(`<option value="${value}" ${value === status ? 'selected class="current-status"' : ''}>${label}</option>`);
                        });
                        $('#status').trigger('change');
                    }
                });
            }

            function showSuccessModal(message, requiresConfirmation) {
                $('#successMessage').text(message);
                $('#successModal .notification-info').toggle(requiresConfirmation);
                $('#successModal').modal('show');
            }

            function ucfirst(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }

            function getStatusMessage(status) {
                switch (status.toLowerCase()) {
                    case 'resolved': return 'This water sentiment has been successfully resolved.';
                    case 'in_progress': return 'This water sentiment is currently being worked on.';
                    case 'closed': return 'This water sentiment has been closed.';
                    case 'pending_customer_confirmation': return 'This water sentiment is pending customer confirmation.';
                    default: return 'This water sentiment is pending review.';
                }
            }

            function getStatusIcon(status) {
                switch (status.toLowerCase()) {
                    case 'resolved': return 'check-circle';
                    case 'in_progress': return 'spinner';
                    case 'closed': return 'lock';
                    case 'pending_customer_confirmation': return 'clock';
                    default: return 'clock';
                }
            }
        });
    </script>
@stop