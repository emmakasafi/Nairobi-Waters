@if($notifications->isEmpty())
    <div class="text-center py-4">
        <i class="fas fa-info-circle fa-2x text-muted"></i>
        <p>No new notifications.</p>
    </div>
@else
    <ul class="list-group list-group-flush">
        @foreach($notifications as $notification)
            @php
                $complaint = $notification->waterSentiment;
                $complaintId = isset($notification->complaint_data['water_sentiment_id']) ? $notification->complaint_data['water_sentiment_id'] : 'Unknown';
            @endphp
            <li class="list-group-item {{ $notification->read_at ? '' : 'bg-light font-weight-bold' }} {{ $notification->action_required ? 'border-left-4 border-warning' : '' }}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <h5 class="mb-0 {{ $notification->read_at ? 'text-muted' : '' }}">
                                <i class="fas fa-file-alt mr-2"></i> Complaint #{{ $complaintId }}
                                <span class="badge bg-{{ $notification->complaint_data['response'] === 'confirmed' ? 'success' : 'danger' }}">
                                    {{ ucfirst($notification->complaint_data['response']) }}
                                </span>
                            </h5>
                            @if(!$notification->read_at)
                                <span class="badge badge-primary ml-2">New</span>
                            @endif
                        </div>
                        <p class="mb-2"><strong>Message:</strong> <span class="notification-message">{{ $notification->message }}</span></p>
                        @if($notification->complaint_data['response'] === 'rejected' && isset($notification->complaint_data['rejection_reason']))
                            <p class="mb-2"><strong>Rejection Reason:</strong> {{ $notification->complaint_data['rejection_reason'] }}</p>
                        @endif
                        @if($complaint)
                            <p class="mb-2"><strong>Issue:</strong> {{ $complaint->original_caption ?? 'No description available' }}</p>
                            <p class="mb-2"><strong>Category:</strong> {{ $complaint->complaint_category ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>Location:</strong> {{ $complaint->subcounty ?? 'N/A' }}, {{ $complaint->ward ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>Current Status:</strong> {{ ucfirst($complaint->status) }}</p>
                        @endif
                        @if($notification->action_required)
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#respondModal{{ $notification->id }}">
                                    <i class="fas fa-reply mr-1"></i> Respond
                                </button>
                            </div>
                            <!-- Respond Modal -->
                            <div class="modal fade" id="respondModal{{ $notification->id }}" tabindex="-1" role="dialog" aria-labelledby="respondModalLabel{{ $notification->id }}">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="respondModalLabel{{ $notification->id }}">Respond to Customer</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span>×</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('officer.notifications.respond', $notification->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="response_notes_{{ $notification->id }}">Response Notes</label>
                                                    <textarea name="response_notes" id="response_notes_{{ $notification->id }}" class="form-control" rows="4" required placeholder="Provide your response or actions taken..."></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="proposed_status_{{ $notification->id }}">Propose New Status (Optional)</label>
                                                    <select name="proposed_status" id="proposed_status_{{ $notification->id }}" class="form-control">
                                                        <option value="">No Status Change</option>
                                                        <option value="resolved">Resolved</option>
                                                        <option value="closed">Closed</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Submit Response</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <small class="d-block text-muted mt-2">
                            <i class="fas fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <div class="ml-3">
                        @if(!$notification->read_at)
                            <form action="{{ route('officer.notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
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
@endif