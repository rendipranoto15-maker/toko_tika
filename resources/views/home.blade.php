@extends('layouts.store')

@section('content')

@php
    $myOrdersCount  = auth()->check() ? auth()->user()->orders()->count() : 0;
    $myPendingCount = auth()->check() ? auth()->user()->orders()->where('order_status', 'pending')->count() : 0;
    $mySpendTotal   = auth()->check() ? auth()->user()->orders()->sum('grand_total') : 0;
@endphp

<!-- STATIC INFO TICKER (COMPLEX & STATIC) -->
<div class="live-ticker-wrap static-ticker">
    <div class="ticker-item">
        <span class="ticker-tag info"><i class="fas fa-bullhorn"></i> INFO LAYANAN</span>
        <span class="ticker-text">Kurir Toko Aktif menjangkau Bekasi Timur & Rawa Kalong</span>
    </div>
    <div class="ticker-separator"></div>
    <div class="ticker-item">
        <span class="ticker-tag garansi"><i class="fas fa-shield-heart"></i> GARANSI SEGAR</span>
        <span class="ticker-text">Retur / ganti baru jika kualitas produk tidak layak konsumsi</span>
    </div>
    <div class="ticker-separator"></div>
    <div class="ticker-item">
        <span class="ticker-tag umkm"><i class="fas fa-hand-holding-dollar"></i> KEMITRAAN</span>
        <span class="ticker-text">100% didukung pasokan langsung produsen lokal UMKM</span>
    </div>
</div>

<!-- DYNAMIC TOP BANNER / CAROUSEL & HERO SIDE CARDS -->
<section class="section hero-complex-section" style="padding-top: 16px; padding-bottom: 32px;">
    <div class="container hero-complex-grid">
        <!-- Left: Carousel -->
        <div class="store-banner-carousel">
            <div class="carousel-track" id="carouselTrack">
                <!-- Slide 1 -->
                <div class="carousel-slide active" style="background: linear-gradient(135deg, #FFF9F3 0%, #F5EFE7 100%);">
                    <div class="slide-content">
                        <span class="slide-badge"><i class="fas fa-certificate"></i> UMKM Lokal Pilihan</span>
                        <h2>Kebutuhan Dapur Segar Langsung Ke Rumah Anda</h2>
                        <p>Mendukung pertumbuhan usaha lokal dengan menyediakan sembako, bumbu masak, dan kebutuhan harian berkualitas terbaik setiap hari.</p>
                        <div class="slide-meta">
                            <span class="meta-tag"><i class="fas fa-check-circle"></i> Teruji Higienis</span>
                            <span class="meta-tag"><i class="fas fa-star"></i> Pilihan Utama</span>
                        </div>
                    </div>
                </div>
                <!-- Slide 2 -->
                <div class="carousel-slide" style="background: linear-gradient(135deg, #F5EFE7 0%, #FFF9F3 100%);">
                    <div class="slide-content">
                        <span class="slide-badge" style="background: rgba(123, 75, 42, 0.1); color: var(--secondary);"><i class="fas fa-truck-fast"></i> Pengiriman Instan</span>
                        <h2>Pesanan Diantar Kurir Toko Secara Cepat</h2>
                        <p>Khusus jangkauan Bekasi Timur dan sekitarnya. Pengiriman terjamin cepat, higienis, dan aman sampai ke pintu rumah Anda.</p>
                        <div class="slide-meta">
                            <span class="meta-tag"><i class="fas fa-clock"></i> Rata-rata 20 Menit</span>
                            <span class="meta-tag"><i class="fas fa-shield-heart"></i> Kurir Profesional</span>
                        </div>
                    </div>
                </div>
                <!-- Slide 3 -->
                <div class="carousel-slide" style="background: linear-gradient(135deg, #FFF9F3 0%, #FFFDFB 100%);">
                    <div class="slide-content">
                        <span class="slide-badge" style="background: rgba(169, 113, 66, 0.1); color: var(--accent);"><i class="fas fa-leaf"></i> Garansi Segar</span>
                        <h2>Bahan Masakan Berkualitas Tanpa Pengawet</h2>
                        <p>Dipilah secara teliti oleh tim kami demi menjamin kualitas konsumsi dan kesehatan keluarga tercinta Anda.</p>
                        <div class="slide-meta">
                            <span class="meta-tag"><i class="fas fa-hand-holding-heart"></i> Bersih & Alami</span>
                            <span class="meta-tag"><i class="fas fa-thumbs-up"></i> Ulasan Terbaik</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-dots" id="carouselDots">
                <span class="dot active" onclick="setSlide(0)"></span>
                <span class="dot" onclick="setSlide(1)"></span>
                <span class="dot" onclick="setSlide(2)"></span>
            </div>
        </div>

        <!-- Right: Side Cards (featuring Warung photo) -->
        <div class="hero-side-cards">
            <!-- Warung Photo Card -->
            <div class="side-card warung-card">
                <div class="warung-image-container">
                    <img src="{{ asset('storage/avatars/warumg.png') }}" alt="Toko Tika">
                    <span class="side-card-badge">Toko Fisik</span>
                </div>
                <div class="warung-card-body">
                    <h3>TOKO TIKA</h3>
                    <p>Kunjungi warung kami langsung di Pasar Rawa Kalong, Bekasi.</p>
                </div>
            </div>

            <!-- Recipe Helper Card -->
            <div class="side-card tips-card">
                <div class="tips-header">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Inspirasi Masak Hari Ini</strong>
                </div>
                <p><strong>Opor Gurih:</strong> Butuh Bawang Merah, Bumbu Dapur Lengkap, & Santan? Cari dan saring produknya langsung di kategori <strong>Bumbu Dapur</strong>!</p>
            </div>

            <!-- Quick Info Card -->
            <div class="side-card info-card">
                <div class="info-row">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Jam Buka</strong>
                        <span>07:00 - 21:00 WIB</span>
                    </div>
                </div>
                <div class="info-row">
                    <i class="fas fa-location-dot"></i>
                    <div>
                        <strong>Lokasi Kami</strong>
                        <span>Rawa Kalong, Bekasi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- INTERACTIVE PRODUCT HUBS WITH DYNAMIC TAB FILTERS -->
<section class="section products-hub-section" style="padding-top: 16px; padding-bottom: 32px;">
    <div class="container">
        <div class="section-header-modern">
            <div class="header-left">
                <span class="section-tag">Katalog Pilihan</span>
                <h2>Eksplorasi Produk Unggulan</h2>
                <p>Temukan kebutuhan rumah tangga terbaik dengan menyaring kategori secara langsung.</p>
            </div>
            
            <!-- Filters Tabs (Interactive, styled anchors/divs) -->
            <div class="hub-tabs" id="hubTabs">
                @foreach($categories as $index => $category)
                    <div class="hub-tab {{ $index === 0 ? 'active' : '' }}" data-filter="cat-{{ $category->id }}">
                        <i class="fas fa-tag"></i> {{ $category->category_name }}
                    </div>
                @endforeach
            </div>
        </div>

        @if ($products->count())
            @php
                $firstCategoryId = $categories->first()?->id;
            @endphp
            <div class="product-grid-modern" id="productsGrid">
                @foreach($products as $index => $product)
                    @php
                        $displayStyle = ($product->category_id == $firstCategoryId) ? 'block' : 'none';
                    @endphp
                    <a href="{{ route('products.show', $product->slug) }}" 
                       class="product-hub-card-link" 
                       data-category="cat-{{ $product->category_id }}"
                       style="display: {{ $displayStyle }};">
                        <div class="product-hub-card">
                            <div class="card-image-wrap">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <img src="https://via.placeholder.com/400x300?text=Produk" alt="{{ $product->name }}">
                                @endif
                            </div>

                            <div class="card-body-wrap">
                                <span class="product-cat-label">{{ $product->category->category_name ?? '-' }}</span>
                                <h3 class="product-title-text">{{ $product->name }}</h3>
                                
                                <div class="price-row">
                                    <span class="current-price">
                                        Rp {{ number_format($product->display_price, 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Simple stock display -->
                                <div class="stock-simple-wrap">
                                    <span>Stok: {{ $product->stock_label ?? $product->stock_quantity }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state-card">
                <i class="fas fa-box-open"></i>
                <p>Belum ada produk unggulan yang tersedia saat ini.</p>
            </div>
        @endif
    </div>
</section>

<!-- VALUE STATEMENT SECTION -->
<section class="section values-section" style="padding-top: 24px; padding-bottom: 48px;">
    <div class="container">
        <div class="values-grid-modern">
            <div class="value-card-modern">
                <div class="value-icon-circle"><i class="fas fa-shield-halved"></i></div>
                <h3>Jaminan Kualitas Harian</h3>
                <p>Setiap bumbu dan produk segar dipilah secara teliti untuk kepuasan dapur Anda.</p>
            </div>
            <div class="value-card-modern">
                <div class="value-icon-circle"><i class="fas fa-truck-fast"></i></div>
                <h3>Kurir Internal Mandiri</h3>
                <p>Pengiriman ditangani langsung oleh staf kami agar pesanan tiba dalam kondisi optimal.</p>
            </div>
            <div class="value-card-modern">
                <div class="value-icon-circle"><i class="fas fa-hands-holding-child"></i></div>
                <h3>Saling Memberdayakan</h3>
                <p>Mendukung pertumbuhan ekonomi produsen rumahan dan komoditas tani lokal.</p>
            </div>
        </div>
    </div>
</section>

<!-- JAVASCRIPT LOGIC FOR SLIDER AND TAB FILTERING -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── BANNER CAROUSEL ──
    const track = document.getElementById('carouselTrack');
    if (track) {
        const slides = Array.from(track.children);
        const dots = Array.from(document.getElementById('carouselDots').children);
        let currentSlideIndex = 0;
        let slideInterval;

        function updateCarousel() {
            slides.forEach((slide, idx) => {
                slide.classList.toggle('active', idx === currentSlideIndex);
            });
            dots.forEach((dot, idx) => {
                dot.classList.toggle('active', idx === currentSlideIndex);
            });
        }

        window.setSlide = function(index) {
            currentSlideIndex = index;
            updateCarousel();
            resetSlideTimer();
        };

        function nextSlide() {
            currentSlideIndex = (currentSlideIndex + 1) % slides.length;
            updateCarousel();
        }

        function resetSlideTimer() {
            clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 6000);
        }
        resetSlideTimer();
    }

    // ── INTERACTIVE DYNAMIC TAB FILTERS ──
    const tabs = document.querySelectorAll('#hubTabs .hub-tab');
    const productGrid = document.getElementById('productsGrid');
    
    if (tabs && productGrid) {
        const cards = productGrid.querySelectorAll('.product-hub-card-link');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');

                cards.forEach(card => {
                    let show = false;

                    if (filterValue === 'all') {
                        show = true;
                    } else {
                        show = card.getAttribute('data-category') === filterValue;
                    }

                    if (show) {
                        card.style.display = 'block';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.96)';
                        setTimeout(() => {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        }, 50);
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }
});
</script>

@endsection
