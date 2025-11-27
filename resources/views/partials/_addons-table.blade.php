{{-- resources/views/admin/add-ons/partials/_addons-table.blade.php --}}
@forelse ($addons as $addon)
    <tr>
        <td class="name-cell">
            <div class="name text-truncate" title="{{ $addon->name }}">
                <strong>{{ $addon->name }}</strong>
            </div>
            @if ($addon->description)
                <div class="text-tiny text-muted text-truncate" title="{{ $addon->description }}">
                    {{ Str::limit($addon->description, 50) }}
                </div>
            @endif
        </td>
        <td class="facility-cell">
            @if ($addon->facility)
                <span class="text-primary">{{ $addon->facility->name }}</span>
                <div class="text-tiny text-muted">ID: {{ $addon->facility->id }}</div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="price-cell">
            ₱ {{ number_format($addon->base_price, 2) }}
        </td>
        <td class="type-cell">
            {{ ucfirst(str_replace('_', ' ', $addon->price_type)) }}
        </td>
        <td class="quantity-cell">
            <span class="badge {{ $addon->is_based_on_quantity ? 'badge-info' : 'badge-secondary' }}">
                {{ $addon->is_based_on_quantity ? 'Yes' : 'No' }}
            </span>
        </td>
        <td class="status-cell">
            <span class="badge {{ $addon->is_available ? 'badge-success' : 'badge-secondary' }}">
                {{ $addon->is_available ? 'Available' : 'Unavailable' }}
            </span>
        </td>
        <td class="refundable-cell">
            <span class="badge {{ $addon->is_refundable ? 'badge-warning' : 'badge-secondary' }}">
                {{ $addon->is_refundable ? 'Yes' : 'No' }}
            </span>
        </td>
        <td class="action-cell">
            <div class="list-icon-function">
                <a href="{{ route('admin.addons.edit', $addon->id) }}" title="Edit Addon">
                    <div class="item edit">
                        <i class="icon-edit-3"></i>
                    </div>
                </a>
                <form action="{{ route('admin.addons.destroy', $addon->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="item text-warning delete" style="border: none; background: none;"
                        title="Archive Addon">
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
                <i class="icon-package"></i>
            </div>
            <h5>No Addons Found</h5>
            <p>No add-ons match your current filters.</p>
            <a href="{{ route('admin.addons.create') }}" class="btn btn-primary">
                <i class="icon-plus"></i> Add New Addon
            </a>
        </td>
    </tr>
@endforelse
