{{-- resources/views/components/addons/addon-selector.blade.php --}}
@props(['addons' => collect(), 'facility' => null])

@php
    $preselected =
        isset($facility) && $facility !== null
            ? $facility->addons->pluck('id')->map(fn($id) => (string) $id)->toArray()
            : [];

    $hiddenValue = isset($facility) && $facility !== null ? $facility->addons->pluck('id')->implode(',') : '';
@endphp

<style>
    .addon-selector-container {
        max-width: 900px;
    }

    .addon-instruction {
        font-size: 1.25rem;
    }

    .addon-checkbox-container {
        background: #fff;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 2rem;
    }

    .addon-checkbox-item {
        margin-bottom: 1.5rem;
    }

    .addon-checkbox-item:last-child {
        margin-bottom: 0;
    }

    .addon-checkbox-item .form-check-input {
        width: 2rem;
        height: 2rem;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 0.25rem;
    }

    .addon-checkbox-item .form-check-input:checked {
        background-color: #4a90e2;
        border-color: #4a90e2;
    }

    .addon-checkbox-item label {
        font-size: 1.5rem;
        color: #000;
        cursor: pointer;
        padding-left: 0.75rem;
        margin-bottom: 0;
        font-weight: 400;
    }

    .addon-show-btn {
        background: #fff;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem 2rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: #000;
        cursor: pointer;
        transition: all 0.2s;
    }

    .addon-show-btn:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
    }

    .selected-title {
        margin-bottom: 1.5rem;
        margin-top: 3rem;
    }

    .selected-addons-box {
        background: #fff;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 2rem 2.5rem;
    }

    .selected-addons-box ul {
        list-style: disc;
        padding-left: 1.5rem;
        margin: 0;
    }

    .selected-addons-box li {
        font-size: 1.5rem;
        color: #000;
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .selected-addons-box li:last-child {
        margin-bottom: 0;
    }

    .no-addons-message {
        font-size: 1.25rem;
        color: #6c757d;
    }
</style>

<div class="mt-3 addon-selector-container">
    <h3 class="addon-title">Available Addons</h3>

    @if ($addons->isEmpty())
        <p class="addon-instruction alert alert-warning">No addons available.</p>
        {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddonModal">
            <i class="icon-plus"></i> Add New Addon
        </button> --}}
        <a href="{{ route('admin.addons') }}" class="btn btn-primary">
            <i class="icon-plus"></i> Add New Addon
        </a>
    @else
        <div class="addon-checkbox-container">
            @foreach ($addons ?? collect() as $a)
                <div class="form-check addon-checkbox-item">
                    <input class="form-check-input addon-checkbox" type="checkbox" value="{{ $a->id }}"
                        id="addon_{{ $a->id }}" {{ in_array((string) $a->id, $preselected) ? 'checked' : '' }}
                        data-name="{{ $a->name }}" data-price="{{ number_format($a->base_price, 2) }}"
                        data-type="{{ $a->price_type === 'flat_rate' ? 'Flat Rate' : ucfirst(str_replace('_', ' ', $a->price_type)) }}">
                    <label class="form-check-label" for="addon_{{ $a->id }}">
                        {{ $a->name }} — ₱{{ number_format($a->base_price, 2) }}
                        ({{ $a->price_type === 'flat_rate' ? 'Flat Rate' : ucfirst(str_replace('_', ' ', $a->price_type)) }})
                    </label>
                </div>
            @endforeach
        </div>
    @endif

    <input type="hidden" name="selected_addons" id="selected_addons" value="{{ $hiddenValue }}">

    <div id="selectedAddonsPreview" style="display:none;">
        <h3 class="selected-title">Selected Addons</h3>
        <div class="selected-addons-box">
            <ul id="selectedAddonsList"></ul>
        </div>
    </div>
</div>
