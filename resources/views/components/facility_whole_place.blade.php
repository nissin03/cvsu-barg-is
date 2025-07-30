{{-- For Calendar styles --}}
<style>
    .fc .fc-day.fully-booked-date,
    .fc .fc-daygrid-day.fully-booked-date,
    .fc-daygrid-day-frame.fully-booked-date {
        background: linear-gradient(135deg, #ff6b6b, #ee5a5a) !important;
        color: white !important;
        border: 2px solid #ff5252 !important;
        position: relative !important;
        opacity: 0.85 !important;
        transition: all 0.3s ease !important;
    }
    
    .fc .fc-day.fully-booked-date .fc-daygrid-day-number,
    .fc .fc-daygrid-day.fully-booked-date .fc-daygrid-day-number {
        color: white !important;
        font-weight: bold !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;
    }
    
    .fc .fc-day.fully-booked-date:hover,
    .fc .fc-daygrid-day.fully-booked-date:hover {
        background: linear-gradient(135deg, #ff5252, #e53935) !important;
        cursor: not-allowed !important;
        opacity: 1 !important;
        transform: scale(0.98) !important;
        box-shadow: 0 4px 8px rgba(255, 82, 82, 0.3) !important;
    }
    
    .fc .fc-day.fully-booked-date::before {
        content: "🚫" !important;
        position: absolute !important;
        top: 2px !important;
        right: 2px !important;
        font-size: 12px !important;
        z-index: 15 !important;
        filter: drop-shadow(1px 1px 1px rgba(0,0,0,0.3)) !important;
    }
    
    .fc .fc-day.fully-booked-date::after,
    .fc .fc-daygrid-day.fully-booked-date::after,
    .fc-daygrid-day-frame.fully-booked-date::after {
        content: "booked" !important;
        position: absolute !important;
        bottom: -10px !important;
        left: 50% !important;
        transform: translateX(-70%) !important;
        font-size: 9px !important;
        color: white !important;
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        padding: 2px 6px !important;
        border-radius: 10px !important;
        pointer-events: none !important;
        white-space: nowrap !important;
        text-transform: lowercase !important;
        font-weight: 500 !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
        z-index: 10 !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
    }
    
    .fc .fc-day.selected-start-date:not(.fully-booked-date),
    .fc .fc-daygrid-day.selected-start-date:not(.fully-booked-date) {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb) !important;
        border: 2px solid #1976d2 !important;
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3) !important;
        transition: all 0.3s ease !important;
    }
    
    .fc .fc-day.selected-end-date:not(.fully-booked-date),
    .fc .fc-daygrid-day.selected-end-date:not(.fully-booked-date) {
        background: linear-gradient(135deg, #e8f5e8, #c8e6c9) !important;
        border: 2px solid #388e3c !important;
        box-shadow: 0 2px 8px rgba(56, 142, 60, 0.3) !important;
        transition: all 0.3s ease !important;
    }
    
    .fc .fc-day.selected-range-date:not(.fully-booked-date),
    .fc .fc-daygrid-day.selected-range-date:not(.fully-booked-date) {
        background: linear-gradient(135deg, #f3e5f5, #e1bee7) !important;
        border: 1px solid #7b1fa2 !important;
        box-shadow: 0 1px 4px rgba(123, 31, 162, 0.2) !important;
        transition: all 0.3s ease !important;
    }
    
    .fc-event.fully-booked-event {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        color: white !important;
        font-size: 9px !important;
        text-transform: lowercase !important;
        font-weight: 500 !important;
        padding: 2px 6px !important;
        text-align: center !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        transition: all 0.3s ease !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
        pointer-events: none !important;
        white-space: nowrap !important;
    }
    
    .fc-event.fully-booked-event:hover {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.4) !important;
    }
    
    .fc-event.fully-booked-event .fc-event-title {
        font-size: 9px !important;
        text-transform: lowercase !important;
        font-weight: 500 !important;
        line-height: 1 !important;
        letter-spacing: 0.2px !important;
        color: white !important;
    }
    
    .fc .fc-daygrid-event.fully-booked-event {
        font-size: 9px !important;
        text-transform: lowercase !important;
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        color: white !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        font-weight: 500 !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
    }
    
    .fc .fc-daygrid-event.fully-booked-event .fc-event-title,
    .fc .fc-daygrid-event.fully-booked-event .fc-event-main,
    .fc .fc-daygrid-event.fully-booked-event .fc-event-main-frame {
        font-size: 9px !important;
        text-transform: lowercase !important;
        color: white !important;
        font-weight: 500 !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
    }
    
    .fc {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }
    
    .fc .fc-daygrid-day {
        transition: all 0.2s ease !important;
    }
    
    .fc .fc-daygrid-day:hover:not(.fully-booked-date) {
        background-color: #f8f9fa !important;
        cursor: pointer !important;
    }
    
    @keyframes pulseReserved {
        0% { opacity: 0.85; }
        50% { opacity: 1; }
        100% { opacity: 0.85; }
    }
    
    .fc .fc-day.fully-booked-date {
        animation: pulseReserved 3s infinite ease-in-out !important;
        position: relative !important;
    }
    
    .fc .fc-day.fully-booked-date:hover::before {
        content: "This date is fully booked" !important;
        position: absolute !important;
        top: -35px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        background: rgba(0, 0, 0, 0.9) !important;
        color: white !important;
        padding: 6px 10px !important;
        border-radius: 6px !important;
        font-size: 11px !important;
        white-space: nowrap !important;
        z-index: 1000 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
        font-weight: normal !important;
        letter-spacing: 0.3px !important;
    }
    
    .fc .fc-day.fully-booked-date:hover::after {
        content: "" !important;
        position: absolute !important;
        top: -8px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        width: 0 !important;
        height: 0 !important;
        border-left: 6px solid transparent !important;
        border-right: 6px solid transparent !important;
        border-top: 6px solid rgba(0, 0, 0, 0.9) !important;
        z-index: 1000 !important;
    }
    
    .fc-day-status {
        position: absolute !important;
        bottom: -10px !important;
        left: 50% !important;
        transform: translateX(-70%) !important;
        font-size: 9px !important;
        color: white !important;
        padding: 2px 6px !important;
        border-radius: 10px !important;
        pointer-events: none !important;
        white-space: nowrap !important;
        text-transform: lowercase !important;
        font-weight: 500 !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
        z-index: 10 !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
    }
    
    .fc-status-booked {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        color: white !important;
    }

</style>
    
<div class="facility-booking-container mb-4">
  <input type="hidden" name="facility_attribute_id" value="{{ $wholeAttr?->id ?? '' }}">
    @if ($facility->prices->isNotEmpty())
        @foreach ($facility->prices as $price)
        @endforeach
    @else
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i>
            <div>No prices available for this facility.</div>
        </div>
    @endif
    
    <div class="capacity-info">
        <div class="capacity-card">
            <i class="fa fa-users"></i>
            <span class="capacity-text">Capacity:</span>
            <span class="capacity-value">{{ $wholeAttr->whole_capacity }}</span>
        </div>
    </div>
    
    @php
        $hasDayBasedPricing = $facility->prices->contains('is_based_on_days', true);
    @endphp
    
    @if ($hasDayBasedPricing)
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-calendar-alt"></i>
                <span>Date Scheduled:</span>
            </div>
            <div class="section-content">
                @php
                    $firstPrice = $facility->prices->where('is_based_on_days', true)->first();
                @endphp
                
                @if ($firstPrice && $firstPrice->date_from && $firstPrice->date_to)
                    <div class="date-range-display">
                        <div class="date-item">
                            <strong>From:</strong> {{ \Carbon\Carbon::parse($firstPrice->date_from)->format('M d, Y') }}
                        </div>
                        <div class="date-item">
                            <strong>To:</strong> {{ \Carbon\Carbon::parse($firstPrice->date_to)->format('M d, Y') }}
                        </div>
                    </div>
                    <input type="hidden" id="date_from" name="date_from" value="{{ $firstPrice->date_from }}">
                    <input type="hidden" id="date_to" name="date_to" value="{{ $firstPrice->date_to }}">
                @endif
            </div>
        </div>
    @else
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-calendar-alt"></i>
                <span>Select Dates</span>
            </div>
            <div class="section-content">
                <input type="hidden" id="date_from" name="date_from" required>
                <input type="hidden" id="date_to" name="date_to" required>
                
                <div class="selected-dates-display">
                    <div class="date-selection-item">
                        <strong>Start Date:</strong> 
                        <span id="start-date-display"></span>
                    </div>
                    <div class="date-selection-item">
                        <strong>End Date:</strong> 
                        <span id="end-date-display"></span>
                    </div>
                </div>
                
                <div id="error-message" class="error-message"></div>
                
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#calendarModal">
                    <i class="fa fa-calendar me-2"></i> Open Calendar
                </button>
            </div>
        </div>
        
        <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="calendarModalLabel">Select Dates</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-8">
                                    <div id="calendar" style="max-width: 100%; margin: 0 auto;"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="selected-dates-card p-3">
                                        <h6 class="fw-bold mb-3">Selected Dates</h6>
                                        <div class="mb-2">
                                            <small class="text-muted">Start Date:</small>
                                            <div id="modal-start-date" class="fw-bold">Not selected</div>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted">End Date:</small>
                                            <div id="modal-end-date" class="fw-bold">Not selected</div>
                                        </div>
                                        <div class="d-grid gap-2">
                                           <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Confirm" id="confirm-dates">
                                                    Confirm Selection
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <div class="booking-section">
        <div class="section-header">
            <i class="fa fa-clock"></i>
            <span>Time Selection</span>
        </div>
        <div class="section-content">
            <div id="time-slot-container" class="time-slot-grid">
                <div class="time-input-group">
                    <label for="time_start" class="time-label">Start Time</label>
                    <input type="time" id="time_start" name="time_start" class="time-input">
                </div>
                <div class="time-input-group">
                    <label for="time_end" class="time-label">End Time <small>(Max 8 hours)</small></label>
                    <input type="time" id="time_end" name="time_end" class="time-input" readonly>
                </div>
            </div>
        </div>
    </div>
    
    <div class="booking-section">
        <div class="section-header">
            <i class="fa fa-user-tag"></i>
            <span><strong>Client Type:</strong></span>
        </div>
        <div class="section-content">
            <select id="client_type" class="client-type-select">
                <option value="" disabled selected>Select a client type</option>
                @foreach ($facility->prices as $price)
                    <option value="{{ $price->value }}" data-name="{{ $price->name }}">
                        {{ $price->name }} (₱{{ number_format($price->value, 2) }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    
    <div id="total-price" class="total-price-section">
        <strong class="total-price-label">Total Price: </strong>
        <span class="total-price-value">₱ 0.00</span>
    </div>

    <input type="hidden" name="total_price" id="total_price_input" value="0">
</div>


{{-- UI STYLES --}}
<style>
    .facility-booking-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 16px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .alert {
        border-radius: 6px;
        border: none;
        padding: 12px;
        margin-bottom: 16px;
        font-weight: 500;
    }

    .alert-warning {
        background-color: #fef3c7;
        color: #92400e;
    }

    .capacity-info {
        margin-bottom: 16px;
    }

    .capacity-card {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: #f8fafc;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        gap: 8px;
    }

    .capacity-card i {
        color: #3b82f6;
        font-size: 16px;
    }

    .capacity-text {
        font-weight: 600;
        color: #1e293b;
    }

    .capacity-value {
        background: #3b82f6;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
    }

    .booking-section {
        margin-bottom: 16px;
        background: #ffffff;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .section-header {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        gap: 8px;
    }

    .section-header i {
        color: #3b82f6;
        font-size: 14px;
    }

    .section-header span {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
    }

    .section-content {
        padding: 16px;
    }

    .date-range-display {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .date-item {
        flex: 1;
        min-width: 150px;
        color: #1e293b;
        font-size: 14px;
    }

    .selected-dates-display {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .date-selection-item {
        flex: 1;
        min-width: 150px;
        color: #1e293b;
        font-size: 14px;
    }

    .error-message {
        color: #ef4444;
        font-size: 14px;
        margin-bottom: 16px;
        padding: 8px;
        background: #fef2f2;
        border-radius: 4px;
        border: 1px solid #fecaca;
        display: none;
    }

    .time-slot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .time-input-group {
        display: flex;
        flex-direction: column;
    }

    .time-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .time-input {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.2s ease;
        background: #ffffff;
    }

    .time-input:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .time-input:read-only {
        background-color: #f9fafb;
        color: #6b7280;
        cursor: not-allowed;
    }

    .client-type-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 14px;
        background: #ffffff;
        transition: border-color 0.2s ease;
    }

    .client-type-select:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .total-price-section {
        background: #5a5b5d;
        border-radius: 6px;
        padding: 16px;
        margin-top: 16px;
    }

    .total-price-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-price-label {
        display: flex;
        align-items: center;
        color: #e2e8f0;
        font-size: 14px;
        font-weight: 600;
        gap: 8px;
    }

    .total-price-label i {
        color: #60a5fa;
        font-size: 16px;
    }

    .total-price-value {
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
    }

    .modal .modal-dialog {
        transition: transform 0.3s ease-out;
        transform: translate(0, -50px);
    }

    .modal.show .modal-dialog {
        transform: none;
    }

    .selected-dates-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .selected-dates-card h6 {
        color: #1a1a1a;
        font-size: 1rem;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 12px;
        margin-bottom: 16px;
    }

    .selected-dates-card small.text-muted {
        font-size: 0.75rem;
        color: #6c757d;
        display: block;
        margin-bottom: 4px;
    }

    .selected-dates-card .fw-bold {
        font-size: 0.9rem;
        color: #1a1a1a;
        margin-bottom: 12px;
        word-break: break-word;
    }

    #confirm-dates {
        background-color: #3b82f6;
        border: none;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s ease;
        margin-top: auto;
    }

    #confirm-dates:hover {
        background-color: #2563eb;
    }

    #confirm-dates:disabled {
        background-color: #9ca3af;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .facility-booking-container {
            padding: 12px;
        }
        
        .section-header {
            padding: 10px 12px;
        }
        
        .section-content {
            padding: 12px;
        }
        
        .date-range-display {
            flex-direction: column;
        }
        
        .selected-dates-display {
            flex-direction: column;
        }
        
        .total-price-content {
            flex-direction: column;
            gap: 8px;
            text-align: center;
        }

        .modal .modal-dialog {
            margin: 0.5rem auto;
        }
        
        .modal .modal-content {
            height: 90vh;
        }
        
        .modal .row {
            flex-direction: column;
            height: 100%;
        }
        
        .modal #calendar {
            height: 60%;
        }
        
        .selected-dates-card {
            height: auto;
            margin-top: 16px;
        }
    }

    @media (max-width: 480px) {
        .time-slot-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reserveBtn = document.getElementById('reserve-btn');
    const clientTypeDropdown = document.getElementById('client_type');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    const hasDayBasedPricing = @json($facility->prices->contains('is_based_on_days', true));
    
    reserveBtn.disabled = true;
    
    function validateReserveButton() {
        if (hasDayBasedPricing) {
            reserveBtn.disabled = !clientTypeDropdown.value;
        } else {
            const hasClientType = clientTypeDropdown.value;
            const hasDateFrom = dateFromInput.value;
            const hasDateTo = dateToInput.value;
            
            reserveBtn.disabled = !(hasClientType && hasDateFrom && hasDateTo);
        }
    }
    
    clientTypeDropdown.addEventListener('change', validateReserveButton);
    
    if (!hasDayBasedPricing) {
        dateFromInput.addEventListener('change', validateReserveButton);
        dateToInput.addEventListener('change', validateReserveButton);
        
        const observer = new MutationObserver(validateReserveButton);
        observer.observe(dateFromInput, { attributes: true, attributeFilter: ['value'] });
        observer.observe(dateToInput, { attributes: true, attributeFilter: ['value'] });
        
        let lastDateFrom = '';
        let lastDateTo = '';
        
        const checkDateChanges = () => {
            if (dateFromInput.value !== lastDateFrom || dateToInput.value !== lastDateTo) {
                lastDateFrom = dateFromInput.value;
                lastDateTo = dateToInput.value;
                validateReserveButton();
            }
        };
        
        setInterval(checkDateChanges, 500);
    }
    
    window.validateReserveButton = validateReserveButton;
    
    validateReserveButton();
    setTimeout(validateReserveButton, 1000);
});
</script>



