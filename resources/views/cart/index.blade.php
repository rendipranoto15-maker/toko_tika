@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <nav class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <span>Keranjang</span>
        </nav>

        <div class="cart-page-header">
            <div>
                <span class="cart-page-badge">Keranjang Belanja</span>
                <h2>Keranjang Kamu</h2>
                <p>Periksa kembali produk sebelum lanjut ke checkout.</p>
            </div>
        </div>

        @if($items->count())
            @php
                $grandTotal = 0;
            @endphp

            <div class="cart-modern-layout flex flex-col lg:flex-row gap-8 items-start">
                <div class="cart-items-card w-full lg:flex-1">
                    @foreach($items as $item)
                        @php
                            $price = $item->variant ? $item->variant->price : $item->product->price;
                            $subtotal = $price * $item->quantity;
                            $grandTotal += $subtotal;

                            // Calculate max allowable order quantity (in packets/units for form validation)
                            if ($item->variant && $item->variant->weight) {
                                $maxOrderQty = (int) floor($item->product->base_stock / $item->variant->weight);
                                $orderUnit = 'paket';
                            } else {
                                if ($item->product->stock_unit === 'kg') {
                                    $maxOrderQty = (int) floor($item->product->base_stock / 1000);
                                    $orderUnit = 'kg';
                                } else {
                                    $maxOrderQty = (int) $item->product->base_stock;
                                    $orderUnit = $item->product->stock_unit ?? 'pcs';
                                }
                            }

                            // Calculate display values in kg/grams or original unit
                            if ($item->product->stock_unit === 'kg') {
                                $displayStock = $item->product->base_stock / 1000;
                                $displayUnit = 'kg';
                                $waitingRestockQty = $item->waiting_restock_quantity / 1000;
                            } else {
                                $displayStock = (int) $item->product->base_stock;
                                $displayUnit = $item->product->stock_unit ?? 'pcs';
                                $waitingRestockQty = $item->waiting_restock_quantity;
                            }

                            $formattedDisplayStock = is_float($displayStock) 
                                ? rtrim(rtrim(number_format($displayStock, 2, '.', ''), '0'), '.') 
                                : $displayStock;

                            $formattedWaitingRestockQty = is_float($waitingRestockQty) 
                                ? rtrim(rtrim(number_format($waitingRestockQty, 2, '.', ''), '0'), '.') 
                                : $waitingRestockQty;
                        @endphp

                        <div class="cart-modern-item flex flex-col sm:flex-row gap-4 p-4 mb-4 border border-gray-100 rounded-xl bg-white shadow-sm">
                            <div class="cart-product-thumb w-24 h-24 sm:w-28 sm:h-28 shrink-0 rounded-lg overflow-hidden border border-gray-100 flex items-center justify-center">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="cart-thumb-placeholder text-gray-300 text-3xl">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="cart-product-main flex-1 w-full min-w-0">
                                <div class="cart-product-title-row flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <h3 class="text-base font-semibold text-gray-900 truncate mb-1">{{ $item->product->name ?? 'Produk tidak tersedia' }}</h3>

                                        <div class="flex flex-wrap gap-2 items-center">
                                            @if($item->variant)
                                                <span class="cart-variant-pill px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs font-medium">
                                                    {{ $item->variant->variant_name }}
                                                </span>
                                            @else
                                                <span class="cart-variant-pill muted px-2 py-0.5 rounded bg-gray-50 text-gray-400 text-xs font-medium">
                                                    Tanpa varian
                                                </span>
                                            @endif
                                            @if($item->is_waiting_restock)
                                                <span class="cart-restock-pill px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-xs font-semibold">
                                                    Menunggu Restok
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="cart-remove-btn text-gray-400 hover:text-red-500 p-2 text-lg transition" title="Hapus produk">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                @if($item->is_waiting_restock)
                                    <div class="cart-restock-note flex gap-3 p-3 mt-3 rounded-lg bg-amber-50 text-amber-900 border border-amber-200">
                                        <i class="fas fa-triangle-exclamation text-amber-600 mt-0.5"></i>
                                        <div class="text-xs">
                                            <strong class="font-semibold block mb-0.5">Pesanan melebihi stok tersedia</strong>
                                            <p class="leading-relaxed">
                                                Stok tersedia saat ini:
                                                <strong>{{ $formattedDisplayStock }} {{ $displayUnit }}</strong>.
                                                Jumlah dipesan:
                                                <strong>{{ $item->quantity }} {{ $orderUnit }}</strong>.
                                                Menunggu restok:
                                                <strong>{{ $formattedWaitingRestockQty }} {{ $orderUnit }}</strong>.
                                            </p>
                                            <small class="block mt-1 text-[10px] text-amber-700">
                                                Estimasi restok:
                                                {{ $item->product->restock_estimation ?? '1 hari' }}.
                                            </small>
                                        </div>
                                    </div>
                                @endif

                                <div class="cart-product-meta grid grid-cols-3 gap-2 mt-4 py-2 border-y border-gray-50">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] text-gray-400 uppercase tracking-wider mb-0.5">Harga</span>
                                        <strong class="text-sm font-semibold text-gray-800">Rp {{ number_format($price, 0, ',', '.') }}</strong>
                                    </div>

                                    <div class="flex flex-col">
                                        <span class="text-[11px] text-gray-400 uppercase tracking-wider mb-0.5">Subtotal</span>
                                        <strong class="text-sm font-semibold text-emerald-600">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                                    </div>

                                    <div class="flex flex-col">
                                        <span class="text-[11px] text-gray-400 uppercase tracking-wider mb-0.5">Tersedia</span>
                                        <strong class="text-sm font-semibold text-gray-800">{{ $formattedDisplayStock }} {{ $displayUnit }}</strong>
                                    </div>
                                </div>

                                <div class="cart-product-footer mt-4">
                                    <form
                                        action="{{ route('cart.update', $item->id) }}"
                                        method="POST"
                                        class="cart-qty-modern-form flex flex-wrap items-center justify-between gap-3"
                                        data-available-stock="{{ $maxOrderQty }}"
                                    >
                                        @csrf

                                        {{-- ✅ FIX: sekarang checkbox, bukan hidden statis.
                                             User bisa aktif/nonaktifkan "tunggu restok" langsung dari cart. --}}
                                        <input
                                            type="hidden"
                                            name="allow_waiting_restock"
                                            class="cart-allow-restock-input"
                                            value="{{ $item->is_waiting_restock ? 1 : 0 }}"
                                        >

                                        <div class="cart-quantity-control flex items-center border border-gray-200 rounded-lg overflow-hidden min-h-[44px]">
                                            <button type="button" class="cart-qty-btn cart-minus w-10 h-10 flex items-center justify-center text-lg font-bold hover:bg-gray-50 transition">−</button>

                                            <input
                                                type="number"
                                                name="quantity"
                                                value="{{ $item->quantity }}"
                                                min="1"
                                                class="cart-qty-input w-12 h-10 text-center border-0 focus:ring-0 text-base font-semibold"
                                                readonly
                                            >

                                            <button type="button" class="cart-qty-btn cart-plus w-10 h-10 flex items-center justify-center text-lg font-bold hover:bg-gray-50 transition">+</button>
                                        </div>

                                        {{-- ✅ FIX: checkbox tampil otomatis lewat JS kalau quantity > stok tersedia --}}
                                        <label class="cart-restock-checkbox flex items-center gap-2 text-xs text-amber-700 bg-amber-50 px-3 py-2 rounded-lg cursor-pointer select-none" style="display:none;">
                                            <input type="checkbox" class="cart-restock-checkbox-input rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                            Tunggu restok
                                        </label>

                                        <button type="submit" class="cart-update-btn min-h-[44px] px-6 rounded-lg bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-700 transition">
                                            Update
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <aside class="cart-summary-modern">
                    <div class="cart-summary-header">
                        <span>Ringkasan Belanja</span>
                        <h3>Total Pesanan</h3>
                    </div>

                    <div class="cart-summary-row">
                        <span>Jumlah Item</span>
                        <strong>{{ $items->sum('quantity') }} item</strong>
                    </div>

                    <div class="cart-summary-row">
                        <span>Subtotal Produk</span>
                        <strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
                    </div>

                    <div class="cart-summary-note">
                        <i class="fas fa-circle-info"></i>
                        <p>Ongkir akan dihitung pada halaman checkout khusus area Bekasi Timur.</p>
                    </div>

                    <div class="cart-summary-grand">
                        <span>Total Belanja</span>
                        <strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
                    </div>

                    <div class="cart-summary-actions">
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary cart-checkout-btn">
                            Lanjut Checkout
                        </a>

                        <a href="{{ route('products.index') }}" class="btn btn-light cart-shop-btn">
                            Tambah Produk Lagi
                        </a>
                    </div>
                </aside>
            </div>
        @else
            <div class="empty-state cart-empty-modern">
                <div class="empty-icon">🛒</div>
                <h3>Keranjang masih kosong</h3>
                <p>Yuk pilih produk favoritmu terlebih dahulu.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Belanja Sekarang</a>
            </div>
        @endif
    </div>
</section>

@if($items->count())
    @php $barGrandTotal = 0; @endphp
    @foreach($items as $item)
        @php
            $barPrice = $item->variant ? $item->variant->price : $item->product->price;
            $barGrandTotal += $barPrice * $item->quantity;
        @endphp
    @endforeach
    <div class="cart-mobile-checkout-bar mobile-only">
        <div class="cart-bar-total">
            <span>Total Belanja</span>
            <strong>Rp {{ number_format($barGrandTotal, 0, ',', '.') }}</strong>
        </div>
        <a href="{{ route('checkout.index') }}" class="btn btn-primary">
            Checkout <i class="fas fa-arrow-right"></i>
        </a>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cart-qty-modern-form').forEach(function (form) {
        const input          = form.querySelector('.cart-qty-input');
        const minusBtn        = form.querySelector('.cart-minus');
        const plusBtn          = form.querySelector('.cart-plus');
        const restockLabel    = form.querySelector('.cart-restock-checkbox');
        const restockCheckbox = form.querySelector('.cart-restock-checkbox-input');
        const allowInput       = form.querySelector('.cart-allow-restock-input');
        const availableStock  = Number(form.dataset.availableStock) || 0;

        if (!input || !minusBtn || !plusBtn) return;

        // ✅ FIX: tampilkan/sembunyikan checkbox "tunggu restok" tergantung quantity vs stok
        function refreshRestockUI() {
            const qty = parseInt(input.value) || 1;

            if (qty > availableStock) {
                if (restockLabel) restockLabel.style.display = 'flex';
            } else {
                if (restockLabel) restockLabel.style.display = 'none';
                if (restockCheckbox) restockCheckbox.checked = false;
                if (allowInput) allowInput.value = '0';
            }
        }

        if (restockCheckbox && allowInput) {
            restockCheckbox.addEventListener('change', function () {
                allowInput.value = restockCheckbox.checked ? '1' : '0';
            });
        }

        minusBtn.addEventListener('click', function () {
            let currentValue = parseInt(input.value) || 1;
            let minValue = parseInt(input.getAttribute('min')) || 1;

            if (currentValue > minValue) {
                input.value = currentValue - 1;
            }
            refreshRestockUI();
        });

        plusBtn.addEventListener('click', function () {
            let currentValue = parseInt(input.value) || 1;
            input.value = currentValue + 1;
            refreshRestockUI();
        });

        // ✅ FIX: sebelum submit, kalau qty melebihi stok tapi checkbox belum dicentang -> cegah & minta centang dulu
        form.addEventListener('submit', function (event) {
            const qty = parseInt(input.value) || 1;

            if (qty > availableStock && (!restockCheckbox || !restockCheckbox.checked)) {
                event.preventDefault();
                alert('Jumlah melebihi stok tersedia (' + availableStock + '). Centang "Tunggu restok" untuk tetap memesan, atau kurangi jumlahnya.');
            }
        });

        refreshRestockUI();
    });
});
</script>
@endsection