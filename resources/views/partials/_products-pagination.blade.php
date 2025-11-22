@if ($products->total())
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <p class="mb-2">
            Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
        </p>
    </div>
@endif

{{ $products->withQueryString()->links('pagination::bootstrap-5') }}
