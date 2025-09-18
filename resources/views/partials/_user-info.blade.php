@if ($user->role === 'student')
    <div class="mb-4">
        <div class="user-info-section" style="max-width: 100%; overflow-x: hidden;">
            <div class="section-header">
                <i class="fas fa-user-graduate"></i>
                <span>User Details</span>
            </div>
            <div class="my-account__coursedept-list">
                <div class="my-account__coursedept-list-item">
                    <div class="my-account__coursedept-list__detail">
                        <div class="mb-3">
                            <div class="info-item-grid">
                                <p class="info-item d-flex flex-nowrap align-items-start gap-2">
                                    <i class="fas fa-user info-icon"></i>
                                    <span class="info-content">
                                        <strong>Full Name:</strong>
                                        <span class="info-value">{{ $user->name }}</span>
                                    </span>
                                </p>
                                <p class="info-item d-flex flex-nowrap align-items-start gap-2">
                                    <i class="fas fa-phone info-icon"></i>
                                    <span class="info-content">
                                        <strong>Phone Number:</strong>
                                        <span class="info-value">{{ $user->phone_number }}</span>
                                    </span>
                                </p>
                                <p class="info-item d-flex flex-nowrap align-items-start gap-2">
                                    <i class="fas fa-graduation-cap info-icon"></i>
                                    <span class="info-content">
                                        <strong>Year Level:</strong>
                                        <span class="info-value">{{ $user->year_level }}</span>
                                    </span>
                                </p>
                                <p class="info-item d-flex flex-nowrap align-items-start gap-2">
                                    <i class="fas fa-building info-icon"></i>
                                    <span class="info-content">
                                        <strong>Department:</strong>
                                        <span class="info-value">{{ $user->college->name }}</span>
                                    </span>
                                </p>
                                <p class="info-item d-flex flex-nowrap align-items-start gap-2">
                                    <i class="fas fa-book info-icon"></i>
                                    <span class="info-content">
                                        <strong>Course:</strong>
                                        <span class="info-value">{{ $user->course->name }}</span>
                                    </span>
                                </p>
                                <p class="info-item d-flex flex-nowrap align-items-start gap-2">
                                    <i class="fas fa-envelope info-icon"></i>
                                    <span class="info-content">
                                        <strong>Email:</strong>
                                        <span class="info-value">{{ $user->email }}</span>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@elseif($user->role === 'employee' || $user->role === 'non-employee')
    <div class="mb-4">
        <div class="user-info-section">
            <div class="section-header">
                <i class="fas fa-user-tie"></i>
                <span>{{ $user->role === 'employee' ? 'Employee' : 'Non-Employee' }} Information</span>
            </div>
            <div class="my-account__coursedept-list">
                <div class="my-account__coursedept-list-item">
                    <div class="my-account__coursedept-list__detail">
                        <div class="mb-3">
                            <div class="info-item-grid">
                                <p class="info-item d-flex flex-nowrap align-items-start gap-2">
                                    <i class="fas fa-user info-icon"></i>
                                    <span class="info-content">
                                        <strong>Full Name:</strong>
                                        <span class="info-value">{{ $user->name }}</span>
                                    </span>
                                </p>
                                <p class="info-item d-flex flex-nowrap align-items-start gap-2">
                                    <i class="fas fa-phone info-icon"></i>
                                    <span class="info-content">
                                        <strong>Phone Number:</strong>
                                        <span class="info-value">{{ $user->phone_number }}</span>
                                    </span>
                                </p>
                                <p class="info-item d-flex flex-nowrap align-items-start gap-2">
                                    <i class="fas fa-envelope info-icon"></i>
                                    <span class="info-content">
                                        <strong>Email:</strong>
                                        <span class="info-value">{{ $user->email }}</span>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
    /* User Info Responsive Styles */
    .user-info-section {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .info-item-grid {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .info-item {
        margin-bottom: 0;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.95rem;
    }

    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-icon {
        flex-shrink: 0;
        width: 18px;
        color: #0d6efd;
        margin-top: 2px;
    }

    .info-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .info-content strong {
        color: #333;
        font-size: 0.9rem;
        margin-bottom: 2px;
    }

    .info-value {
        color: #666;
        word-wrap: break-word;
        word-break: break-word;
        line-height: 1.4;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 767.98px) {
        .user-info-section {
            padding: 15px;
            margin: 0 -5px;
            border-radius: 8px;
        }

        .info-item {
            padding: 10px 0;
            font-size: 0.9rem;
        }

        .info-content {
            gap: 2px;
        }

        .info-content strong {
            font-size: 0.85rem;
        }

        .info-value {
            font-size: 0.85rem;
        }

        .info-icon {
            width: 16px;
            font-size: 0.9rem;
        }

        .section-header {
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .section-header i {
            font-size: 1rem;
        }
    }

    /* Tablet Responsive Styles */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .info-item {
            font-size: 0.92rem;
        }

        .info-content strong {
            font-size: 0.88rem;
        }

        .info-value {
            font-size: 0.88rem;
        }
    }

    /* Desktop Styles */
    @media (min-width: 992px) {
        .user-info-section {
            padding: 25px;
        }

        .info-item {
            padding: 15px 0;
        }
    }

    /* Long text handling for all devices */
    .info-value {
        overflow-wrap: break-word;
        hyphens: auto;
    }

    /* Ensure proper spacing */
    .my-account__coursedept-list {
        margin: 0;
    }

    .my-account__coursedept-list-item,
    .my-account__coursedept-list__detail {
        margin: 0;
        padding: 0;
    }
</style>
