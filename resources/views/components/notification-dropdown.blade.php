@auth
    <div class="popup-wrap message type-header">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown"
                aria-expanded="false">
                <span class="header-item">
                    <span class="text-tiny notification-count">{{ Auth::user()->unreadNotifications()->count() }}</span>
                    <i class="fa-regular fa-bell"></i>
                </span>
            </button>

            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton2">
                <div class="dropdown-header">
                    <div class="notification-actions">
                        <button type="button" id="markAllReadBtn" class="mark-read">Mark all as read</button>
                        <button type="button" class="remove-all">Remove all</button>
                    </div>
                </div>

                <div id="notification-list">
                    @if (Auth::user()->unreadNotifications->isEmpty())
                        <div class="notification-item">
                            <div class="notification-content">
                                <p class="notification-text text-center">No notifications</p>
                            </div>
                        </div>
                    @else
                        @foreach (Auth::user()->unreadNotifications->take(5) as $notification)
                            <div class="notification-item" data-notification-id="{{ $notification->id }}">
                                <div class="badge-icon h5">
                                    <i class="{{ $notification->data['icon'] ?? 'fas fa-bell' }} text-dark"></i>
                                </div>
                                <div class="notification-content">
                                    <p class="notification-text fw-bold">
                                        {{ $notification->data['title'] ?? ($notification->data['name'] ?? 'Notification') }}
                                    </p>
                                    <p class="notification-subtext">
                                    <div class="unread-indicator"></div>
                                    {{ Str::limit($notification->data['body'] ?? ($notification->data['message'] ?? 'No message'), 30) }}
                                    </p>
                                </div>

                            </div>
                        @endforeach
                    @endif
                </div>

                <div id="all-notification-list" class="notification-list-container all-notifications"
                    style="display: none;">
                </div>
                <div class="dropdown-footer">
                    <button id="toggle-notifications" class="btn">See all notifications</button>
                </div>
            </div>
        </div>
    </div>
@endauth
