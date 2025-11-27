{{-- resources/views/admin/signature/partials/_signatures-pagination.blade.php --}}
<div class="pagination-container" style="padding: 15px 0; overflow: visible; min-height: 60px;">
    {{ $signatures->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
