{{-- resources/views/admin/signature/partials/_signatures-table.blade.php --}}
@forelse ($signatures as $signature)
    <tr>
        <td class="label-cell">
            {{ $signature->label }}
        </td>
        <td class="name-cell">
            <div class="name text-truncate" title="{{ $signature->name }}">
                <strong>{{ $signature->name }}</strong>
            </div>
        </td>
        <td class="position-cell">
            {{ $signature->position }}
        </td>
        <td class="category-cell">
            <span class="badge {{ $signature->category == 'facility' ? 'badge-info' : 'badge-warning' }}">
                {{ ucfirst($signature->category) }}
            </span>
        </td>
        <td class="report-type-cell">
            <span class="badge bg-dark text-center">
                {{ ucfirst($signature->report_type) }}
            </span>
        </td>
        <td class="order-cell">
            #{{ $signature->order_by }}
        </td>
        <td class="status-cell">
            <span class="badge {{ $signature->is_active ? 'badge-success' : 'badge-secondary' }}">
                {{ $signature->is_active ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="action-cell">
            <div class="list-icon-function">
                <a href="{{ route('admin.signatures.edit', $signature->id) }}" title="Edit Signature">
                    <div class="item edit">
                        <i class="icon-edit-3"></i>
                    </div>
                </a>
                <form action="{{ route('admin.signatures.destroy', $signature->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="item text-warning archive" style="border: none; background: none;"
                        title="Archive Signature">
                        <i class="icon-archive"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center empty-state-table">
            <div class="empty-icon">
                <i class="icon-file-text"></i>
            </div>
            <h5>No Signatures Found</h5>
            <p>No signatures match your current filters.</p>
            <a href="{{ route('admin.signatures.create') }}" class="btn btn-primary">
                <i class="icon-plus"></i> Add New Signature
            </a>
        </td>
    </tr>
@endforelse
