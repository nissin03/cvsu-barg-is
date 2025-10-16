@extends('layouts.admin')

@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Create Addon</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <a href="{{ route('admin.addons') }}">
                            <div class="text-tiny">Addons</div>
                        </a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <div class="text-tiny">New Addon</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <form class="form-style-1" action="{{ route('admin.addons.store') }}" method="POST">
                    @csrf

                    <fieldset class="name">
                        <div class="body-title">Addon Name <span class="tf-color-1">*</span></div>
                        <input type="text" class="flex-grow" name="name" placeholder="Addon name"
                            value="{{ old('name') }}" required>
                    </fieldset>
                    @error('name')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    <fieldset class="name">
                        <div class="body-title">Price Type <span class="tf-color-1">*</span></div>
                        <div class="select w-100">
                            <select name="price_type" id="price_type" class="d-block" required>
                                <option value="">Select Price Type</option>
                                <option value="per_unit" {{ old('price_type') == 'per_unit' ? 'selected' : '' }}>Per Unit
                                </option>
                                <option value="flat_rate" {{ old('price_type') == 'flat_rate' ? 'selected' : '' }}>Flat Rate
                                </option>
                                <option value="per_night" {{ old('price_type') == 'per_night' ? 'selected' : '' }}>Per Night
                                </option>
                            </select>
                        </div>
                    </fieldset>
                    @error('price_type')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                    <fieldset>
                        <div class="body-title">Base Price <span class="tf-color-1">*</span></div>
                        <input type="number" class="flex-grow" name="base_price" placeholder="0.00" min="0"
                            step="0.01" value="{{ old('base_price') }}" required>
                    </fieldset>
                    @error('base_price')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    <fieldset id="capacity_field" style="display:none;">
                        <div class="body-title">Capacity <span class="tf-color-1">*</span></div>
                        <input type="number" name="capacity" class="flex-grow" placeholder="e.g. 5" min="1"
                            value="{{ old('capacity') }}">
                    </fieldset>
                    @error('capacity')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                    <fieldset>
                        <div class="body-title">Description</div>
                        <textarea name="description" class="flex-grow" rows="3" placeholder="Enter addon description">{{ old('description') }}</textarea>
                    </fieldset>
                    @error('description')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror

                    <!-- Checkboxes -->
                    <fieldset>
                        <label><input type="checkbox" name="is_available" {{ old('is_available') ? 'checked' : '' }}>
                            Available</label><br>
                        <label><input type="checkbox" name="is_based_on_quantity" id="is_based_on_quantity"
                                {{ old('is_based_on_quantity') ? 'checked' : '' }}> Based on Quantity</label><br>
                        <label><input type="checkbox" name="is_refundable" id="is_refundable"
                                {{ old('is_refundable') ? 'checked' : '' }}> Refundable</label>
                    </fieldset>

                    <fieldset class="name">
                        <div class="body-title">Show <span class="tf-color-1">*</span></div>
                        <div class="select w-100">
                            <select name="show" required>
                                <option value="">Select visibility</option>
                                <option value="both" {{ old('show') == 'both' ? 'selected' : '' }}>Both</option>
                                <option value="staff" {{ old('show') == 'staff' ? 'selected' : '' }}>Staff Only</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('show')
                        <span class="alert alert-danger">{{ $message }}</span>
                    @enderror
                    <div class="bot">
                        <div></div>
                        <button type="submit" class="tf-button w208">Save Addon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const priceType = document.getElementById("price_type");
            const capacityField = document.getElementById("capacity_field");
            const isBasedOnQuantity = document.getElementById("is_based_on_quantity");
            const isRefundable = document.getElementById("is_refundable");

            function toggleFields() {
                if (priceType.value === "flat_rate") {
                    isBasedOnQuantity.checked = false;
                    isBasedOnQuantity.disabled = true;
                } else if (priceType.value === "per_unit" || priceType.value === "per_night") {
                    isBasedOnQuantity.disabled = false;
                } else {
                    isBasedOnQuantity.disabled = false;
                }
                if ((priceType.value === "per_unit" || priceType.value === "per_night") && isBasedOnQuantity
                    .checked) {
                    capacityField.style.display = "block";
                    console.log("Showing capacity field");
                } else {
                    capacityField.style.display = "none";
                    console.log("Hiding capacity field");
                }

                if (priceType.value === "per_unit" || priceType.value === "per_night") {
                    isRefundable.checked = false;
                    isRefundable.disabled = true;
                } else if (priceType.value === "flat_rate") {
                    isRefundable.disabled = false;
                } else {
                    isRefundable.disabled = false;
                }
            }

            priceType.addEventListener("change", toggleFields);
            isBasedOnQuantity.addEventListener("change", toggleFields);

            toggleFields();
        });
    </script>
@endpush
