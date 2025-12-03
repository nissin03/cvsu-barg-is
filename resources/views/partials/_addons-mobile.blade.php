{{-- resources/views/admin/add-ons/partials/_addons-mobile.blade.php --}}
@forelse ($addons as $addon)
    <div class="mobile-card">
        <div class="mobile-card-header">
            <h5 class="mobile-card-title">{{ $addon->name }}</h5>
            <span class="badge {{ $addon->is_available ? 'badge-success' : 'badge-secondary' }}">
                {{ $addon->is_available ? 'Available' : 'Unavailable' }}
            </span>
        </div>
        <div class="mobile-card-body">
            <div class="mobile-card-details">
                <p><strong>Price:</strong> ₱{{ number_format($addon->base_price, 2) }}
                    ({{ ucfirst(str_replace('_', ' ', $addon->price_type)) }})
                </p>
                <p><strong>Type:</strong>
                    {{ $addon->is_based_on_quantity ? 'Quantity-based' : 'Flat rate' }}</p>
                <p><strong>Refundable:</strong> {{ $addon->is_refundable ? 'Yes' : 'No' }}</p>
                <p><strong>Show:</strong>
                    <span class="badge {{ $addon->show == 'staff' ? 'badge-purple' : 'badge-info' }}">
                        {{ ucfirst($addon->show) }}
                    </span>
                </p>
            </div>
            <div class="mobile-card-actions">
                <a href="{{ route('admin.addons.edit', $addon->id) }}" class="btn btn-sm btn-primary mobile-btn">
                    <i class="icon-edit-3"></i> Edit
                </a>
                <form action="{{ route('admin.addons.destroy', $addon->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-warning delete mobile-btn">
                        <i class="icon-archive"></i> Archive
                    </button>
                </form>
            </div>
        </div>
    </div>
@empty
    <div class="empty-state">
        <div class="empty-icon">
            <i class="icon-package"></i>
        </div>
        <h4>No Addons Found</h4>
        <p>Start by creating your first addon.</p>
        <a href="{{ route('admin.addons.create') }}" class="btn btn-primary">
            <i class="icon-plus"></i> Add New Addon
        </a>
    </div>
@endforelse
