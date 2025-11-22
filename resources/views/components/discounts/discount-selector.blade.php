{{-- D:\Herd\cvsu-barg-is\resources\views\components\discounts\discount-selector.blade.php --}}
{{-- @props(['discounts' => collect(), 'facility' => null])

@php
    $preselected =
        isset($facility) && $facility !== null
            ? $facility->discounts->pluck('id')->map(fn($id) => (string) $id)->toArray()
            : [];

    $hiddenValue = isset($facility) && $facility !== null ? $facility->discounts->pluck('id')->implode(',') : '';
@endphp
<div class="wg-box mt-3" id="discountBox" style="display:none;">
    <h6 class="mb-2">Available Discounts (for Whole Place and Both)</h6>
    <div class="mb-2 small text-muted">Use Ctrl + Left Click to select multiple items.</div>

    <select id="discountMultiSelect" class="form-select" size="8" multiple>
        @foreach ($discounts ?? collect() as $d)
            <option value="{{ $d->id }}" {{ in_array((string) $d->id, $preselected) ? 'selected' : '' }}>
                {{ $d->name }}
                ({{ rtrim(rtrim(number_format($d->percent, 2, '.', ''), '0'), '.') }}%{{ $d->applies_to === 'venue_only' ? ' - Venue Only' : '' }})
            </option>
        @endforeach
    </select>

    <input type="hidden" name="selected_discounts" id="selected_discounts" value="{{ $hiddenValue }}">

    <div class="mt-2">
        <button type="button" class="btn btn-outline-primary btn-sm" id="showSelectedDiscountsBtn">
            Show Selected
        </button>
    </div>

    <div class="mt-2 border rounded p-2" id="selectedDiscountsPreview"
        style="max-height: 150px; overflow-y: auto; display:none;">
    </div>
</div> --}}

{{-- resources/views/components/discounts/discount-selector.blade.php --}}
@props(['discounts' => collect(), 'facility' => null])

@php
    $preselected =
        isset($facility) && $facility !== null
            ? $facility->discounts->pluck('id')->map(fn($id) => (string) $id)->toArray()
            : [];

    $hiddenValue = isset($facility) && $facility !== null ? $facility->discounts->pluck('id')->implode(',') : '';
@endphp

<style>
    .discount-selector-container {
        max-width: 900px;
    }

    .discount-subtitle {
        font-size: 1rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }


    .discount-checkbox-container {
        background: #fff;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 2rem;
        max-height: 400px;
        overflow-y: auto;
    }

    .discount-checkbox-item {
        margin-bottom: 1.5rem;
    }

    .discount-checkbox-item:last-child {
        margin-bottom: 0;
    }

    .discount-checkbox-item .form-check-input {
        width: 2rem;
        height: 2rem;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 0.25rem;
    }

    .discount-checkbox-item .form-check-input:checked {
        background-color: #4a90e2;
        border-color: #4a90e2;
    }

    .discount-checkbox-item label {
        font-size: 1.5rem;
        color: #000;
        cursor: pointer;
        padding-left: 0.75rem;
        margin-bottom: 0;
        font-weight: 400;
    }

    .selected-discounts-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #000;
        margin-bottom: 1.5rem;
        margin-top: 3rem;
    }

    .selected-discounts-box {
        background: #fff;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 2rem 2.5rem;
    }

    .selected-discounts-box ul {
        list-style: disc;
        padding-left: 1.5rem;
        margin: 0;
    }

    .selected-discounts-box li {
        font-size: 1.5rem;
        color: #000;
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .selected-discounts-box li:last-child {
        margin-bottom: 0;
    }

    .no-discounts-message {
        font-size: 1.25rem;
        color: #6c757d;
    }
</style>

<div class="wg-box mt-3 discount-selector-container" id="discountBox" style="display:none;">
    <h3 class="discount-title">Available Discounts</h1>
        <div class="discount-checkbox-container">
            @foreach ($discounts ?? collect() as $d)
                <div class="form-check discount-checkbox-item">
                    <input class="form-check-input discount-checkbox" type="checkbox" value="{{ $d->id }}"
                        id="discount_{{ $d->id }}" {{ in_array((string) $d->id, $preselected) ? 'checked' : '' }}
                        data-name="{{ $d->name }}"
                        data-percent="{{ rtrim(rtrim(number_format($d->percent, 2, '.', ''), '0'), '.') }}"
                        data-applies-to="{{ $d->applies_to === 'venue_only' ? ' - Venue Only' : '' }}">
                    <label class="form-check-label" for="discount_{{ $d->id }}">
                        {{ $d->name }}
                        ({{ rtrim(rtrim(number_format($d->percent, 2, '.', ''), '0'), '.') }}%{{ $d->applies_to === 'venue_only' ? ' - Venue Only' : '' }})
                    </label>
                </div>
            @endforeach
        </div>

        <input type="hidden" name="selected_discounts" id="selected_discounts" value="{{ $hiddenValue }}">

        <div id="selectedDiscountsPreview" style="display:none;">
            <h2 class="selected-discounts-title">Selected Discounts</h2>
            <div class="selected-discounts-box">
                <ul id="selectedDiscountsList"></ul>
            </div>
        </div>
</div>
