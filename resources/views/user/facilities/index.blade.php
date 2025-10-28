@extends('layouts.app')
@section('content')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --surface-white: #ffffff;
            --surface-light: #f8fafc;
            --surface-card: #fefefe;
            --text-primary: #1a202c;
            --text-secondary: #4a5568;
            --text-muted: #718096;
            --border-light: #e2e8f0;
            --shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-medium: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-large: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: var(--surface-light);
        }

        .facility-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            padding: 0 1rem;
        }

        .facility-card {
            background: var(--surface-card);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            border: 1px solid var(--border-light);
            backdrop-filter: blur(10px);
        }

        .facility-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-large);
            border-color: rgba(102, 126, 234, 0.2);
        }

        .facility-image {
            position: relative;
            height: 260px;
            overflow: hidden;
            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
        }

        .facility-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.95;
        }

        .facility-card:hover .facility-image img {
            transform: scale(1.08);
            opacity: 1;
        }

        .facility-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: var(--primary-gradient);
            color: white;
            padding: 10px 18px;
            border-radius: var(--radius-xl);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            box-shadow: var(--shadow-medium);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .facility-content {
            padding: 2rem;
            position: relative;
        }

        .facility-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            line-height: 1.3;
            letter-spacing: -0.025em;
        }

        .facility-description {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 1.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 400;
        }

        .facility-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: rgba(102, 126, 234, 0.04);
            border-radius: var(--radius-md);
            border: 1px solid rgba(102, 126, 234, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .detail-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--primary-gradient);
            border-radius: 0 2px 2px 0;
        }

        .detail-item:hover {
            background: rgba(102, 126, 234, 0.08);
            transform: translateX(4px);
        }

        .detail-icon {
            width: 22px;
            height: 22px;
            color: #667eea;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .detail-content {
            flex: 1;
            min-width: 0;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            letter-spacing: 0.025em;
        }

        .detail-value {
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .price-tag {
            background: var(--success-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
            margin: 0.25rem 0.5rem 0.25rem 0;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.1);
            letter-spacing: 0.025em;
        }

        .capacity-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .capacity-item {
            background: rgba(102, 126, 234, 0.1);
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-xl);
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid rgba(102, 126, 234, 0.15);
            transition: all 0.3s ease;
        }

        .capacity-item:hover {
            background: rgba(102, 126, 234, 0.15);
            transform: translateY(-1px);
        }

        .reserve-button {
            width: 100%;
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-medium);
            letter-spacing: 0.025em;
        }

        .reserve-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-large);
        }

        .reserve-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reserve-button:hover::before {
            left: 100%;
        }

        .no-facilities {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
            background: var(--surface-card);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-soft);
            margin: 2rem 1rem;
        }

        .no-facilities-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.6;
            filter: grayscale(1);
        }

        .no-facilities h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .no-facilities p {
            font-size: 1rem;
            line-height: 1.6;
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: var(--radius-md);
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 3rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/></svg>');
            background-size: 40px 40px;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(0px) translateX(0px);
            }

            100% {
                transform: translateY(-100px) translateX(100px);
            }
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
            letter-spacing: -0.025em;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            font-weight: 400;
        }

        .container-fluid {
            background: var(--surface-light);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .alert {
            border-radius: var(--radius-md);
            border: none;
            box-shadow: var(--shadow-soft);
            margin: 0 1rem 2rem;
            backdrop-filter: blur(10px);
        }

        .alert-info {
            background: rgba(79, 172, 254, 0.1);
            color: #1e40af;
            border-left: 4px solid #4facfe;
        }

        /* Pricing Collapsible Styles */
        .pricing-container {
            position: relative;
        }

        .pricing-visible {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem 0.5rem;
            align-items: center;
        }

        .pricing-hidden {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem 0.5rem;
            margin-top: 0.5rem;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, margin-top 0.3s ease-out;
        }

        .pricing-hidden.expanded {
            max-height: 200px;
            margin-top: 0.5rem;
        }

        .pricing-toggle {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 1px solid rgba(102, 126, 234, 0.2);
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-left: 0.25rem;
            white-space: nowrap;
        }

        .pricing-toggle:hover {
            background: rgba(102, 126, 234, 0.15);
            transform: translateY(-1px);
        }

        .pricing-toggle svg {
            width: 14px;
            height: 14px;
            transition: transform 0.3s ease;
        }

        .pricing-toggle.expanded svg {
            transform: rotate(180deg);
        }

        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            .facility-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 0 0.5rem;
            }

            .facility-content {
                padding: 1.5rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .page-subtitle {
                font-size: 1rem;
            }

            .facility-image {
                height: 220px;
            }

            .facility-badge {
                top: 12px;
                right: 12px;
                padding: 8px 14px;
                font-size: 10px;
            }

            .detail-item {
                padding: 0.875rem;
            }

            .capacity-list {
                gap: 0.375rem;
            }

            .capacity-item {
                padding: 0.375rem 0.75rem;
                font-size: 0.8rem;
            }

            .pricing-toggle {
                padding: 0.35rem 0.7rem;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .facility-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0 0.25rem;
            }

            .facility-content {
                padding: 1.25rem;
            }

            .facility-title {
                font-size: 1.25rem;
            }

            .facility-description {
                font-size: 0.9rem;
            }

            .no-facilities {
                padding: 3rem 1rem;
                margin: 1rem 0.5rem;
            }

            .no-facilities-icon {
                font-size: 3rem;
            }
        }

        /* Enhanced hover effects */
        .facility-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(102, 126, 234, 0.05) 50%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .facility-card:hover::after {
            opacity: 1;
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Focus states for accessibility */
        .facility-card:focus-visible {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }

        .reserve-button:focus-visible {
            outline: 2px solid white;
            outline-offset: 2px;
        }

        .pricing-toggle:focus-visible {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }
    </style>
    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <div class="container-fluid">
        <div class="container">
            @if (session('message'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($facilities->isEmpty())
                <div class="no-facilities">
                    <div class="no-facilities-icon">🏢</div>
                    <h3>No Facilities Available</h3>
                    <p>We're currently updating our facility listings. Please check back soon!</p>
                </div>
            @else
                <div class="facility-grid">
                    @foreach ($facilities as $facility)
                        <div class="facility-card"
                            onclick="window.location.href='{{ route('user.facilities.details', ['slug' => $facility->slug]) }}'"
                            tabindex="0" role="button" aria-label="View details for {{ $facility->name }}">
                            <div class="facility-image">
                                <img src="{{ asset('storage/' . $facility->image) }}" alt="{{ $facility->name }}"
                                    loading="lazy">
                                <div class="facility-badge">
                                    @if ($facility->facility_type === 'whole_place')
                                        Whole Place Reservation
                                    @elseif($facility->facility_type === 'individual')
                                        Individual Reservation
                                    @elseif($facility->facility_type === 'both')
                                        Individual or Whole Place Reservation
                                    @else
                                        {{ ucfirst($facility->facility_type) }}
                                    @endif
                                </div>
                            </div>

                            <div class="facility-content">
                                <h2 class="facility-title">{{ $facility->name }}</h2>
                                <p class="facility-description">{{ $facility->description }}</p>

                                <div class="facility-details">
                                    @if ($facility->prices->isNotEmpty())
                                        <div class="detail-item">
                                            <svg class="detail-icon" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                                </path>
                                            </svg>
                                            <div class="detail-content">
                                                <div class="detail-label">Pricing</div>
                                                <div class="detail-value">
                                                    <div class="pricing-container">
                                                        <div class="pricing-visible">
                                                            @foreach ($facility->prices->take(2) as $price)
                                                                <span class="price-tag">{{ $price->name }}:
                                                                    ₱{{ number_format($price->value, 2) }}</span>
                                                            @endforeach
                                                            @if ($facility->prices->count() > 2)
                                                                <button class="pricing-toggle"
                                                                    onclick="event.stopPropagation(); togglePricing(this)"
                                                                    aria-label="Show more pricing options">
                                                                    <span
                                                                        class="toggle-text">+{{ $facility->prices->count() - 2 }}
                                                                        more</span>
                                                                    <svg fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                        </div>
                                                        @if ($facility->prices->count() > 2)
                                                            <div class="pricing-hidden">
                                                                @foreach ($facility->prices->skip(2) as $price)
                                                                    <span class="price-tag">{{ $price->name }}:
                                                                        ₱{{ number_format($price->value, 2) }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->whole_capacity)
                                        <div class="detail-item">
                                            <svg class="detail-icon" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            <div class="detail-content">
                                                <div class="detail-label">Total Capacity</div>
                                                <div class="detail-value">
                                                    {{ $facility->facilityAttributes->first()->whole_capacity }} People
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (in_array($facility->facility_type, ['individual', 'both']))
                                        @php
                                            $roomNumbers = $facility->facilityAttributes
                                                ->pluck('room_name')
                                                ->filter()
                                                ->map(function ($name) {
                                                    return preg_replace('/[^0-9]/', '', $name);
                                                })
                                                ->sort()
                                                ->values();

                                            $roomDetails = $facility->facilityAttributes
                                                ->filter(
                                                    fn($attribute) => $attribute->room_name && $attribute->capacity,
                                                )
                                                ->map(
                                                    fn($attribute) => [
                                                        'room_number' => preg_replace(
                                                            '/[^0-9]/',
                                                            '',
                                                            $attribute->room_name,
                                                        ),
                                                        'capacity' => $attribute->capacity,
                                                    ],
                                                )
                                                ->sortBy('room_number')
                                                ->values();

                                            $groupedRooms = $roomDetails->groupBy('capacity');
                                        @endphp

                                        @if ($roomNumbers->isNotEmpty())
                                            <div class="detail-item">
                                                <svg class="detail-icon" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                    </path>
                                                </svg>
                                                <div class="detail-content">
                                                    <div class="detail-label">Room Range</div>
                                                    <div class="detail-value">
                                                        Room
                                                        {{ $roomNumbers->first() }}{{ $roomNumbers->first() != $roomNumbers->last() ? ' - ' . $roomNumbers->last() : '' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($groupedRooms->isNotEmpty())
                                            <div class="detail-item">
                                                <svg class="detail-icon" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                    </path>
                                                </svg>
                                                <div class="detail-content">
                                                    <div class="detail-label">Room Capacities</div>
                                                    <div class="capacity-list">
                                                        @foreach ($groupedRooms as $capacity => $rooms)
                                                            @php
                                                                $roomNumbers = $rooms
                                                                    ->pluck('room_number')
                                                                    ->map(fn($num) => "R{$num}");
                                                                $range =
                                                                    $roomNumbers->count() > 1
                                                                        ? $roomNumbers->first() .
                                                                            '-' .
                                                                            $roomNumbers->last()
                                                                        : $roomNumbers->first();
                                                            @endphp
                                                            <span class="capacity-item">{{ $range }}:
                                                                {{ $capacity }} People</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // @if (session('success'))
        //     Swal.fire({
        //         icon: 'success',
        //         title: '{{ session('title', 'Success!') }}',
        //         text: "{{ session('success') }}",
        //         @if (session('showConfirmButton', true))
        //             showConfirmButton: true,
        //         @else
        //             showConfirmButton: false,
        //             timer: 3000,
        //         @endif
        //         position: 'center'
        //     });
        // @endif

        // Toast Notif for checking
        @if (session('success'))
            Swal.fire({
                toast: true,
                icon: 'success',
                title: '{{ session('title', 'Success!') }}',
                text: "{{ session('success') }}",
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#f8f9fa',
                iconColor: '#28a745',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        @endif

        @if (session('profile_completed'))
            Swal.fire({
                toast: true,
                icon: 'success',
                title: 'Profile Completed!',
                html: "{{ session('profile_completed') }}",
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        @endif

        // @if (session('error'))
        //     Swal.fire({
        //         icon: 'error',
        //         title: 'Oops...',
        //         text: "{{ session('error') }}",
        //         showConfirmButton: false,
        //         timer: 3000,
        //         toast: true,
        //         position: 'top-end'
        //     });
        // @endif

        function togglePricing(button) {
            const hiddenPricing = button.closest('.pricing-container').querySelector('.pricing-hidden');
            const toggleText = button.querySelector('.toggle-text');
            const isExpanded = hiddenPricing.classList.contains('expanded');

            if (isExpanded) {
                hiddenPricing.classList.remove('expanded');
                button.classList.remove('expanded');
                const totalCount = parseInt(button.getAttribute('data-total-count')) || 0;
                const visibleCount = 2;
                toggleText.textContent = `+${totalCount - visibleCount} more`;
                button.setAttribute('aria-label', 'Show more pricing options');
            } else {
                hiddenPricing.classList.add('expanded');
                button.classList.add('expanded');
                toggleText.textContent = 'Show less';
                button.setAttribute('aria-label', 'Show fewer pricing options');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.facility-image img');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                });
            });

            const facilityCards = document.querySelectorAll('.facility-card');
            facilityCards.forEach(card => {
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            const pricingToggles = document.querySelectorAll('.pricing-toggle');
            pricingToggles.forEach(toggle => {
                const container = toggle.closest('.pricing-container');
                const allPriceTags = container.querySelectorAll('.price-tag');
                toggle.setAttribute('data-total-count', allPriceTags.length);
            });

            // pricingToggles.forEach(toggle => {
            //     toggle.addEventListener('keydown', function(e) {
            //         if (e.key === 'Enter' || e.key === ' ') {
            //             e.preventDefault();
            //             e.stopPropagation();
            //             togglePricing(this);
            //         }
            //     });
            // });

        });
    </script>
@endpush
