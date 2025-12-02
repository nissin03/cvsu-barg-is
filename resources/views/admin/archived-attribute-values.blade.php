@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Archived Product Variants</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <a href="{{ route('admin.products') }}">
                            <div class="text-tiny">Products</div>
                        </a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <div class="text-tiny">Archived Variants</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="table-all-user table-responsive">
                    @if (Session::has('status'))
                        <p class="alert alert-success">{{ Session::get('status') }}</p>
                    @endif

                    <table class="table table-striped table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Variant Name</th>
                                <th>Attribute Type</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Archived Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($archivedVariants as $variant)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="name">
                                            <strong>{{ $variant->value }}</strong>
                                            @if ($variant->description)
                                                <br>
                                                <small
                                                    class="text-muted">{{ Str::limit($variant->description, 50) }}</small>
                                            @endif
                                            <br>
                                            <small class="text-muted">Product:
                                                {{ $variant->product->name ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-info">{{ $variant->productAttribute->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">₱{{ number_format($variant->price, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $variant->quantity }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $variant->deleted_at->format('M d, Y h:i A') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="list-icon-function">
                                            <form action="{{ route('admin.variant.restore', ['id' => $variant->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-success restore">
                                                    <i class="icon-rotate-ccw"></i> Restore
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="icon-archive" style="font-size: 48px; color: #ccc;"></i>
                                            <p class="text-muted mt-3">No archived variants found.</p>
                                            <a href="{{ route('admin.products') }}" class="btn btn-primary mt-2">Return to
                                                Products</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    {{ $archivedVariants->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
