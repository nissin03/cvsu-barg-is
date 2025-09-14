<div class="modal fade" id="addCoursesModal" tabindex="-1" aria-labelledby="addCoursesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCoursesModalLabel">
                    <i class="icon-plus me-2"></i>Add New Courses
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="addCoursesForm" method="POST" action="{{ route('admin.courses.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="text-muted mb-0">Add one or more courses to the system</p>
                            <span class="badge bg-primary" id="courseCount">1 Course</span>
                        </div>
                    </div>
                    
                    <div class="accordion" id="coursesAccordion">
                        <div class="accordion-item course-accordion-item active" data-index="1">
                            <h2 class="accordion-header" id="courseHeadingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#courseCollapseOne" aria-expanded="true" aria-controls="courseCollapseOne">
                                    <span class="course-counter">1</span>
                                    <span class="course-title">New Course</span>
                                </button>
                            </h2>
                            <div id="courseCollapseOne" class="accordion-collapse collapse show" aria-labelledby="courseHeadingOne" data-bs-parent="#coursesAccordion">
                                <div class="accordion-body">
                                    <button type="button" class="remove-course-btn" title="Remove this course">
                                        <i class="icon-trash-2"></i>
                                    </button>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="course_name_1" class="form-label">Course Name *</label>
                                            <input type="text" class="form-control course-name" id="course_name_1" 
                                                   name="name" placeholder="e.g., Bachelor of Science in Computer Science" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="course_code_1" class="form-label">Course Code *</label>
                                            <input type="text" class="form-control course-code" id="course_code_1" 
                                                   name="code" placeholder="e.g., BSCS" 
                                                   style="text-transform: uppercase;" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="course_college_id" class="form-label">Select College *</label>
                        <select class="form-control" id="course_college_id" name="college_id" required>
                            <option value="">Select College</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->code }} - {{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="button" class="add-another-btn" id="addAnotherCourse">
                        <i class="icon-plus"></i>
                        <span>Add Another Course</span>
                    </button>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="tf-button style-2" data-bs-dismiss="modal">
                        <i class="icon-close"></i>Cancel
                    </button>
                    <button type="submit" class="tf-button" id="courseSubmitBtn">
                        <span class="loading-spinner">
                            <i class="icon-loading"></i>
                        </span>
                        <i class="icon-check"></i>Save Courses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .course-accordion-item {
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
    
    .course-counter {
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
    
    .course-title {
        margin-left: 10px;
    }
    
    .accordion-body {
        position: relative;
        padding: 20px;
    }
    
    .remove-course-btn {
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
    
    .remove-course-btn:hover {
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
</style>

@push('scripts')
<script>
    $(function() {
        let courseIndex = 1;
        
        function showCourseAlert(icon, title, text, confirmButtonText = 'OK') {
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
        
        $('#addAnotherCourse').on('click', function() {
            courseIndex++;
            addCourseAccordion();
            updateCourseCount();
            updateRemoveCourseButtons();
        });
        
        $(document).on('click', '.remove-course-btn', function() {
            if ($('.course-accordion-item').length > 1) {
                $(this).closest('.course-accordion-item').fadeOut(300, function() {
                    $(this).remove();
                    updateCourseNumbers();
                    updateCourseCount();
                    updateRemoveCourseButtons();
                });
            }
        });
        
        $(document).on('input', '.course-name', function() {
            const $codeField = $(this).closest('.accordion-body').find('.course-code');
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
            updateCourseTitle($(this));
        });
        
        function addCourseAccordion() {
            const newAccordion = `
                <div class="accordion-item course-accordion-item" data-index="${courseIndex}">
                    <h2 class="accordion-header" id="courseHeading${courseIndex}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#courseCollapse${courseIndex}" aria-expanded="false" aria-controls="courseCollapse${courseIndex}">
                            <span class="course-counter">${courseIndex}</span>
                            <span class="course-title">New Course</span>
                        </button>
                    </h2>
                    <div id="courseCollapse${courseIndex}" class="accordion-collapse collapse" aria-labelledby="courseHeading${courseIndex}" data-bs-parent="#coursesAccordion">
                        <div class="accordion-body">
                            <button type="button" class="remove-course-btn" title="Remove this course">
                                <i class="icon-trash-2"></i>
                            </button>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="course_name_${courseIndex}" class="form-label">Course Name *</label>
                                    <input type="text" class="form-control course-name" id="course_name_${courseIndex}" 
                                           name="name" placeholder="e.g., Bachelor of Science in Computer Science" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="course_code_${courseIndex}" class="form-label">Course Code *</label>
                                    <input type="text" class="form-control course-code" id="course_code_${courseIndex}" 
                                           name="code" placeholder="e.g., BSCS" 
                                           style="text-transform: uppercase;" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#coursesAccordion').append(newAccordion);
            new bootstrap.Collapse(document.getElementById('courseCollapse' + courseIndex));
        }
        
        function updateCourseNumbers() {
            $('.course-accordion-item').each(function(index) {
                $(this).attr('data-index', index + 1);
                $(this).find('.course-counter').text(index + 1);
                
                const headingId = 'courseHeading' + (index + 1);
                const collapseId = 'courseCollapse' + (index + 1);
                $(this).find('.accordion-header').attr('id', headingId);
                $(this).find('.accordion-button').attr('data-bs-target', '#' + collapseId).attr('aria-controls', collapseId);
                $(this).find('.accordion-collapse').attr('id', collapseId).attr('aria-labelledby', headingId);
                
                $(this).find('input').each(function() {
                    const id = $(this).attr('id');
                    if (id) {
                        const newId = id.replace(/_\d+$/, `_${index + 1}`);
                        $(this).attr('id', newId);
                        $(this).prev('label').attr('for', newId);
                    }
                });
            });
        }
        
        function updateCourseCount() {
            const count = $('.course-accordion-item').length;
            $('#courseCount').text(`${count} Course${count !== 1 ? 's' : ''}`);
        }
        
        function updateRemoveCourseButtons() {
            const count = $('.course-accordion-item').length;
            if (count === 1) {
                $('.remove-course-btn').hide();
            } else {
                $('.remove-course-btn').show();
            }
        }
        
        function updateCourseTitle($input) {
            const name = $input.val().trim();
            const $title = $input.closest('.course-accordion-item').find('.course-title');
            
            if (name) {
                $title.text(name);
            } else {
                $title.text('New Course');
            }
        }
        
        function validateCourseForm() {
            let isValid = true;
            const errors = [];
            const existingCodes = [];
            const existingNames = [];
            
            const collegeId = $('#course_college_id').val();
            if (!collegeId) {
                $('#course_college_id').addClass('is-invalid');
                errors.push(`Please select a college`);
                isValid = false;
            } else {
                $('#course_college_id').removeClass('is-invalid');
            }
            
            $('.course-accordion-item').each(function() {
                const name = $(this).find('.course-name').val().trim();
                const code = $(this).find('.course-code').val().trim();
                const index = $(this).attr('data-index');
                
                $(this).find('.form-control').removeClass('is-invalid');
                
                if (!name) {
                    $(this).find('.course-name').addClass('is-invalid');
                    errors.push(`Course ${index}: Name is required`);
                    isValid = false;
                } else if (existingNames.includes(name.toLowerCase())) {
                    $(this).find('.course-name').addClass('is-invalid');
                    errors.push(`Course ${index}: Name "${name}" is already used in this form`);
                    isValid = false;
                } else {
                    existingNames.push(name.toLowerCase());
                }
                
                if (!code) {
                    $(this).find('.course-code').addClass('is-invalid');
                    errors.push(`Course ${index}: Code is required`);
                    isValid = false;
                } else if (existingCodes.includes(code.toUpperCase())) {
                    $(this).find('.course-code').addClass('is-invalid');
                    errors.push(`Course ${index}: Code "${code}" is already used in this form`);
                    isValid = false;
                } else {
                    existingCodes.push(code.toUpperCase());
                }
            });
            
            if (!isValid) {
                showCourseAlert('error', 'Validation Error', 'Please fix the following errors:\n\n' + errors.join('\n'));
            }
            
            return isValid;
        }
        
        function resetCourseForm() {
            $('#coursesAccordion').html(`
                <div class="accordion-item course-accordion-item active" data-index="1">
                    <h2 class="accordion-header" id="courseHeadingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#courseCollapseOne" aria-expanded="true" aria-controls="courseCollapseOne">
                            <span class="course-counter">1</span>
                            <span class="course-title">New Course</span>
                        </button>
                    </h2>
                    <div id="courseCollapseOne" class="accordion-collapse collapse show" aria-labelledby="courseHeadingOne" data-bs-parent="#coursesAccordion">
                        <div class="accordion-body">
                            <button type="button" class="remove-course-btn" title="Remove this course">
                                <i class="icon-trash-2"></i>
                            </button>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="course_name_1" class="form-label">Course Name *</label>
                                    <input type="text" class="form-control course-name" id="course_name_1" 
                                           name="name" placeholder="e.g., Bachelor of Science in Computer Science" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="course_code_1" class="form-label">Course Code *</label>
                                    <input type="text" class="form-control course-code" id="course_code_1" 
                                           name="code" placeholder="e.g., BSCS" 
                                           style="text-transform: uppercase;" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            courseIndex = 1;
            $('#course_college_id').val('');
            updateCourseCount();
            $('.form-control').removeClass('is-invalid');
            updateRemoveCourseButtons();
        }
        
        $('#addCoursesForm').on('submit', function(e) {
            e.preventDefault();
            
            if (!validateCourseForm()) {
                return;
            }
            
            const $submitBtn = $('#courseSubmitBtn');
            $submitBtn.addClass('btn-loading').prop('disabled', true);
            
            // For single course submission, we'll submit one course at a time
            const firstCourse = $('.course-accordion-item').first();
            const formData = {
                name: firstCourse.find('.course-name').val(),
                code: firstCourse.find('.course-code').val(),
                college_id: $('#course_college_id').val(),
                _token: $('input[name="_token"]').val()
            };
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showCourseAlert('success', 'Success!', response.message || 'Course has been added successfully to the system.')
                            .then((result) => {
                                resetCourseForm();
                                $('#addCoursesModal').modal('hide');
                                location.reload();
                            });
                    } else {
                        let errorMsg = response.message || 'An error occurred while processing your request.';
                        if (response.errors) {
                            errorMsg += '\n\n' + Object.values(response.errors).flat().join('\n');
                        }
                        showCourseAlert('error', 'Operation Failed', errorMsg);
                    }
                },
                error: function(xhr) {
                    console.error('Submit error:', xhr);
                    let errorMessage = 'An error occurred while saving course to the system.';
                    
                    if (xhr.status === 422) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('\n\n');
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showCourseAlert('error', 'Error Occurred', errorMessage);
                },
                complete: function() {
                    $submitBtn.removeClass('btn-loading').prop('disabled', false);
                }
            });
        });
        
        $('#addCoursesModal').on('hidden.bs.modal', function() {
            if (!$('#courseSubmitBtn').hasClass('btn-loading')) {
                resetCourseForm();
            }
        });
        
        updateRemoveCourseButtons();
    });
</script>
@endpush