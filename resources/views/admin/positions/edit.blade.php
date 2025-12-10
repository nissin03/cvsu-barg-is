@extends('layouts.admin')
@section('content')
    <style>
        .positions-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
            margin-bottom: 32px
        }

        .position-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            transition: all .3s ease
        }

        .position-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12)
        }

        .position-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #e2e8f0
        }

        .position-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9
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
            border: 2px solid #e2e8f0
        }

        .position-title {
            color: #334155;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            flex-grow: 1;
            margin-left: 16px
        }

        .position-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px
        }

        @media (max-width:768px) {
            .position-fields {
                grid-template-columns: 1fr
            }

            .position-header {
                flex-direction: column;
                gap: 12px;
                text-align: center
            }

            .position-title {
                margin-left: 0
            }
        }

        .field-group {
            position: relative
        }

        .field-label {
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            display: block
        }

        .required-asterisk {
            color: #dc2626;
            margin-left: 4px
        }

        .field-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #334155;
            font-size: 16px;
            transition: all .2s ease;
            box-sizing: border-box
        }

        .field-input::placeholder {
            color: #94a3b8
        }

        .field-input:focus {
            outline: none;
            border-color: #94a3b8;
            box-shadow: 0 0 0 3px rgba(148, 163, 184, .15)
        }

        .error-message {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            display: block;
            background: #fef2f2;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #fecaca
        }

        .save-btn {
            background: #334155;
            color: #fff;
            border: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all .3s ease
        }

        .save-btn:hover:not(:disabled) {
            background: #475569
        }

        .save-btn:disabled {
            opacity: .7;
            cursor: not-allowed
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 32px;
            flex-wrap: wrap;
            gap: 16px
        }

        .cancel-btn {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all .3s ease;
            text-decoration: none;
            display: inline-block
        }

        .cancel-btn:hover {
            background: #e2e8f0;
            border-color: #94a3b8
        }

        @media (max-width:768px) {
            .form-actions {
                flex-direction: column;
                align-items: stretch
            }

            .cancel-btn,
            .save-btn {
                width: 100%;
                text-align: center
            }
        }

        .form-container {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
            border: 1px solid #e2e8f0
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Edit Position</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <a href="{{ route('positions.index') }}">
                            <div class="text-tiny">Positions</div>
                        </a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <div class="text-tiny">Edit Position</div>
                    </li>
                </ul>
            </div>

            <div class="form-container">
                <form id="positionForm" action="{{ route('positions.update', $position->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div id="positions-container" class="positions-container">
                        <div class="position-card" id="position-0">
                            <div class="position-header">
                                <div class="position-number">1</div>
                                <h4 class="position-title">Edit Position</h4>
                            </div>

                            <div class="position-fields">
                                <div class="field-group">
                                    <label class="field-label">
                                        Position Name <span class="required-asterisk">*</span>
                                    </label>
                                    <input class="field-input @error('name') error @enderror" type="text"
                                        placeholder="Enter position name" name="name"
                                        value="{{ old('name', $position->name) }}" required>
                                    @error('name')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label class="field-label">
                                        Position Code <span class="required-asterisk">*</span>
                                    </label>
                                    <input class="field-input @error('code') error @enderror" type="text"
                                        placeholder="Enter position code" name="code"
                                        value="{{ old('code', $position->code) }}" required>
                                    @error('code')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('positions.index') }}" class="cancel-btn">
                            <i class="icon-x"></i> Cancel
                        </a>

                        <button class="tf-button w208" type="submit">
                            <i class="icon-save"></i> Update Position
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
