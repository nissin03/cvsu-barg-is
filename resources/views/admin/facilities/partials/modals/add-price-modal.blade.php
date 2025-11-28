<div class="modal fade" id="addPrice" tabindex="-1" aria-labelledby="addPriceLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addPriceLabel">Add Price</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="priceFormTemplate" style="display:none;">
                    <div class="price-form-card mb-3 p-3 border rounded">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Price Name</label>
                                <input type="text" class="form-control price-name" placeholder="Enter price name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price</label>
                                <input type="number" class="form-control price-value" min="1"
                                    placeholder="Enter price">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Price Type</label>
                                <select class="price-type">
                                    <option value="">Choose Price Type</option>
                                    <option value="individual">Individual</option>
                                    <option value="whole">Whole Place</option>
                                </select>
                            </div>
                            <div class="row mt-3 p-3 rounded d-flex justify-content-between align-items-center gap-3">
                                <div id="isThisADiscountContainer" class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 price-checkbox-item">
                                        <input type="checkbox"
                                            class="is-this-a-discount form-check-input price-checkbox"
                                            id="isThisADiscount">
                                        <label for="isThisADiscount" class="mb-0">Is This Discounted?</label>
                                    </div>
                                </div>
                            </div>

                            {{-- @php
                                $facilityType = $facility->facility_type ?? request()->input('facility_type') ?? 'both';
                            @endphp

                            @if (in_array($facilityType, ['whole_place', 'both']))
                                <div class="row mt-3 p-3 rounded d-flex justify-content-between align-items-center gap-3">
                                    <label class="form-label">Available Discounts (optional)</label>
                                    <div class="discount-checkboxes-container border rounded p-3 bg-light"
                                        style="max-height: 200px; overflow-y: auto;">
                                        @if (isset($discounts) && $discounts->count() > 0)
                                            @foreach ($discounts as $discount)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input price-discount-checkbox" type="checkbox"
                                                        value="{{ $discount->id }}" id="modal_price_discount_{{ $discount->id }}"
                                                        data-discount-id="{{ $discount->id }}"
                                                        data-discount-name="{{ $discount->name }}"
                                                        data-discount-percent="{{ rtrim(rtrim(number_format($discount->percent, 2, '.', ''), '0'), '.') }}">
                                                    <label class="form-check-label" for="modal_price_discount_{{ $discount->id }}">
                                                        {{ $discount->name }}
                                                        ({{ rtrim(rtrim(number_format($discount->percent, 2, '.', ''), '0'), '.') }}%)
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted mb-0 small">
                                                <i class="bi bi-info-circle me-1"></i>
                                                No discounts available. You can add discounts in the Discounts management
                                                section.
                                            </p>
                                        @endif
                                    </div>
                                    <small class="text-muted">Select which discounts apply to this price</small>
                                </div>
                            @endif --}}
                        </div>
                        <div class="col-md-12 d-flex align-items-center justify-items-center mt-5 gap-5">
                            <button type="button" class="btn btn-lg btn-outline-danger removePriceBtn mb-3">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="priceFormContainer">
                </div>
                <button type="button" id="addMultiplePricesRowBtn" style="margin-top: 10px;">
                    <i class="fa-solid fa-plus"></i> Add Another Price
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveMultiplePricesBtn">Save
                    All</button>
            </div>
        </div>
    </div>
</div>
