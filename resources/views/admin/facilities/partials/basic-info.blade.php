{{-- resources/views/admin/facilities/partials/basic-info.blade.php --}}
<div class="wg-box">
    <div class="container mx-auto p-3" style="{{ $errors->any() ? '' : 'display: none;' }}">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <fieldset class="name">
        <div class="body-title mb-10">Facility name <span class="tf-color-1">*</span></div>
        <input class="form-control" type="text" value="{{ old('name', $facility->name ?? '') }}"
            placeholder="Facility name ..." name="name" tabindex="0" required>
    </fieldset>
    @error('name')
        <span class="alert alert-danger text-center">{{ $message }}</span>
    @enderror

    <div class="gap22 cols">
        <fieldset class="type">
            <div class="body-title mb-10">Facility Type<span class="tf-color-1">*</span></div>
            <div class="select">
                <select id="rentalType" name="facility_type" required {{ isset($facility) ? 'disabled' : '' }}>
                    <option value="" selected disabled>Choose Facility Type...</option>
                    <option value="individual"
                        {{ old('facility_type', $facility->facility_type ?? '') === 'individual' ? 'selected' : '' }}>
                        Individual
                    </option>
                    <option value="whole_place"
                        {{ old('facility_type', $facility->facility_type ?? '') === 'whole_place' ? 'selected' : '' }}>
                        Whole Place
                    </option>
                    <option value="both"
                        {{ old('facility_type', $facility->facility_type ?? '') === 'both' ? 'selected' : '' }}>
                        Both
                    </option>
                </select>
            </div>

            {{-- Hidden input to ensure the value is submitted when disabled --}}
            @if (isset($facility))
                <input type="hidden" name="facility_type" value="{{ $facility->facility_type }}">
                <small class="text-muted mt-1 d-block">
                    <i class="bi bi-info-circle me-1"></i>
                    Facility type cannot be changed in edit mode to preserve data integrity.
                </small>
            @endif
        </fieldset>
    </div>
    @error('facility_type')
        <span class="alert alert-danger text-center">{{ $message }}</span>
    @enderror

    {{-- Description --}}
    <fieldset class="description">
        <div class="body-title mb-10">Description <span class="tf-color-1">*</span></div>
        <textarea class="mb-10" name="description" placeholder="Description" tabindex="0" aria-required="true"
            required="">{{ old('description', $facility->description ?? '') }}</textarea>
    </fieldset>
    @error('description')
        <span class="alert alert-danger text-center">{{ $message }}</span>
    @enderror

    <fieldset class="rules_and_regulations">
        <div class="body-title mb-10">Rules and Regulation <span class="tf-color-1">*</span></div>
        <textarea class="mb-10" id="rules" name="rules_and_regulations" placeholder="rules_and_regulations" tabindex="0"
            aria-required="true">{{ old('rules_and_regulations', $facility->description ?? '') }}</textarea>
    </fieldset>
    @error('rules_and_regulations')
        <span class="alert alert-danger text-center">{{ $message }}</span>
    @enderror
</div>
