@extends('adminlte::page')

@section('title', 'Notifications')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Notifications</h1>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Notifications</h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                @if($notifications->isEmpty())
                    <p class="text-center">No notifications.</p>
                @else
                    <ul class="list-group">
                        @foreach($notifications as $notification)
                            <li class="list-group-item {{ $notification->read_at ? '' : 'bg-light' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $notification->title }}</strong>
                                        <p>{{ $notification->message }}</p>
                                        @if($notification->type === 'status_confirmation_required' && $notification->action_required)
                                            <div class="mt-2">
                                                <form action="{{ route('customer.notifications.respond', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="response" value="confirmed">
                                                    <button type="submit" class="btn btn-sm btn-success mr-2">
                                                        <i class="fas fa-check"></i> Confirm
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal{{ $notification->id }}">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal{{ $notification->id }}" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel{{ $notification->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="rejectModalLabel{{ $notification->id }}">Reject Status Change</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="{{ route('customer.notifications.respond', $notification->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <input type="hidden" name="response" value="rejected">
                                                                <div class="form-group">
                                                                    <label for="rejection_reason">Reason for Rejection</label>
                                                                    <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" required placeholder="Please provide the reason for rejecting this status change..."></textarea>
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
                                        <small class="d-block text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div>
                                        @if(!$notification->read_at)
                                            <a href="{{ route('customer.notifications.markAsRead', $notification->id) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Mark as Read
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
@stop