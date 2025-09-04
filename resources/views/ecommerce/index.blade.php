<x-app-layout>
    @section('title')
        Dashboard E-Commerce
    @endsection

    {{-- Script Alpine.js untuk fungsionalitas interaktif --}}
    <script src="//unpkg.com/alpinejs" defer></script>

    <div class="py-12">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            <!-- Bagian Atas: Yang Perlu Dilakukan (Dengan Modal Alpine.js) -->
            <div
                x-data="{ openModal: false, modalTitle: '', modalData: [] }"
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
            >
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-1">Yang Perlu Dilakukan</h2>
                    <p class="text-gray-500 mb-6">Hal-hal yang perlu kamu tangani</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 text-center gap-y-8">
                        <!-- Tombol Pemicu Modal -->
                        <button @click="modalTitle = 'Belum Bayar'; openModal = true" class="px-4 text-center"><p class="text-3xl font-bold text-blue-600">0</p><p class="text-sm text-gray-600 mt-1">Belum Bayar</p></button>
                        <button @click="modalTitle = 'Pengiriman Perlu Diproses'; openModal = true" class="px-4 lg:border-l lg:border-gray-200 text-center"><p class="text-3xl font-bold text-blue-600">0</p><p class="text-sm text-gray-600 mt-1">Pengiriman Perlu Diproses</p></button>
                        <button @click="modalTitle = 'Pengiriman Telah Diproses'; openModal = true" class="px-4 lg:border-l lg:border-gray-200 md:border-l text-center"><p class="text-3xl font-bold text-blue-600">0</p><p class="text-sm text-gray-600 mt-1">Pengiriman Telah Diproses</p></button>
                        <button @click="modalTitle = 'Menunggu Respon Pengembalian'; openModal = true" class="px-4 lg:border-l lg:border-gray-200 text-center"><p class="text-3xl font-bold text-blue-600">0</p><p class="text-sm text-gray-600 mt-1">Menunggu Respon Pengembalian</p></button>
                        <button @click="modalTitle = 'Menunggu Respon Pembatalan'; openModal = true" class="px-4 lg:border-l lg:border-gray-200 md:border-l text-center"><p class="text-3xl font-bold text-blue-600">0</p><p class="text-sm text-gray-600 mt-1">Menunggu Respon Pembatalan</p></button>
                        <button @click="modalTitle = 'Produk Diblokir'; openModal = true" class="px-4 lg:border-l lg:border-gray-200 text-center"><p class="text-3xl font-bold text-blue-600">0</p><p class="text-sm text-gray-600 mt-1">Produk Diblokir</p></button>
                        <button @click="modalTitle = 'Produk Habis'; openModal = true" class="px-4 lg:border-l lg:border-gray-200 md:border-l text-center"><p class="text-3xl font-bold text-blue-600">0</p><p class="text-sm text-gray-600 mt-1">Produk Habis</p></button>
                    </div>
                </div>

                <!-- Komponen Modal -->
                <div x-show="openModal" x-cloak style="display: none;" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div @click="openModal = false" class="fixed inset-0 bg-gray-900 bg-opacity-75"></div>
                    <div @click.outside="openModal = false" class="bg-white rounded-lg shadow-xl overflow-hidden max-w-2xl w-full z-10">
                        <div class="flex justify-between items-center px-6 py-4 border-b"><h3 class="text-lg font-bold" x-text="modalTitle"></h3><button @click="openModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div>
                        <div class="p-6"><p class="text-center text-gray-500">Tidak ada data "<span x-text="modalTitle.toLowerCase()"></span>" yang perlu ditangani saat ini.</p></div>
                        <div class="px-6 py-3 bg-gray-50 text-right"><button @click="openModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Tutup</button></div>
                    </div>
                </div>
            </div>

            <!-- Bagian Peringatan Stok Rendah (MODIFIED) -->
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Peringatan Stok Rendah</h2>
                        <a href="/ecommerce/products" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">Lihat Semua Produk</a>
                    </div>
                    {{-- Wrapper div untuk fungsionalitas scroll --}}
                    <div class="max-h-[196px] overflow-y-auto pr-2">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Stok</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                {{-- Contoh Data 1 --}}
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16"><img class="h-16 w-16 rounded-md object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Baju Kemeja"></div>
                                            <div class="ml-4"><div class="text-sm font-medium text-gray-900">Baju Kemeja Polos</div></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">3</span></td>
                                </tr>
                                {{-- Contoh Data 2 --}}
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16"><img class="h-16 w-16 rounded-md object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Celana Jeans"></div>
                                            <div class="ml-4"><div class="text-sm font-medium text-gray-900">Celana Jeans Biru</div></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">5</span></td>
                                </tr>
                                {{-- Tambahan data untuk menunjukkan scroll berfungsi --}}
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16"><img class="h-16 w-16 rounded-md object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Kacamata"></div>
                                            <div class="ml-4"><div class="text-sm font-medium text-gray-900">Kacamata Hitam</div></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">8</span></td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16"><img class="h-16 w-16 rounded-md object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Kaos Putih"></div>
                                            <div class="ml-4"><div class="text-sm font-medium text-gray-900">Kaos Putih Polos</div></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">2</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Satu Baris Untuk Top Sales dan Grafik -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- KIRI: TOP 3 PRODUK TERLARIS -->
                <div class="lg:col-span-2" x-data="{ startDate: '', endDate: '' }" x-init="const today = new Date().toISOString().split('T')[0]; startDate = today; endDate = today;">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-bold text-center mb-4">Top 3 Produk Terlaris</h2>
                            <div class="flex items-center justify-center space-x-2 md:space-x-4">
                                <div class="flex-1"><label for="topProductStartDate" class="block text-sm font-medium text-gray-700">Mulai</label><input type="date" id="topProductStartDate" x-model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                                <div class="flex-1"><label for="topProductEndDate" class="block text-sm font-medium text-gray-700">Selesai</label><input type="date" id="topProductEndDate" x-model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                                <button class="self-end px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Filter</button>
                            </div>
                            <div class="flex items-end justify-center space-x-4 text-center mt-6">
                                <div class="w-1/4"><img src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Produk 2" class="w-16 h-16 object-cover mx-auto rounded-full border-4 border-gray-300"><h4 class="mt-2 font-semibold text-sm">Kaos Putih</h4><div class="bg-gray-300 rounded-t-lg h-24 mt-2 flex items-center justify-center"><span class="text-3xl font-bold text-white">2</span></div></div>
                                <div class="w-1/3"><img src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Produk 1" class="w-16 h-16 object-cover mx-auto rounded-full border-4 border-yellow-400"><h4 class="mt-2 font-semibold text-sm">Kacamata</h4><div class="bg-yellow-400 rounded-t-lg h-32 mt-2 flex items-center justify-center"><span class="text-4xl font-bold text-white">1</span></div></div>
                                <div class="w-1/4"><img src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Produk 3" class="w-16 h-16 object-cover mx-auto rounded-full border-4 border-yellow-600"><h4 class="mt-2 font-semibold text-sm">Kaos Hitam</h4><div class="bg-yellow-600 rounded-t-lg h-20 mt-2 flex items-center justify-center"><span class="text-3xl font-bold text-white">3</span></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KANAN: GRAFIK PENJUALAN DINAMIS DENGAN SEMUA FILTER -->
                <div class="lg:col-span-3"
                    x-data="{
                        salesChart: null,
                        startDate: '',
                        endDate: '',
                        selectedProduct: 'all',
                        chartMetric: 'revenue',
                        products: [
                            { id: 'all', name: 'Semua Produk' },
                            { id: 'product_a', name: 'Baju Kemeja Polos' },
                            { id: 'product_b', name: 'Celana Jeans Biru' },
                            { id: 'product_c', name: 'Kacamata' }
                        ],
                        chartData: {
                            revenue: { all: [12.5, 19.2, 14.3, 25.5, 22.1, 30.7, 28.4], product_a: [5.1, 8.2, 6.0, 11.3, 9.5, 12.1, 11.9], product_b: [3.5, 4.1, 3.8, 7.2, 6.8, 9.9, 9.1], product_c: [3.9, 6.9, 4.5, 7.0, 5.8, 8.7, 7.4] },
                            quantity: { all: [120, 150, 135, 198, 180, 250, 241], product_a: [50, 65, 55, 80, 75, 98, 95], product_b: [35, 40, 38, 55, 52, 77, 72], product_c: [35, 45, 42, 63, 53, 75, 74] }
                        },
                        updateChart() {
                            const data = this.chartData[this.chartMetric][this.selectedProduct];
                            const isRevenue = this.chartMetric === 'revenue';
                            this.salesChart.data.datasets[0].data = data;
                            this.salesChart.data.datasets[0].label = isRevenue ? 'Pendapatan (Juta Rp)' : 'Jumlah Terjual';
                            this.salesChart.options.scales.y.ticks.callback = (value) => isRevenue ? value + ' Jt' : value;
                            this.salesChart.options.plugins.tooltip.callbacks.label = (context) => {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) { label += isRevenue ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y * 1000000) : context.parsed.y + ' unit'; }
                                return label;
                            };
                            this.salesChart.update();
                        }
                    }"
                    x-init="
                        const today = new Date().toISOString().split('T')[0]; startDate = today; endDate = today;
                        const ctx = $refs.salesChartCanvas.getContext('2d');
                        salesChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
                                datasets: [{
                                    label: 'Pendapatan (Juta Rp)',
                                    data: chartData.revenue.all,
                                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 2,
                                    tension: 0.3
                                }]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                scales: { y: { beginAtZero: true, ticks: { callback: (value) => value + ' Jt' } } },
                                plugins: {
                                    legend: { display: true, position: 'top' },
                                    tooltip: { callbacks: { label: (context) => { let label = context.dataset.label || ''; if (label) { label += ': '; } if (context.parsed.y !== null) { label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y * 1000000); } return label; } } }
                                }
                            }
                        });
                    "
                >
                     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4"><h2 class="text-xl font-bold">Grafik Penjualan</h2></div>
                            <!-- Area Filter Grafik -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Filter Tanggal -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div><label for="chartStartDate" class="block text-sm font-medium text-gray-700">Mulai</label><input type="date" id="chartStartDate" x-model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"></div>
                                    <div><label for="chartEndDate" class="block text-sm font-medium text-gray-700">Selesai</label><input type="date" id="chartEndDate" x-model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"></div>
                                </div>
                                <!-- Filter Produk & Metrik -->
                                <div class="flex items-end gap-2">
                                    <div class="flex-grow"><label class="block text-sm font-medium text-gray-700">Produk</label><select x-model="selectedProduct" @change="updateChart()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"><template x-for="product in products" :key="product.id"><option :value="product.id" x-text="product.name"></option></template></select></div>
                                    <div class="flex-shrink-0"><label class="block text-sm font-medium text-gray-700">Metrik</label><div class="inline-flex rounded-md shadow-sm mt-1" role="group"><button @click="chartMetric = 'revenue'; updateChart()" type="button" :class="{ 'bg-indigo-600 text-white': chartMetric === 'revenue', 'bg-white text-gray-700 hover:bg-gray-50': chartMetric !== 'revenue' }" class="px-3 py-2 text-sm font-medium rounded-l-md border">Rp</button><button @click="chartMetric = 'quantity'; updateChart()" type="button" :class="{ 'bg-indigo-600 text-white': chartMetric === 'quantity', 'bg-white text-gray-700 hover:bg-gray-50': chartMetric !== 'quantity' }" class="px-3 py-2 text-sm font-medium rounded-r-md border">Qty</button></div></div>
                                </div>
                            </div>
                            <div class="h-80"><canvas x-ref="salesChartCanvas"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Kolom E-Commerce: Shopee dan Tokopedia (Sudah Dimodifikasi) -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- KARTU SHOPEE -->
                <div class="bg-orange-500 text-white rounded-lg shadow-lg p-6" x-data="{ startDate: '', endDate: '' }" x-init="const today = new Date().toISOString().split('T')[0]; startDate = today; endDate = today;">
                    <h3 class="text-2xl font-bold flex items-center mb-4"><svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path><path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>Shopee</h3>
                    <div class="flex items-end gap-2 mb-6">
                        <div class="flex-1"><label for="shopeeStartDate" class="block text-sm font-medium text-orange-100">Mulai</label><input type="date" id="shopeeStartDate" x-model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-800 sm:text-sm"></div>
                        <div class="flex-1"><label for="shopeeEndDate" class="block text-sm font-medium text-orange-100">Selesai</label><input type="date" id="shopeeEndDate" x-model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-800 sm:text-sm"></div>
                        <button class="px-4 py-2 bg-white text-orange-600 rounded-md hover:bg-orange-50 font-bold">Filter</button>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center"><span class="text-lg text-orange-100">Total Pesanan</span><span class="text-2xl font-bold">12 Ton</span></div>
                        <div class="flex justify-between items-center"><span class="text-lg text-orange-100">Total Nilai</span><span class="text-2xl font-bold">Rp 1.250.000</span></div>
                        <div class="flex justify-between items-center"><span class="text-lg text-orange-100">Total Pembeli</span><span class="text-2xl font-bold">89 Pembeli</span></div>
                    </div>

                    <!-- Tambahan Top 3 Pembeli -->
                    <div class="mt-6 pt-4 border-t border-orange-400">
                        <h4 class="font-bold text-lg mb-2">Top 3 Pembeli</h4>
                        <div class="space-y-3 text-sm">
                            <!-- Pembeli 1 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img src="https://i.pravatar.cc/40?u=user1" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-orange-200">
                                    <div>
                                        <p class="font-semibold">Budi_Susanto</p>
                                        <p class="text-xs text-orange-100">Produk: Kacamata</p>
                                    </div>
                                </div>
                                <span class="font-bold bg-white text-orange-600 px-2 py-1 rounded-full text-xs">15x Beli</span>
                            </div>
                            <!-- Pembeli 2 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img src="https://i.pravatar.cc/40?u=user2" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-orange-200">
                                    <div>
                                        <p class="font-semibold">Citra_Lestari</p>
                                        <p class="text-xs text-orange-100">Produk: Baju Kemeja Polos</p>
                                    </div>
                                </div>
                                <span class="font-bold bg-white text-orange-600 px-2 py-1 rounded-full text-xs">12x Beli</span>
                            </div>
                            <!-- Pembeli 3 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img src="https://i.pravatar.cc/40?u=user3" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-orange-200">
                                    <div>
                                        <p class="font-semibold">Dewi_Ayu</p>
                                        <p class="text-xs text-orange-100">Produk: Celana Jeans Biru</p>
                                    </div>
                                </div>
                                <span class="font-bold bg-white text-orange-600 px-2 py-1 rounded-full text-xs">9x Beli</span>
                            </div>
                        </div>
                    </div>

                    <a href="#" class="mt-6 inline-block bg-white text-orange-600 font-bold py-2 px-4 rounded-lg hover:bg-orange-100 transition">Buka Seller Centre</a>
                </div>
                <!-- KARTU TOKOPEDIA -->
                <div class="bg-green-600 text-white rounded-lg shadow-lg p-6" x-data="{ startDate: '', endDate: '' }" x-init="const today = new Date().toISOString().split('T')[0]; startDate = today; endDate = today;">
                    <h3 class="text-2xl font-bold flex items-center mb-4"><svg class="w-8 h-8 mr-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.658-.463 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>Tokopedia</h3>
                    <div class="flex items-end gap-2 mb-6">
                        <div class="flex-1"><label for="tokpedStartDate" class="block text-sm font-medium text-green-100">Mulai</label><input type="date" id="tokpedStartDate" x-model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-800 sm:text-sm"></div>
                        <div class="flex-1"><label for="tokpedEndDate" class="block text-sm font-medium text-green-100">Selesai</label><input type="date" id="tokpedEndDate" x-model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-800 sm:text-sm"></div>
                        <button class="px-4 py-2 bg-white text-green-600 rounded-md hover:bg-green-50 font-bold">Filter</button>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center"><span class="text-lg text-green-100">Total Pesanan</span><span class="text-2xl font-bold">8 Ton</span></div>
                        <div class="flex justify-between items-center"><span class="text-lg text-green-100">Total Nilai</span><span class="text-2xl font-bold">Rp 840.000</span></div>
                        <div class="flex justify-between items-center"><span class="text-lg text-green-100">Total Pembeli</span><span class="text-2xl font-bold">56 Pembeli</span></div>
                    </div>

                    <!-- Tambahan Top 3 Pembeli -->
                    <div class="mt-6 pt-4 border-t border-green-500">
                        <h4 class="font-bold text-lg mb-2">Top 3 Pembeli</h4>
                        <div class="space-y-3 text-sm">
                            <!-- Pembeli 1 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img src="https://i.pravatar.cc/40?u=user4" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-green-200">
                                    <div>
                                        <p class="font-semibold">Eko_Prasetyo</p>
                                        <p class="text-xs text-green-100">Produk: Kaos Hitam</p>
                                    </div>
                                </div>
                                <span class="font-bold bg-white text-green-600 px-2 py-1 rounded-full text-xs">18x Beli</span>
                            </div>
                            <!-- Pembeli 2 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img src="https://i.pravatar.cc/40?u=user5" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-green-200">
                                    <div>
                                        <p class="font-semibold">Fitri_Nur</p>
                                        <p class="text-xs text-green-100">Produk: Kaos Putih</p>
                                    </div>
                                </div>
                                <span class="font-bold bg-white text-green-600 px-2 py-1 rounded-full text-xs">14x Beli</span>
                            </div>
                            <!-- Pembeli 3 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img src="https://i.pravatar.cc/40?u=user6" alt="User" class="w-8 h-8 rounded-full mr-3 border-2 border-green-200">
                                    <div>
                                        <p class="font-semibold">Gita_Sari</p>
                                        <p class="text-xs text-green-100">Produk: Celana Jeans Biru</p>
                                    </div>
                                </div>
                                <span class="font-bold bg-white text-green-600 px-2 py-1 rounded-full text-xs">11x Beli</span>
                            </div>
                        </div>
                    </div>

                    <a href="#" class="mt-6 inline-block bg-white text-green-600 font-bold py-2 px-4 rounded-lg hover:bg-green-100 transition">Buka Seller Center</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Script untuk Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</x-app-layout>