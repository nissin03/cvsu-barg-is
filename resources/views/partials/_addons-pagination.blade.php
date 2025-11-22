<div class="pagination-container" style="padding: 15px 0; overflow: visible; min-height: 60px;">
    {{ $addons->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
