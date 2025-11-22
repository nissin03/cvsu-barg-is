@if ($contacts->count() > 0)
    <div class="row g-4">
        @foreach ($contacts as $contact)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div>
                                <h5 class="card-title mb-0">{{ $contact->user?->name }}</h5>
                                <p class="text-muted mb-0">{{ $contact->user?->email }}</p>
                            </div>
                        </div>
                        <p class="card-text"><strong>Phone:</strong> {{ $contact->user?->phone_number }}</p>
                        <p class="card-text">
                            <strong>Message:</strong>
                            <span class="message-preview">{{ Str::limit($contact->message, 100) }}</span>
                            @if (strlen($contact->message) > 100)
                                <a href="#" class="text-primary view-full-message" data-bs-toggle="modal"
                                    data-bs-target="#messageModal{{ $contact->id }}">Read more...</a>
                            @endif
                        </p>
                        <p class="card-text">
                            <small class="text-muted">Received on
                                {{ $contact->created_at->format('M d, Y  h:i A') }}
                            </small>
                        </p>
                        @if ($contact->replies->count() > 0)
                            <span class="badge bg-success">
                                <i class="icon-check"></i> Replied
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="icon-clock"></i> Pending
                            </span>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <button class="btn btn-outline-primary btn-lg" data-bs-toggle="modal"
                            data-bs-target="#messageModal{{ $contact->id }}">
                            <i class="icon-eye"></i> View Details
                        </button>
                        <form action="{{ route('admin.contact.delete', ['id' => $contact->id]) }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger btn-lg delete">
                                <i class="icon-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal for full message and reply -->
            <div class="modal fade" id="messageModal{{ $contact->id }}" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" aria-labelledby="messageModalLabel{{ $contact->id }}"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="messageModalLabel{{ $contact->id }}">
                                Message from {{ $contact->user?->name }}
                            </h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Contact Details -->
                            <p><strong>Email:</strong> {{ $contact->user?->email }}</p>
                            <p><strong>Phone:</strong> {{ $contact->user?->phone_number }}</p>
                            <p><strong>Sent on:</strong> {{ $contact->created_at->format('M d, Y at H:i') }}</p>
                            <hr>
                            <p><strong>Message:</strong></p>
                            <p>{{ $contact->message }}</p>
                            <hr>

                            <!-- Show previous replies if any -->
                            @if ($contact->replies->count() > 0)
                                <div class="alert alert-success">
                                    <strong><i class="icon-check"></i> Previous Replies:</strong>
                                    @foreach ($contact->replies as $reply)
                                        <div class="mt-2 p-2 border-start border-3 border-success">
                                            <small class="text-muted">
                                                Replied by {{ $reply->admin->name }} on
                                                {{ $reply->created_at->format('M d, Y at H:i') }}
                                            </small>
                                            <p class="mb-0 mt-1">{{ $reply->admin_reply }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Admin's Reply Form -->
                            <form action="{{ route('admin.contact.reply', ['id' => $contact->id]) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="replyMessage{{ $contact->id }}" class="form-label">
                                        {{ $contact->replies->count() > 0 ? 'Send Another Reply:' : 'Your Reply:' }}
                                    </label>
                                    <textarea class="form-control" name="replyMessage" id="replyMessage{{ $contact->id }}" rows="10" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon-send"></i> Send Reply
                                </button>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info text-center py-5">
        <i class="icon-info-circle" style="font-size: 48px;"></i>
        <h5 class="mt-3">No messages found</h5>
        <p class="text-muted">Try adjusting your search or filter criteria.</p>
    </div>
@endif
