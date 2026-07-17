@extends('layouts.store')

@section('content')

@php
    $selectedCategory = request('category')
        ? $categories->firstWhere('id', request('category'))
        : null;

    $activeFilterCount = collect([request('category'), request('search')])->filter()->count();
@endphp

@if($selectedCategory || request('search'))
<section class="products-catalog-header">
    <div class="container">
        <div class="catalog-header-sleek">
            <div class="catalog-header-content">
                @if($selectedCategory)
                    <h1>{{ $selectedCategory->category_name }}</h1>
                    <p>Temukan berbagai produk berkualitas dalam kategori {{ $selectedCategory->category_name }} pilihan Toko Tika.</p>
                @elseif(request('search'))
                    <h1>Hasil Pencarian</h1>
                    <p>Menampilkan hasil pencarian untuk "{{ request('search') }}"</p>
                @endif
            </div>

            <div class="products-active-filter-sleek">
                <div class="active-filter-items">
                    @if(request('search'))
                        <span class="filter-pill">
                            Pencarian: "{{ request('search') }}"
                            <a href="{{ route('products.index', request('category') ? ['category' => request('category')] : []) }}"><i class="fas fa-xmark"></i></a>
                        </span>
                    @endif

                    @if($selectedCategory)
                        <span class="filter-pill">
                            Kategori: {{ $selectedCategory->category_name }}
                            <a href="{{ route('products.index', request('search') ? ['search' => request('search')] : []) }}"><i class="fas fa-xmark"></i></a>
                        </span>
                    @endif
                </div>
                <a href="{{ route('products.index') }}" class="reset-filter-link">Reset Semua <i class="fas fa-rotate-right"></i></a>
            </div>
        </div>
    </div>
</section>
@endif

<section class="section products-catalog-section">
    <div class="container">

        <div class="category-scroll-wrap mobile-only">
            <div class="category-scroll-info-bar">
                <span>Kategori Produk</span>
                <span class="catalog-result-count"><strong>{{ $products->total() }}</strong> Produk</span>
            </div>
            <div class="category-scroll-inner">
                <a href="{{ route('products.index', request('search') ? ['search' => request('search')] : []) }}"
                   class="category-pill {{ !request('category') ? 'is-active' : '' }}">
                    <i class="fas fa-boxes-stacked"></i>
                    <span>Semua</span>
                </a>

                @foreach ($categories as $category)
                    <a href="{{ route('products.index', array_filter(['category' => $category->id, 'search' => request('search')])) }}"
                       class="category-pill {{ request('category') == $category->id ? 'is-active' : '' }}">
                        <i class="fas fa-basket-shopping"></i>
                        <span>{{ $category->category_name }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="catalog-premium-layout">
            <aside class="catalog-sidebar desktop-only">
                <div class="catalog-sidebar-card">
                    <div class="catalog-sidebar-head">
                        <span>Etalase</span>
                        <h3>Kategori Produk</h3>
                    </div>

                    <a href="{{ route('products.index', request('search') ? ['search' => request('search')] : []) }}"
                       class="catalog-category-link {{ !request('category') ? 'is-active' : '' }}">
                        <i class="fas fa-border-all"></i>
                        <span>Semua Produk</span>
                    </a>

                    @foreach ($categories as $category)
                        <a href="{{ route('products.index', array_filter(['category' => $category->id, 'search' => request('search')])) }}"
                           class="catalog-category-link {{ request('category') == $category->id ? 'is-active' : '' }}">
                            <i class="fas fa-basket-shopping"></i>
                            <span>{{ $category->category_name }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="catalog-service-card">
                    <i class="fas fa-headset"></i>
                    <h4>Butuh bantuan?</h4>
                    <p>Gunakan chatbot atau hubungi admin untuk rekomendasi produk.</p>
                </div>
            </aside>

            <div class="catalog-main">
                <div class="catalog-topbar">
                    <div class="catalog-topbar-info">
                        <span class="catalog-eyebrow">Etalase Toko Tika</span>
                        <h2>{{ $selectedCategory ? 'Produk ' . $selectedCategory->category_name : 'Semua Produk' }}</h2>
                        <p>{{ $selectedCategory ? 'Menampilkan produk kategori ' . $selectedCategory->category_name . '.' : 'Menampilkan produk pilihan Toko Tika.' }}</p>
                    </div>

                    <div class="catalog-topbar-actions">
                        <div class="catalog-result-pill">
                            <i class="fas fa-box-open"></i>
                            <span>{{ $products->total() }} produk</span>
                        </div>
                    </div>
                </div>

                @if ($products->count())
                    <div class="product-grid luxury-product-grid">
                        @foreach($products as $product)
                            <a href="{{ route('products.show', $product->slug) }}" class="product-card-link">
                                <div class="product-card clickable-product-card luxury-product-card">
                                    <div class="product-card-image">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                        @else
                                            <img src="https://via.placeholder.com/400x300?text=Produk" alt="{{ $product->name }}">
                                        @endif
                                    </div>

                                    <div class="product-card-body">
                                        <div class="product-top-row">
                                            <span class="product-category">{{ $product->category->category_name ?? '-' }}</span>


                                        </div>

                                        <h3 class="product-title">{{ $product->name }}</h3>

                                        <div class="product-price">
                                            @if($product->variants->count())
                                                Mulai dari Rp {{ number_format($product->display_price, 0, ',', '.') }}
                                            @else
                                                Rp {{ number_format($product->display_price, 0, ',', '.') }}
                                            @endif
                                        </div>

                                        <div class="product-card-footer">
                                            @php
                                                $qty = $product->stock_quantity;
                                                $dotClass = 'in-stock';
                                                $dotLabel = 'Stok: ' . ($product->stock_label ?? $qty);
                                                if ($qty <= 0) {
                                                    $dotClass = 'out-of-stock';
                                                    $dotLabel = 'Stok Habis';
                                                }
                                            @endphp
                                            <span class="product-stock-status {{ $dotClass }}">
                                                <span class="stock-dot"></span>
                                                <span class="stock-text">{{ $dotLabel }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="premium-pagination-wrap">
                            <div class="premium-pagination-info">
                                Menampilkan {{ $products->firstItem() }} - {{ $products->lastItem() }} dari {{ $products->total() }} produk
                            </div>

                            <div class="premium-pagination">
                                @if ($products->onFirstPage())
                                    <span class="page-btn disabled">Sebelumnya</span>
                                @else
                                    <a href="{{ $products->previousPageUrl() }}" class="page-btn">Sebelumnya</a>
                                @endif

                                @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                    @if ($page == $products->currentPage())
                                        <span class="page-number active">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="page-number">{{ $page }}</a>
                                    @endif
                                @endforeach

                                @if ($products->hasMorePages())
                                    <a href="{{ $products->nextPageUrl() }}" class="page-btn">Berikutnya</a>
                                @else
                                    <span class="page-btn disabled">Berikutnya</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="empty-state catalog-empty-state">
                        <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                        <h3>Produk tidak ditemukan</h3>
                        <p>Coba pilih kategori lain, ubah kata pencarian, atau reset filter.</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">Lihat Semua Produk</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const catalogMain = document.querySelector('.catalog-main');
    if (!catalogMain) return;

    // Helper: update active page elements from a fetched HTML string
    function updateCatalog(htmlString, targetUrl) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlString, 'text/html');

        // Extract and replace product grid
        const oldGrid = catalogMain.querySelector('.luxury-product-grid');
        const oldEmpty = catalogMain.querySelector('.catalog-empty-state');
        const newGrid = doc.querySelector('.luxury-product-grid');
        const newEmpty = doc.querySelector('.catalog-empty-state');

        // Remove old grid/empty state
        if (oldGrid) oldGrid.remove();
        if (oldEmpty) oldEmpty.remove();

        // Put new items
        const topbar = catalogMain.querySelector('.catalog-topbar');
        if (newGrid) {
            topbar.after(newGrid);
        } else if (newEmpty) {
            topbar.after(newEmpty);
        }

        // Extract and replace pagination
        const oldPagination = document.querySelector('.premium-pagination-wrap');
        const newPagination = doc.querySelector('.premium-pagination-wrap');
        if (oldPagination) oldPagination.remove();
        if (newPagination && newGrid) {
            catalogMain.appendChild(newPagination);
        }

        // Update Total Product Count Badge
        const oldCountPill = document.querySelector('.catalog-result-pill span');
        const newCountPill = doc.querySelector('.catalog-result-pill span');
        if (oldCountPill && newCountPill) {
            oldCountPill.textContent = newCountPill.textContent;
        }
        
        const oldMobileCount = document.querySelector('.catalog-result-count strong');
        const newMobileCount = doc.querySelector('.catalog-result-count strong');
        if (oldMobileCount && newMobileCount) {
            oldMobileCount.textContent = newMobileCount.textContent;
        }

        // Update Active Category Pill / Links (Desktop & Mobile)
        // Mobile pills
        const oldMobilePills = document.querySelectorAll('.category-scroll-inner .category-pill');
        const newMobilePills = doc.querySelectorAll('.category-scroll-inner .category-pill');
        oldMobilePills.forEach((pill, idx) => {
            if (newMobilePills[idx]) {
                pill.className = newMobilePills[idx].className;
                pill.href = newMobilePills[idx].href;
            }
        });

        // Desktop category links
        const oldDesktopLinks = document.querySelectorAll('.catalog-sidebar-card .catalog-category-link');
        const newDesktopLinks = doc.querySelectorAll('.catalog-sidebar-card .catalog-category-link');
        oldDesktopLinks.forEach((link, idx) => {
            if (newDesktopLinks[idx]) {
                link.className = newDesktopLinks[idx].className;
                link.href = newDesktopLinks[idx].href;
            }
        });

        // Update Header H1 & Description (e.g. category switch)
        const oldHeaderH1 = document.querySelector('.catalog-header-content h1');
        const newHeaderH1 = doc.querySelector('.catalog-header-content h1');
        if (oldHeaderH1 && newHeaderH1) {
            oldHeaderH1.textContent = newHeaderH1.textContent;
        }

        const oldHeaderDesc = document.querySelector('.catalog-header-content p');
        const newHeaderDesc = doc.querySelector('.catalog-header-content p');
        if (oldHeaderDesc && newHeaderDesc) {
            oldHeaderDesc.textContent = newHeaderDesc.textContent;
        }

        // Update Filter Pill section
        const oldActiveFilter = document.querySelector('.products-active-filter-sleek');
        const newActiveFilter = doc.querySelector('.products-active-filter-sleek');
        if (oldActiveFilter) oldActiveFilter.remove();
        
        if (newActiveFilter) {
            document.querySelector('.catalog-header-sleek').appendChild(newActiveFilter);
        }

        // Update sorting dropdown active option
        const oldSortSelect = document.getElementById('catalogSortSelect');
        const newSortSelect = doc.getElementById('catalogSortSelect');
        if (oldSortSelect && newSortSelect) {
            oldSortSelect.value = newSortSelect.value;
        }

        // Re-attach image loaders
        document.querySelectorAll('.product-card-image img').forEach(function (img) {
            const wrapper = img.closest('.product-card-image');
            if (!wrapper) return;
            wrapper.classList.add('is-loading');
            if (img.complete) { wrapper.classList.remove('is-loading'); return; }
            img.addEventListener('load',  () => wrapper.classList.remove('is-loading'));
            img.addEventListener('error', () => wrapper.classList.remove('is-loading'));
        });

        // Update History API
        window.history.pushState({ html: htmlString }, '', targetUrl);

        // Fade in new grid
        const finalGrid = catalogMain.querySelector('.luxury-product-grid') || catalogMain.querySelector('.catalog-empty-state');
        if (finalGrid) {
            finalGrid.style.opacity = '0';
            requestAnimationFrame(() => {
                finalGrid.style.transition = 'opacity 0.25s ease';
                finalGrid.style.opacity = '1';
            });
        }
    }

    // Generate shimmer loading effect
    function showSkeleton() {
        const grid = catalogMain.querySelector('.luxury-product-grid');
        const emptyState = catalogMain.querySelector('.catalog-empty-state');
        if (grid) {
            grid.style.opacity = '0.5';
            let skeletonHtml = '';
            for(let i = 0; i < 8; i++) {
                skeletonHtml += `
                    <div class="skeleton-card">
                        <div class="skeleton-image shimmer"></div>
                        <div class="skeleton-body">
                            <div class="skeleton-top shimmer"></div>
                            <div class="skeleton-title shimmer"></div>
                            <div class="skeleton-title short shimmer"></div>
                            <div class="skeleton-price shimmer"></div>
                            <div class="skeleton-footer shimmer"></div>
                        </div>
                    </div>
                `;
            }
            grid.innerHTML = skeletonHtml;
            grid.style.opacity = '1';
        } else if (emptyState) {
            emptyState.remove();
            const gridContainer = document.createElement('div');
            gridContainer.className = 'product-grid luxury-product-grid';
            let skeletonHtml = '';
            for(let i = 0; i < 8; i++) {
                skeletonHtml += `
                    <div class="skeleton-card">
                        <div class="skeleton-image shimmer"></div>
                        <div class="skeleton-body">
                            <div class="skeleton-top shimmer"></div>
                            <div class="skeleton-title shimmer"></div>
                            <div class="skeleton-title short shimmer"></div>
                            <div class="skeleton-price shimmer"></div>
                            <div class="skeleton-footer shimmer"></div>
                        </div>
                    </div>
                `;
            }
            gridContainer.innerHTML = skeletonHtml;
            catalogMain.querySelector('.catalog-topbar').after(gridContainer);
        }
        
        const paginationWrap = document.querySelector('.premium-pagination-wrap');
        if (paginationWrap) {
            paginationWrap.style.opacity = '0.3';
            paginationWrap.style.pointerEvents = 'none';
        }
    }

    // Main fetch handler
    async function fetchProducts(url) {
        showSkeleton();

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Fetch failed');
            const html = await response.text();
            updateCatalog(html, url);
        } catch (error) {
            console.error('Error fetching products:', error);
            window.location.href = url; // Fallback to full page load
        }
    }

    // Intercept clicks on links
    document.addEventListener('click', function (e) {
        const categoryLink = e.target.closest('.catalog-category-link, .category-pill, .reset-filter-link, .filter-pill a');
        if (categoryLink) {
            e.preventDefault();
            const href = categoryLink.getAttribute('href');
            if (href) fetchProducts(href);
            return;
        }

        const paginationLink = e.target.closest('.premium-pagination a');
        if (paginationLink) {
            e.preventDefault();
            const href = paginationLink.getAttribute('href');
            if (href) {
                fetchProducts(href);
                const targetScroll = document.querySelector('.products-catalog-section');
                if (targetScroll) {
                    targetScroll.scrollIntoView({ behavior: 'smooth' });
                }
            }
        }
    });

    // Handle Sorting Select
    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'catalogSortSelect') {
            const sortVal = e.target.value;
            const url = new URL(window.location.href);
            if (sortVal) {
                url.searchParams.set('sort', sortVal);
            } else {
                url.searchParams.delete('sort');
            }
            url.searchParams.delete('page');
            fetchProducts(url.toString());
        }
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.html) {
            updateCatalog(e.state.html, window.location.href);
        } else {
            window.location.reload();
        }
    });
});
</script>
@endsection
