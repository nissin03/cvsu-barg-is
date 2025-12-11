@extends('layouts.admin')

@section('content')
    <style>
        .positions-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
            margin-bottom: 32px;
        }

        .position-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .position-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .position-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #e2e8f0;
        }

        .position-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .position-number {
            background: #f8fafc;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-weight: 600;
            font-size: 18px;
            border: 2px solid #e2e8f0;
        }

        .position-title {
            color: #334155;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            flex-grow: 1;
            margin-left: 16px;
        }

        .remove-position {
            background: #ac0927;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            color: #e3e6eb;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .remove-position:hover {
            background: #6b0409;
            color: #f2f3f5;
            border-color: #cbd5e1;
        }

        .position-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .position-fields {
                grid-template-columns: 1fr;
            }

            .position-header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }

            .position-title {
                margin-left: 0;
            }
        }

        .field-group {
            position: relative;
        }

        .field-label {
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .required-asterisk {
            color: #dc2626;
            margin-left: 4px;
        }

        .field-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            color: #334155;
            font-size: 16px;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .field-input::placeholder {
            color: #94a3b8;
        }

        .field-input:focus {
            outline: none;
            border-color: #94a3b8;
            box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.15);
        }

        .error-message {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            display: block;
            background: #fef2f2;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #fecaca;
        }

        .add-position-btn {
            background: white;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-position-btn:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .save-btn {
            background: #334155;
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .save-btn:hover:not(:disabled) {
            background: #475569;
        }

        .save-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .add-position-btn,
            .save-btn,
            .tf-button.w208 {
                width: 100%;
                justify-content: center;
            }
        }

        .breadcrumb-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .page-title {
            color: #1e293b;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: #64748b;
        }

        .breadcrumbs a {
            color: #475569;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumbs a:hover {
            color: #334155;
        }

        .breadcrumb-separator {
            color: #cbd5e1;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Positions</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <a href="{{ route('positions.index') }}">
                            <div class="text-tiny">Positions</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Add New Positions</div>
                    </li>
                </ul>
            </div>

            <div class="form-container">
                <form id="positionForm" action="{{ route('positions.store') }}" method="POST">
                    @csrf
                    <div id="positions-container" class="positions-container">
                        <div class="position-card" id="position-0">
                            <div class="position-header">
                                <div class="position-number">1</div>
                                <h4 class="position-title">Position #1</h4>
                                <button type="button" class="remove-position" data-id="0">
                                    <i class="icon-trash-2"></i>
                                    Remove
                                </button>
                            </div>

                            <div class="position-fields">
                                <div class="field-group">
                                    <label class="field-label">
                                        Position Name
                                        <span class="required-asterisk">*</span>
                                    </label>
                                    <input class="field-input" type="text" placeholder="Enter position name"
                                        name="positions[0][name]" tabindex="0" value="{{ old('positions.0.name') }}"
                                        aria-required="true" required>
                                    @error('positions.0.name')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label class="field-label">
                                        Position Code
                                        <span class="required-asterisk">*</span>
                                    </label>
                                    <input class="field-input" type="text" placeholder="Enter position code"
                                        name="positions[0][code]" tabindex="0" value="{{ old('positions.0.code') }}"
                                        aria-required="true" required>
                                    @error('positions.0.code')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" id="add-position" class="add-position-btn">
                            <i class="icon-plus"></i>
                            Add Another Position
                        </button>

                        <button class="tf-button w208" type="submit">
                            <i class="icon-save"></i>
                            Save All Positions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            function updatePositionNumbers() {
                $('.position-card').each(function(index) {
                    // id
                    $(this).attr('id', 'position-' + index);

                    // number + title
                    $(this).find('.position-number').text(index + 1);
                    $(this).find('.position-title').text('Position #' + (index + 1));

                    // remove button index
                    $(this).find('.remove-position').data('id', index);

                    // input names
                    $(this).find('input[name*="[name]"]').attr('name', 'positions[' + index + '][name]');
                    $(this).find('input[name*="[code]"]').attr('name', 'positions[' + index + '][code]');
                });
            }

            $('#add-position').click(function() {
                // use current count of visible cards
                const index = $('.position-card').length;

                const newEntry = `
                    <div class="position-card" id="position-${index}">
                        <div class="position-header">
                            <div class="position-number">${index + 1}</div>
                            <h4 class="position-title">Position #${index + 1}</h4>
                            <button type="button" class="remove-position" data-id="${index}">
                                <i class="icon-trash-2"></i>
                                Remove
                            </button>
                        </div>

                        <div class="position-fields">
                            <div class="field-group">
                                <label class="field-label">
                                    Position Name
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input
                                    class="field-input"
                                    type="text"
                                    placeholder="Enter position name"
                                    name="positions[${index}][name]"
                                    required
                                >
                            </div>

                            <div class="field-group">
                                <label class="field-label">
                                    Position Code
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input
                                    class="field-input"
                                    type="text"
                                    placeholder="Enter position code"
                                    name="positions[${index}][code]"
                                    required
                                >
                            </div>
                        </div>
                    </div>
                `;

                $('#positions-container').append(newEntry);

                const newCard = $(`#position-${index}`);
                newCard.css('opacity', '0').animate({
                    opacity: 1
                }, 300);

                // make sure all visible cards are numbered 1..N
                updatePositionNumbers();
            });

            $(document).on('click', '.remove-position', function() {
                // don't allow removing the last remaining card
                if ($('.position-card').length === 1) {
                    alert('You must have at least one position entry.');
                    return;
                }

                const id = $(this).data('id');
                const cardToRemove = $(`#position-${id}`);

                cardToRemove.animate({
                    opacity: 0,
                    height: 0,
                    marginBottom: 0,
                    paddingTop: 0,
                    paddingBottom: 0
                }, 300, function() {
                    cardToRemove.remove();
                    // re-number based on currently visible forms
                    updatePositionNumbers();
                });
            });

            $(document).on('focus', '.field-input', function() {
                $(this).parent().addClass('focused');
            });

            $(document).on('blur', '.field-input', function() {
                $(this).parent().removeClass('focused');
            });
        });
    </script>
@endpush
