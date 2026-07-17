@extends('layouts.store')

@section('content')
<section class="page-shell">
    <div class="container">
        
        <!-- Hero Section -->
        <div class="checkout-hero flex flex-col lg:flex-row gap-8 items-center bg-emerald-50 rounded-2xl p-6 md:p-12 mb-8">
            <div class="checkout-hero-left w-full lg:w-1/2">
                <span class="checkout-badge inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-xs font-semibold mb-4"><i class="fas fa-location-dot"></i> Khusus Bekasi Timur</span>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Lengkapi Pesanan Anda</h1>
                <p class="text-sm md:text-base text-gray-600 mb-6">
                    Belanja kebutuhan dapur menjadi lebih mudah bersama Toko Tika.
                    Kami melayani pengiriman khusus wilayah Bekasi Timur menggunakan
                    ojek toko atau pesanan dapat diambil langsung di toko.
                </p>
                <div class="checkout-feature-grid grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                    <div class="checkout-feature-card flex flex-col items-center text-center p-3 bg-white rounded-xl shadow-sm">
                        <div class="feature-icon text-xl text-emerald-600 mb-2"><i class="fas fa-motorcycle"></i></div>
                        <h4 class="text-sm font-semibold text-gray-800">Ojek Toko</h4>
                        <span class="text-xs text-gray-400">Pengiriman Cepat</span>
                    </div>
                    <div class="checkout-feature-card flex flex-col items-center text-center p-3 bg-white rounded-xl shadow-sm">
                        <div class="feature-icon text-xl text-emerald-600 mb-2"><i class="fas fa-store"></i></div>
                        <h4 class="text-sm font-semibold text-gray-800">Ambil</h4>
                        <span class="text-xs text-gray-400">Di Toko</span>
                    </div>
                    <div class="checkout-feature-card flex flex-col items-center text-center p-3 bg-white rounded-xl shadow-sm">
                        <div class="feature-icon text-xl text-emerald-600 mb-2"><i class="fas fa-qrcode"></i></div>
                        <h4 class="text-sm font-semibold text-gray-800">QRIS</h4>
                        <span class="text-xs text-gray-400">COD Tersedia</span>
                    </div>
                    <div class="checkout-feature-card flex flex-col items-center text-center p-3 bg-white rounded-xl shadow-sm">
                        <div class="feature-icon text-xl text-emerald-600 mb-2"><i class="fab fa-whatsapp"></i></div>
                        <h4 class="text-sm font-semibold text-gray-800">WhatsApp</h4>
                        <span class="text-xs text-gray-400">Konfirmasi Admin</span>
                    </div>
                </div>
            </div>
            <div class="checkout-hero-right w-full lg:w-1/2 hidden lg:block">
                <img src="{{ asset('storage/avatars/checkout-delivery.jpg') }}" alt="Checkout" class="w-full h-auto rounded-xl object-cover">
            </div>
        </div>

        @php
            $subtotal = 0;
            foreach($cart->items as $item) {
                $price = $item->variant ? $item->variant->price : $item->product->price;
                $subtotal += $price * $item->quantity;
            }
            $ojekCost   = 10000;
            $pickupCost = 0;
        @endphp

        {{-- Flash messages --}}
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-circle-xmark"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST" class="checkout-modern-form">
            @csrf

            <div class="checkout-wrapper flex flex-col lg:flex-row gap-8 items-start">

                <!-- Left Column (Separate Steps just like the mockup) -->
                <div class="checkout-left w-full lg:flex-1">

                    {{-- ============================= --}}
                    {{-- STEP 1: INFORMASI PENERIMA   --}}
                    {{-- ============================= --}}
                    <div class="checkout-form-card">
                        <div class="checkout-card-title">
                            <span>1</span>
                            <div>
                                <h3>Informasi Penerima</h3>
                                <p>Isi data penerima agar pesanan mudah dikonfirmasi.</p>
                            </div>
                        </div>

                        <div class="checkout-form-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <!-- WhatsApp Field -->
                            <div class="checkout-field-group">
                                <div class="checkout-field-icon">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="checkout-field-main">
                                    <label>Nomor WhatsApp</label>
                                    <input
                                        type="text"
                                        name="customer_whatsapp"
                                        value="{{ old('customer_whatsapp', auth()->user()->phone ?? $lastOrder->customer_whatsapp ?? '') }}"
                                        placeholder="Contoh: 082125052233"
                                        class="w-full min-h-[44px] p-3 border border-gray-300 rounded-lg text-base"
                                        required
                                    >
                                    @error('customer_whatsapp')
                                        <small class="checkout-error">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- Alamat Lengkap Field -->
                            <div class="checkout-field-group">
                                <div class="checkout-field-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="checkout-field-main">
                                    <label>Alamat Lengkap</label>
                                    <textarea
                                        name="shipping_address"
                                        rows="2"
                                        placeholder="Contoh: Jl. Mawar No. 12, RT 03/RW 01, Bekasi Timur"
                                        class="w-full p-3 border border-gray-300 rounded-lg text-base"
                                        required
                                    >{{ old('shipping_address', auth()->user()->address ?? $lastOrder->shipping_address ?? '') }}</textarea>
                                    @error('shipping_address')
                                        <small class="checkout-error">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- Kelurahan Field -->
                            <div class="checkout-field-group">
                                <div class="checkout-field-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="checkout-field-main">
                                    <label>Kelurahan</label>
                                    <select name="shipping_area" id="shipping_area" class="w-full min-h-[44px] p-3 border border-gray-300 rounded-lg text-base cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                        <option value="">Pilih Kelurahan</option>
                                        @foreach($shippingAreas as $area)
                                            <option
                                                value="{{ $area->id }}"
                                                data-cost="{{ $area->shipping_cost }}"
                                                {{ (old('shipping_area', $lastOrder->shipping_area_id ?? '') == $area->id) ? 'selected' : '' }}>
                                                {{ $area->kelurahan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shipping_area')
                                        <small class="checkout-error">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- Catatan Pesanan Field -->
                            <div class="checkout-field-group">
                                <div class="checkout-field-icon">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <div class="checkout-field-main">
                                    <label>Catatan Pesanan</label>
                                    <textarea
                                        name="notes"
                                        rows="2"
                                        maxlength="200"
                                        id="checkout_notes"
                                        class="w-full p-3 border border-gray-300 rounded-lg text-base"
                                        placeholder="Contoh: tolong antar sore, hubungi dulu sebelum sampai"
                                    >{{ old('notes', $lastOrder->notes ?? '') }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ============================= --}}
                    {{-- STEP 2: METODE PENGIRIMAN    --}}
                    {{-- ============================= --}}
                    <div class="checkout-form-card">
                        <div class="checkout-card-title">
                            <span>2</span>
                            <div>
                                <h3>Metode Pengiriman</h3>
                                <p>Pilih metode pengiriman yang tersedia untuk area Bekasi Timur.</p>
                            </div>
                        </div>

                        <div class="delivery-options flex flex-col gap-4">
                            <!-- Ojek Toko Option -->
                            <label class="delivery-option-card active flex flex-col sm:flex-row gap-4 p-4 border rounded-xl cursor-pointer" id="delivery_ojek_label" data-cost="{{ $ojekCost }}">
                                <input type="radio" name="delivery_method" value="ojek_toko" data-cost="{{ $ojekCost }}" checked>
                                <div class="delivery-icon text-xl text-emerald-600">
                                    <i class="fas fa-motorcycle"></i>
                                </div>
                                <div style="flex:1;">
                                    <strong>Pengiriman Ojek Toko <span class="delivery-badge bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 rounded-full ml-1">Rekomendasi</span></strong>
                                    <p class="text-sm text-gray-500 mt-1">Dikirim oleh kurir pribadi Toko Tika khusus wilayah Bekasi Timur.</p>
                                    <div class="delivery-stats flex flex-wrap gap-4 mt-2 text-xs text-gray-400">
                                        <div><span>Estimasi:</span> <strong class="text-gray-700">30 - 60 menit</strong></div>
                                        <div><span>Ongkir:</span> <strong class="text-gray-700" id="deliveryCostInfo">Pilih kelurahan</strong></div>
                                    </div>
                                </div>
                            </label>

                            <!-- Ambil di Toko Option -->
                            <label class="delivery-option-card flex flex-col sm:flex-row gap-4 p-4 border rounded-xl cursor-pointer" id="delivery_pickup_label" data-cost="{{ $pickupCost }}">
                                <input type="radio" name="delivery_method" value="ambil_di_toko" data-cost="{{ $pickupCost }}">
                                <div class="delivery-icon text-xl text-emerald-600">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div style="flex:1;">
                                    <strong>Ambil di Toko</strong>
                                    <p class="text-sm text-gray-500 mt-1">Pesanan diambil langsung di toko setelah dikonfirmasi admin.</p>
                                    <div class="delivery-stats flex flex-wrap gap-4 mt-2 text-xs text-gray-400">
                                        <div><span>Estimasi:</span> <strong class="text-gray-700">Hari ini</strong></div>
                                        <div><span>Ongkir:</span> <strong class="text-gray-700">Gratis</strong></div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- ============================= --}}
                    {{-- STEP 3: METODE PEMBAYARAN    --}}
                    {{-- ============================= --}}
                    <div class="checkout-form-card">
                        <div class="checkout-card-title">
                            <span>3</span>
                            <div>
                                <h3>Metode Pembayaran</h3>
                                <p>Pilih metode pembayaran yang ingin digunakan.</p>
                            </div>
                        </div>

                        <div class="payment-method-options flex flex-col sm:flex-row gap-4">
                            <!-- QRIS Option -->
                            <label class="payment-method-card active flex-1 flex gap-4 p-4 border rounded-xl cursor-pointer" id="payment_qris_label">
                                <input type="radio" name="payment_method" value="qris" checked>
                                <div class="payment-method-icon text-xl text-emerald-600">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <div>
                                    <strong>QRIS</strong>
                                    <p class="text-sm text-gray-500 mt-1">Bayar menggunakan QRIS melalui GoPay, Mobile Banking, atau e-wallet.</p>
                                </div>
                            </label>

                            <!-- COD Option -->
                            <label class="payment-method-card flex-1 flex gap-4 p-4 border rounded-xl cursor-pointer" id="payment_cod_label">
                                <input type="radio" name="payment_method" value="cod">
                                <div class="payment-method-icon text-xl text-emerald-600">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <strong>COD (Bayar di Tempat)</strong>
                                    <p class="text-sm text-gray-500 mt-1">Bayar tunai saat pesanan diterima.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Right Column (Exactly matching the mockup structure) -->
                <div class="checkout-right w-full lg:w-[380px] shrink-0 lg:sticky lg:top-24">
                    
                    <!-- White Card Wrapper ONLY for Product Summary -->
                    <aside class="checkout-summary-card bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mb-6">
                        <div class="checkout-card-title checkout-summary-title flex items-center gap-3 mb-6 pb-4 border-b border-gray-50">
                            <div class="payment-method-icon checkout-summary-icon text-xl text-emerald-600">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="checkout-summary-heading">
                                <h3 class="text-lg font-bold text-gray-950">Ringkasan Pesanan</h3>
                                <span class="text-xs text-gray-400">{{ $cart->items->count() }} Produk</span>
                            </div>
                        </div>

                        <!-- Product List -->
                        <div class="checkout-items-modern flex flex-col gap-4 mb-6">
                            @foreach($cart->items as $item)
                                @php
                                    $price        = $item->variant ? $item->variant->price : $item->product->price;
                                    $itemSubtotal = $price * $item->quantity;
                                @endphp
                                <div class="checkout-product-mini flex gap-3 items-start pb-4 border-b border-gray-50">
                                    <div class="checkout-product-image w-16 h-16 shrink-0 rounded-lg overflow-hidden border border-gray-50 flex items-center justify-center">
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-box-open text-gray-300 text-xl"></i>
                                        @endif
                                    </div>
                                    <div class="checkout-product-info flex-1 min-w-0">
                                        <strong class="block text-sm font-semibold text-gray-900 truncate">{{ $item->product->name ?? 'Produk' }}</strong>
                                        @if($item->variant)
                                            <span class="block text-[11px] text-gray-400 mt-0.5">{{ $item->variant->variant_name }}</span>
                                        @endif
                                        @if(isset($item->is_waiting_restock) && $item->is_waiting_restock)
                                            <span class="cart-restock-pill inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 text-[9px] font-semibold mt-1"><i class="fas fa-clock text-[8px]"></i> Menunggu restok</span>
                                        @endif
                                        <small class="block text-xs text-gray-400 mt-1">{{ $item->quantity }} x Rp {{ number_format($price, 0, ',', '.') }}</small>
                                    </div>
                                    <div class="checkout-product-price text-sm font-semibold text-gray-900">
                                        Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Spacing Details -->
                        <div class="checkout-summary-lines flex flex-col gap-3 py-4 border-b border-gray-50 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal Produk</span>
                                <strong class="text-gray-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Ongkir</span>
                                <strong class="text-gray-850 font-semibold" id="shippingCostText">Pilih Kelurahan</strong>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="checkout-grand-total flex justify-between items-center pt-6">
                            <span class="text-gray-600 font-medium">Total Pembayaran</span>
                            <strong class="text-xl font-bold text-emerald-600" id="grandTotalText">
                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                            </strong>
                        </div>

                        <input type="hidden" id="subtotalValue" value="{{ $subtotal }}">
                    </aside>

                    <!-- Safe Alert Info Banner (Placed outside card on the page background) -->
                    <div class="checkout-area-note flex gap-3 p-4 rounded-2xl bg-emerald-50/50 text-emerald-900 border border-emerald-100/50 mb-6">
                        <i class="fas fa-location-dot text-emerald-600 mt-0.5"></i>
                        <p class="text-xs leading-relaxed">
                            Pengiriman hanya untuk area Bekasi Timur. Jika alamat di luar area,
                            admin akan menghubungi melalui WhatsApp.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary checkout-submit-btn w-full min-h-[48px] rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition" id="checkoutBtn">
                        <i class="fas fa-lock"></i> Lanjut ke Pembayaran
                    </button>

                    <div class="checkout-safe-note row-variant flex justify-between mt-6 pt-6 border-t border-gray-100 text-xs text-gray-400">
                        <div class="flex items-center gap-1"><i class="fas fa-shield-alt text-emerald-600"></i><span>Aman</span></div>
                        <div class="flex items-center gap-1"><i class="fas fa-check-circle text-emerald-600"></i><span>Berkualitas</span></div>
                        <div class="flex items-center gap-1"><i class="fas fa-star text-emerald-600"></i><span>Terpercaya</span></div>
                    </div>

                </div>

            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deliveryCards  = document.querySelectorAll('.delivery-option-card');
    const deliveryInputs = document.querySelectorAll('input[name="delivery_method"]');
    const shippingText   = document.getElementById('shippingCostText');
    const grandTotalText = document.getElementById('grandTotalText');
    const subtotalValue  = document.getElementById('subtotalValue');
    const checkoutBtn    = document.getElementById('checkoutBtn');
    const deliveryCostInfo = document.getElementById('deliveryCostInfo');
    const notesTextarea  = document.getElementById('checkout_notes');
    const charCounter    = document.getElementById('char_counter');

    const formatRupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

    const areaSelect = document.getElementById('shipping_area');

    const updateTotal = function () {
        const selectedDelivery = document.querySelector('input[name="delivery_method"]:checked');
        const subtotal = parseInt(subtotalValue.value) || 0;
        
        // 1. Dapatkan biaya ojek berdasarkan kelurahan terpilih (untuk tampilan statis di dalam kartu Ojek)
        let ojekCost = 0;
        if (areaSelect.value !== "") {
            ojekCost = parseInt(areaSelect.options[areaSelect.selectedIndex].dataset.cost) || 0;
            deliveryCostInfo.textContent = formatRupiah(ojekCost);
        } else {
            deliveryCostInfo.textContent = "Pilih kelurahan";
        }

        // 2. Tentukan ongkos kirim aktual yang digunakan untuk total belanja
        let actualShippingCost = 0;
        if (selectedDelivery.value === "ojek_toko") {
            if (areaSelect.value !== "") {
                actualShippingCost = ojekCost;
                shippingText.textContent = formatRupiah(actualShippingCost);
            } else {
                shippingText.textContent = "Pilih Kelurahan";
                grandTotalText.textContent = formatRupiah(subtotal);
                
                // Set visual card active states
                deliveryCards.forEach(card => card.classList.remove("active"));
                selectedDelivery.closest(".delivery-option-card").classList.add("active");
                return;
            }
        } else {
            // Ambil di toko (gratis)
            actualShippingCost = 0;
            shippingText.textContent = "Gratis";
        }

        grandTotalText.textContent = formatRupiah(subtotal + actualShippingCost);

        // Update active class kartu pengiriman
        deliveryCards.forEach(card => card.classList.remove("active"));
        selectedDelivery.closest(".delivery-option-card").classList.add("active");
    };

    // Listeners for shipping area and delivery methods
    deliveryInputs.forEach(i => i.addEventListener('change', updateTotal));
    areaSelect.addEventListener('change', updateTotal);

    // Active visual states for payment cards
    const paymentQrisLabel = document.getElementById('payment_qris_label');
    const paymentCodLabel = document.getElementById('payment_cod_label');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const isQris = this.value === 'qris';
            paymentQrisLabel.classList.toggle('active', isQris);
            paymentCodLabel.classList.toggle('active', !isQris);
        });
    });

    // Character counter logic for notes textarea
    if (notesTextarea && charCounter) {
        const updateCharCount = function () {
            charCounter.textContent = notesTextarea.value.length + " / 200";
        };
        notesTextarea.addEventListener('input', updateCharCount);
        updateCharCount();
    }

    // Disable tombol submit agar tidak double-submit
    let isSubmitted = false;
    document.querySelector('.checkout-modern-form').addEventListener('submit', function (e) {
        if (isSubmitted) {
            e.preventDefault();
            return false;
        }
        isSubmitted = true;
        checkoutBtn.disabled    = true;
        checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    });

    updateTotal();
});
</script>
@endsection
