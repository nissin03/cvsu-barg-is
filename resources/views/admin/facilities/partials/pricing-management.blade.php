<div id="dormitoryFields" class="d-flex justify-content-between align-items-center border-bottom pb-3">
    <h4>Prices</h4>
    <button type="button" data-bs-toggle="modal" data-bs-target="#addPrice">
        <i class="bi bi-plus-circle"></i> Add Price
    </button>
</div>


<div class="row mt-3 p-3 rounded d-flex justify-content-between align-items-center gap-3">
    <!-- Is Based on Days -->
    <div id="isBasedOnDaysContainer" class="flex-grow-1" style="display: none">
        <div class="d-flex align-items-center gap-2 mb-2">
            <input type="checkbox" class="is-based-on-days" id="isBasedOnDaysGlobal">
            <label for="isBasedOnDaysGlobal" class="mb-0">Is Based on Days?</label>
        </div>
        <div id="dateFieldsContainerGlobal" class="mt-3" style="display: none;">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="date_from_global">Date From:</label>
                    <input type="date" id="date_from_global" name="date_from_global" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="date_to_global">Date To:</label>
                    <input type="date" id="date_to_global" name="date_to_global" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Is There a Quantity -->
    <div id="isThereAQuantityContainer" class="flex-grow-1" style="display: none">
        <div class="d-flex align-items-center gap-2">
            <input type="checkbox" class="is-there-a-quantity" id="isThereAQuantityGlobal">
            <label for="isThereAQuantityGlobal" class="mb-0">Is There a Quantity?</label>
        </div>
    </div>
</div>

<p id="noPricesMessage" class="alert alert-warning mt-3">
    <i class="bi bi-info-circle me-2"></i> No prices added yet :(
</p>

<div id="priceTypeContainer" style="display: none;">
    <div class="d-flex align-items-center justify-items-center gap-4 text-white">
        <div class="box bg-primary"></div>
        <p>Individual</p>
        <div class="box bg-warning "></div>
        <p>Whole Place</p>
    </div>
</div>
<div id="priceContainer" class="mt-4">
    <div class="row" id="priceCardsContainer">
    </div>
</div>
@if ($errors->has('prices_json'))
    <span class="alert alert-danger text-center">{{ $errors->first('prices_json') }}</span>
@endif
