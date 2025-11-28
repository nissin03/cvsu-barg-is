<div class="modal fade" id="addCollegesModal" tabindex="-1" aria-labelledby="addCollegesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCollegesModalLabel">
                    <i class="icon-plus me-2"></i>Add New Colleges
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="addCollegesForm" method="POST" action="{{ route('admin.colleges.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="text-muted mb-0">Add one or more colleges to the system</p>
                            <span class="badge bg-primary" id="collegeCount">1 College</span>
                        </div>
                    </div>
                    
                    <div class="accordion" id="collegesAccordion">
                        <div class="accordion-item college-accordion-item active" data-index="1">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <span class="college-counter">1</span>
                                    <span class="college-title">New College</span>
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#collegesAccordion">
                                <div class="accordion-body">
                                    <button type="button" class="remove-college-btn" title="Remove this college">
                                        <i class="icon-trash-2"></i>
                                    </button>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="college_name_1" class="form-label">College Name *</label>
                                            <input type="text" class="form-control college-name" id="college_name_1" 
                                                   name="colleges[0][name]" placeholder="e.g., College of Engineering" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="college_code_1" class="form-label">College Code *</label>
                                            <input type="text" class="form-control college-code" id="college_code_1" 
                                                   name="colleges[0][code]" placeholder="e.g., COE" 
                                                   style="text-transform: uppercase;" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="add-another-btn" id="addAnotherCollege">
                        <i class="icon-plus"></i>
                        <span>Add Another College</span>
                    </button>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="tf-button style-2" data-bs-dismiss="modal">
                        <i class="icon-close"></i>Cancel
                    </button>
                    <button type="submit" class="tf-button" id="submitBtn">
                        <span class="loading-spinner">
                            <i class="icon-loading"></i>
                        </span>
                        <i class="icon-check"></i>Save Colleges
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .college-accordion-item {
        margin-bottom: 10px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #dee2e6;
    }
    
    .accordion-button {
        position: relative;
        padding-left: 50px;
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #e8f4fd;
        color: #0c63e4;
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
    }
    
    .college-counter {
        position: absolute;
        left: 15px;
        background: #007bff;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }
    
    .college-title {
        margin-left: 10px;
    }
    
    .accordion-body {
        position: relative;
        padding: 20px;
    }
    
    .remove-college-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        color: #dc3545;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        background: transparent;
    }
    
    .remove-college-btn:hover {
        color: #c82333;
        transform: scale(1.1);
    }
    
    .add-another-btn {
        border: 2px dashed #28a745;
        background: transparent;
        color: #28a745;
        padding: 12px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        margin-top: 15px;
        transition: all 0.3s ease;
    }
    
    .add-another-btn:hover {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border-bottom: none;
    }
    
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    .loading-spinner {
        display: none;
    }
    
    .btn-loading .loading-spinner {
        display: inline-block;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Custom SweetAlert2 Styles for Larger Size and Text */
    .swal2-popup {
        width: 32rem !important;
        max-width: 90% !important;
        padding: 2rem !important;
        border-radius: 12px !important;
        font-size: 16px !important;
    }
    
    .swal2-title {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        margin-bottom: 1rem !important;
        line-height: 1.4 !important;
    }
    
    .swal2-html-container {
        font-size: 16px !important;
        line-height: 1.6 !important;
        margin: 1rem 0 1.5rem 0 !important;
        text-align: left !important;
        white-space: pre-line !important;
    }
    
    .swal2-icon {
        width: 80px !important;
        height: 80px !important;
        margin: 1rem auto 1.5rem auto !important;
    }
    
    .swal2-icon.swal2-success .swal2-success-ring {
        width: 80px !important;
        height: 80px !important;
    }
    
    .swal2-icon.swal2-error .swal2-error-x {
        width: 54px !important;
        height: 54px !important;
    }
    
    .swal2-icon.swal2-warning {
        font-size: 60px !important;
    }
    
    .swal2-actions {
        margin-top: 1.5rem !important;
        justify-content: center !important;
    }
    
    .swal2-confirm {
        font-size: 16px !important;
        padding: 0.75rem 2rem !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        min-width: 120px !important;
    }
    
    .swal2-deny, .swal2-cancel {
        font-size: 16px !important;
        padding: 0.75rem 2rem !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        min-width: 120px !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .swal2-popup {
            width: 95% !important;
            padding: 1.5rem !important;
        }
        
        .swal2-title {
            font-size: 1.25rem !important;
        }
        
        .swal2-html-container {
            font-size: 14px !important;
        }
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        let collegeIndex = 1;
        
        function showAlert(icon, title, text, confirmButtonText = 'OK') {
            return Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonText: confirmButtonText,
                width: '50rem',
                padding: '2.5rem',
                customClass: {
                    confirmButton: 'tf-button',
                    popup: 'large-swal-popup',
                    title: 'large-swal-title',
                    content: 'large-swal-content'
                },
                buttonsStyling: false,
                allowOutsideClick: false,
                allowEscapeKey: true,
                showCloseButton: true,
                focusConfirm: true,
                heightAuto: false
            });
        }
        
        $('#addAnotherCollege').on('click', function() {
            collegeIndex++;
            addCollegeAccordion();
            updateCollegeCount();
            updateRemoveButtons();
        });
        
        $(document).on('click', '.remove-college-btn', function() {
            if ($('.college-accordion-item').length > 1) {
                $(this).closest('.college-accordion-item').fadeOut(300, function() {
                    $(this).remove();
                    updateCollegeNumbers();
                    updateCollegeCount();
                    updateRemoveButtons();
                });
            }
        });
        
        $(document).on('input', '.college-name', function() {
            const $codeField = $(this).closest('.accordion-body').find('.college-code');
            if (!$codeField.val()) {
                const name = $(this).val();
                const code = name.split(' ')
                    .filter(word => word.length > 2)
                    .map(word => word.charAt(0))
                    .join('')
                    .toUpperCase()
                    .substring(0, 5);
                $codeField.val(code);
            }
            updateTitle($(this));
        });
        
        function addCollegeAccordion() {
            const newAccordion = `
                <div class="accordion-item college-accordion-item" data-index="${collegeIndex}">
                    <h2 class="accordion-header" id="heading${collegeIndex}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${collegeIndex}" aria-expanded="false" aria-controls="collapse${collegeIndex}">
                            <span class="college-counter">${collegeIndex}</span>
                            <span class="college-title">New College</span>
                        </button>
                    </h2>
                    <div id="collapse${collegeIndex}" class="accordion-collapse collapse" aria-labelledby="heading${collegeIndex}" data-bs-parent="#collegesAccordion">
                        <div class="accordion-body">
                            <button type="button" class="remove-college-btn" title="Remove this college">
                                <i class="icon-trash-2"></i>
                            </button>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="college_name_${collegeIndex}" class="form-label">College Name *</label>
                                    <input type="text" class="form-control college-name" id="college_name_${collegeIndex}" 
                                           name="colleges[${collegeIndex-1}][name]" placeholder="e.g., College of Engineering" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="college_code_${collegeIndex}" class="form-label">College Code *</label>
                                    <input type="text" class="form-control college-code" id="college_code_${collegeIndex}" 
                                           name="colleges[${collegeIndex-1}][code]" placeholder="e.g., COE" 
                                           style="text-transform: uppercase;" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#collegesAccordion').append(newAccordion);
            new bootstrap.Collapse(document.getElementById('collapse' + collegeIndex));
        }
        
        function updateCollegeNumbers() {
            $('.college-accordion-item').each(function(index) {
                $(this).attr('data-index', index + 1);
                $(this).find('.college-counter').text(index + 1);
                $(this).find('input, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        const newName = name.replace(/\[\d+\]/, `[${index}]`);
                        $(this).attr('name', newName);
                    }
                    const id = $(this).attr('id');
                    if (id) {
                        const newId = id.replace(/_\d+$/, `_${index + 1}`);
                        $(this).attr('id', newId);
                        $(this).prev('label').attr('for', newId);
                    }
                });
                
                const headingId = 'heading' + (index + 1);
                const collapseId = 'collapse' + (index + 1);
                $(this).find('.accordion-header').attr('id', headingId);
                $(this).find('.accordion-button').attr('data-bs-target', '#' + collapseId).attr('aria-controls', collapseId);
                $(this).find('.accordion-collapse').attr('id', collapseId).attr('aria-labelledby', headingId);
            });
        }
        
        function updateCollegeCount() {
            const count = $('.college-accordion-item').length;
            $('#collegeCount').text(`${count} College${count !== 1 ? 's' : ''}`);
        }
        
        function updateRemoveButtons() {
            const count = $('.college-accordion-item').length;
            if (count === 1) {
                $('.remove-college-btn').hide();
            } else {
                $('.remove-college-btn').show();
            }
        }
        
        function updateTitle($input) {
            const name = $input.val().trim();
            const $title = $input.closest('.college-accordion-item').find('.college-title');
            
            if (name) {
                $title.text(name);
            } else {
                $title.text('New College');
            }
        }
        
        function validateForm() {
            let isValid = true;
            const errors = [];
            const existingCodes = [];
            const existingNames = [];
            
            $('.college-accordion-item').each(function() {
                const name = $(this).find('.college-name').val().trim();
                const code = $(this).find('.college-code').val().trim();
                const index = $(this).attr('data-index');
                
                $(this).find('.form-control').removeClass('is-invalid');
                
                if (!name) {
                    $(this).find('.college-name').addClass('is-invalid');
                    errors.push(`College ${index}: Name is required`);
                    isValid = false;
                } else if (existingNames.includes(name.toLowerCase())) {
                    $(this).find('.college-name').addClass('is-invalid');
                    errors.push(`College ${index}: Name "${name}" is already used in this form`);
                    isValid = false;
                } else {
                    existingNames.push(name.toLowerCase());
                }
                
                if (!code) {
                    $(this).find('.college-code').addClass('is-invalid');
                    errors.push(`College ${index}: Code is required`);
                    isValid = false;
                } else if (existingCodes.includes(code.toUpperCase())) {
                    $(this).find('.college-code').addClass('is-invalid');
                    errors.push(`College ${index}: Code "${code}" is already used in this form`);
                    isValid = false;
                } else {
                    existingCodes.push(code.toUpperCase());
                }
            });
            
            if (!isValid) {
                showAlert('error', 'Validation Error', 'Please fix the following errors:\n\n' + errors.join('\n'));
            }
            
            return isValid;
        }
        
        function resetForm() {
            $('#collegesAccordion').html(`
                <div class="accordion-item college-accordion-item active" data-index="1">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <span class="college-counter">1</span>
                            <span class="college-title">New College</span>
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#collegesAccordion">
                        <div class="accordion-body">
                            <button type="button" class="remove-college-btn" title="Remove this college">
                                <i class="icon-trash-2"></i>
                            </button>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="college_name_1" class="form-label">College Name *</label>
                                    <input type="text" class="form-control college-name" id="college_name_1" 
                                           name="colleges[0][name]" placeholder="e.g., College of Engineering" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="college_code_1" class="form-label">College Code *</label>
                                    <input type="text" class="form-control college-code" id="college_code_1" 
                                           name="colleges[0][code]" placeholder="e.g., COE" 
                                           style="text-transform: uppercase;" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            collegeIndex = 1;
            updateCollegeCount();
            $('.form-control').removeClass('is-invalid');
            updateRemoveButtons();
        }
        
        $('#addCollegesForm').on('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm()) {
                return;
            }
            
            const $submitBtn = $('#submitBtn');
            $submitBtn.addClass('btn-loading').prop('disabled', true);
            
            const formData = $(this).serialize();
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Success!', response.message || 'Colleges have been added successfully to the system.')
                            .then((result) => {
                                resetForm();
                                $('#addCollegesModal').modal('hide');
                                location.reload();
                            });
                    } else {
                        let errorMsg = response.message || 'An error occurred while processing your request.';
                        if (response.errors) {
                            errorMsg += '\n\n' + Object.values(response.errors).flat().join('\n');
                        }
                        showAlert('error', 'Operation Failed', errorMsg);
                    }
                },
                error: function(xhr) {
                    console.error('Submit error:', xhr);
                    let errorMessage = 'An error occurred while saving colleges to the system.';
                    
                    if (xhr.status === 422) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            if (errors['colleges.0.name'] || errors['colleges.1.name'] || errors['colleges.2.name']) {
                                errorMessage = 'A college with this name already exists in the system. Please use a unique name for each college.';
                            } else {
                                errorMessage = Object.values(errors).flat().join('\n\n');
                            }
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showAlert('error', 'Error Occurred', errorMessage);
                },
                complete: function() {
                    $submitBtn.removeClass('btn-loading').prop('disabled', false);
                }
            });
        });
        
        $('#addCollegesModal').on('hidden.bs.modal', function() {
            if (!$('#submitBtn').hasClass('btn-loading')) {
                resetForm();
            }
        });
        
        updateRemoveButtons();
    });
</script>
@endpush