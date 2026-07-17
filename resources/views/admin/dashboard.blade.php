@extends('layouts.store')

@section('content')
<section class="page-shell admin-dashboard-page" style="padding-top: 10px; padding-bottom: 50px;">
    <div class="container">

        {{-- WELCOME BANNER --}}
        <div class="admin-welcome-banner">
            <div class="banner-content">
                <span class="admin-hero-badge"><i class="fas fa-lock"></i> SECURE CONTROL CENTER</span>
                <h1>Selamat Datang Kembali, {{ auth()->user()->name }}!</h1>
                <p>Dashboard operasional berjalan lancar hari ini. Pantau seluruh metrik transaksi, performa produk, dan data pelanggan langsung di bawah.</p>
            </div>
            <div class="banner-date">
                <i class="far fa-calendar"></i>
                <span>{{ now()->translatedFormat('d F Y') }}</span>
            </div>
        </div>

        {{-- 4 KPI CARDS --}}
        <div class="admin-kpi-premium-grid">
            <div class="admin-kpi-premium-card">
                <div class="admin-kpi-icon brown"><i class="fas fa-bag-shopping"></i></div>
                <span>Total Pesanan</span>
                <strong>{{ $totalOrders }}</strong>
            </div>

            <div class="admin-kpi-premium-card">
                <div class="admin-kpi-icon green"><i class="fas fa-wallet"></i></div>
                <span>Pemasukan Hari Ini</span>
                <strong>Rp {{ number_format($todayRevenue ?? 0, 0, ',', '.') }}</strong>
            </div>

            <div class="admin-kpi-premium-card">
                <div class="admin-kpi-icon blue"><i class="fas fa-chart-line"></i></div>
                <span>Pemasukan Bulan Ini</span>
                <strong>Rp {{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }}</strong>
            </div>

            <div class="admin-kpi-premium-card">
                <div class="admin-kpi-icon orange"><i class="fas fa-bell"></i></div>
                <span>Order Baru</span>
                <strong>{{ $newOrdersCount ?? 0 }}</strong>
            </div>
        </div>

        {{-- MAIN CONTENT GRID (LEFT: LISTS, RIGHT: SIDEBAR INFO & ACTIONS) --}}
        <div class="admin-dashboard-grid">
            
            <!-- Left Main Column (Orders, Stock alerts, Customers) -->
            <div class="admin-main-left-column" style="display: flex; flex-direction: column; gap: 24px;">
                <!-- Recent Orders list -->
                <div class="admin-list-card">
                    <div class="admin-card-head">
                        <div>
                            <h3>Pesanan Terbaru</h3>
                            <p>Transaksi yang baru saja masuk ke sistem.</p>
                        </div>
                    </div>
                    <div class="admin-mini-list">
                        @forelse($latestOrders as $order)
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="admin-mini-row admin-mini-row-link">
                                <div class="admin-order-code-badge">{{ $order->order_code }}</div>
                                <div>
                                    <strong>{{ $order->user->name ?? 'User' }}</strong>
                                    <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <div class="admin-mini-right">
                                    <b>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</b>
                                    <div class="badges-row-wrap" style="display: flex; gap: 4px; margin-top: 4px; justify-content: flex-end;">
                                        <small class="admin-status-pill {{ $order->payment_method === 'qris' ? 'blue' : 'orange' }}">
                                            {{ strtoupper($order->payment_method) }}
                                        </small>
                                        <small class="admin-status-pill {{ $order->payment_status === 'paid' ? 'green' : 'yellow' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </small>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="admin-empty-mini">
                                <i class="fas fa-inbox"></i>
                                <p>Belum ada pesanan terbaru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- STOK HAMPIR HABIS + PRODUK TERLARIS --}}
                <div class="admin-inner-two-column-grid">
                    <!-- Out of stock list -->
                    <div class="admin-list-card">
                        <div class="admin-card-head">
                            <div>
                                <h3>Stok Habis</h3>
                                <p>Daftar produk yang kehabisan stok.</p>
                            </div>
                        </div>
                        <div class="admin-mini-list">
                            @forelse($outOfStockProducts as $product)
                                <div class="admin-product-alert-row">
                                    <div class="admin-product-alert-img">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                        @else
                                            <i class="fas fa-box"></i>
                                        @endif
                                    </div>

                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <span>{{ $product->category->category_name ?? '-' }}</span>
                                    </div>

                                    <small class="admin-stock-badge stock-empty">
                                        Habis
                                    </small>
                                </div>
                            @empty
                                <div class="admin-empty-mini">
                                    <i class="fas fa-check-circle" style="color:#10b981;font-size:20px;"></i>
                                    <p>Tidak ada produk yang kehabisan stok.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Negative stock list -->
                    <div class="admin-list-card">
                        <div class="admin-card-head">
                            <div>
                                <h3>Stok Minus</h3>
                                <p>Daftar produk dengan stok negatif (kurang/berutang).</p>
                            </div>
                        </div>
                        <div class="admin-mini-list">
                            @forelse($negativeStockProducts as $product)
                                @php
                                    $formattedEffectiveStock = floor(abs($product->effective_stock)) == abs($product->effective_stock)
                                        ? (string) (int) abs($product->effective_stock)
                                        : rtrim(rtrim(number_format(abs($product->effective_stock), 2, ',', ''), '0'), ',');
                                @endphp
                                <div class="admin-product-alert-row">
                                    <div class="admin-product-alert-img">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                        @else
                                            <i class="fas fa-box"></i>
                                        @endif
                                    </div>

                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <span>{{ $product->category->category_name ?? '-' }}</span>
                                    </div>

                                    <small class="admin-stock-badge stock-minus">
                                        Mines {{ $formattedEffectiveStock }} {{ $product->stock_unit }}
                                    </small>
                                </div>
                            @empty
                                <div class="admin-empty-mini">
                                    <i class="fas fa-check-circle" style="color:#10b981;font-size:20px;"></i>
                                    <p>Tidak ada produk dengan stok minus.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- PELANGGAN TERBARU --}}
                <div class="admin-list-card">
                    <div class="admin-card-head">
                        <div>
                            <h3>Pelanggan Terbaru</h3>
                            <p>Pelanggan yang baru saja mendaftarkan akun di toko.</p>
                        </div>
                    </div>
                    <div class="admin-customer-grid">
                        @forelse($latestCustomers as $customer)
                            <div class="admin-customer-card">
                                <div class="admin-customer-avatar">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div class="customer-info-wrap">
                                    <strong>{{ $customer->name }}</strong>
                                    <span>{{ $customer->email }}</span>
                                    <small>{{ $customer->created_at->format('d M Y') }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="admin-empty-mini" style="grid-column:1/-1;">
                                <i class="fas fa-users" style="color:#d1d5db;font-size:20px;"></i>
                                <p>Belum ada pelanggan terbaru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Column (Actions & Status Info) -->
            <div class="admin-side-card-column" style="display: flex; flex-direction: column; gap: 24px;">
                <!-- Modern Quick Action Buttons Grid -->
                <div class="admin-side-card" style="margin-bottom: 0;">
                    <div class="admin-card-head">
                        <div>
                            <h3>Quick Action</h3>
                            <p>Akses cepat navigasi sistem.</p>
                        </div>
                    </div>
                    <div class="admin-quick-action-grid">
                        <a href="{{ route('admin.products.create') }}" class="quick-action-btn">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Produk</span>
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="quick-action-btn">
                            <i class="fas fa-box-open"></i>
                            <span>Kelola Produk</span>
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="quick-action-btn">
                            <i class="fas fa-layer-group"></i>
                            <span>Kelola Kategori</span>
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="quick-action-btn">
                            <i class="fas fa-receipt"></i>
                            <span>Kelola Pesanan</span>
                        </a>
                    </div>
                </div>

                <!-- Status Server & Notifikasi -->
                <div class="admin-side-card" style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 0;">
                    <!-- System status panel -->
                    <div class="system-status-panel">
                        <h4>Status Operasional</h4>
                        <div class="status-indicator-row">
                            <span class="status-dot online"></span>
                            <strong>Server & Database Online</strong>
                        </div>
                        <div class="status-details">
                            <div class="status-item-line">
                                <span>Engine:</span>
                                <span>Laravel 11 & PHP 8.2</span>
                            </div>
                            <div class="status-item-line">
                                <span>Timezone:</span>
                                <span>Asia/Jakarta</span>
                            </div>
                        </div>
                    </div>

                    <!-- System metrics summary panel -->
                    <div class="system-summary-panel">
                        <h4>Ringkasan Sistem</h4>
                        <div class="metrics-mini-list">
                            <div class="metric-mini-item">
                                <div class="metric-icon"><i class="fas fa-boxes-packing"></i></div>
                                <div class="metric-info">
                                    <span>Total Produk</span>
                                    <strong>{{ \App\Models\Product::count() }} Item</strong>
                                </div>
                            </div>
                            <div class="metric-mini-item">
                                <div class="metric-icon"><i class="fas fa-users-viewfinder"></i></div>
                                <div class="metric-info">
                                    <span>Total Pelanggan</span>
                                    <strong>{{ \App\Models\User::whereHas('role', function($q){$q->where('role_name', 'user');})->count() }} Orang</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>
@endsection