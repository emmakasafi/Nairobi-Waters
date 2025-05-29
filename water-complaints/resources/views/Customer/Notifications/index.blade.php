@extends('adminlte::page')

@section('title', 'Notifications')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-bell mr-2"></i> Notifications</h1>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span>×</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span>×</span>
                </button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0"><i class="fas fa-bell mr-2"></i> Your Notifications</h3>
            </div>
            <div class="card-body">
                @if($notifications->isEmpty())
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle mr-2"></i> You have no active notifications.
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            @php
                                $complaint = $notification->waterSentiment;
                                $statusUpdate = $complaint ? $complaint->statusUpdates->first() : null;
                                $complaintId = isset($notification->complaint_data['water_sentiment_id']) ? $notification->complaint_data['water_sentiment_id'] : 'Unknown';
                            @endphp
                            <li class="list-group-item {{ $notification->read_at ? '' : 'bg-light font-weight-bold' }} {{ $notification->type === 'status_confirmation_required' && $notification->action_required ? 'border-left-4 border-warning' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        @if($notification->type === 'status_confirmation_required')
                                            <div class="d-flex align-items-center mb-2">
                                                <h5 class="mb-0 {{ $notification->read_at ? 'text-muted' : '' }}">
                                                    <i class="fas fa-file-alt mr-2"></i> Complaint #{{ $complaintId }}
                                                    <span class="badge bg-{{ $statusUpdate && $statusUpdate->new_status === 'resolved' ? 'success' : 'info' }}">
                                                        {{ ucfirst($statusUpdate->new_status ?? 'Pending') }}
                                                    </span>
                                                </h5>
                                                @if(!$notification->read_at)
                                                    <span class="badge badge-primary ml-2">New</span>
                                                @endif
                                            </div>
                                            <p class="mb-2"><strong>Issue:</strong> {{ $complaint->original_caption ?? 'No description available' }}</p>
                                            <p class="mb-2"><strong>Category:</strong> {{ $complaint->complaint_category ?? 'N/A' }}</p>
                                            <p class="mb-2"><strong>Location:</strong> {{ $complaint->subcounty ?? 'N/A' }}, {{ $complaint->ward ?? 'N/A' }}</p>
                                            <p class="mb-2"><strong>Submitted:</strong> {{ $complaint ? $complaint->timestamp->format('M d, Y H:i') : 'N/A' }}</p>
                                            <p class="mb-2"><strong>Message:</strong> <span class="notification-message">{{ $notification->message }}</span></p>
                                            @if($notification->action_required)
                                                <div class="mt-3">
                                                    <form action="{{ route('customer.notifications.respond', $notification->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="response" value="confirmed">
                                                        <button type="submit" class="btn btn-sm btn-success mr-2">
                                                            <i class="fas fa-check mr-1"></i> Confirm
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal{{ $notification->id }}">
                                                        <i class="fas fa-times mr-1"></i> Reject
                                                    </button>
                                                </div>
                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectModal{{ $notification->id }}" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel{{ $notification->id }}">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title" id="rejectModalLabel{{ $notification->id }}">Reject Status Change</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span>×</span>
                                                                </button>
                                                            </div>
                                                            <form action="{{ route('customer.notifications.respond', $notification->id) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="response" value="rejected">
                                                                    <div class="form-group">
                                                                        <label for="rejection_reason_{{ $notification->id }}">Reason for Rejection</label>
                                                                        <textarea name="rejection_reason" id="rejection_reason_{{ $notification->id }}" class="form-control" rows="4" required placeholder="Please provide the reason for rejecting this status change..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger">Submit Rejection</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <!-- Handle response_acknowledgement notifications -->
                                            <div class="d-flex align-items-center mb-2">
                                                <h5 class="mb-0 {{ $notification->read_at ? 'text-muted' : '' }}">
                                                    <i class="fas fa-reply mr-2"></i> {{ $notification->title }}
                                                </h5>
                                                @if(!$notification->read_at)
                                                    <span class="badge badge-primary ml-2">New</span>
                                                @endif
                                            </div>
                                            <p class="mb-2"><strong>Message:</strong> <span class="notification-message">{{ $notification->message }}</span></p>
                                            @if(isset($notification->complaint_data['water_sentiment_id']))
                                                <p class="mb-2"><strong>Related Complaint:</strong> #{{ $notification->complaint_data['water_sentiment_id'] }}</p>
                                            @endif
                                        @endif
                                        <small class="d-block text-muted mt-2">
                                            <i class="fas fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div class="ml-3">
                                        @if(!$notification->read_at)
                                            <form action="{{ route('customer.notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-check mr-1"></i> Mark as Read
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .list-group-item.border-left-4 {
            border-left: 4px solid #ffc107 !important;
        }
        .list-group-item.bg-light {
            background-color: #f8f9fa !important;
        }
        .card {
            border-radius: 10px;
        }
        .card-header {
            border-radius: 10px 10px 0 0;
        }
        .pagination .page-link {
            border-radius: 5px;
            margin: 0 2px;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        .notification-message {
            color: #2c3e50;
            font-weight: 500;
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
@stopw