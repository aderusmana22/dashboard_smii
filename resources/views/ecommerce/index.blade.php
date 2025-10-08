<x-app-layout>
    @section('title')
        Dashboard E-Commerce
    @endsection

    {{-- Kode CSS untuk dark mode tetap sama --}}
    <style>
        .dark-skin .bg-white { background-color: rgb(31 41 55 / 1); }
        .dark-skin .bg-gray-50 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .bg-gray-100 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .divide-gray-200> :not([hidden])~ :not([hidden]) { border-color: rgb(55 65 81 / 1); }
        .dark-skin .text-gray-900 { color: rgb(249 250 251 / 1); }
        .dark-skin .text-gray-800 { color: rgb(229 231 235 / 1); }
        .dark-skin .text-gray-700 { color: rgb(209 213 219 / 1); }
        .dark-skin .text-gray-500 { color: rgb(209 213 219 / 1); }
        .dark-skin .border-gray-300 { border-color: rgb(75 85 99 / 1); }
        .dark-skin .text-indigo-600 { color: #818cf8; }
        .dark-skin .text-indigo-600:hover { color: #a5b4fc; }
        .dark-skin .text-red-600 { color: #f87171; }
        .dark-skin .text-red-600:hover { color: #fca5a5; }
        .dark-skin .modal-cancel-button { background-color: rgb(75 85 99 / 1); color: rgb(229 231 235 / 1); }
        .dark-skin .modal-cancel-button:hover { background-color: rgb(107 114 128 / 1); }
        /* Gaya untuk input date agar konsisten */
        input[type="date"] {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            font-size: 0.875rem;
            padding: 0.4rem 0.8rem;
            cursor: pointer;
        }
        .dark-skin input[type="date"] {
            background-color: rgb(55 65 81 / 1);
            border-color: rgb(75 85 99 / 1);
            color: rgb(229 231 235 / 1);
        }
    </style>

    {{-- Script Alpine.js dan jQuery --}}
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <div class="py-6">
        <div class="w-full mx-auto">
            <!-- Bagian Atas: Status Toko -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">

                {{-- KOLOM KIRI: TOKOPEDIA/TIKTOK --}}
                <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <img src="https://assets.tokopedia.net/assets-tokopedia-lite/v2/zeus/production/e5b8438b.svg" alt="Tokopedia Logo" class="inline-block h-6 mr-2">
                        Status Toko Tokopedia
                    </h2>
                    @if ($tiktokShopData && !empty($tiktokShopData['shops']))
                        @foreach ($tiktokShopData['shops'] as $shop)
                            <div class="text-sm">
                                <p class="font-semibold text-lg text-gray-900">{{ $shop['name'] ?: 'Sinar Meadow' }}</p>
                                <p class="text-green-600 font-semibold">Sudah Terhubung</p>
                            </div>
                        @endforeach
                    @else
                        <div class="text-sm text-yellow-800 bg-yellow-100 p-3 rounded-md">
                            Toko TikTok/Tokopedia belum terhubung. Silakan hubungkan di halaman
                            <a href="{{ route('ecommerce.settings.index') }}" class="font-bold underline hover:text-yellow-900">Konfigurasi</a>.
                        </div>
                    @endif
                </div>

                {{-- KOLOM KANAN: SHOPEE --}}
                <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <img src="https://logospng.org/download/shopee/logo-shopee-1024.png" alt="Shopee Logo" class="inline-block h-7 mr-2">
                        Status Toko Shopee
                    </h2>
                    @if ($shopeeShop)
                        <div class="text-sm">
                            <p class="font-semibold text-lg text-gray-900">{{ $shopeeShop->shop_name ?: 'Sinar Meadow' }}</p>
                            <p class="text-green-600 font-semibold">Sudah Terhubung</p>
                        </div>
                    @else
                        <div class="text-sm text-yellow-800 bg-yellow-100 p-3 rounded-md">
                            Toko Shopee belum terhubung. Silakan hubungkan di halaman
                            <a href="{{ route('ecommerce.settings.index') }}" class="font-bold underline hover:text-yellow-900">Konfigurasi</a>.
                        </div>
                    @endif
                </div>
            </div>

            <!-- BAGIAN 2: DASHBOARD AKSI CEPAT -->
            <div x-data="dashboardAksiCepat()" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Dashboard Aksi Cepat</h2>

                    {{-- Grid Tombol Aksi --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 text-center gap-y-8">
                        <button @click="openModal('perluDiproses')" class="px-4 text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ $quickActionData['perluDiproses'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 mt-1">Perlu diproses</p>
                        </button>
                        <button @click="openModal('dalamPengiriman')" class="px-4 lg:border-l lg:border-gray-200 text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ $quickActionData['dalamPengiriman'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 mt-1">Dalam pengiriman</p>
                        </button>
                        <button @click="openModal('menungguPenyelesaian')" class="px-4 lg:border-l lg:border-gray-200 md:border-l text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ $quickActionData['menungguPenyelesaian'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 mt-1">Menunggu penyelesaian</p>
                        </button>
                        <button @click="openModal('transaksiSelesai')" class="px-4 lg:border-l lg:border-gray-200 text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ $quickActionData['transaksiSelesai'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 mt-1">Transaksi Selesai</p>
                        </button>
                        <button @click="openModal('transaksiDibatalkan')" class="px-4 lg:border-l lg:border-gray-200 md:border-l text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ $quickActionData['transaksiDibatalkan'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 mt-1">Transaksi Dibatalkan</p>
                        </button>
                        <button @click="openModal('produkTidakAktif')" class="px-4 lg:border-l lg:border-gray-200 text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ $quickActionData['produkTidakAktif'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 mt-1">Produk tidak aktif</p>
                        </button>
                        <button @click="openModal('produkHabis')" class="px-4 lg:border-l lg:border-gray-200 md:border-l text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ $quickActionData['produkHabis'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 mt-1">Produk Habis</p>
                        </button>
                    </div>
                </div>

                {{-- Include semua modal Anda di sini --}}
                @include('ecommerce.partials.modals.perlu-diproses')
                @include('ecommerce.partials.modals.dalam-pengiriman')
                @include('ecommerce.partials.modals.menunggu-penyelesaian')
                @include('ecommerce.partials.modals.transaksi-selesai')
                @include('ecommerce.partials.modals.transaksi-dibatalkan')
                @include('ecommerce.partials.modals.produk-tidak-aktif')
                @include('ecommerce.partials.modals.produk-habis')
            </div>

            <!-- Bagian Peringatan Stok Rendah -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Peringatan Stok Rendah</h2>
                        <a href="{{ route('ecommerce.products.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">Lihat Semua Produk</a>
                    </div>
                    <div class="max-h-[260px] overflow-y-auto pr-2">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Stok</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($lowStockProducts as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-16 w-16">
                                                    <img class="h-16 w-16 rounded-md object-cover" 
                                                         src="{{ $product->main_image_url ?? 'https://via.placeholder.com/150' }}" 
                                                         alt="{{ $product->title }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($product->total_stock <= 5)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ $product->total_stock }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ $product->total_stock }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada produk dengan stok rendah.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Satu Baris Untuk Top Sales dan Grafik -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mb-8">
                
             <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6 bg-white border-gray-200 flex flex-col">
                            <div>
                                <h2 class="text-xl font-bold text-center mb-4">Top 3 Produk Terlaris</h2>

                                {{-- Form Filter Tanggal --}}
                                <form method="GET" action="{{ route('dashboard.ecommerce') }}" class="flex items-center justify-center space-x-2 md:space-x-4">
                                    <div class="flex-1">
                                        <label for="topProductStartDate" class="block text-sm font-medium text-gray-700">Mulai</label>
                                        <input type="date" id="topProductStartDate" name="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label for="topProductEndDate" class="block text-sm font-medium text-gray-700">Selesai</label>
                                        <input type="date" id="topProductEndDate" name="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="self-end flex items-center space-x-1">
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Filter</button>
                                        @if($startDate || $endDate)
                                        <a href="{{ route('dashboard.ecommerce') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">Reset</a>
                                        @endif
                                    </div>
                                </form>

                                {{-- Tampilan Podium Dinamis --}}
                                @if($topProducts->isNotEmpty())
                                <div class="flex items-end justify-center space-x-4 text-center mt-6">
                                    {{-- Posisi 2 --}}
                                    <div class="w-1/4">
                                        @if(isset($topProducts[1]))
                                        <img src="{{ $topProducts[1]->image_url }}" alt="{{ $topProducts[1]->product_name }}" class="w-16 h-16 object-cover mx-auto rounded-full border-4 border-gray-300">
                                        <h4 class="mt-2 font-semibold text-sm truncate">{{ $topProducts[1]->product_name }}</h4>
                                        <div class="bg-gray-300 rounded-t-lg h-24 mt-2 flex items-center justify-center">
                                            <span class="text-3xl font-bold text-white">2</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ $topProducts[1]->total_sold }} terjual</p>
                                        @endif
                                    </div>
                                    {{-- Posisi 1 --}}
                                    <div class="w-1/3">
                                        @if(isset($topProducts[0]))
                                        <img src="{{ $topProducts[0]->image_url }}" alt="{{ $topProducts[0]->product_name }}" class="w-16 h-16 object-cover mx-auto rounded-full border-4 border-yellow-400">
                                        <h4 class="mt-2 font-semibold text-sm truncate">{{ $topProducts[0]->product_name }}</h4>
                                        <div class="bg-yellow-400 rounded-t-lg h-32 mt-2 flex items-center justify-center">
                                            <span class="text-4xl font-bold text-white">1</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ $topProducts[0]->total_sold }} terjual</p>
                                        @endif
                                    </div>
                                    {{-- Posisi 3 --}}
                                    <div class="w-1/4">
                                        @if(isset($topProducts[2]))
                                        <img src="{{ $topProducts[2]->image_url }}" alt="{{ $topProducts[2]->product_name }}" class="w-16 h-16 object-cover mx-auto rounded-full border-4 border-yellow-600">
                                        <h4 class="mt-2 font-semibold text-sm truncate">{{ $topProducts[2]->product_name }}</h4>
                                        <div class="bg-yellow-600 rounded-t-lg h-20 mt-2 flex items-center justify-center">
                                            <span class="text-3xl font-bold text-white">3</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ $topProducts[2]->total_sold }} terjual</p>
                                        @endif
                                    </div>
                                </div>
                                @else
                                <div class="text-center mt-10 text-gray-500">
                                    <p>Tidak ada data penjualan untuk rentang tanggal yang dipilih.</p>
                                </div>
                                @endif
                            </div>

                            <!-- BAGIAN BARU: 3 TRANSAKSI TERAKHIR -->
                            <div class="mt-8 pt-6 border-t border-gray-200 flex-grow">
                                <h3 class="text-lg font-bold text-center mb-4">3 Transaksi Terakhir</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk & Pembeli</th>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse ($recentTransactions as $transaction)
                                                <tr>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-10 w-10">
                                                                <img class="h-10 w-10 rounded-md object-cover" src="{{ $transaction->product_image ?? 'https://via.placeholder.com/150' }}" alt="">
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-gray-900 truncate" title="{{ $transaction->product_name }}">{{ $transaction->product_name }}</div>
                                                                <div class="text-sm text-gray-500">oleh {{ $transaction->recipient_name }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                        <div class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($transaction->transaction_time)->format('d M Y, H:i') }}</div>
                                                        <div class="text-xs">{{ \Carbon\Carbon::parse($transaction->transaction_time)->diffForHumans() }}</div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="px-6 py-4 text-center text-gray-500">
                                                        Tidak ada transaksi terbaru pada rentang tanggal ini.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<div class="lg:col-span-3" x-data="{ 
    salesChart: null, 
    startDate: '{{ $startDate ?? now()->subDays(30)->toDateString() }}', 
    endDate: '{{ $endDate ?? now()->toDateString() }}', 
    selectedProduct: 'all', 
    chartMetric: 'revenue', 
    isLoading: true, 
    hasData: false,
    updateTimeout: null,
    isRendering: false,
    
    destroyChart() {
        if (this.salesChart) {
            try {
                this.salesChart.destroy();
            } catch (e) {
                console.warn('Chart already destroyed');
            }
            this.salesChart = null;
        }
    },
    
    initChart(labels, data) { 
        if (this.isRendering) {
            console.warn('Chart rendering in progress');
            return;
        }
        
        this.isRendering = true;
        this.destroyChart();
        
        this.$nextTick(() => {
            setTimeout(() => {
                const canvas = this.$refs.salesChartCanvas;
                
                if (!canvas || !canvas.getContext) {
                    console.warn('Canvas not ready');
                    this.isRendering = false;
                    return;
                }
                
                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    this.isRendering = false;
                    return;
                }
                
                const isRevenue = this.chartMetric === 'revenue';
                let yAxisMin, yAxisMax;
                const dataMin = Math.min(...data);
                const dataMax = Math.max(...data);
                const range = dataMax - dataMin;
                
                if (range === 0) {
                    const padding = isRevenue ? Math.max(dataMax * 0.1, 0.5) : Math.max(dataMax * 0.1, 1);
                    yAxisMin = dataMin - padding;
                    yAxisMax = dataMax + padding;
                } else {
                    const padding = range * 0.05;
                    yAxisMin = dataMin - padding;
                    yAxisMax = dataMax + padding;
                }
                
                if (dataMin >= 0) {
                    yAxisMin = Math.max(0, yAxisMin);
                }
                
                try {
                    this.salesChart = new Chart(ctx, { 
                        type: 'line', 
                        data: { 
                            labels: labels, 
                            datasets: [{ 
                                label: isRevenue ? 'Pendapatan (Juta Rp)' : 'Jumlah Terjual', 
                                data: data, 
                                backgroundColor: 'rgba(79, 70, 229, 0.2)', 
                                borderColor: 'rgba(79, 70, 229, 1)', 
                                borderWidth: 2, 
                                tension: 0.3, 
                                pointRadius: 4, 
                                pointBackgroundColor: 'rgba(79, 70, 229, 1)', 
                                pointHoverRadius: 6, 
                            }] 
                        }, 
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false, 
                            animation: {
                                duration: 750
                            },
                            scales: { 
                                y: { 
                                    min: yAxisMin, 
                                    max: yAxisMax, 
                                    ticks: { 
                                        maxTicksLimit: 8, 
                                        callback: (value) => isRevenue ? value.toFixed(2) + ' Jt' : Math.round(value) 
                                    } 
                                } 
                            }, 
                            plugins: { 
                                legend: { 
                                    display: true, 
                                    position: 'top' 
                                }, 
                                tooltip: { 
                                    callbacks: { 
                                        label: (context) => { 
                                            let label = context.dataset.label || ''; 
                                            if (label) { 
                                                label += ': '; 
                                            } 
                                            if (context.parsed.y !== null) { 
                                                label += isRevenue ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y * 1000000) : context.parsed.y + ' unit'; 
                                            } 
                                            return label; 
                                        } 
                                    } 
                                } 
                            } 
                        } 
                    });
                } catch (e) {
                    console.error('Failed to create chart:', e);
                }
                
                this.isRendering = false;
            }, 150);
        });
    }, 
    
    updateChartDataDebounced() {
        if (this.updateTimeout) {
            clearTimeout(this.updateTimeout);
        }
        
        this.updateTimeout = setTimeout(() => {
            this.updateChartData();
        }, 300);
    },
    
    updateChartData() { 
        if (this.isRendering) {
            console.warn('Please wait for current render to complete');
            return;
        }
        
        this.isLoading = true; 
        
        $.ajax({ 
            url: '{{ route('ecommerce.dashboard.chart_data') }}', 
            type: 'GET', 
            data: { 
                start_date: this.startDate, 
                end_date: this.endDate, 
                product_name: this.selectedProduct 
            }, 
            success: (response) => { 
                const originalData = this.chartMetric === 'revenue' ? response.revenue : response.quantity; 
                const originalLabels = response.labels; 
                const filteredLabels = []; 
                const filteredData = []; 
                
                originalData.forEach((value, index) => { 
                    if (value > 0) { 
                        filteredData.push(value); 
                        filteredLabels.push(originalLabels[index]); 
                    } 
                }); 
                
                if (filteredData.length > 0) { 
                    this.hasData = true; 
                    this.$nextTick(() => { 
                        if (this.$refs.salesChartCanvas) { 
                            this.initChart(filteredLabels, filteredData); 
                        } 
                    }); 
                } else { 
                    this.hasData = false; 
                    this.destroyChart();
                } 
            }, 
            error: () => { 
                alert('Gagal memuat data grafik. Silakan coba lagi.'); 
                this.hasData = false; 
                this.destroyChart();
            }, 
            complete: () => { 
                this.isLoading = false; 
            } 
        }); 
    },
    
    forceRerender() {
        this.destroyChart();
        this.isRendering = false;
        this.updateChartData();
    }
}" 
x-init="updateChartData();"
@alpine:destroyed="destroyChart()">

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full flex flex-col">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Grafik Penjualan</h2>
                <!-- <button 
                    @click="forceRerender()"
                    :disabled="isLoading || isRendering"
                    class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span x-show="!isLoading && !isRendering">🔄 Refresh</span>
                    <span x-show="isLoading || isRendering">Loading...</span>
                </button> -->
            </div>
            
            <div class="flex flex-wrap items-end gap-x-4 gap-y-2">
                <!-- Filter Tanggal -->
                <div>
                    <label for="chartStartDate" class="block text-sm font-medium text-gray-700">Mulai</label>
                    <input type="date" id="chartStartDate" x-model="startDate" 
                        @change="updateChartDataDebounced()"
                        :disabled="isLoading || isRendering"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                </div>
                <div>
                    <label for="chartEndDate" class="block text-sm font-medium text-gray-700">Selesai</label>
                    <input type="date" id="chartEndDate" x-model="endDate" 
                        @change="updateChartDataDebounced()"
                        :disabled="isLoading || isRendering"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                </div>
                
                <!-- Filter Produk -->
                <div class="flex-grow min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700">Produk</label>
                    <select x-model="selectedProduct" 
                            @change="updateChartDataDebounced()"
                            :disabled="isLoading || isRendering"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <option value="all">Semua Produk</option>
                        @foreach($productsForFilter as $productName)
                        <option value="{{ $productName }}">{{ $productName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Metrik & Tombol Aksi -->
                <div class="flex items-end gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Metrik</label>
                        <div class="inline-flex rounded-md shadow-sm mt-1" role="group">
                            <button @click="chartMetric = 'revenue'; updateChartDataDebounced()" type="button" 
                                    :disabled="isLoading || isRendering"
                                    :class="{ 'bg-indigo-600 text-white': chartMetric === 'revenue', 'bg-white text-gray-700 hover:bg-gray-50': chartMetric !== 'revenue' }" 
                                    class="px-3 py-2 text-sm font-medium rounded-l-md border disabled:opacity-50 disabled:cursor-not-allowed">Rp</button>
                            <button @click="chartMetric = 'quantity'; updateChartDataDebounced()" type="button" 
                                    :disabled="isLoading || isRendering"
                                    :class="{ 'bg-indigo-600 text-white': chartMetric === 'quantity', 'bg-white text-gray-700 hover:bg-gray-50': chartMetric !== 'quantity' }" 
                                    class="px-3 py-2 text-sm font-medium rounded-r-md border disabled:opacity-50 disabled:cursor-not-allowed">Qty</button>
                        </div>
                    </div>
                    <div>
                        <button @click="updateChartData()" 
                                :disabled="isLoading || isRendering"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 h-full disabled:bg-indigo-400 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!isLoading && !isRendering">Filter</span>
                            <span x-show="isLoading || isRendering">Memuat...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-grow p-6 pt-0">
            <div class="h-[450px] relative">
                <canvas x-show="!isLoading && hasData" x-ref="salesChartCanvas"></canvas>

                <!-- Loading State -->
                <div x-show="isLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-20 rounded-b-lg">
                    <div class="text-center">
                        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Memuat data grafik...</p>
                    </div>
                </div>

                <!-- No Data State -->
                <div x-show="!isLoading && !hasData" class="absolute inset-0 flex items-center justify-center z-10">
                    <div class="text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6v-3m3 3v-1m-6-10H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-3l-4-4z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak Ada Data Penjualan</h3>
                        <p class="mt-1 text-sm text-gray-500">Tidak ada aktivitas penjualan pada rentang tanggal yang dipilih.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                
            </div>

            <!-- Bagian Kolom E-Commerce: Shopee dan Tokopedia -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- KARTU SHOPEE -->
                <div class="bg-orange-500 text-white rounded-lg shadow-lg p-6">
                    <h3 class="text-2xl font-bold flex items-center mb-4">
                        <svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path><path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                        Shopee
                    </h3>
                    
                    {{-- Filter Area --}}
                    <div class="flex items-end gap-2 mb-6">
                        <div class="flex-1">
                            <label for="shopee-start-date" class="block text-sm font-medium text-orange-100">Mulai</label>
                            <input type="date" id="shopee-start-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-800 sm:text-sm">
                        </div>
                        <div class="flex-1">
                            <label for="shopee-end-date" class="block text-sm font-medium text-orange-100">Selesai</label>
                            <input type="date" id="shopee-end-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-800 sm:text-sm">
                        </div>
                        <button id="shopee-filter-btn" class="px-4 py-2 bg-white text-orange-600 rounded-md hover:bg-orange-50 font-bold">Filter</button>
                        <button id="shopee-reset-btn" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 font-bold">Reset</button>
                    </div>

                    {{-- Area Statistik --}}
                    <div id="shopee-stats-container" class="relative">
                        {{-- Loading Spinner --}}
                        <div id="shopee-loading" class="absolute inset-0 bg-orange-500 bg-opacity-75 flex items-center justify-center z-10 hidden">
                            <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <div class="space-y-4">
                                        <div class="flex justify-between items-center">
                <span class="text-lg text-orange-100">Total Tonase</span>
                <span id="shopee-total-tonase" class="text-2xl font-bold">{{ number_format($shopeeCardData['total_tonnage'], 2, ',', '.') }} Ton</span>
            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-lg text-orange-100">Total Nilai</span>
                                <span id="shopee-total-nilai" class="text-2xl font-bold">Rp {{ number_format($shopeeCardData['total_nilai'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-lg text-orange-100">Total Pembeli</span>
                                <span id="shopee-total-pembeli" class="text-2xl font-bold">{{ $shopeeCardData['total_pembeli'] }} Pembeli</span>
                            </div>
                        </div>

                        {{-- Top 3 Pembeli --}}
                        <div class="mt-6 pt-4 border-t border-orange-400">
                            <h4 class="font-bold text-lg mb-2">Top 3 Pembeli</h4>
                            <div id="shopee-top-buyers" class="space-y-3 text-sm">
                                @forelse ($shopeeCardData['top_buyers'] as $buyer)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <img src="https://i.pravatar.cc/40?u={{ urlencode($buyer->recipient_name) }}" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-orange-200">
                                            <p class="font-semibold">{{ $buyer->recipient_name }}</p>
                                        </div>
                                        <span class="font-bold bg-white text-orange-600 px-2 py-1 rounded-full text-xs">{{ $buyer->purchase_count }}x Beli</span>
                                    </div>
                                @empty
                                    <p class="text-orange-100">Tidak ada data pembeli.</p>
                                @endforelse
                            </div>
                        </div>
                        <a href="https://seller.shopee.co.id/" 
                           class="mt-6 inline-block bg-white text-orange-600 font-bold py-2 px-4 rounded-lg hover:bg-orange-100 transition" 
                           target="_blank" 
                           rel="noopener noreferrer">
                           Buka Seller Centre
                        </a>
                    </div>
                </div>

                <!-- KARTU TOKOPEDIA -->
                <div class="bg-green-600 text-white rounded-lg shadow-lg p-6">
                    <h3 class="text-2xl font-bold flex items-center mb-4">
                        <svg class="w-8 h-8 mr-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.658-.463 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                        Tokopedia
                    </h3>
                    
                    {{-- Filter Area --}}
                    <div class="flex items-end gap-2 mb-6">
                        <div class="flex-1">
                            <label for="tokopedia-start-date" class="block text-sm font-medium text-green-100">Mulai</label>
                            <input type="date" id="tokopedia-start-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-800 sm:text-sm">
                        </div>
                        <div class="flex-1">
                            <label for="tokopedia-end-date" class="block text-sm font-medium text-green-100">Selesai</label>
                            <input type="date" id="tokopedia-end-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-800 sm:text-sm">
                        </div>
                        <button id="tokopedia-filter-btn" class="px-4 py-2 bg-white text-green-600 rounded-md hover:bg-green-50 font-bold">Filter</button>
                        <button id="tokopedia-reset-btn" class="px-4 py-2 bg-green-700 text-white rounded-md hover:bg-green-800 font-bold">Reset</button>
                    </div>

                    {{-- Area Statistik --}}
                    <div id="tokopedia-stats-container" class="relative">
                        {{-- Loading Spinner --}}
                        <div id="tokopedia-loading" class="absolute inset-0 bg-green-600 bg-opacity-75 flex items-center justify-center z-10 hidden">
                            <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                <span class="text-lg text-green-100">Total Tonase</span>
                <span id="tokopedia-total-tonase" class="text-2xl font-bold">{{ number_format($tokopediaCardData['total_tonnage'], 2, ',', '.') }} Ton</span>
            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-lg text-green-100">Total Pesanan</span>
                                <span class="text-2xl font-bold">8 Ton</span> {{-- Dummy value --}}
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-lg text-green-100">Total Nilai</span>
                                <span id="tokopedia-total-nilai" class="text-2xl font-bold">Rp {{ number_format($tokopediaCardData['total_nilai'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-lg text-green-100">Total Pembeli</span>
                                <span id="tokopedia-total-pembeli" class="text-2xl font-bold">{{ $tokopediaCardData['total_pembeli'] }} Pembeli</span>
                            </div>
                        </div>

                        {{-- Top 3 Pembeli --}}
                        <div class="mt-6 pt-4 border-t border-green-500">
                            <h4 class="font-bold text-lg mb-2">Top 3 Pembeli</h4>
                            <div id="tokopedia-top-buyers" class="space-y-3 text-sm">
                                @forelse ($tokopediaCardData['top_buyers'] as $buyer)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <img src="https://i.pravatar.cc/40?u={{ urlencode($buyer->recipient_name) }}" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-green-200">
                                            <p class="font-semibold">{{ $buyer->recipient_name }}</p>
                                        </div>
                                        <span class="font-bold bg-white text-green-600 px-2 py-1 rounded-full text-xs">{{ $buyer->purchase_count }}x Beli</span>
                                    </div>
                                @empty
                                    <p class="text-green-100">Tidak ada data pembeli.</p>
                                @endforelse
                            </div>
                        </div>
                        <a href="https://seller-id.tokopedia.com/" 
                           class="mt-6 inline-block bg-white text-green-600 font-bold py-2 px-4 rounded-lg hover:bg-green-100 transition" 
                           target="_blank" 
                           rel="noopener noreferrer">
                           Buka Seller Center
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script untuk Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Script untuk AJAX Kartu Shopee & Tokopedia -->
    <script>
    // =================================================================
    // == PERBAIKAN: FUNGSI INI DIPINDAHKAN KE LUAR DOCUMENT.READY ======
    // Ini membuatnya 'global' dan bisa ditemukan oleh Alpine.js
    // =================================================================
    const formatTonnage = (number) => {
        return `${parseFloat(number).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} Ton`;
    };
    
    function dashboardAksiCepat() {
        return {
            activeModal: '', // Variabel untuk menyimpan modal mana yang aktif
            modalData: {
                shopee: [],
                tokopedia: []
            },
            isLoading: false,
            
            // Fungsi untuk membuka modal dan mengambil data
            openModal(category) {
                this.isLoading = true;
                this.activeModal = category; // Set modal yang aktif
                
                // Ambil data dari server
                fetch(`{{ route('ecommerce.dashboard.modalData') }}?category=${category}`)
                    .then(response => response.json())
                    .then(data => {
                        this.modalData = data;
                        this.isLoading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching modal data:', error);
                        this.isLoading = false;
                        alert('Gagal memuat data. Silakan coba lagi.');
                    });
            },

            // Fungsi untuk menutup modal
            closeModal() {
                this.activeModal = '';
                this.modalData = { shopee: [], tokopedia: [] }; // Reset data
            }
        }
    }

    // Kode jQuery tetap berada di dalam document.ready
    $(document).ready(function() {
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        }

        function updateShopeeCard() {
            const startDate = $('#shopee-start-date').val();
            const endDate = $('#shopee-end-date').val();
            $('#shopee-loading').removeClass('hidden');

            $.ajax({
                url: '{{ route("ecommerce.dashboard.shopee_stats") }}',
                type: 'GET',
                data: { start_date: startDate, end_date: endDate },
                success: function(data) {
                    $('#shopee-total-nilai').text(formatRupiah(data.total_nilai));
                    $('#shopee-total-pembeli').text(data.total_pembeli + ' Pembeli');
                    const topBuyersContainer = $('#shopee-top-buyers').empty();
                    if (data.top_buyers && data.top_buyers.length > 0) {
                        data.top_buyers.forEach(buyer => {
                            topBuyersContainer.append(`
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <img src="https://i.pravatar.cc/40?u=${encodeURIComponent(buyer.recipient_name)}" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-orange-200">
                                        <p class="font-semibold">${buyer.recipient_name}</p>
                                    </div>
                                    <span class="font-bold bg-white text-orange-600 px-2 py-1 rounded-full text-xs">${buyer.purchase_count}x Beli</span>
                                </div>
                            `);
                        });
                    } else {
                        topBuyersContainer.html('<p class="text-orange-100">Tidak ada data pembeli.</p>');
                    }
                },
                error: function() { alert('Gagal memuat data Shopee.'); },
                complete: function() { $('#shopee-loading').addClass('hidden'); }
            });
        }

        $('#shopee-filter-btn').on('click', updateShopeeCard);
        $('#shopee-reset-btn').on('click', function() {
            $('#shopee-start-date').val('');
            $('#shopee-end-date').val('');
            updateShopeeCard();
        });

        function updateTokopediaCard() {
            const startDate = $('#tokopedia-start-date').val();
            const endDate = $('#tokopedia-end-date').val();
            $('#tokopedia-loading').removeClass('hidden');

            $.ajax({
                url: '{{ route("ecommerce.dashboard.tokopedia_stats") }}',
                type: 'GET',
                data: { start_date: startDate, end_date: endDate },
                success: function(data) {
                    $('#tokopedia-total-nilai').text(formatRupiah(data.total_nilai));
                    $('#tokopedia-total-pembeli').text(data.total_pembeli + ' Pembeli');
                    const topBuyersContainer = $('#tokopedia-top-buyers').empty();
                    if (data.top_buyers && data.top_buyers.length > 0) {
                        data.top_buyers.forEach(buyer => {
                            topBuyersContainer.append(`
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <img src="https://i.pravatar.cc/40?u=${encodeURIComponent(buyer.recipient_name)}" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-green-200">
                                        <p class="font-semibold">${buyer.recipient_name}</p>
                                    </div>
                                    <span class="font-bold bg-white text-green-600 px-2 py-1 rounded-full text-xs">${buyer.purchase_count}x Beli</span>
                                </div>
                            `);
                        });
                    } else {
                        topBuyersContainer.html('<p class="text-green-100">Tidak ada data pembeli.</p>');
                    }
                },
                error: function() { alert('Gagal memuat data Tokopedia.'); },
                complete: function() { $('#tokopedia-loading').addClass('hidden'); }
            });
        }

        $('#tokopedia-filter-btn').on('click', updateTokopediaCard);
        $('#tokopedia-reset-btn').on('click', function() {
            $('#tokopedia-start-date').val('');
            $('#tokopedia-end-date').val('');
            updateTokopediaCard();
        });
    });
    </script>
</x-app-layout>