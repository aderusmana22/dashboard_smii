<x-app-layout>

    @section('title')
    Dashboard Sales (Data Dinamis)
    @endsection

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Example font */
        }
        #map-ui-container {
            position: relative;
            width: 100%;
            height: calc(
                100vh - 57px /* Adjust if your header height is different */
            );
            overflow: hidden;
            background-color: #f0f0f0;
        }

        .leaflet-control-zoom {
            display: none !important;
        }
        #map {
            height: 100%;
            width: 100%;
            background-color: #aadaff; /* Light blue background for map area */
        }

        .info-tooltip-global,
        #sales-tooltip-indonesia {
            position: absolute;
            padding: 8px;
            color: white;
            border-radius: 4px;
            font-size: 12px;
            z-index: 800;
            pointer-events: none;
            display: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .info-tooltip-global {
            background: rgba(0, 0, 0, 0.8);
        }
        #sales-tooltip-indonesia {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            border: 1px solid #ccc;
            font-size: 13px;
        }

        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px;
            border-radius: 8px;
            z-index: 1000;
            font-size: 16px;
            text-align: center;
            display: none;
        }
        .geoboundaries-watermark {
            position: absolute;
            bottom: 3px;
            right: 50px;
            font-size: 9px;
            color: #555;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 2px 4px;
            border-radius: 3px;
            z-index: 700;
        }

        #left-column-stats-container { /* Renamed for clarity, acts as right column */
            position: absolute;
            top: 65px; /* Below filter menu */
            right: 15px;
            left: auto;
            z-index: 709; /* Below filter menu */
            width: 450px; /* Increased width */
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 75px - 200px - 20px - 10px - 15px); /* Adjust for filter menu and chart */
            padding-bottom: 10px;
        }

        #super-region-stats-container {
            background: rgba(255, 255, 255, 0.92);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 12px;
            width: 100%;
            display: block; /* Always visible for Indonesia context */
        }
        #super-region-stats-container h3 {
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        #super-region-stats-table {
            width: 100%;
            border-collapse: collapse;
        }

        #super-region-stats-table thead,
        #super-region-stats-table tfoot {
            display: table;
            width: calc(100% - 17px); /* Account for scrollbar width if tbody overflows */
            table-layout: fixed;
        }
        #super-region-stats-table tbody {
            display: block;
            max-height: 150px; /* Adjust as needed */
            overflow-y: auto;
            width: 100%;
        }
        #super-region-stats-table tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        #super-region-stats-table th,
        #super-region-stats-table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 10px;
        }
        #super-region-stats-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        #super-region-stats-table td.number-cell {
            text-align: right;
        }

        #super-region-stats-table .col-region { width: 28%; }
        #super-region-stats-table .col-budget { width: 18%; }
        #super-region-stats-table .col-dispatch { width: 18%; }
        #super-region-stats-table .col-achieve { width: 16%; }
        #super-region-stats-table .col-lastyear { width: 20%; }

        #international-stats-container {
            position: absolute;
            top: 65px; /* Below filter menu */
            left: 10px;
            right: auto;
            z-index: 709; /* Below filter menu */
            background: rgba(255, 255, 255, 0.92);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 12px;
            width: 430px;
            max-height: calc(100vh - 75px - 200px - 20px - 15px); /* Adjust for filter menu and chart */
            overflow-y: auto;
            display: none; /* Initially hidden, shown for world view */
        }
        #international-stats-container h3 {
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        #international-stats-table {
            width: 100%;
            border-collapse: collapse;
        }
        #international-stats-table th,
        #international-stats-table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 10px;
        }
        #international-stats-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        #international-stats-table td.number-cell {
            text-align: right;
        }
        #international-stats-table .col-country { width: 28%; }
        #international-stats-table .col-sales { width: 18%; }
        #international-stats-table .col-budget { width: 18%; }
        #international-stats-table .col-achieve { width: 16%; }
        #international-stats-table .col-lastyear { width: 20%; }

        #chart-container {
            position: absolute;
            bottom: 75px; /* Adjusted bottom to make space for legend if needed */
            left: 10px;
            width: 380px;
            height: 180px; 
            background: rgba(255, 255, 255, 0.92);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            z-index: 710;
            display: block; /* Always visible */
        }
        #chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }

        #filter-menu {
            position: absolute;
            top: 5px;
            right: 15px; 
            left: auto; 
            background: rgba(255, 255, 255, 0.95);
            padding: 8px 12px;
            border-radius: 8px;
            z-index: 720; 
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-wrap: wrap; 
            gap: 8px; 
            align-items: center;
        }
        #filter-menu label {
            font-size: 13px;
            margin-right: 2px;
            font-weight: 500;
        }
        #filter-menu input[type="date"],
        #filter-menu select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 12px;
        }
        #filter-menu select {
            max-width: 130px; 
        }
        #reset-all-filters {
            padding: 5px 10px;
            font-size: 12px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
        }
        #reset-all-filters:hover {
            background-color: #e0e0e0;
        }


        #back-to-world-btn-dynamic {
            position: absolute;
            top: 10px; 
            left: 15px;
            background: #fff;
            color: #337ab7;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 720; 
            display: none; 
        }
        #back-to-world-btn-dynamic:hover {
            background: #f0f0f0;
        }

        #indonesia-legend-floating {
            position: absolute;
            bottom: 75px; /* Matched chart-container bottom */
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
            z-index: 700;
            width: 200px;
            display: none; 
        }
        #indonesia-legend-floating h4 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 13px;
            padding-bottom: 3px;
            border-bottom: 1px solid #eee;
        }
        .legend-items-scroll-container {
            max-height: 130px; 
            overflow-y: auto;
            font-size: 11px;
        }
        .legend-items-scroll-container div {
            margin-bottom: 3px;
            display: flex;
            align-items: center;
        }
        .legend-items-scroll-container i {
            width: 12px;
            height: 12px;
            margin-right: 5px;
            border: 1px solid #ccc;
            flex-shrink: 0; 
        }

    </style>

    <div id="map-ui-container">
        <div id="map"></div>
        <div id="loading" class="loading">Memuat Peta...</div>

        <div class="geoboundaries-watermark">
            This map uses boundaries from <a href="https://www.geoboundaries.org" target="_blank"
                rel="noopener noreferrer">geoBoundaries</a>.
        </div>

        <!-- Filter Menu -->
        <div id="filter-menu">
            <label for="start-date-select">Start:</label>
            <input type="date" id="start-date-select">
            <label for="end-date-select">End:</label>
            <input type="date" id="end-date-select">
            
            <label for="brand-filter">Brand:</label>
            <select id="brand-filter"></select>

            <label for="code-cmmt-filter">Region/Code:</label>
            <select id="code-cmmt-filter"></select>

            <label for="city-filter">City:</label>
            <select id="city-filter"></select>

            <button id="reset-all-filters">Reset Filters</button>
        </div>

        <!-- Back to World Button -->
        <a href="#" id="back-to-world-btn-dynamic">← Kembali ke Peta Dunia</a>


        <!-- Container for Indonesia Stats (Right Side) -->
        <div id="left-column-stats-container">
            <div id="super-region-stats-container">
                <h3>Indonesia Region Sales</h3>
                <table id="super-region-stats-table">
                    <thead>
                        <tr>
                            <th class="col-region">Region</th>
                            <th class="col-budget">Budget (Ton)</th>
                            <th class="col-dispatch">Dispatch (Ton)</th>
                            <th class="col-achieve">Achieve %</th>
                            <th class="col-lastyear">Dispatch LY (Ton)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td class="col-region"></td>
                            <td class="col-budget number-cell"></td>
                            <td class="col-dispatch number-cell"></td>
                            <td class="col-achieve number-cell"></td>
                            <td class="col-lastyear number-cell"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Container for International Stats (Left Side) -->
        <div id="international-stats-container">
            <h3>International Export Sales</h3>
            <table id="international-stats-table">
                <thead>
                    <tr>
                        <th class="col-country">Country</th>
                        <th class="col-sales">Dispatch (Ton)</th>
                        <th class="col-budget">Budget (Ton)</th>
                        <th class="col-achieve">Achieve %</th>
                        <th class="col-lastyear">Dispatch LY (Ton)</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                     <tr>
                        <td class="col-country"></td>
                        <td class="col-sales number-cell"></td>
                        <td class="col-budget number-cell"></td>
                        <td class="col-achieve number-cell"></td>
                        <td class="col-lastyear number-cell"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Chart Container -->
        <div id="chart-container">
            <canvas id="salesChart"></canvas>
        </div>

        <!-- Tooltips -->
        <div id="info-box" class="info-tooltip-global"></div>
        <div id="sales-tooltip-indonesia"></div>

        <!-- Indonesia Legend -->
        <div id="indonesia-legend-floating">
            <h4>Region Pemasaran</h4>
            <div class="legend-items-scroll-container">
                {{-- Legend items will be populated here by JS --}}
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/topojson/3.0.2/topojson.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://unpkg.com/tinycolor2"></script>

    <script>
        let currentMapView = 'world'; 
        let map, geoLayer, previousIndonesiaZoom = null;
        const INDIA_CENTER = [20.5937, 78.9629]; // Adjusted for a more central world view if needed

        const WORLD_TOPOJSON_URL = '{{ asset('maps/dunia.topojson') }}';
        const INDONESIA_TOPOJSON_URL = '{{ asset('maps/indo.topojson') }}';
        const WORLD_CACHE_KEY = 'world-custom-topojson-v4-dynamic'; 
        const INDONESIA_CACHE_KEY = 'indonesia-adm2-topojson-v16-dynamic'; 
        const MAX_CACHE_SIZE_MB = 5;
        const CALCULATION_BATCH_SIZE = 50; 

        const INDONESIA_DEFAULT_ZOOM_LEVEL = 5.75;
        const INDONESIA_MIN_ZOOM = 5.0; 
        const INDONESIA_MAX_ZOOM = 10; 
        const WORLD_DEFAULT_ZOOM_LEVEL = 3; 
        const WORLD_MIN_ZOOM = 2.5;
        const WORLD_MAX_ZOOM = 6; // Slightly increased max zoom for world if showing city markers


        let salesDataGlobal = {}; 
        let superRegionSales = {}; 
        let cityMarkersData = []; // For Indonesian cities
        let internationalCityMarkersData = []; // NEW: For international cities
        let cityMarkersLayerGroup = L.layerGroup(); 

        const superRegionDefinitions = {
            "REGION1A": ["Pontianak", "Serang", "Tangerang", "Lampung"], "REGION1B": ["Bandung", "Tasikmalaya", "Cirebon"],
            "REGION1C": ["Jakarta Timur", "Jakarta Pusat", "Jakarta Utara", "Jakarta Barat", "Jakarta Selatan", "Depok"],
            "REGION1D": ["Karawang", "Bekasi", "Bogor"], "REGION3A": ["Palembang", "Bangka Belitung", "Jambi"],
            "REGION3B": ["Padang", "Pekanbaru", "Batam"], "REGION3C": ["Medan", "Aceh", "Nias"],
            "REGION2A": ["Semarang", "Kudus", "Tegal", "Pekalongan", "Bojonegoro"],
            "REGION2B": ["Malang", "Surabaya", "Jember", "Madura", "Banyuwangi"],
            "REGION2C": ["Bali", "Flores", "Kupang", "Lombok"], "REGION2D": ["Solo", "Purwokerto", "Magelang", "Yogyakarta", "Tulungagung", "Madiun"],
            "REGION4A": ["Sorong", "Gorontalo", "Ternate", "Jayapura", "Manado", "Manokwari", "Timika", "Merauke"],
            "REGION4B": ["Tarakan", "Ambon", "Palu", "Samarinda", "Bau Bau", "Banjarmasin", "Palangkaraya", "Sampit", "Pangkalanbun", "Makassar", "Kendari", "Balikpapan"],
        };

        const cityToSuperRegionMap = {};
        for (const superReg in superRegionDefinitions) {
            superRegionDefinitions[superReg].forEach(city => cityToSuperRegionMap[city.toLowerCase().trim()] = superReg);
        }
        cityToSuperRegionMap["bali (horeka)"] = "REGION2C"; 

        const regionColors = { 
            "REGION1A": "#8dd3c7", "REGION1B": "#ffffb3", "REGION1C": "#bebada", "REGION1D": "#fb8072",
            "REGION2A": "#80b1d3", "REGION2B": "#fdb462", "REGION2C": "#b3de69", "REGION2D": "#fccde5",
            "REGION3A": "#bc80bd", "REGION3B": "#ccebc5", "REGION3C": "#ffed6f",
            "REGION4A": "#99cce0", "REGION4B": "#f7cac9",
            "KEYACCOUNT": "#d9d9d9", "COMMERCIAL": "#a9a9a9",
            "OTHER_BASE": "#cccccc" 
        };

        let seedRegionData = []; 
        const featureCentroidCache = new Map();
        const worldBaseColor = "#dddddd"; 
        let worldMaxSales = 1, worldMinSales = 0; 

        let salesPieChart; 
        const mapUiContainer = document.getElementById('map-ui-container');
        const loadingDiv = document.getElementById('loading');
        let backToWorldBtnDynamic;
        let indonesiaLegendContainer;
        let legendItemsScrollContainer;
        let internationalStatsContainer;

        const infoTooltipGlobalDiv = document.getElementById('info-box');
        const salesTooltipIndonesiaDiv = document.getElementById('sales-tooltip-indonesia');

        const dateRanges = @json($dateRanges);
        const filterValues = @json($filterValues ?? ['brands' => [], 'cities' => [], 'code_cmmts' => []]); 

        document.addEventListener('DOMContentLoaded', () => {
            internationalStatsContainer = document.getElementById('international-stats-container');
            indonesiaLegendContainer = document.getElementById('indonesia-legend-floating');
            if (indonesiaLegendContainer) {
                 legendItemsScrollContainer = indonesiaLegendContainer.querySelector('.legend-items-scroll-container');
            }

            initMap();
            populateNewFilters(); 
            initUIElements();     
            updateUIVisibilityBasedOnView(currentMapView);
            handleFilterChange(); 
        });

        function showLoading(message = 'Memuat...') {
            if (loadingDiv) { loadingDiv.textContent = message; loadingDiv.style.display = 'block'; }
        }
        function hideLoading() {
            if (loadingDiv) { loadingDiv.style.display = 'none'; }
        }

        function initMap() {
            map = L.map('map', {
                worldCopyJump: true,
                zoomControl: false, 
                zoomSnap: 0.25,
                zoomDelta: 0.25
            }).setView(INDIA_CENTER, WORLD_DEFAULT_ZOOM_LEVEL);

            map.on('mousemove', function (e) {
                if (currentMapView === 'world' && infoTooltipGlobalDiv.style.display === 'block') {
                    infoTooltipGlobalDiv.style.left = (e.containerPoint.x + 15) + 'px';
                    infoTooltipGlobalDiv.style.top = (e.containerPoint.y + 15) + 'px';
                } else if (currentMapView === 'indonesia' && salesTooltipIndonesiaDiv.style.display === 'block') {
                    salesTooltipIndonesiaDiv.style.left = (e.containerPoint.x + 15) + 'px';
                    salesTooltipIndonesiaDiv.style.top = (e.containerPoint.y + 15) + 'px';
                }
            });
             map.on('zoomend', function() {
                if (currentMapView === 'indonesia') {
                    previousIndonesiaZoom = map.getZoom(); 
                }
            });
        }

        function populateNewFilters() {
            const brandSelect = document.getElementById('brand-filter');
            brandSelect.innerHTML = ''; 
            brandSelect.add(new Option('All Brands', 'ALL'));
            (filterValues.brands || []).forEach(brand => {
                brandSelect.add(new Option(brand, brand));
            });

            const codeCmmtSelect = document.getElementById('code-cmmt-filter');
            codeCmmtSelect.innerHTML = '';
            codeCmmtSelect.add(new Option('All Regions/Codes', 'ALL'));
            (filterValues.code_cmmts || []).forEach(code => {
                let displayText = code;
                if (typeof code === 'string' && code.match(/^REGION[0-9][A-Z]$/)) {
                    displayText = code.replace(/([A-Z]+)([0-9]+[A-Z]*)/g, '$1 $2');
                } else if (typeof code === 'string') {
                    displayText = code.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
                }
                codeCmmtSelect.add(new Option(displayText, code));
            });
            
            const citySelect = document.getElementById('city-filter');
            citySelect.innerHTML = '';
            citySelect.add(new Option('All Cities', 'ALL'));
            (filterValues.cities || []).forEach(city => {
                let displayText = city;
                if (typeof city === 'string') {
                     displayText = city.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
                }
                citySelect.add(new Option(displayText, city)); 
            });
        }

        function initUIElements() {
            const startDateSelect = document.getElementById('start-date-select');
            const endDateSelect = document.getElementById('end-date-select');

            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0'); 
            const dd = String(today.getDate()).padStart(2, '0');
            const todayISO = `${yyyy}-${mm}-${dd}`;

            startDateSelect.min = dateRanges.min_date_iso;
            startDateSelect.max = dateRanges.max_date_iso; 

            endDateSelect.min = dateRanges.min_date_iso;
            endDateSelect.max = dateRanges.max_date_iso;   

            startDateSelect.value = todayISO; // Default start date to today
            endDateSelect.value = todayISO;   // Default end date to today

            startDateSelect.addEventListener('change', handleFilterChange);
            endDateSelect.addEventListener('change', handleFilterChange);
            document.getElementById('brand-filter').addEventListener('change', handleFilterChange);
            document.getElementById('code-cmmt-filter').addEventListener('change', handleFilterChange);
            document.getElementById('city-filter').addEventListener('change', handleFilterChange);

            document.getElementById('reset-all-filters').addEventListener('click', () => {
                startDateSelect.value = todayISO; 
                endDateSelect.value = todayISO;         
                document.getElementById('brand-filter').value = 'ALL';
                document.getElementById('code-cmmt-filter').value = 'ALL';
                document.getElementById('city-filter').value = 'ALL';
                handleFilterChange();
            });

            const salesChartCanvasEl = document.getElementById('salesChart');
            if (salesChartCanvasEl) {
                 const salesChartCtx = salesChartCanvasEl.getContext('2d');
                 if (salesChartCtx) {
                    salesPieChart = new Chart(salesChartCtx, {
                        type: 'pie', data: { labels: [], datasets: [] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                    });
                } else {
                    console.error("Failed to get 2D context for salesChart canvas.");
                }
            } else {
                console.error("salesChart canvas element not found.");
            }

            backToWorldBtnDynamic = document.getElementById('back-to-world-btn-dynamic');
            if (backToWorldBtnDynamic) {
                backToWorldBtnDynamic.addEventListener('click', (e) => {
                    e.preventDefault();
                    switchToView('world');
                });
            } else {
                console.error("back-to-world-btn-dynamic element not found.");
            }
        }

        function updateUIVisibilityBasedOnView(viewType) {
            const superRegionContainer = document.getElementById('super-region-stats-container');
            const leftColContainer = document.getElementById('left-column-stats-container'); 

            if (internationalStatsContainer) {
                internationalStatsContainer.style.display = (viewType === 'world') ? 'block' : 'none';
            }
            if (backToWorldBtnDynamic) {
                backToWorldBtnDynamic.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            }
            if (indonesiaLegendContainer) {
                indonesiaLegendContainer.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            }

            if (superRegionContainer) superRegionContainer.style.display = 'block'; 
            if (leftColContainer) leftColContainer.style.display = 'flex';

            // Manage city markers layer visibility
            if (viewType === 'indonesia' && cityMarkersData.length > 0 && !map.hasLayer(cityMarkersLayerGroup)) {
                 cityMarkersLayerGroup.addTo(map);
            } else if (viewType === 'world' && internationalCityMarkersData.length > 0 && !map.hasLayer(cityMarkersLayerGroup)) {
                 cityMarkersLayerGroup.addTo(map);
            } else if ((viewType === 'world' && cityMarkersData.length > 0 && map.hasLayer(cityMarkersLayerGroup)) || 
                       (viewType === 'indonesia' && internationalCityMarkersData.length > 0 && map.hasLayer(cityMarkersLayerGroup))) {
                // This case handles if markers from the *other* view are somehow still present
                // Though updateCityMarkers should handle clearing appropriately
                // map.removeLayer(cityMarkersLayerGroup); // Potentially clear and re-add in updateCityMarkers
            }
            // The actual adding/removing of markers is now more robustly handled in updateCityMarkers
        }

        function switchToView(viewType) {
            const oldView = currentMapView;
            currentMapView = viewType;
            
            // Explicitly hide tooltips BEFORE any other action
            infoTooltipGlobalDiv.style.display = 'none';
            salesTooltipIndonesiaDiv.style.display = 'none';

            showLoading(viewType === 'world' ? 'Memuat Peta Dunia...' : 'Memuat Peta Indonesia...');
            
            cityMarkersLayerGroup.clearLayers(); // Clear all markers regardless of type
            if (map.hasLayer(cityMarkersLayerGroup)) {
                 map.removeLayer(cityMarkersLayerGroup);
            }

            updateUIVisibilityBasedOnView(currentMapView); // Update UI elements like tables/legends visibility

            handleFilterChange(); // This will fetch data for the new view and redraw everything
        }

        async function loadAndDisplayMapData(url, viewType, cacheKey = null) {
            let topoData;
            if (cacheKey) {
                try {
                    const cached = localStorage.getItem(cacheKey);
                    if (cached) topoData = JSON.parse(cached);
                } catch (e) { console.error('Cache read error:', e); localStorage.removeItem(cacheKey); }
            }

            if (!topoData) {
                try {
                    showLoading(`Mengunduh data peta ${viewType}...`);
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status} for ${url}`);
                    topoData = await response.json();
                    if (cacheKey) {
                        try {
                            const dataString = JSON.stringify(topoData);
                            if (new TextEncoder().encode(dataString).length / (1024 * 1024) < MAX_CACHE_SIZE_MB) {
                                localStorage.setItem(cacheKey, dataString);
                            } else console.warn(`${viewType} map data too large to cache.`);
                        } catch (e) { console.error('Cache write error:', e); }
                    }
                } catch (error) {
                    console.error(`Gagal memuat TopoJSON ${viewType}:`, error);
                    alert(`Gagal memuat peta ${viewType}. Error: ${error.message}`);
                    hideLoading();
                    if (viewType === 'indonesia' && currentMapView === 'indonesia') { 
                        currentMapView = 'world'; 
                        switchToView('world'); 
                    }
                    return;
                }
            }
            await processTopoJSONAndRender(topoData, viewType);
        }

        async function processTopoJSONAndRender(topojsonInputData, viewType) {
            // Explicitly hide tooltips before redrawing geoLayer
            infoTooltipGlobalDiv.style.display = 'none';
            salesTooltipIndonesiaDiv.style.display = 'none';

            showLoading(`Memproses data peta ${viewType}...`);
            if (!topojsonInputData || typeof topojsonInputData.objects !== 'object' || Object.keys(topojsonInputData.objects).length === 0) {
                console.error("Invalid TopoJSON structure", topojsonInputData); alert("Gagal memproses peta: Struktur tidak valid."); hideLoading(); return;
            }
            let objectName = Object.keys(topojsonInputData.objects).find(key =>
                key.toLowerCase().includes('countries') || key.toLowerCase().includes('states') ||
                key.toLowerCase().includes('provinces') || key.toLowerCase().includes('adm2') ||
                key.toLowerCase().includes('adm') || key.toLowerCase().includes('boundaries')
            ) || Object.keys(topojsonInputData.objects)[0];

            if (!topojsonInputData.objects[objectName]) {
                 console.error(`TopoJSON object '${objectName}' not found.`); alert(`Gagal memproses peta: objek '${objectName}' tidak ditemukan.`); hideLoading(); return;
            }
            const geojson = window.topojson.feature(topojsonInputData, topojsonInputData.objects[objectName]);
            if (!geojson || !geojson.features) {
                console.error("Failed to convert TopoJSON or no features.", geojson); alert("Gagal konversi data peta."); hideLoading(); return;
            }

            if (viewType === 'world') calculateWorldMinMaxSales();
            else if (viewType === 'indonesia') {
                seedRegionData = []; featureCentroidCache.clear();
                geojson.features.forEach(f => {
                    const cN = getCleanedShapeNameFromProps(f.properties), sRN = cityToSuperRegionMap[cN.toLowerCase().trim()];
                    if (sRN) { const cen = getFeatureCentroid(f.geometry); if (cen) seedRegionData.push({ centroid: cen, superRegionName: sRN }); }
                });
                await calculateNearestSuperRegionsAsync(geojson.features); 
            }

            if (geoLayer) map.removeLayer(geoLayer);
            geoLayer = L.geoJSON(geojson, { style: styleFeatureMap, onEachFeature: onEachFeatureMap, viewType: viewType }).addTo(map);

            if (viewType === 'world') {
                map.options.minZoom = WORLD_MIN_ZOOM; map.options.maxZoom = WORLD_MAX_ZOOM;
                map.setView(INDIA_CENTER, WORLD_DEFAULT_ZOOM_LEVEL); 
                map.setMaxBounds(null); 
            } else if (viewType === 'indonesia') {
                map.options.minZoom = INDONESIA_MIN_ZOOM; map.options.maxZoom = INDONESIA_MAX_ZOOM;
                const bounds = geoLayer.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds.pad(0.05)); 
                    map.setMaxBounds(bounds.pad(0.2)); 
                     if (previousIndonesiaZoom !== null && previousIndonesiaZoom >= INDONESIA_MIN_ZOOM && previousIndonesiaZoom <= INDONESIA_MAX_ZOOM) {
                        map.setZoom(previousIndonesiaZoom);
                    }
                } else { 
                    map.setView([-2.5, 118], INDONESIA_DEFAULT_ZOOM_LEVEL); 
                    map.setMaxBounds(null);
                }
            }
            hideLoading();
        }

        function getCleanedShapeNameFromProps(properties) {
            if (!properties) return "";
            const potentialNameProps = ['shapeName', 'NAME_2', 'NAME_1', 'name', 'ADM2_EN', 'kabkot', 'ADMIN', 'NAME', 'name_long', 'formal_en', 'COUNTRY', 'NAME_EN', 'NAME_0'];
            for (const prop of potentialNameProps) {
                if (properties[prop]) return String(properties[prop]).toLowerCase().trim();
            }
            return "";
        }

        function getFeatureCentroid(geometry) {
            if (!geometry || !geometry.coordinates) return null;
            const cacheKey = geometry.type + JSON.stringify(geometry.coordinates.slice(0,1));
            if (featureCentroidCache.has(cacheKey)) return featureCentroidCache.get(cacheKey);
            let centroid;
            try {
                if (geometry.type === 'Polygon') {
                    const coords = geometry.coordinates[0]; let x=0,y=0,count=0;
                    coords.forEach(c => { if (c && c.length === 2) { x += c[0]; y += c[1]; count++; }});
                    centroid = count > 0 ? [y / count, x / count] : null;
                } else if (geometry.type === 'MultiPolygon') {
                    let largestPolygonArea = 0, largestPolygonCentroid = null;
                    geometry.coordinates.forEach(polygonCoords => {
                        const coords = polygonCoords[0]; let x=0,y=0,count=0,currentArea=0;
                        for (let i=0; i<coords.length-1; i++) {
                             if (coords[i] && coords[i].length === 2 && coords[i+1] && coords[i+1].length === 2) {
                                x+=coords[i][0]; y+=coords[i][1]; count++;
                                currentArea+=(coords[i][0]*coords[i+1][1] - coords[i+1][0]*coords[i][1]);
                             }
                        }
                        currentArea=Math.abs(currentArea/2);
                        if(count>0 && currentArea > largestPolygonArea){
                            largestPolygonArea=currentArea; largestPolygonCentroid=[y/count,x/count];
                        }
                    });
                    centroid = largestPolygonCentroid;
                }
            } catch (error) {
                console.error("Error calculating centroid:", error, geometry);
                return null;
            }
            if (centroid) featureCentroidCache.set(cacheKey, centroid);
            return centroid;
        }

        async function calculateNearestSuperRegionsAsync(features) {
             return new Promise(resolve => {
                let index = 0; const totalFeatures = features.length;
                function processBatch() {
                    const batchStartTime = Date.now();
                    while (index < totalFeatures && (Date.now() - batchStartTime) < CALCULATION_BATCH_SIZE) {
                        const feature = features[index];
                        const cleanedName = getCleanedShapeNameFromProps(feature.properties);
                        if (!cityToSuperRegionMap[cleanedName] && seedRegionData.length > 0) {
                            const featureCentroid = getFeatureCentroid(feature.geometry);
                            if (featureCentroid) {
                                let minDistance = Infinity, nearestSuperRegion = null;
                                seedRegionData.forEach(regionSeed => {
                                    const dist = L.latLng(featureCentroid).distanceTo(L.latLng(regionSeed.centroid));
                                    if (dist < minDistance) { minDistance = dist; nearestSuperRegion = { name: regionSeed.superRegionName, distance: dist }; }
                                });
                                if (nearestSuperRegion) feature.properties.calculatedSuperRegion = nearestSuperRegion;
                            }
                        }
                        index++;
                    }
                    if (index < totalFeatures) {
                        showLoading(`Menghitung region... (${Math.round((index/totalFeatures)*100)}%)`);
                        requestAnimationFrame(processBatch);
                    } else { hideLoading(); resolve(); }
                }
                if (totalFeatures > 0) processBatch(); else resolve(); 
            });
        }

        function calculateWorldMinMaxSales() {
            const salesValues = Object.values(salesDataGlobal)
                .map(data => Number(data.sales) || 0)
                .filter(s => s > 0);

            if(salesValues.length > 0){
                worldMinSales = Math.min(...salesValues);
                worldMaxSales = Math.max(...salesValues);
            } else {
                worldMinSales = 0; worldMaxSales = 1;
            }

            const indonesiaData = salesDataGlobal["Indonesia"];
            if (indonesiaData) {
                const indonesiaSales = Number(indonesiaData.sales) || 0;
                if(indonesiaSales > worldMaxSales) worldMaxSales = indonesiaSales;
                if(indonesiaSales > 0 && (worldMinSales === 0 || indonesiaSales < worldMinSales) ) {
                     worldMinSales = indonesiaSales;
                }
            }

            if(worldMinSales === 0 && worldMaxSales > 0) worldMinSales = Math.min(1, worldMaxSales / 1000); 
            else if(worldMinSales === 0 && worldMaxSales === 0) { worldMinSales = 0; worldMaxSales = 1; }
            if (worldMinSales >= worldMaxSales && worldMaxSales > 0) worldMinSales = worldMaxSales / 2; 
            if (worldMaxSales === 0) worldMaxSales = 1; 
        }


        function getWorldFeatureColor(salesAmount) {
            const sales = Number(salesAmount) || 0;
            if (sales <= 0) return worldBaseColor;
            if (worldMaxSales <= worldMinSales || worldMaxSales === 0) {
                return tinycolor(worldBaseColor).darken(10 + Math.random()*5).toString(); 
            }

            const logMax = Math.log10(worldMaxSales);
            const logMinVal = worldMinSales > 0 ? worldMinSales : (worldMaxSales / 10000 > 0.0001 ? worldMaxSales / 10000 : 0.0001); 
            const logMin = Math.log10(logMinVal);
            const logSales = Math.log10(sales > 0 ? sales : logMinVal);

            let intensity = 0.5;
            if (logMax > logMin) { 
                intensity = (logSales - logMin) / (logMax - logMin);
            }
            intensity = Math.max(0, Math.min(1, intensity));

            const startColor = {r:255,g:255,b:204}; 
            const endColor = {r:128,g:0,b:38}; 
            const r=Math.round(startColor.r+(endColor.r-startColor.r)*intensity);
            const g=Math.round(startColor.g+(endColor.g-startColor.g)*intensity);
            const b=Math.round(startColor.b+(endColor.b-startColor.b)*intensity);
            return `rgb(${r},${g},${b})`;
        }


        function styleFeatureMap(feature) {
            if (currentMapView === 'world') {
                let name = getCleanedShapeNameFromProps(feature.properties);
                if (name === "united states of america") name = "united states"; 
                
                const countryKey = Object.keys(salesDataGlobal).find(k => k.toLowerCase() === name);
                const countryData = countryKey ? salesDataGlobal[countryKey] : null;

                const sales = countryData ? (countryData.sales || 0) : 0;
                return { fillColor: getWorldFeatureColor(sales), weight: 0.5, opacity: 1, color: '#bbb', fillOpacity: 0.75 };
            } else if (currentMapView === 'indonesia') {
                const cleanedName = getCleanedShapeNameFromProps(feature.properties); 
                let fillColor = regionColors.OTHER_BASE; let fillOpacity = 0.70;
                
                const superRegionKeyFromDirectMap = cityToSuperRegionMap[cleanedName]; 
                const regionData = superRegionKeyFromDirectMap ? superRegionSales[superRegionKeyFromDirectMap] : null;

                if (superRegionKeyFromDirectMap && regionColors[superRegionKeyFromDirectMap]) {
                    fillColor = regionColors[superRegionKeyFromDirectMap];
                    fillOpacity = (regionData && regionData.sales > 0) ? 0.85 : 0.70;
                }
                else if (feature.properties.calculatedSuperRegion) {
                    const calculatedSRName = feature.properties.calculatedSuperRegion.name;
                    const calculatedRegionData = superRegionSales[calculatedSRName]; 
                    if (regionColors[calculatedSRName] && tinycolor) {
                        if (calculatedRegionData && calculatedRegionData.sales > 0) {
                            fillColor = tinycolor(regionColors[calculatedSRName]).setAlpha(0.75).toRgbString(); 
                        } else {
                            fillColor = tinycolor(regionColors[calculatedSRName]).lighten(15).setAlpha(0.60).toRgbString();
                        }
                        fillOpacity = 1; 
                    }
                }
                return { weight: 0.5, opacity: 1, color: 'white', fillOpacity: fillOpacity, fillColor: fillColor };
            }
            return { fillColor: '#ccc', weight: 1, opacity: 1, color: 'white', fillOpacity: 0.7 }; 
        }

        function onEachFeatureMap(feature, layer) {
             if (currentMapView === 'world') {
                layer.on({
                    mouseover: (e)=>{
                        let p=e.target.feature.properties;
                        let name=getCleanedShapeNameFromProps(p);
                        if(name==="united states of america") name="united states";

                        const countryKey = Object.keys(salesDataGlobal).find(k => k.toLowerCase() === name);
                        const countryData = countryKey ? salesDataGlobal[countryKey] : null;

                        const sales = countryData ? (countryData.sales || 0) : 0;
                        const budget = countryData ? (countryData.budget || 0) : 0;
                        const lastYearSales = countryData ? (countryData.lastYearSales || 0) : 0;

                        const displayName = countryKey || name.split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' ');

                        let tooltipText = `<strong>${displayName}</strong>`;
                        tooltipText += `<br>Sales: ${sales.toLocaleString(undefined, {maximumFractionDigits:0})} Ton`;
                        if (budget > 0) tooltipText += `<br>Budget: ${budget.toLocaleString(undefined, {maximumFractionDigits:0})} Ton`;
                        if (lastYearSales > 0) tooltipText += `<br>Sales LY: ${lastYearSales.toLocaleString(undefined, {maximumFractionDigits:0})} Ton`;

                        infoTooltipGlobalDiv.innerHTML = tooltipText; infoTooltipGlobalDiv.style.display='block';
                        layer.setStyle({weight:1.5,color:'#666',fillOpacity: 0.9});
                    },
                    mouseout: ()=>{ infoTooltipGlobalDiv.style.display='none'; if (geoLayer) geoLayer.resetStyle(layer); },
                    click: (e)=>{
                        const p=e.target.feature.properties;
                        const n=getCleanedShapeNameFromProps(p);
                        if(n==='indonesia') {
                            switchToView('indonesia');
                        }
                    }
                });
            } else if (currentMapView === 'indonesia') {
                layer.on({
                    mouseover: (e)=>{
                        const p=e.target.feature.properties;
                        const cN=getCleanedShapeNameFromProps(p); 
                        let sRK = cityToSuperRegionMap[cN] || (p.calculatedSuperRegion ? p.calculatedSuperRegion.name : null);

                        const dNKK=cN.split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' ');
                        let displayNameSuperRegion = 'Tidak terpetakan';
                        if (sRK) {
                            displayNameSuperRegion = formatRegionKeyForDisplay(sRK);
                        }

                        let tT=`<strong>${dNKK}</strong><br>Super Region: ${displayNameSuperRegion}`;
                        if(sRK && superRegionSales[sRK]) {
                            const srData = superRegionSales[sRK];
                            tT+=`<br>Total Sales (Region): ${(srData.sales || 0).toLocaleString(undefined, {maximumFractionDigits:0})} Ton`;
                            if ((srData.budget || 0) > 0) tT+=`<br>Total Budget (Region): ${(srData.budget || 0).toLocaleString(undefined, {maximumFractionDigits:0})} Ton`;
                            if ((srData.lastYearSales || 0) > 0) tT+=`<br>Total Sales LY (Region): ${(srData.lastYearSales || 0).toLocaleString(undefined, {maximumFractionDigits:0})} Ton`;
                        }
                        if(p.calculatedSuperRegion && !cityToSuperRegionMap[cN]) tT+=`<br><small>(Estimasi via kedekatan)</small>`;

                        salesTooltipIndonesiaDiv.innerHTML=tT; salesTooltipIndonesiaDiv.style.display='block';
                        layer.setStyle({weight:2,color:'#555', fillOpacity: 0.95});
                    },
                    mouseout: ()=>{ salesTooltipIndonesiaDiv.style.display='none'; if (geoLayer) geoLayer.resetStyle(layer); }
                });
            }
        }

        function updateDashboardPanels() {
            updateSuperRegionStatsTable();
            updateInternationalStatsTable();
            updateSalesChart();
            updateLegend();
        }

        function formatRegionKeyForDisplay(regionKey) {
            if (!regionKey) return 'N/A';
            return regionKey.replace(/([A-Z]+)([0-9]+[A-Z]*)/g, '$1 $2')
                           .replace(/([A-Z])([A-Z]+)/g, (match, p1, p2) => p1 + p2.toLowerCase())
                           .replace(/\b(Keyaccount|Commercial)\b/gi, m => m.charAt(0).toUpperCase() + m.slice(1).toLowerCase());
        }

        function updateSuperRegionStatsTable() {
            const tableBody = document.querySelector('#super-region-stats-table tbody');
            const tfootRow = document.querySelector('#super-region-stats-table tfoot tr');
            if (!tableBody || !tfootRow) return;
            tableBody.innerHTML = ''; Array.from(tfootRow.cells).forEach(cell => cell.textContent = '');

            if (Object.keys(superRegionSales).length === 0 && currentMapView === 'indonesia') {
                tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No Indonesia region data for current filters.</td></tr>';
                return;
            }
             if (Object.keys(superRegionSales).length === 0 && currentMapView !== 'world') { // Avoid showing this if world view is active but no IDN sales
                return;
            }

            let totalDispatch = 0, totalBudget = 0, totalLastYearDispatch = 0;
            const sortedRegionKeys = Object.keys(superRegionSales).sort((a,b) => a.localeCompare(b)); 

            for (const regionKey of sortedRegionKeys) {
                const regionData = superRegionSales[regionKey]; if (!regionData) continue;
                const dispatch = regionData.sales || 0; const budget = regionData.budget || 0; const lastYearDispatch = regionData.lastYearSales || 0;
                const achievement = budget > 0 ? (dispatch / budget * 100) : (dispatch > 0 ? 100 : 0); 
                totalDispatch += dispatch; totalBudget += budget; totalLastYearDispatch += lastYearDispatch;

                const row = tableBody.insertRow();
                row.insertCell().textContent = formatRegionKeyForDisplay(regionKey); row.cells[0].classList.add('col-region');
                row.insertCell().textContent = budget.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[1].classList.add('number-cell', 'col-budget');
                row.insertCell().textContent = dispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[2].classList.add('number-cell', 'col-dispatch');
                row.insertCell().textContent = achievement.toFixed(1) + '%'; row.cells[3].classList.add('number-cell', 'col-achieve');
                row.insertCell().textContent = lastYearDispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[4].classList.add('number-cell', 'col-lastyear');
            }
            if (totalDispatch > 0 || totalBudget > 0 || totalLastYearDispatch > 0) {
                const totalAchievement = totalBudget > 0 ? (totalDispatch / totalBudget * 100) : (totalDispatch > 0 ? 100 : 0);
                tfootRow.cells[0].textContent = "TOTAL INDONESIA"; tfootRow.cells[0].style.fontWeight = "bold";
                tfootRow.cells[1].textContent = totalBudget.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[1].style.fontWeight = "bold";
                tfootRow.cells[2].textContent = totalDispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[2].style.fontWeight = "bold";
                tfootRow.cells[3].textContent = totalAchievement.toFixed(1) + '%'; tfootRow.cells[3].style.fontWeight = "bold";
                tfootRow.cells[4].textContent = totalLastYearDispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[4].style.fontWeight = "bold";
            }
        }

        function updateInternationalStatsTable() {
            const tableBody = document.querySelector('#international-stats-table tbody');
            const tfootRow = document.querySelector('#international-stats-table tfoot tr');
            if(!tableBody || !tfootRow) return; tableBody.innerHTML = ''; Array.from(tfootRow.cells).forEach(cell => cell.textContent = '');

            if (currentMapView !== 'world' || Object.keys(salesDataGlobal).length === 0) {
                 tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No export data for world view or current filters.</td></tr>';
                return;
            }
            let dataForTable = [];
            const exportCountriesData = Object.entries(salesDataGlobal)
                .filter(([country, data]) => country.toLowerCase() !== 'indonesia' && ((data.sales || 0) > 0 || (data.budget || 0) > 0 || (data.lastYearSales || 0) > 0))
                .sort(([,a],[,b]) => (b.sales || 0) - (a.sales || 0)); 

            const maxCountriesInTable = 7; 
            let otherSalesSum = 0, otherBudgetSum = 0, otherLYSalesSum = 0;

            exportCountriesData.forEach(([country, data], index) => {
                if (index < maxCountriesInTable) dataForTable.push({ country, ...data });
                else { otherSalesSum += (data.sales || 0); otherBudgetSum += (data.budget || 0); otherLYSalesSum += (data.lastYearSales || 0); }
            });
            if (otherSalesSum > 0 || otherBudgetSum > 0 || otherLYSalesSum > 0) {
                dataForTable.push({ country: "Other Exports", sales: otherSalesSum, budget: otherBudgetSum, lastYearSales: otherLYSalesSum });
            }
            if (dataForTable.length === 0) {
                 tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No significant export sales for current filters.</td></tr>'; return;
            }

            let totalSalesFooter = 0, totalBudgetFooter = 0, totalLYSalesFooter = 0;
            dataForTable.forEach(item => {
                const sales = item.sales || 0; const budget = item.budget || 0; const lastYearSales = item.lastYearSales || 0;
                const achieve = budget > 0 ? (sales / budget * 100) : (sales > 0 ? 100: 0);
                const row = tableBody.insertRow();
                row.insertCell().textContent = item.country; row.cells[0].classList.add('col-country');
                row.insertCell().textContent = sales.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[1].classList.add('number-cell','col-sales');
                row.insertCell().textContent = budget.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[2].classList.add('number-cell','col-budget');
                row.insertCell().textContent = achieve.toFixed(1) + '%'; row.cells[3].classList.add('number-cell','col-achieve');
                row.insertCell().textContent = lastYearSales.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[4].classList.add('number-cell','col-lastyear');

                totalSalesFooter += sales;
                totalBudgetFooter += budget;
                totalLYSalesFooter += lastYearSales;
            });

            if (tfootRow && (totalSalesFooter > 0 || totalBudgetFooter > 0 || totalLYSalesFooter > 0)) {
                const totalAchExport = totalBudgetFooter > 0 ? (totalSalesFooter / totalBudgetFooter * 100) : (totalSalesFooter > 0 ? 100:0);
                tfootRow.cells[0].textContent = "TOTAL EXPORT"; tfootRow.cells[0].style.fontWeight = "bold";
                tfootRow.cells[1].textContent = totalSalesFooter.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[1].style.fontWeight="bold";
                tfootRow.cells[2].textContent = totalBudgetFooter.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[2].style.fontWeight="bold";
                tfootRow.cells[3].textContent = totalAchExport.toFixed(1) + '%'; tfootRow.cells[3].style.fontWeight="bold";
                tfootRow.cells[4].textContent = totalLYSalesFooter.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[4].style.fontWeight="bold";
            }
        }

        function chartTooltipCallback(context) {
            let label = context.label || '';
            if (label) { label += ': '; }
            if (context.parsed !== null && typeof context.parsed !== 'undefined') {
                label += context.parsed.toLocaleString(undefined, {maximumFractionDigits:0}) + ' Ton';
                const total = context.dataset.data.reduce((s, v) => s + v, 0);
                if (total > 0) {
                    const percentage = (context.parsed / total * 100).toFixed(1) + '%';
                    label += ` (${percentage})`;
                }
            }
            return label;
        }


        function updateSalesChart() {
            const sCC=document.getElementById('salesChart'); if(!sCC)return;
            if(salesPieChart)salesPieChart.destroy(); 

            const ctx=sCC.getContext('2d');
            let chartConfig;

            if(currentMapView==='world'){
                const indonesiaData = salesDataGlobal['Indonesia'];
                const iS = indonesiaData ? (indonesiaData.sales || 0) : 0;
                let tGS=0;
                Object.values(salesDataGlobal).forEach(data => { tGS += (data.sales || 0); });
                const oES=tGS-iS; 

                let L=[],D=[],B=[];
                if(iS>0){L.push('Indonesia');D.push(iS);B.push('#FF6384');} 
                if(oES>0){L.push('Global Export');D.push(oES);B.push('#36A2EB');} 
                if(L.length===0){L.push('No Sales Data');D.push(1);B.push('#CCCCCC');} 

                chartConfig={type:'pie',data:{labels:L,datasets:[{label:'Global Sales',data:D,backgroundColor:B,hoverOffset:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:10},padding:3}},title:{display:true,text:'Sales: Indonesia vs Global Export',font:{size:13},padding:{bottom:8}},tooltip:{callbacks:{label:chartTooltipCallback}}}}};
            } else if(currentMapView==='indonesia'){
                const sSRFC=Object.entries(superRegionSales).filter(([,data])=>(data.sales || 0)>0).sort(([,a],[,b])=>(b.sales || 0)-(a.sales || 0)); 

                let l_sr=sSRFC.map(([r])=>formatRegionKeyForDisplay(r));
                let d_sr=sSRFC.map(([,data])=>data.sales);
                let c_sr=sSRFC.map(([r])=>regionColors[r]||'#808080'); 

                if(l_sr.length===0){l_sr.push('No Super Region Sales');d_sr.push(1);c_sr.push('#CCCCCC');}
                chartConfig={type:'pie',data:{labels:l_sr,datasets:[{label:'Super Region Sales (Indonesia)',data:d_sr,backgroundColor:c_sr,borderColor:'#fff',borderWidth:1}]},options:{responsive:true,maintainAspectRatio:false,plugins:{title:{display:true,text:'Sales per Super-Region (ID)',font:{size:13},padding:{bottom:8}},legend:{position:'bottom',labels:{font:{size:9},boxWidth:10,padding:5,generateLabels: function(chart) { 
                    const data = chart.data;
                    if (data.labels.length && data.datasets.length) {
                        const dataset = data.datasets[0];
                        const sortedLabels = data.labels.map((label, i) => ({label, value: dataset.data[i], color: dataset.backgroundColor[i]}))
                            .sort((a,b) => b.value - a.value);
                        
                        const totalSum = dataset.data.reduce((a,b) => a + b, 0);

                        const legendItems = sortedLabels.slice(0, 5).map(item => ({ 
                            text: `${item.label} (${totalSum > 0 ? ((item.value / totalSum) * 100).toFixed(1) : '0.0'}%)`,
                            fillStyle: item.color,
                            hidden: false,
                            index: data.labels.indexOf(item.label.split(' (')[0]) 
                        }));
                         if (sortedLabels.length > 5) {
                             legendItems.push({text: 'Others...', fillStyle: '#ccc', hidden: false, index: -1});
                         }
                        return legendItems;
                    }
                    return [];
                }}},tooltip:{callbacks:{label:chartTooltipCallback}}}}};
            }
            if(chartConfig)salesPieChart=new Chart(ctx,chartConfig);
        }

        function updateLegend() {
            if (!legendItemsScrollContainer || currentMapView !== 'indonesia') {
                 if(legendItemsScrollContainer) legendItemsScrollContainer.innerHTML = '';
                return;
            }
            legendItemsScrollContainer.innerHTML = '';
            let legendHTML = '';

            const legendOrder = Object.keys(regionColors).filter(k => k !== "OTHER_BASE").sort();

            legendOrder.forEach(superRegKey => {
                if (regionColors[superRegKey] && typeof superRegionSales[superRegKey] !== 'undefined') {
                    const regionData = superRegionSales[superRegKey];
                    const salesVal = regionData ? (regionData.sales || 0) : 0;
                    let salesInfo = (salesVal > 0) ? ` (${salesVal.toLocaleString(undefined, {maximumFractionDigits:0})} Ton)` : "";
                    let displayName = formatRegionKeyForDisplay(superRegKey);
                    legendHTML += `<div><i style="background:${regionColors[superRegKey]}"></i> ${displayName}${salesInfo}</div>`;
                }
            });
            const exampleMappedNoSalesColor = (tinycolor && regionColors.REGION1A) ? tinycolor(regionColors.REGION1A).lighten(10).setAlpha(0.65).toRgbString() : '#e0e0e0';
            legendHTML += `<div><i style="background:${exampleMappedNoSalesColor}"></i> Area S.Region (No Sales)</div>`;
            const otherExampleColor = (tinycolor && regionColors.REGION1A) ? tinycolor(regionColors.REGION1A).lighten(15).setAlpha(0.60).toRgbString() : '#d3d3d3';
            legendHTML += `<div><i style="background:${otherExampleColor}"></i> Estimasi Dekat (No Sales)</div>`;

            legendHTML += `<div><i style="background:${regionColors.OTHER_BASE}"></i> Lainnya/Tanpa Data</div>`;
            legendItemsScrollContainer.innerHTML = legendHTML;
        }

        function updateCityMarkers() {
            cityMarkersLayerGroup.clearLayers(); 

            const commonMarkerIcon = L.icon({
                iconUrl: '{{ asset("maps/marker.svg") }}', 
                iconSize: [28, 28], 
                iconAnchor: [14, 28], 
                popupAnchor: [0, -28] 
            });

            if (currentMapView === 'indonesia' && cityMarkersData && cityMarkersData.length > 0) {
                cityMarkersData.forEach(city => {
                    if (city.lat && city.lng && city.sales >= 0) { 
                        const marker = L.marker([city.lat, city.lng], { icon: commonMarkerIcon });
                        const salesFormatted = (city.sales || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        const popupContent = `<strong>${city.name} (ID)</strong><br>Sales: ${salesFormatted} Ton`;
                        marker.bindPopup(popupContent);
                        cityMarkersLayerGroup.addLayer(marker);
                    }
                });
            } else if (currentMapView === 'world' && internationalCityMarkersData && internationalCityMarkersData.length > 0) {
                internationalCityMarkersData.forEach(city => {
                    if (city.lat && city.lng && city.sales > 0) { 
                        const marker = L.marker([city.lat, city.lng], { icon: commonMarkerIcon });
                        const salesFormatted = (city.sales || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        const popupContent = `<strong>${city.name} (${city.country || ''})</strong><br>Sales: ${salesFormatted} Ton`;
                        marker.bindPopup(popupContent);
                        cityMarkersLayerGroup.addLayer(marker);
                    }
                });
            }

            if (cityMarkersLayerGroup.getLayers().length > 0) { 
                if (!map.hasLayer(cityMarkersLayerGroup)) {
                    cityMarkersLayerGroup.addTo(map);
                }
            } else {
                if (map.hasLayer(cityMarkersLayerGroup)) {
                    map.removeLayer(cityMarkersLayerGroup);
                }
            }
        }


        async function handleFilterChange() {
            // Explicitly hide tooltips at the very start of a filter change
            infoTooltipGlobalDiv.style.display = 'none';
            salesTooltipIndonesiaDiv.style.display = 'none';

            const startDate = document.getElementById('start-date-select').value;
            const endDate = document.getElementById('end-date-select').value;
            const selectedBrand = document.getElementById('brand-filter').value;
            const selectedCodeCmmt = document.getElementById('code-cmmt-filter').value;
            const selectedCity = document.getElementById('city-filter').value;


            if (!startDate || !endDate) { alert("Please select valid start and end dates."); hideLoading(); return; }
            if (new Date(startDate) > new Date(endDate)) { alert("Start date cannot be after end date."); hideLoading(); return; }

            showLoading('Mengambil data penjualan...');
            if(map.hasLayer(cityMarkersLayerGroup)) cityMarkersLayerGroup.clearLayers(); 

            try {
                const params = new URLSearchParams({
                    startDate: startDate,
                    endDate: endDate
                });
                if (selectedBrand && selectedBrand !== 'ALL') params.append('brand', selectedBrand);
                if (selectedCodeCmmt && selectedCodeCmmt !== 'ALL') params.append('code_cmmt', selectedCodeCmmt);
                if (selectedCity && selectedCity !== 'ALL') params.append('city', selectedCity);

                const response = await fetch(`{{ route('api.sales.data') }}?${params.toString()}`, {
                    method: 'GET', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')}
                });
                if (!response.ok) {
                    const errorData = await response.json().catch(()=>({message:`HTTP error! Status: ${response.status}`}));
                    throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
                }
                const data = await response.json();

                salesDataGlobal = {};
                if (data.worldSales) {
                    for (const countryName in data.worldSales) {
                        let key = countryName;
                        if (countryName.toLowerCase() === "united states of america") key = "United States";
                        else if (countryName.toLowerCase() === "indonesia") key = "Indonesia";

                        salesDataGlobal[key] = { 
                            sales: Number(data.worldSales[countryName].sales) || 0,
                            budget: Number(data.worldSales[countryName].budget) || 0,
                            lastYearSales: Number(data.worldSales[countryName].lastYearSales) || 0
                        };
                    }
                }

                superRegionSales = {};
                if (data.indonesiaSuperRegionSales) {
                     for (const regionKey in data.indonesiaSuperRegionSales) {
                        superRegionSales[regionKey] = {
                            sales: Number(data.indonesiaSuperRegionSales[regionKey].sales) || 0,
                            budget: Number(data.indonesiaSuperRegionSales[regionKey].budget) || 0,
                            lastYearSales: Number(data.indonesiaSuperRegionSales[regionKey].lastYearSales) || 0
                        };
                    }
                }

                cityMarkersData = data.cityMarkers || [];
                internationalCityMarkersData = data.internationalCityMarkers || []; // Populate international markers

                const mapUrl = currentMapView === 'world' ? WORLD_TOPOJSON_URL : INDONESIA_TOPOJSON_URL;
                const cacheKey = currentMapView === 'world' ? WORLD_CACHE_KEY : INDONESIA_CACHE_KEY;

                await loadAndDisplayMapData(mapUrl, currentMapView, cacheKey); 
                updateDashboardPanels(); 
                updateCityMarkers();     

            } catch (error) {
                console.error('Gagal memproses data:', error);
                alert(`Gagal memuat data: ${error.message}`);
                salesDataGlobal = {}; superRegionSales = {}; cityMarkersData = []; internationalCityMarkersData = [];
                updateDashboardPanels(); updateCityMarkers(); 
                hideLoading();
            }
        }
    </script>

</x-app-layout>