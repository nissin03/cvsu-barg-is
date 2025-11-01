<div class="modal fade" id="addAddonModal" tabindex="-1" aria-labelledby="addAddonLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAddonLabel">Add New Addon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addonModalForm">
                    <div class="mb-3">
                        <label class="form-label">
                            Addon Name
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="addonName" name="name"
                            placeholder="Enter addon name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="addonDescription" name="description" rows="3"
                            placeholder="Enter addon description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Price Type
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="addonPriceType" name="price_type" required>
                                <option value="">Select Price Type</option>
                                <option value="per_unit">Per Unit</option>
                                <option value="flat_rate">Flat Rate</option>
                                <option value="per_night">Per Night / Per Day</option>
                                <option value="per_item">Per Item</option>
                                <option value="per_hour">Per Hour</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Base Price
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="addonBasePrice" name="base_price"
                                step="0.01" min="0" placeholder="0.00" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">
                            Billing Cycle
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="addonBillingCycle" name="billing_cycle" required>
                            <option value="">Select Billing Cycle</option>
                            <option value="per_day" selected>Per Day</option>
                            <option value="per_contract">Per Contract</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Show
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="addonShow" name="show" required>
                            <option value="">Select Where It Will be Showing</option>
                            <option value="both">User and Staff</option>
                            <option value="staff">Staff only</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Conditional Fields -->
                    <div id="addonConditionalFields">
                        <!-- Per Unit Fields -->
                        <div id="addonPerUnitFields" class="conditional-section d-none">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="addonIsAvailableUnit"
                                    name="is_available" value="1" checked>
                                <label class="form-check-label" for="addonIsAvailableUnit">
                                    Currently available
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    Capacity
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="addonCapacity" name="capacity"
                                    min="1" placeholder="Enter capacity">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Flat Rate Fields -->
                        <div id="addonFlatRateFields" class="conditional-section d-none">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="addonIsAvailableFlat"
                                    name="is_available" value="1" checked>
                                <label class="form-check-label" for="addonIsAvailableFlat">
                                    Currently available
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="addonIsRefundableFlat"
                                    name="is_refundable" value="1">
                                <label class="form-check-label" for="addonIsRefundableFlat">
                                    Refundable
                                </label>
                            </div>
                        </div>

                        <!-- Per Night Fields -->
                        <div id="addonPerNightFields" class="conditional-section d-none">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="addonIsAvailableNight"
                                    name="is_available" value="1" checked>
                                <label class="form-check-label" for="addonIsAvailableNight">
                                    Currently available
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    Quantity <span class="text-muted">(optional)</span>
                                </label>
                                <input type="number" class="form-control" id="addonQuantityNight" name="quantity"
                                    min="1" placeholder="Enter quantity (optional)">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Per Item Fields -->
                        <div id="addonPerItemFields" class="conditional-section d-none">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="addonIsAvailableItem"
                                    name="is_available" value="1" checked>
                                <label class="form-check-label" for="addonIsAvailableItem">
                                    Currently available
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="addonIsBasedOnQuantity"
                                    name="is_based_on_quantity" value="1" checked>
                                <label class="form-check-label" for="addonIsBasedOnQuantity">
                                    Based on quantity
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    Quantity
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="addonQuantityItem" name="quantity"
                                    min="1" placeholder="Enter quantity">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Per Hour Fields -->
                        <div id="addonPerHourFields" class="conditional-section d-none">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="addonIsAvailableHour"
                                    name="is_available" value="1" checked>
                                <label class="form-check-label" for="addonIsAvailableHour">
                                    Currently available
                                </label>
                            </div>
                            <div class="alert alert-info">
                                <strong>Note:</strong> Per hour addons are only visible to staff members.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveAddonBtn">Create Addon</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        // Store the route URL outside the async function
        const ADDON_STORE_URL = '{{ route('admin.addons.store') }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';

        // Wait for modal to be in DOM
        function initAddonModal() {
            const modal = document.getElementById('addAddonModal');
            if (!modal) {
                console.log('Addon modal not found, retrying...');
                setTimeout(initAddonModal, 200);
                return;
            }

            const form = document.getElementById('addonModalForm');
            const priceTypeSelect = document.getElementById('addonPriceType');
            const showField = document.getElementById('addonShow');
            const billingCycleField = document.getElementById('addonBillingCycle');
            const saveBtn = document.getElementById('saveAddonBtn');

            if (!form || !priceTypeSelect || !showField || !billingCycleField || !saveBtn) {
                console.error('Required addon modal elements not found');
                return;
            }

            console.log('Addon modal initialized successfully');

            const conditionalSections = {
                per_unit: document.getElementById('addonPerUnitFields'),
                flat_rate: document.getElementById('addonFlatRateFields'),
                per_night: document.getElementById('addonPerNightFields'),
                per_item: document.getElementById('addonPerItemFields'),
                per_hour: document.getElementById('addonPerHourFields')
            };

            function hideAllConditional() {
                Object.values(conditionalSections).forEach(section => {
                    if (section) section.classList.add('d-none');
                });
            }

            function toggleConditionalFields() {
                hideAllConditional();
                showField.disabled = false;
                billingCycleField.disabled = false;

                const priceType = priceTypeSelect.value;

                if (priceType === 'per_hour') {
                    showField.value = 'staff';
                    showField.disabled = true;
                    if (conditionalSections.per_hour) {
                        conditionalSections.per_hour.classList.remove('d-none');
                    }
                } else if (priceType && conditionalSections[priceType]) {
                    conditionalSections[priceType].classList.remove('d-none');
                }
            }

            function clearForm() {
                if (!form) return;

                form.reset();
                hideAllConditional();

                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

            function displayErrors(errors) {
                if (!form) return;

                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                Object.keys(errors).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) {
                        field.classList.add('is-invalid');
                        const feedback = field.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = errors[key][0];
                        }
                    }
                });
            }

            // Handle form submission
            saveBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Create Addon button clicked');

                if (!form) {
                    console.error('Form element not found');
                    alert('Form not found. Please refresh the page.');
                    return;
                }

                // Validate form
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const formData = new FormData(form);

                // Log form data for debugging
                console.log('Form data being sent:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }

                saveBtn.disabled = true;
                const originalText = saveBtn.textContent;
                saveBtn.textContent = 'Creating...';

                try {
                    console.log('Sending request to:', ADDON_STORE_URL);

                    const response = await fetch(ADDON_STORE_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    console.log('Response status:', response.status);

                    const data = await response.json();
                    console.log('Response data:', data);

                    if (response.ok) {
                        console.log('Addon created successfully');
                        let modalInstance = bootstrap.Modal.getInstance(modal);
                        if (!modalInstance) {
                            modalInstance = new bootstrap.Modal(modal);
                        }
                        modalInstance.hide();
                        // if (modalInstance) modalInstance.hide();
                        clearForm();

                        alert('Addon created successfully!');
                        window.location.reload();
                    } else {
                        console.error('Server error:', data);
                        if (data.errors) {
                            displayErrors(data.errors);
                            alert('Please fix the validation errors.');
                        } else {
                            alert(data.message || 'An error occurred. Please try again.');
                        }
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    alert('Network error occurred. Please check your connection and try again.');
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                }
            });

            // Event listeners
            priceTypeSelect.addEventListener('change', toggleConditionalFields);

            modal.addEventListener('hidden.bs.modal', clearForm);

            modal.addEventListener('shown.bs.modal', function() {
                console.log('Modal shown');
                toggleConditionalFields();
            });
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAddonModal);
        } else {
            initAddonModal();
        }
    })();
</script>
