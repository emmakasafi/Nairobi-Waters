@extends('adminlte::page')

@section('title', 'Complaint Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
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
        <a href="{{ route('officer.officer.index') }}" class="btn btn-outline-light btn-lg px-4">
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
            <div class="d-flex align-items-center">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
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

    <div class="row">
        <!-- Main Complaint Details -->
        <div class="col-lg-8">
            <!-- Complaint Information Card -->
            <div class="card modern-card gradient-primary mb-4">
                <div class="card-header">
                    <h3 class="card-title text-white font-weight-bold">
                        <i class="fas fa-file-alt mr-2"></i> Complaint Details
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Status Banner -->
                    <div class="status-banner status-{{ $waterSentiment->status }} mb-4">
                        <div class="d-flex align-items-center">
                            <div class="status-icon">
                                <i class="fas fa-{{ $waterSentiment->status === 'resolved' ? 'check-circle' : ($waterSentiment->status === 'in_progress' ? 'spinner' : 'clock') }}"></i>
                            </div>
                            <div class="status-content">
                                <h5 class="mb-1 font-weight-bold">Status: {{ ucfirst(str_replace('_', ' ', $waterSentiment->status)) }}</h5>
                                <p class="mb-0">
                                    @if($waterSentiment->status === 'resolved')
                                        This complaint has been successfully resolved.
                                    @elseif($waterSentiment->status === 'in_progress')
                                        This complaint is currently being worked on.
                                    @else
                                        This complaint is pending review.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Complaint Text -->
                    <div class="content-section mb-4">
                        <h5 class="section-title">
                            <i class="fas fa-comment-alt mr-2"></i>Complaint Description
                        </h5>
                        <div class="content-box">
                            <p class="mb-0">{{ $waterSentiment->text }}</p>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="content-section mb-4">
                        <h5 class="section-title">
                            <i class="fas fa-map-marker-alt mr-2"></i>Location Information
                        </h5>
                        <div class="content-box">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt text-danger mr-3 fa-lg"></i>
                                <div>
                                    <strong class="d-block">{{ $waterSentiment->location ?? 'Location not specified' }}</strong>
                                    @if($waterSentiment->subcounty)
                                        <small class="text-muted">{{ $waterSentiment->subcounty }} Subcounty</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Officer Notes -->
                    @if($waterSentiment->officer_notes)
                        <div class="content-section">
                            <h5 class="section-title">
                                <i class="fas fa-sticky-note mr-2"></i>Officer Notes
                            </h5>
                            <div class="content-box notes-box">
                                <p class="mb-0">{{ $waterSentiment->officer_notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Update Status Card -->
            <div class="card modern-card gradient-success">
                <div class="card-header">
                    <h3 class="card-title text-white font-weight-bold">
                        <i class="fas fa-edit mr-2"></i> Update Complaint Status
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('officer.officer.updateStatus', $waterSentiment->id) }}" id="updateComplaintForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-4">
                            <label for="status" class="form-label">Update Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control modern-select @error('status') is-invalid @enderror" required>
                                <option value="">-- Select New Status --</option>
                                <option value="pending" {{ $waterSentiment->status === 'pending' ? 'selected' : '' }}>
                                    📋 Pending Review
                                </option>
                                <option value="in_progress" {{ $waterSentiment->status === 'in_progress' ? 'selected' : '' }}>
                                    ⚡ In Progress
                                </option>
                                <option value="resolved" {{ $waterSentiment->status === 'resolved' ? 'selected' : '' }}>
                                    ✅ Resolved
                                </option>
                                <option value="closed" {{ $waterSentiment->status === 'closed' ? 'selected' : '' }}>
                                    🔒 Closed
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="notes" class="form-label">Officer Notes</label>
                            <textarea name="notes" id="notes" class="form-control modern-textarea @error('notes') is-invalid @enderror" rows="6" placeholder="Add detailed notes about actions taken, findings, or next steps...">{{ old('notes', $waterSentiment->officer_notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Provide comprehensive notes about the current status, actions taken, or planned next steps.
                            </small>
                        </div>

                        <div class="d-flex justify-content-between flex-wrap gap-3">
                            <button type="submit" class="btn btn-success btn-lg modern-btn">
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

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <!-- Quick Info Card -->
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
                    
                    @if($waterSentiment->updated_at && $waterSentiment->updated_at !== $waterSentiment->timestamp)
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

            <!-- User Information Card -->
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

            <!-- Department Information Card -->
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
        /* Root Variables */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --warning-gradient: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);
            --danger-gradient: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
            --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.37);
            --shadow-hover: 0 15px 35px rgba(31, 38, 135, 0.5);
        }

        /* Page Background */
        .content-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Modern Cards */
        .modern-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .modern-card .card-header {
            background: transparent;
            border: none;
            padding: 1.5rem 2rem;
            position: relative;
        }

        .modern-card .card-body {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 2rem;
            border-radius: 0 0 20px 20px;
        }

        /* Gradient Card Headers */
        .gradient-primary .card-header {
            background: var(--primary-gradient);
        }

        .gradient-success .card-header {
            background: var(--success-gradient);
        }

        .gradient-info .card-header {
            background: var(--info-gradient);
        }

        .gradient-secondary .card-header {
            background: var(--secondary-gradient);
        }

        .gradient-dark .card-header {
            background: var(--dark-gradient);
        }

        /* Status Banner */
        .status-banner {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            border-left: 5px solid;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .status-banner.status-resolved {
            border-left-color: #27ae60;
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.1) 0%, rgba(46, 204, 113, 0.1) 100%);
        }

        .status-banner.status-in_progress {
            border-left-color: #f39c12;
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.1) 0%, rgba(241, 196, 15, 0.1) 100%);
        }

        .status-banner.status-pending {
            border-left-color: #3498db;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(155, 89, 182, 0.1) 100%);
        }

        .status-banner.status-closed {
            border-left-color: #95a5a6;
            background: linear-gradient(135deg, rgba(149, 165, 166, 0.1) 0%, rgba(127, 140, 141, 0.1) 100%);
        }

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

        .status-icon i {
            font-size: 1.5rem;
            color: #2c3e50;
        }

        .status-content h5 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .status-content p {
            color: #7f8c8d;
            margin: 0;
        }

        /* Content Sections */
        .content-section {
            margin-bottom: 2rem;
        }

        .section-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .section-title i {
            color: #3498db;
        }

        .content-box {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            line-height: 1.6;
        }

        .notes-box {
            border-left: 4px solid #f39c12;
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.05) 0%, rgba(241, 196, 15, 0.05) 100%);
        }

        /* Form Elements */
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .modern-select,
        .modern-textarea {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .modern-select:focus,
        .modern-textarea:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1), 0 4px 15px rgba(0, 0, 0, 0.1);
            outline: none;
        }

        .modern-textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* Modern Buttons */
        .modern-btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .modern-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .modern-btn:hover::before {
            left: 100%;
        }

        .modern-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-success.modern-btn {
            background: var(--success-gradient);
        }

        .btn-outline-light.modern-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
        }

        .btn-outline-light.modern-btn:hover {
            background: rgba(255, 255, 255, 0.9);
            color: #2c3e50;
        }

        /* Info Items */
        .info-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #34495e;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
        }

        /* Modern Badges */
        .modern-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .badge-primary.modern-badge {
            background: var(--primary-gradient);
        }

        .badge-success.modern-badge {
            background: var(--success-gradient);
        }

        .badge-warning.modern-badge {
            background: var(--warning-gradient);
        }

        .badge-danger.modern-badge {
            background: var(--danger-gradient);
        }

        /* Department Info */
        .department-info {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Modern Alerts */
        .modern-alert {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border: none;
            border-radius: 15px;
            border-left: 5px solid;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }

        .alert-success.modern-alert {
            border-left-color: #27ae60;
        }

        .alert-danger.modern-alert {
            border-left-color: #e74c3c;
        }

        .alert-icon {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .alert-content {
            flex: 1;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #7f8c8d;
            cursor: pointer;
            padding: 0;
            margin-left: auto;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modern-card .card-header,
            .modern-card .card-body {
                padding: 1rem;
            }

            .status-banner {
                padding: 1rem;
            }

            .status-icon {
                width: 50px;
                height: 50px;
            }

            .status-icon i {
                font-size: 1.2rem;
            }

            .d-flex.justify-content-between.flex-wrap {
                flex-direction: column;
            }

            .d-flex.justify-content-between.flex-wrap .btn {
                margin-bottom: 1rem;
            }
        }

        /* Smooth animations */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
        }

        /* Content Header Styling */
        .content-header {
            background: transparent;
            padding: 2rem 0;
        }

        .content-header h1 {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .content-header p {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
    </style>
@stop

@section('js')

    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.modern-alert').fadeOut('slow');
            }, 5000);

            // Form validation with better UX
            $('#updateComplaintForm').on('submit', function(e) {
                var status = $('#status').val();
                var notes = $('#notes').val().trim();
                
                if ((status === 'resolved' || status === 'closed') && notes === '') {
                    e.preventDefault();
                    showCustomAlert('Officer notes are required when marking a complaint as ' + status.toUpperCase() + '.', 'warning');
                    $('#notes').focus();
                    return false;
                }
            });

            // Dynamic form requirements based on status
            $('#status').on('change', function() {
                var status = $(this).val();
                var notesField = $('#notes');
                var notesLabel = notesField.closest('.form-group').find('label');
                
                if (status === 'resolved' || status === 'closed') {
                    notesField.attr('required', true);
                    notesLabel.html('Officer Notes <span class="text-danger">*</span>');
                    notesField.addClass('border-warning');
                } else {
                    notesField.removeAttr('required');
                    notesLabel.html('Officer Notes');
                    notesField.removeClass('border-warning');
                }
                
                // Update placeholder based on status
                var placeholders = {
                    'resolved': 'Describe the resolution steps taken and final outcome...',
                    'closed': 'Provide reason for closing and any final notes...',
                    'in_progress': 'Detail current actions being taken and next steps...',
                    'pending': 'Add any initial observations or assignment notes...'
                };
                
                if (placeholders[status]) {
                    notesField.attr('placeholder', placeholders[status]);
                }
            });

            // Enhanced confirmation dialog
            $('#updateComplaintForm').on('submit', function(e) {
                var status = $('#status').val();
                if (status === 'resolved' || status === 'closed') {
                    if (!confirm('⚠️ Are you sure you want to mark this complaint as ' + status.toUpperCase() + '?\n\nThis action will update the complaint status and notify relevant parties.')) {
                        e.preventDefault();
                    }
                }
            });

            // Custom alert function
            function showCustomAlert(message, type) {
                var alertClass = 'alert-' + (type === 'warning' ? 'warning' : 'danger');
                var alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show modern-alert" role="alert">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon">
                                <i class="fas fa-${type === 'warning' ? 'exclamation-triangle' : 'times-circle'}"></i>
                            </div>
                            <div class="alert-content">
                                <strong>${type === 'warning' ? 'Warning!' : 'Error!'}</strong> ${message}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('.content').prepend(alertHtml);
                setTimeout(function() {
                    $('.modern-alert').fadeOut('slow');
                }, 5000);
            }
        });
    </script>
@stop