 <div class="modal fade" id="addPrice" tabindex="-1" aria-labelledby="addPriceLabel">
     <div class="modal-dialog">
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
