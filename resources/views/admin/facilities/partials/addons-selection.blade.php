 <div class="wg-box" id="addonsBox">
     <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
         <h4>Addons</h4>
     </div>

     <div id="selectedAddonsContainer" class="mt-3 d-flex flex-wrap gap-2">
         @if (isset($facility) && $facility->addons)
             @foreach ($facility->addons as $addon)
                 <span class="badge bg-primary px-3 py-2" data-id="{{ $addon->id }}" style="cursor: pointer;">
                     {{ $addon->name }}
                 </span>
             @endforeach
         @endif
     </div>
     <div class="addons-scroll mt-3 p-3 border rounded d-flex overflow-auto gap-3">
         @foreach ($addons as $addon)
             <div class="addon-item flex-shrink-0 px-3 py-2 border rounded text-center
            {{ isset($facility) && $facility->addons->contains($addon->id) ? 'text-decoration-line-through' : '' }}"
                 data-id="{{ $addon->id }}" data-name="{{ $addon->name }}"
                 style="cursor: pointer; min-width: 120px;">
                 {{ $addon->name }}
             </div>
         @endforeach
     </div>
     <input type="hidden" name="addons" id="selectedAddonsInput"
         value="{{ isset($facility) ? $facility->addons->pluck('id')->implode(',') : '' }}">
 </div>
