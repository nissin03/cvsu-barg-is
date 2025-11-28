@extends('layouts.app')
@section('content')
    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="Shopping Cart" />
    <main class="container my-5">
        <h2 class="mb-4">Shopping Cart</h2>
        @if (session('error'))
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
                <div id="errorToast" class="toast align-items-center text-bg-danger border-0 show" role="alert"
                    aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ session('error') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">

            <div class="col-md-12">
                @if ($items->count() > 0)
                    <div class="table-responsive">
                        <table class="table cart-table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Variations</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    @php
                                        $product = \App\Models\Product::with('attributeValues.productAttribute')->find(
                                            $item->id,
                                        );
                                        $groupedAttributes = [];
                                        $uniqueAttributes = [];
                                        foreach ($product->attributeValues as $value) {
                                            if ($value->productAttribute) {
                                                $attributeId = $value->product_attribute_id;
                                                $groupedAttributes[$attributeId][] = $value;
                                                if (!isset($uniqueAttributes[$attributeId])) {
                                                    $uniqueAttributes[$attributeId] = $value->productAttribute;
                                                }
                                            }
                                        }
                                    @endphp
                                    <tr id="cart-item-{{ $item->rowId }}">
                                        <td>
                                            <div class="shopping-cart__product-item d-flex align-items-center">
                                                <img loading="lazy"
                                                    src="{{ asset('uploads/products/thumbnails') }}/{{ $item->model->image }}"
                                                    width="80" height="80" alt="{{ $item->name }}" class="me-3">
                                                <div>
                                                    <p class="mb-1"
                                                        style="font-size: 14px; font-weight: normal; color: #333;">
                                                        {{ $item->name }}</p>
                                                    @if (isset($item->options['variant_attributes']) && !empty($item->options['variant_attributes']))
                                                        <p class="mb-0" style="font-size: 12px; color: #999;">
                                                            @foreach ($item->options['variant_attributes'] as $attributeName => $value)
                                                                {{ $attributeName }}: {{ $value }}@if (!$loop->last)
                                                                    ,
                                                                @endif
                                                            @endforeach
                                                        </p>
                                                    @else
                                                        <p class="mb-0" style="font-size: 12px; color: #999;">No variant
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="shopping-cart__product-item__detail">
                                                <form
                                                    action="{{ route('cart.item.updateVariant', ['rowId' => $item->rowId]) }}"
                                                    method="POST" class="variant-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="variant-selection">
                                                        @foreach ($uniqueAttributes as $attributeId => $attribute)
                                                            <div class="mb-2">
                                                                <label
                                                                    style="font-size: 12px; color: #333;">{{ $attribute->name }}:</label>
                                                                <select name="attribute[{{ $attributeId }}]"
                                                                    class="form-select form-select-sm variant-select"
                                                                    style="width: auto; display: inline-block;">
                                                                    @if (isset($groupedAttributes[$attributeId]))
                                                                        @foreach ($groupedAttributes[$attributeId] as $variant)
                                                                            <option value="{{ $variant->id }}"
                                                                                @if (isset($item->options['variant_attributes'][$attribute->name]) &&
                                                                                        $item->options['variant_attributes'][$attribute->name] == $variant->value) selected @endif>
                                                                                {{ $variant->value }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="variant-actions" style="display: none;">
                                                        <button type="button"
                                                            class="btn btn-secondary btn-sm cancel-variant">CANCEL</button>
                                                        <button type="submit"
                                                            class="btn btn-primary btn-sm confirm-variant">CONFIRM</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                        <td><span class="shopping-cart__subtotal">&#8369;
                                                {{ number_format($item->price, 2) }}</span></td>
                                        <td>
                                            @php
                                                $maxQuantity =
                                                    isset($item->options['is_variant']) && $item->options['is_variant']
                                                        ? $item->options['variant_quantity']
                                                        : $item->model->quantity;
                                            @endphp
                                            <div class="product-single__qtytocart" id="cart-item-{{ $item->rowId }}">
                                                <div class="qty-control cart-qty-control position-relative">
                                                    <input type="number" name="quantity" value="{{ $item->qty }}"
                                                        min="1" max="{{ $maxQuantity }}"
                                                        class="qty-control__number text-center" readonly>

                                                    <button type="button"
                                                        class="qty-control__reduce cart-qty-control__reduce"
                                                        data-row-id="{{ $item->rowId }}">-</button>
                                                    <button type="button"
                                                        class="qty-control__increase cart-qty-control__increase"
                                                        data-row-id="{{ $item->rowId }}">+</button>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span
                                                class="item-total">&#8369;{{ number_format($item->price * $item->qty, 2) }}</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('cart.item.remove', ['rowId' => $item->rowId]) }}"
                                                method="post" class="remove-item-form">
                                                @csrf
                                                @method('DELETE')
                                                <a href="javascript:void(0)" class="remove-cart btn-action"
                                                    data-row-id="{{ $item->rowId }}"
                                                    data-item-name="{{ $item->name }}">
                                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="#767676"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M0.259435 8.85506L9.11449 0L10 0.885506L1.14494 9.74056L0.259435 8.85506Z" />
                                                        <path
                                                            d="M0.885506 0.0889838L9.74057 8.94404L8.85506 9.82955L0 0.97449L0.885506 0.0889838Z" />
                                                    </svg>
                                                </a>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
            </div>

            <div class="col-md-12">
                <div class="d-flex justify-content-between mb-4">
                    <button class="btn btn-black">
                        <a href="{{ route('shop.index') }}" class="text-white">Continue Shopping</a>
                    </button>
                    <form action="{{ route('cart.empty') }}" method="post" id="clear-cart-form">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-black text-uppercase" type="button" id="clear-cart-btn">CLEAR CART</button>
                    </form>
                </div>
                <div class="cart-total-container">
                    <div class="cart-total">
                        <h5>Cart Total</h5>
                        <p>Total: <span id="total">{{ Cart::total() }}</span></p>
                        <a href="{{ route('cart.checkout') }}" class="btn btn-black w-100">Process Checkout</a>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="com-md-12 text-center pt-5 pb-5">
                    <p class="text-text-info">No item found in you cart</p>
                    <a href="{{ route('shop.index') }}" class="btn-btn-info  text-dark">Shop Now</a>
                </div>
            </div>
            @endif
        </div>
    </main>
@endsection

@push('scripts')
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Handle remove item click with SweetAlert confirmation
            $(".remove-cart").on("click", function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const itemName = $(this).data('item-name');

                Swal.fire({
                    title: 'Remove Item',
                    text: `Are you sure you want to remove "${itemName}" from your cart?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Handle clear cart with confirmation
            $("#clear-cart-btn").on("click", function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Clear Cart',
                    text: 'Are you sure you want to remove all items from your cart?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, clear cart!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#clear-cart-form").submit();
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const variantSelects = document.querySelectorAll('.variant-select');
            const variantForms = document.querySelectorAll('.variant-form');
            const decreaseBtns = document.querySelectorAll('.cart-qty-control__reduce');
            const increaseBtns = document.querySelectorAll('.cart-qty-control__increase');

            variantSelects.forEach(select => {
                select.addEventListener('change', function() {
                    // Show the confirm and cancel buttons when variant is selected
                    const form = this.closest('.variant-form');
                    const variantActions = form.querySelector('.variant-actions');
                    variantActions.style.display = 'block';

                    // Handle form submission for updating the variant
                    form.querySelector('.confirm-variant').addEventListener('click', function(
                        event) {
                        event.preventDefault();
                        form.submit();
                    });

                    form.querySelector('.cancel-variant').addEventListener('click', function(
                        event) {
                        event.preventDefault();
                        variantActions.style.display = 'none';
                    });
                });
            });

            function updateCart(rowId, action) {
                // Get current quantity
                const currentQty = parseInt(document.querySelector(`#cart-item-${rowId} .qty-control__number`)
                    .value);
                const itemName = document.querySelector(`#cart-item-${rowId} .shopping-cart__product-item p`)
                    .textContent.trim();

                // If decreasing and current quantity is 1, show confirmation
                if (action === 'decrease' && currentQty === 1) {
                    Swal.fire({
                        title: 'Remove Item',
                        text: `This will remove "${itemName}" from your cart. Are you sure?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, remove it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Proceed with the decrease which will remove the item
                            performCartUpdate(rowId, action);
                        }
                    });
                    return;
                }

                // For other cases, proceed normally
                performCartUpdate(rowId, action);
            }

            function performCartUpdate(rowId, action) {
                fetch(`/cart/qty/${action}/${rowId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // If item was removed (quantity became 0), remove the row from DOM
                            if (data.newQty === 0 || data.removed) {
                                const row = document.querySelector(`#cart-item-${rowId}`);
                                if (row) {
                                    row.remove();
                                }
                                // Check if cart is empty and reload page
                                const remainingItems = document.querySelectorAll('.cart-table tbody tr');
                                if (remainingItems.length === 0) {
                                    location.reload();
                                }
                            } else {
                                // Update the quantity and totals
                                document.querySelector(`#cart-item-${rowId} .qty-control__number`).value = data
                                    .newQty;
                                document.querySelector(`#cart-item-${rowId} .item-total`).textContent =
                                    `₱${data.itemTotal}`;
                            }

                            // Update cart total
                            document.querySelector('#total').textContent = `₱${data.total}`;
                        } else {
                            // Show error message
                            Swal.fire({
                                title: 'Error',
                                text: data.error || 'An error occurred while updating the cart.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while updating the cart.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            }

            decreaseBtns.forEach(btn => {
                btn.onclick = function(e) {
                    e.preventDefault();
                    const rowId = this.getAttribute('data-row-id');
                    updateCart(rowId, 'decrease');
                };
            });

            increaseBtns.forEach(btn => {
                btn.onclick = function(e) {
                    e.preventDefault();
                    const rowId = this.getAttribute('data-row-id');
                    updateCart(rowId, 'increase');
                };
            });
        });
    </script>
@endpush
