{{-- resources/views/admin/signature/partials/_signatures-mobile.blade.php --}}
@forelse ($signatures as $signature)
    <div class="mobile-card">
        <div class="mobile-card-header">
            <h5 class="mobile-card-title">{{ $signature->name }}</h5>
            <span class="badge {{ $signature->is_active ? 'badge-success' : 'badge-secondary' }}">
                {{ $signature->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="mobile-card-body">
            <div class="mobile-card-details">
                <p><strong>Label:</strong> {{ $signature->label }}</p>
                <p><strong>Position:</strong> {{ $signature->position }}</p>
                <p><strong>Category:</strong>
                    <span class="badge {{ $signature->category == 'facility' ? 'badge-info' : 'badge-warning' }}">
                        {{ ucfirst($signature->category) }}
                    </span>
                </p>
                <p><strong>Report Type:</strong>
                    <span class="badge bg-dark">{{ ucfirst($signature->report_type) }}</span>
                </p>
                <p><strong>Order:</strong> #{{ $signature->order_by }}</p>
            </div>
            <div class="mobile-card-actions">
                <a href="{{ route('admin.signatures.edit', $signature->id) }}" class="btn btn-sm btn-primary mobile-btn">
                    <i class="icon-edit-3"></i> Edit
                </a>
                <form action="{{ route('admin.signatures.destroy', $signature->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-warning archive mobile-btn">
                        <i class="icon-archive"></i> Archive
                    </button>
                </form>
            </div>
        </div>
    </div>
@empty
    <div class="empty-state">
        <div class="empty-icon">
            <i class="icon-file-text"></i>
        </div>
        <h4>No Signatures Found</h4>
        <p>No signatures match your current filters.</p>
        <a href="{{ route('admin.signatures.create') }}" class="btn btn-primary">
            <i class="icon-plus"></i> Add New Signature
        </a>
    </div>
@endforelse
