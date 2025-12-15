<x-app-layout>
    @section('title')
        Oil Stock Monitoring
    @endsection

    {{-- Header & Breadcrumb Section --}}
    <div class="px-2 py-2 w-full mx-auto">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Title --}}
            <h4 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="bg-blue-100 text-blue-600 p-2 rounded-lg">
                    <i class="mdi mdi-chart-bar"></i>
                </span>
                Oil Stock Monitoring
            </h4>

            {{-- Modern Breadcrumb --}}
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 bg-white px-4 py-2 rounded-full shadow-sm border border-gray-100">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                            <i class="mdi mdi-home-outline text-lg"></i>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="mdi mdi-chevron-right text-gray-400 text-lg"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Data Dashboard</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="mdi mdi-chevron-right text-gray-400 text-lg"></i>
                            <span class="ml-1 text-xs font-bold text-blue-600 md:ml-2 bg-blue-50 px-3 py-1 rounded-full">
                                Oil Stock
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Main Content --}}
    <section class="px-2 pb-12">
        <div class="w-full bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6">
                
                {{-- Component Navigation --}}
                <div class="mb-3">
                    
                    {{-- Gap dikurangi jadi gap-2 agar lebih rapat --}}
                    <div class="flex flex-wrap gap-2" id="button-container">
                        <button type="button" class="component-btn group" data-component="tank_yard_bdt">
                            <i class="mdi mdi-silo mr-1.5"></i> Tank Yard 80T
                        </button>
                        <button type="button" class="component-btn group" data-component="batch_refinery">
                            <i class="mdi mdi-factory mr-1.5"></i> Batch Refinery
                        </button>
                        <button type="button" class="component-btn group" data-component="fat_blend_tank">
                            <i class="mdi mdi-beaker-outline mr-1.5"></i> Fat Blend Tank
                        </button>
                        <button type="button" class="component-btn group" data-component="tank_yard_1t">
                            <i class="mdi mdi-tank mr-1.5"></i> Tank Yard 1T
                        </button>
                        <button type="button" class="component-btn group" data-component="bleached_oil_tank">
                            <i class="mdi mdi-water-opacity mr-1.5"></i> Bleached Oil
                        </button>
                        <button type="button" class="component-btn group" data-component="packing_room">
                            <i class="mdi mdi-package-variant-closed mr-1.5"></i> Packing Room
                        </button>
                        <button type="button" class="component-btn group" data-component="current_oil_stock">
                            <i class="mdi mdi-chart-line mr-1.5"></i> Current Stock
                        </button>
                        <button type="button" class="component-btn group" data-component="hydrogen_nitrogen_ammonia">
                            <i class="mdi mdi-gas-cylinder mr-1.5"></i> Gas Stock
                        </button>
                    </div>
                </div>

                <div class="h-px bg-gray-100 w-full mb-6"></div>

                {{-- Dynamic Content Container --}}
                <div id="component-container" class="relative min-h-[400px]">
                    {{-- Default Loading State --}}
                    <div class="flex flex-col justify-center items-center h-96">
                        <svg class="animate-spin h-10 w-10 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-gray-500 text-sm font-medium">Loading data...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            $(function () {
                const componentContainer = $('#component-container');
                const buttons = $('.component-btn');

                // --- CONFIGURATION: TAILWIND CLASSES ---
                
                // Style UMUM (Base Classes) untuk semua tombol agar rapi
                const baseClasses = [
                    'px-4', 'py-2',         // Padding lebih rapat
                    'rounded-lg',           // Sudut rounded standar (bukan XL)
                    'text-sm', 'font-medium', 
                    'transition-all', 'duration-200', 'ease-in-out', 
                    'flex', 'items-center', 'justify-center'
                ];

                // Tombol MATI (Inactive)
                const inactiveClasses = [
                    'bg-white', 
                    'text-gray-600', 
                    'border', 'border-gray-200', 
                    'hover:bg-gray-50', 'hover:text-blue-600', 'hover:border-blue-300'
                ];

                // Tombol HIDUP (Active) - Solid Blue Primary
                const activeClasses = [
                    'bg-blue-600',          // Warna Primary Solid
                    'text-white',           // Teks Putih
                    'border', 'border-transparent',
                    'shadow-md', 'shadow-blue-500/30', // Shadow biru halus
                    'font-semibold'         // Teks sedikit lebih tebal
                ];

                // Fungsi update style
                function updateButtonStyles(activeBtn) {
                    buttons.each(function() {
                        const btn = $(this);
                        
                        // Reset semua class dinamis
                        btn.removeClass(activeClasses.join(' '));
                        btn.removeClass(inactiveClasses.join(' '));
                        
                        // Pastikan base class selalu ada (hanya sekali add)
                        if (!btn.hasClass('px-4')) {
                            btn.addClass(baseClasses.join(' '));
                        }

                        if (this === activeBtn) {
                            btn.addClass(activeClasses.join(' '));
                        } else {
                            btn.addClass(inactiveClasses.join(' '));
                        }
                    });
                }

                // Loader HTML
                const loaderHtml = `
                    <div class="flex flex-col justify-center items-center h-96 fade-in">
                        <svg class="animate-spin h-8 w-8 text-blue-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs text-gray-400 font-medium">Memuat data...</span>
                    </div>
                `;

                function loadComponent(componentName, buttonElement) {
                    componentContainer.html(loaderHtml);
                    updateButtonStyles(buttonElement);

                    const url = '{{ route("oil.loadComponent", ["componentName" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', componentName);

                    $.get(url)
                        .done(function (response) {
                            componentContainer.hide().html(response).fadeIn(300);
                        })
                        .fail(function (jqXHR, textStatus, errorThrown) {
                            const errorHtml = `
                                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg flex items-center gap-3">
                                    <i class="mdi mdi-alert-circle text-xl"></i>
                                    <span class="font-medium text-sm">Gagal memuat data. Silakan coba lagi.</span>
                                </div>`;
                            componentContainer.html(errorHtml);
                        });
                }

                // Initial Setup
                buttons.addClass(baseClasses.join(' ')).addClass(inactiveClasses.join(' '));
                
                buttons.on('click', function () {
                    loadComponent($(this).data('component'), this);
                });

                // Load Default
                const defaultButton = $('.component-btn[data-component="tank_yard_bdt"]');
                if (defaultButton.length) {
                    defaultButton.trigger('click');
                }
            });
        </script>
    @endpush
</x-app-layout>