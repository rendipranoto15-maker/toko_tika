@extends('layouts.store')

@section('content')
<style>
    /* Keep theme colors brown */
    .detail-category {
        background: linear-gradient(135deg, #FFF9F3 0%, #F5EFE7 100%) !important;
        color: #7B4B2A !important;
        border: 1px solid #E8D8C6 !important;
    }
    .detail-price {
        color: #7B4B2A !important;
    }
    .detail-price::before {
        background: linear-gradient(to bottom, #A97142, #7B4B2A) !important;
    }
    .quantity-control:focus-within {
        border-color: #7B4B2A !important;
    }
    .qty-btn {
        color: #7B4B2A !important;
        background: #F5EFE7 !important;
    }
    .qty-btn:hover {
        background: #E8D8C6 !important;
        color: #2E1A0F !important;
    }
    .qty-box select:focus {
        border-color: #7B4B2A !important;
        box-shadow: 0 0 0 4px rgba(123, 75, 42, 0.15) !important;
    }
    .cart-action-btn {
        border-color: #7B4B2A !important;
        color: #7B4B2A !important;
    }
    .cart-action-btn:hover {
        background: #FFF9F3 !important;
        box-shadow: 0 8px 20px rgba(123, 75, 42, 0.15) !important;
    }
    .btn-buy-now-modern {
        background: linear-gradient(135deg, #A97142 0%, #7B4B2A 100%) !important;
        box-shadow: 0 6px 20px rgba(123, 75, 42, 0.25) !important;
        color: #ffffff !important;
    }
    .btn-buy-now-modern:hover {
        background: linear-gradient(135deg, #7B4B2A 0%, #5C3B1E 100%) !important;
        box-shadow: 0 10px 25px rgba(123, 75, 42, 0.35) !important;
    }
    .btn-back-modern {
        border-color: #E8D8C6 !important;
        color: #7B4B2A !important;
    }
    .btn-back-modern:hover {
        background: #F5EFE7 !important;
        border-color: #E8D8C6 !important;
        color: #2E1A0F !important;
    }
    .tab-btn.active {
        color: #7B4B2A !important;
    }
    .tab-btn.active::after {
        background: #7B4B2A !important;
    }
    .product-price {
        color: #7B4B2A !important;
    }
    .product-card:hover {
        border-color: #A97142 !important;
    }
</style>
<section class="section">
    <div class="container">

        <div class="product-detail-grid grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-start">
            <div class="product-detail-image-box">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-auto rounded-xl object-cover">
                @else
                    <img src="https://via.placeholder.com/700x600?text=Produk" alt="{{ $product->name }}" class="w-full h-auto rounded-xl object-cover">
                @endif
            </div>

            <div class="product-detail-info">
                <div class="detail-category-row">
                    <span class="detail-category bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-xs font-semibold">{{ $product->category->category_name ?? 'UMKM' }}</span>
                </div>

                <h1 class="detail-title">{{ $product->name }}</h1>

                <div class="detail-price" id="product-price">
                    @if($product->variants->count())
                        Mulai dari Rp {{ number_format($product->display_price, 0, ',', '.') }}
                    @else
                        Rp {{ number_format($product->display_price, 0, ',', '.') }}
                    @endif
                </div>

                <div class="detail-meta-box">
                    <div class="detail-meta-item">
                        <span class="meta-label">Stok</span>
                        {{-- ✅ FIX: id ditambahkan supaya JS bisa update teks stok sesuai varian yang dipilih --}}
                        <strong id="stock-display">{{ $product->stock_label }}</strong>
                    </div>
                    <div class="detail-meta-item">
                        <span class="meta-label">Kategori</span>
                        <strong>{{ $product->category->category_name ?? '-' }}</strong>
                    </div>
                </div>

                @auth
                    <form
                        id="detailCartForm"
                        action="{{ route('cart.add', $product->id) }}"
                        method="POST"
                        class="detail-cart-form"
                    >
                        @csrf
                        <input type="hidden" name="allow_waiting_restock" id="allow_waiting_restock" value="0">

                        @if($product->variants->count())
                            <div class="qty-box mb-4">
                                <label for="variant_id" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Berat</label>
                                {{-- ✅ FIX: data-stock ditambahkan di tiap option, supaya JS tahu stok PER VARIAN --}}
                                <select name="variant_id" id="variant_id" class="w-full min-h-[44px] p-3 border border-gray-300 rounded-lg text-base cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                    <option value="">-- Pilih Berat --</option>
                                    @foreach($product->variants as $variant)
                                        @php
                                            $variantStock = $variant->weight ? (int) floor($product->base_stock / $variant->weight) : 0;
                                        @endphp
                                        <option
                                            value="{{ $variant->id }}"
                                            data-price="{{ $variant->price }}"
                                            data-stock="{{ $variantStock }}"
                                        >
                                            {{ $variant->variant_name }} - Rp {{ number_format($variant->price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="qty-box mb-6">
                            <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">Jumlah</label>
                            <div class="quantity-control flex items-center border border-gray-300 rounded-lg max-w-[160px] overflow-hidden min-h-[44px]">
                                <button type="button" class="qty-btn qty-minus w-12 h-11 flex items-center justify-center text-lg font-bold border-r border-gray-300 hover:bg-gray-100 transition">−</button>
                                <input
                                    type="number"
                                    name="quantity"
                                    id="quantity"
                                    class="qty-input w-12 h-11 text-center border-0 focus:ring-0 text-base font-semibold"
                                    value="1"
                                    min="1"
                                    readonly
                                    required
                                >
                                <button type="button" class="qty-btn qty-plus w-12 h-11 flex items-center justify-center text-lg font-bold border-l border-gray-300 hover:bg-gray-100 transition">+</button>
                            </div>
                        </div>
                    </form>

                    <div class="product-action-modern flex flex-wrap md:flex-nowrap gap-3 items-center mt-6 w-full">
                        <button
                            type="submit"
                            form="detailCartForm"
                            formaction="{{ route('cart.add', $product->id) }}"
                            class="icon-action-btn cart-action-btn flex items-center justify-center min-w-[50px] min-h-[48px] rounded-lg border border-emerald-600 text-emerald-600 hover:bg-emerald-50 transition"
                            title="Tambah ke Keranjang"
                            aria-label="Tambah ke Keranjang"
                        >
                            <i class="fas fa-cart-shopping"></i>
                        </button>

                        <button
                            type="submit"
                            form="detailCartForm"
                            formaction="{{ route('cart.buyNow', $product->id) }}"
                            class="btn-buy-now-modern flex-1 min-h-[48px] px-6 rounded-lg bg-emerald-600 text-white font-semibold text-base hover:bg-emerald-700 transition"
                        >
                            Beli Sekarang
                        </button>

                        <a href="{{ route('products.index') }}" class="btn-back-modern flex-1 min-h-[48px] flex items-center justify-center px-6 rounded-lg border border-gray-300 text-gray-700 font-semibold text-base hover:bg-gray-50 transition">
                            Kembali
                        </a>
                    </div>
                @else
                    <div class="product-action-modern">
                        <a href="{{ route('login') }}" class="btn-buy-now-modern">
                            Login untuk Belanja
                        </a>
                        <a href="{{ route('products.index') }}" class="btn-back-modern">
                            Kembali
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Tab: Deskripsi & Layanan saja --}}
        <div class="product-info-tabs product-info-tabs-full">
            <div class="tab-buttons">
                <button type="button" class="tab-btn active" data-tab="desc">Deskripsi</button>
                <button type="button" class="tab-btn" data-tab="service">Layanan</button>
            </div>

            <div class="tab-content active" id="tab-desc">
                <p>{{ $product->description ?? 'Produk pilihan dari Toko Tika dengan kualitas terbaik untuk kebutuhan harian.' }}</p>
            </div>

            <div class="tab-content" id="tab-service">
                <div class="service-wrapper">

                    <h4 class="service-title">Metode Pembayaran</h4>

                    <div class="service-grid">
                        <div class="service-card">
                            <div class="service-icon">📱</div>

                            <div>
                                <h5>QRIS</h5>
                                <p>Pembayaran mudah menggunakan QRIS.</p>
                            </div>
                        </div>

                        <div class="service-card">
                            <div class="service-icon">💵</div>

                            <div>
                                <h5>COD</h5>
                                <p>Bayar langsung saat pesanan diterima.</p>
                            </div>
                        </div>
                    </div>

                    <h4 class="service-title" style="margin-top: 28px;">
                        Pengantaran / Pengambilan
                    </h4>

                    <div class="service-grid">
                        <div class="service-card">
                            <div class="service-icon">🛵</div>

                            <div>
                                <h5>Diantar Ojek Warung</h5>
                                <p>Pesanan diantar menggunakan ojek warung.</p>
                            </div>
                        </div>

                        <div class="service-card">
                            <div class="service-icon">🏪</div>

                            <div>
                                <h5>Ambil ke Warung</h5>
                                <p>Ambil pesanan langsung ke warung kami.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        {{-- Produk Terkait --}}
        @if($relatedProducts->count())
        <section class="related-products-section">
            <h3>Produk Terkait</h3>
            <div class="product-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($relatedProducts as $related)
                    <a href="{{ route('products.show', $related->slug) }}" class="product-card-link">
                        <div class="product-card clickable-product-card">
                            <div class="product-card-image">
                                @if($related->image)
                                    <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->name }}">
                                @else
                                    <img src="https://via.placeholder.com/400x300?text=Produk" alt="{{ $related->name }}">
                                @endif
                            </div>
                            <div class="product-card-body">
                                <h3 class="product-title">{{ $related->name }}</h3>
                                <div class="product-price">
                                    Rp {{ number_format($related->display_price ?? $related->price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>
</section>

{{-- Modal Stok --}}
<div class="stock-warning-modal" id="stockWarningModal">
    <div class="stock-warning-card">
        <button type="button" class="stock-warning-close" id="stockWarningClose">×</button>
        <div class="stock-warning-icon">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h3>Stok Belum Mencukupi</h3>
        <p>
            Maaf, stok <strong>{{ $product->name }}</strong> saat ini hanya
            <strong id="modalStockText">{{ $product->stock_quantity }} {{ $product->stock_unit }}</strong>.
        </p>
        <p>
            Produk sedang dalam proses restok dan diperkirakan tersedia dalam
            <strong>{{ $product->restock_estimation ?? '1 hari' }}</strong>.
        </p>
        <div class="stock-warning-actions">
            <button type="button" class="btn btn-light" id="reduceToStockBtn">
                Kurangi ke stok tersedia
            </button>
            <button type="button" class="btn btn-primary" id="waitRestockBtn">
                Tetap Pesan & Tunggu Restok
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const variantSelect = document.getElementById('variant_id');
    const priceElement  = document.getElementById('product-price');
    const stockDisplay  = document.getElementById('stock-display');
    const modalStockText = document.getElementById('modalStockText');

    // ✅ FIX: stok produk utama dipakai HANYA sebagai fallback untuk produk TANPA varian
    const productStock = Number("{{ $product->stock_quantity }}");
    const productUnit  = "{{ $product->stock_unit }}";

    // ✅ FIX: availableStock sekarang dinamis, mengikuti varian yang dipilih
    let availableStock = productStock;

    function updateAvailableStock() {
        if (variantSelect && variantSelect.value) {
            const selectedOption = variantSelect.options[variantSelect.selectedIndex];

            availableStock = Number(selectedOption.dataset.stock) || 0;

            if (stockDisplay) {
                stockDisplay.innerText = "{{ $product->stock_label }}";
            }

            if (modalStockText) {
                modalStockText.innerText = "{{ $product->stock_label }}";
            }
        } else {
            availableStock = productStock;

            if (stockDisplay) {
                stockDisplay.innerText = "{{ $product->stock_label }}";
            }

            if (modalStockText) {
                modalStockText.innerText = "{{ $product->stock_label }}";
            }
        }
    }

    if (variantSelect && priceElement) {
        variantSelect.addEventListener('change', function () {
            const price = this.options[this.selectedIndex].getAttribute('data-price');
            if (price) {
                priceElement.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
            }
            updateAvailableStock();
        });

        // Set nilai awal saat halaman dimuat
        updateAvailableStock();
    }

    const qtyInput = document.getElementById('quantity');
    const minusBtn = document.querySelector('.qty-minus');
    const plusBtn  = document.querySelector('.qty-plus');

    if (qtyInput && minusBtn && plusBtn) {
        minusBtn.addEventListener('click', function () {
            const min = parseInt(qtyInput.getAttribute('min')) || 1;
            const val = parseInt(qtyInput.value) || 1;
            if (val > min) qtyInput.value = val - 1;
        });
        plusBtn.addEventListener('click', function () {
            qtyInput.value = (parseInt(qtyInput.value) || 1) + 1;
        });
    }

    const detailCartForm      = document.getElementById('detailCartForm');
    const stockWarningModal   = document.getElementById('stockWarningModal');
    const stockWarningClose   = document.getElementById('stockWarningClose');
    const reduceToStockBtn    = document.getElementById('reduceToStockBtn');
    const waitRestockBtn      = document.getElementById('waitRestockBtn');
    const allowWaitingRestock = document.getElementById('allow_waiting_restock');
    let pendingSubmitter      = null;

    if (detailCartForm && qtyInput && stockWarningModal) {
        detailCartForm.addEventListener('submit', function (event) {
            const requestedQty = parseInt(qtyInput.value) || 1;

            // ✅ FIX: cek terhadap availableStock (dinamis, sesuai varian dipilih)
            if (requestedQty > availableStock && allowWaitingRestock?.value !== '1') {
                event.preventDefault();
                pendingSubmitter = event.submitter;
                stockWarningModal.classList.add('active');
            }
        });
    }

    if (stockWarningClose) {
        stockWarningClose.addEventListener('click', () => stockWarningModal.classList.remove('active'));
    }

    if (reduceToStockBtn) {
        reduceToStockBtn.addEventListener('click', function () {
            qtyInput.value = availableStock > 0 ? availableStock : 1;
            stockWarningModal.classList.remove('active');
        });
    }

    if (waitRestockBtn && allowWaitingRestock) {
        waitRestockBtn.addEventListener('click', function () {
            allowWaitingRestock.value = '1';
            stockWarningModal.classList.remove('active');
            pendingSubmitter ? pendingSubmitter.click() : detailCartForm.submit();
        });
    }

    document.querySelectorAll('.tab-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            button.classList.add('active');
            const target = document.getElementById('tab-' + button.dataset.tab);
            if (target) target.classList.add('active');
        });
    });
});
</script>
@endsection