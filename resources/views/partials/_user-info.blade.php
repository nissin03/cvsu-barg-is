@if ($user->role === 'student')
    <div class="mb-4">
        <div class="user-info-section" style="max-width: 100%; overflow-x: hidden;">
            <div class="section-header">
                <i class="fas fa-user-graduate"></i>
                <span>Student Information</span>
            </div>
            <div class="my-account__coursedept-list">
                <div class="my-account__coursedept-list-item">
                    <div class="my-account__coursedept-list__detail">
                        <div class="mb-3">
                            <p class="info-item d-flex flex-nowrap align-items-center gap-1">
                                <i class="fas fa-user"></i>
                                <strong>Full Name:</strong> {{ $user->name }}
                            </p>
                            <p class="info-item d-flex flex-nowrap align-items-center gap-1">
                                <i class="fas fa-phone"></i>
                                <strong>Phone Number:</strong> {{ $user->phone_number }}
                            </p>
                            <p class="info-item d-flex flex-nowrap align-items-center gap-1">
                                <i class="fas fa-graduation-cap"></i>
                                <strong>Year Level:</strong> {{ $user->year_level }}
                            </p>
                            <p class="info-item d-flex flex-nowrap align-items-center gap-1">
                                <i class="fas fa-building"></i>
                                <strong>Department:</strong> {{ $user->department }}
                            </p>
                            <p class="info-item d-flex flex-nowrap align-items-center gap-1">
                                <i class="fas fa-book"></i>
                                <strong>Course:</strong> {{ $user->course }}
                            </p>
                            <p class="info-item d-flex flex-nowrap align-items-center gap-1">
                                <i class="fas fa-envelope"></i>
                                <strong>Email:</strong> {{ $user->email }}
                            </p>
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
                            <p class="info-item">
                                <i class="fas fa-user"></i>
                                <strong>Full Name:</strong> {{ $user->name }}
                            </p>
                            <p class="info-item">
                                <i class="fas fa-phone"></i>
                                <strong>Phone Number:</strong> {{ $user->phone_number }}
                            </p>
                            <p class="info-item">
                                <i class="fas fa-envelope"></i>
                                <strong>Email:</strong> {{ $user->email }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
