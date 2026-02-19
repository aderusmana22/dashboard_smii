<x-app-layout>
    @section('title')
        Data Input Station
    @endsection

    {{-- Load Library SweetAlert & Icons secara global --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    {{-- Pastikan jQuery diload --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <div class="py-6 px-4 sm:px-6 lg:px-8 mx-auto min-h-screen">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
            <div>
                <h4 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
                    <span class="bg-indigo-100 text-indigo-600 p-2 rounded-xl shadow-sm">
                        <i class="mdi mdi-clipboard-text-outline"></i>
                    </span>
                    Input Station
                </h4>
                <p class="text-slate-500 text-sm mt-1 ml-1">Select an area to start inputting data</p>
            </div>

            {{-- Breadcrumb --}}
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 px-4 py-2 rounded-full shadow-sm border border-slate-200 bg-white">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-indigo-600 transition-colors">
                            <i class="mdi mdi-home-outline text-lg"></i>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="mdi mdi-chevron-right text-slate-400 text-lg"></i>
                            <span class="ml-1 text-sm font-medium text-slate-500">Oil Station</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="mdi mdi-chevron-right text-slate-400 text-lg"></i>
                            <span class="ml-1 text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">
                                Input Data
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        {{-- MAIN CARD --}}
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-100 min-h-[600px]">
            
            {{-- DROPDOWN SELECTION --}}
            <div class="mb-6 max-w-md">
                <label for="type_selector" class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                    Select Data Input Area:
                </label>
                
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="mdi mdi-filter-variant text-indigo-500 text-xl"></i>
                    </div>

                    <select id="type_selector" 
                            class="block w-full pl-10 pr-10 py-3 text-base font-bold text-slate-700 bg-slate-50 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl transition shadow-sm cursor-pointer hover:bg-white">
                        
                        {{-- Opsi Default (Selected jika currentType kosong) --}}
                        <option value="" disabled {{ is_null($currentType) ? 'selected' : '' }}>
                            -- Click Here to Select Area --
                        </option>

                        <option value="batch_refinery" {{ $currentType === 'batch_refinery' ? 'selected' : '' }}>
                            🏭 Batch Refinery (Tank Input)
                        </option>

                        <option value="utility_gas" {{ $currentType === 'utility_gas' ? 'selected' : '' }}>
                            ⛽ Utility Gas (Gas Input)
                        </option>
                    </select>

                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i id="dropdown-spinner" class="mdi mdi-loading mdi-spin text-indigo-600 hidden mr-2"></i>
                    
                    </div>
                </div>
            </div>

            <div class="h-px bg-slate-100 w-full mb-6"></div>

            {{-- DYNAMIC CONTENT CONTAINER --}}
            <div id="input_content_area" class="animate-fade-in-up min-h-[400px]">
                @if(!empty($initialData))
                    {!! $initialData !!}
                @else
                    {{-- BLANK STATE (Default Tampilan) --}}
                    <div class="flex flex-col justify-center items-center h-80 opacity-60">
                        <div class="bg-slate-100 p-6 rounded-full mb-4">
                            <i class="mdi mdi-gesture-tap text-6xl text-slate-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-600">No Form Selected</h3>
                        <p class="text-slate-500 mt-2">Please select an input area from the dropdown above.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- SCRIPT --}}
    <script>
        $(function () {
            const selector = $('#type_selector');
            const container = $('#input_content_area');
            const spinner = $('#dropdown-spinner');

            function loadForm(type) {
                // UI Loading
                spinner.removeClass('hidden');
                selector.prop('disabled', true);

                // Update URL
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('type', type);
                window.history.pushState({}, '', newUrl);

                const url = `{{ route('oil.input_station.index') }}?type=${type}`;
                
                $.get(url)
                    .done(function (response) {
                        container.hide().html(response).fadeIn(300);
                    })
                    .fail(function (jqXHR) {
                        container.html(`
                            <div class="bg-red-50 p-6 rounded-xl text-center border border-red-100 mt-10">
                                <h3 class="text-red-700 font-bold">Failed to load form</h3>
                                <p class="text-red-500 text-sm">Error: ${jqXHR.status}</p>
                            </div>
                        `);
                    })
                    .always(function() {
                        spinner.addClass('hidden');
                        selector.prop('disabled', false);
                        container.css('opacity', '1');
                    });
            }

            selector.on('change', function () {
                const selectedType = $(this).val();
                if(selectedType) {
                    loadForm(selectedType);
                }
            });
            
            // Cek URL params saat load, set dropdown value jika ada
            // Ini untuk handle kasus user refresh halaman saat form sudah dipilih
            const urlParams = new URLSearchParams(window.location.search);
            const typeParam = urlParams.get('type');
            if(typeParam) {
                selector.val(typeParam);
            }
        });
    </script>
</x-app-layout>