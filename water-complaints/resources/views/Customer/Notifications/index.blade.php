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
                @if($notifications->isEmpty())
                    <p class="text-center">No notifications.</p>
                @else
                    <ul class="list-group">
                        @foreach($notifications as $notification)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $notification->type }}</strong>
                                        <p>{{ $notification->message }}</p>
                                        <small>{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div>
                                        <a href="{{ route('customer.notifications.markAsRead', $notification->id) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Mark as Read
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection