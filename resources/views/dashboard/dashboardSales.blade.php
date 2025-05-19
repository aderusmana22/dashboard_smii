<x-app-layout>

    @section('title')
    Dashboard Sales (Data Dinamis)
    @endsection

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; }

        #map-ui-container {
            position: relative;
            width: 100%;
            height: calc(100vh - 57px); /* Adjust 57px if your x-app-layout header height differs */
            overflow: hidden;
            background-color: #f0f0f0;
        }

        /* Default Leaflet Zoom Control Hidden */
        .leaflet-control-zoom { display: none !important; }

        #map { height: 100%; width: 100%; }

        .info-tooltip-global {
            position: absolute; padding: 8px; background: rgba(0, 0, 0, 0.8);
            color: white; border-radius: 4px; font-size: 12px;
            z-index: 800; pointer-events: none; display: none;
        }
        #sales-tooltip-indonesia {
            position: absolute; padding: 10px; background: rgba(255, 255, 255, 0.95);
            color: #333; border: 1px solid #ccc; border-radius: 5px; font-size: 13px;
            z-index: 800; pointer-events: none; display: none; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .loading {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7); color: white; padding: 20px; border-radius: 8px;
            z-index: 1000; font-size: 16px; text-align: center; display: none;
        }
        .geoboundaries-watermark {
            position: absolute; bottom: 3px; right: 50px; font-size: 9px; color: #555;
            background-color: rgba(255, 255, 255, 0.7); padding: 2px 4px; border-radius: 3px; z-index: 700;
        }

        /* Wrapper for left-side stat tables (now primarily Super Region) */
        #left-column-stats-container {
            position: absolute;
            top: 55px; /* Below header + padding */
            left: 10px;
            z-index: 710;
            width: 380px;
            display: flex;
            flex-direction: column;
            /* gap: 15px; /* No longer needed if only one primary table here */
            max-height: calc(100vh - 70px - 200px - 20px - 10px); /* 100vh - top_offset - chart_height - chart_bottom_margin - container_bottom_padding */
            overflow-y: auto; /* Scroll the column if content overflows */
            padding-bottom: 10px; /* Add some padding at the bottom if it scrolls */
        }

        /* Super Region Stats Table (always visible, in left column) */
        #super-region-stats-container {
            background: rgba(255, 255, 255, 0.92); padding: 10px; border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); font-size: 12px;
            width: 100%; /* Fill wrapper */
            display: block; /* Always visible */
        }
        #super-region-stats-container h3 {
            margin-top: 0; margin-bottom: 8px; font-size: 15px;
            border-bottom: 1px solid #eee; padding-bottom: 5px;
        }
        #super-region-stats-table { width: 100%; border-collapse: collapse; }
        #super-region-stats-table th, #super-region-stats-table td {
            border: 1px solid #ddd; padding: 5px; text-align: left;
        }
        #super-region-stats-table th { background-color: #f2f2f2; font-size: 11px; }
        #super-region-stats-table td { font-size: 11px; }
        #super-region-stats-table td.number-cell { text-align: right; }

        /* International Stats Table - Repositioned to top-right */
        #international-stats-container {
            position: absolute;
            top: 75px; /* Positioned below filter-menu */
            right: 15px; /* Aligned with filter-menu */
            background: rgba(255, 255, 255, 0.92); padding: 10px; border-radius: 8px;
            z-index: 709; /* Slightly below filter menu if they could overlap */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); font-size: 12px;
            width: 330px; /* Adjust width as desired */
            max-height: calc(100vh - 65px - 20px); /* Viewport height - top offset - some bottom padding */
            overflow-y: auto; /* Scrollable if content exceeds max-height */
            display: none; /* Default hidden, JS will show in world view */
        }
        #international-stats-container h3 {
            margin-top: 0; margin-bottom: 8px; font-size: 15px;
            border-bottom: 1px solid #eee; padding-bottom: 5px;
        }
        #international-stats-table { width: 100%; border-collapse: collapse; }
        #international-stats-table th, #international-stats-table td {
            border: 1px solid #ddd; padding: 5px; text-align: left;
        }
        #international-stats-table th { background-color: #f2f2f2; font-size: 11px; }
        #international-stats-table td { font-size: 11px; }
        #international-stats-table td.number-cell { text-align: right; }


        #chart-container {
            position: absolute; bottom: 100px; left: 10px; /* Positioned independently */
            width: 380px; /* Matched width of left column for alignment */
            height: 200px;
            background: rgba(255, 255, 255, 0.92); padding: 10px;
            border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            z-index: 710; display: block; /* Always visible */
        }
        #chart-container canvas { width: 100% !important; height: 100% !important; }

        #filter-menu {
            position: absolute; top: 15px; right: 15px;
            background: rgba(255, 255, 255, 0.92); padding: 10px 15px;
            border-radius: 8px; z-index: 710; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            display: flex; gap: 10px; align-items: center;
        }
        #filter-menu label { font-size: 14px; margin-right: 3px; }
        #filter-menu input[type="date"] {
            padding: 5px; border-radius: 4px; border: 1px solid #ccc; font-size: 13px;
        }

        #back-to-world-btn-dynamic {
            position: absolute; top: 5px; left: 15px;
            background: #fff; color: #337ab7; padding: 8px 12px; border-radius: 5px;
            text-decoration: none; font-size: 13px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 720; display: none; /* Visible only in Indonesia view */
        }
        #back-to-world-btn-dynamic:hover { background: #f0f0f0; }

        #indonesia-legend-floating {
            position: absolute; bottom: 80px; right: 10px;
            background: rgba(255,255,255,0.9); padding: 10px; border-radius: 5px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.2); z-index: 700;
            max-height: 375px; /* Increased max-height */
            overflow-y: auto; font-size: 11px; display: none; /* Visible only in Indonesia view */
        }
        #indonesia-legend-floating h4 { margin-top: 0; margin-bottom: 5px; font-size: 13px; }
        #indonesia-legend-floating div { margin-bottom: 3px; }
        #indonesia-legend-floating i {
            width: 12px; height: 12px; float: left; margin-right: 5px; border: 1px solid #ccc;
        }
    </style>

    <div id="map-ui-container">
        <div id="map"></div>
        <div id="loading" class="loading">Memuat Peta...</div>

        <div class="geoboundaries-watermark">
            This map uses boundaries from <a href="https://www.geoboundaries.org" target="_blank"
                rel="noopener noreferrer">geoBoundaries</a>.
        </div>

        {{-- Wrapper for left-side stat tables (now primarily Super Region) --}}
        <div id="left-column-stats-container">
            <div id="super-region-stats-container">
                <h3>Indonesia Super Region Sales</h3>
                <table id="super-region-stats-table">
                    <thead>
                        <tr>
                            <th>Region</th>
                            <th>Budget (Ton)</th>
                            <th>Dispatch (Ton)</th>
                            <th>Achieve %</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
            </div>
        </div>

        {{-- International Stats Table - Repositioned to top-right --}}
        <div id="international-stats-container">
            <h3>International Export Sales</h3> {{-- Title changed --}}
            <table id="international-stats-table">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Sales (Ton)</th>
                        <th>Budget (Est.)</th>
                        <th>Achieve % (Est.)</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot></tfoot>
            </table>
        </div>


        <div id="filter-menu">
            <label for="start-date-select">Start:</label>
            <input type="date" id="start-date-select">
            <label for="end-date-select">End:</label>
            <input type="date" id="end-date-select">
        </div>

        <div id="chart-container">
            <canvas id="salesChart"></canvas>
        </div>

        <div id="info-box" class="info-tooltip-global"></div>
        <div id="sales-tooltip-indonesia"></div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/topojson/3.0.2/topojson.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/tinycolor2"></script>

    <script>
        // --- Variabel Global & Konfigurasi ---
        let currentMapView = 'world'; // Default view
        let map, geoLayer;

        const WORLD_TOPOJSON_URL = '{{ asset('maps/dunia.topojson') }}';
        const INDONESIA_TOPOJSON_URL = '{{ asset('maps/indo.topojson') }}';
        const WORLD_CACHE_KEY = 'world-custom-topojson-v2-dynamic';
        const INDONESIA_CACHE_KEY = 'indonesia-adm2-topojson-v14-dynamic';
        const MAX_CACHE_SIZE_MB = 5;
        const CALCULATION_BATCH_SIZE = 50; // Milliseconds for batch processing
        const INDONESIA_DEFAULT_ZOOM_LEVEL = 6;
        const INDONESIA_MIN_ZOOM = 6;
        const INDONESIA_MAX_ZOOM = 10;
        const WORLD_DEFAULT_ZOOM_LEVEL = 4;
        const WORLD_MIN_MAX_ZOOM = 4; // Fixed zoom for world view


        let salesDataGlobal = {};
        let superRegionSales = {};

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
            superRegionDefinitions[superReg].forEach(city => cityToSuperRegionMap[city.toLowerCase()] = superReg);
        }
        cityToSuperRegionMap["bali (horeka)"] = "REGION2C";

        const regionColors = {
            "REGION1A": "#8dd3c7", "REGION1B": "#ffffb3", "REGION1C": "#bebada", "REGION1D": "#fb8072",
            "REGION2A": "#80b1d3", "REGION2B": "#fdb462", "REGION2C": "#b3de69", "REGION2D": "#fccde5",
            "REGION3A": "#bc80bd", "REGION3B": "#ccebc5", "REGION3C": "#ffed6f",
            "REGION4A": "#99cce0", "REGION4B": "#f7cac9",
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
        let indonesiaLegendFloating;
        let internationalStatsContainer; // Cached element

        const infoTooltipGlobalDiv = document.getElementById('info-box');
        const salesTooltipIndonesiaDiv = document.getElementById('sales-tooltip-indonesia');

        function showLoading(message = 'Memuat...') {
            if (loadingDiv) {
                loadingDiv.textContent = message;
                loadingDiv.style.display = 'block';
            }
        }

        function hideLoading() {
            if (loadingDiv) {
                loadingDiv.style.display = 'none';
            }
        }

        const dateRanges = @json($dateRanges);
        // console.log('dateRanges:', dateRanges);

        document.addEventListener('DOMContentLoaded', () => {
            internationalStatsContainer = document.getElementById('international-stats-container'); // Cache element early

            initMap();
            initUIElements();
            updateUIVisibilityBasedOnView(currentMapView); // Set initial UI visibility
            handleFilterChange(); // Then load data
        });

        function initMap() {
            map = L.map('map', {
                worldCopyJump: true,
                zoomControl: false, // Default zoom control disabled
            }).setView([20, 0], WORLD_DEFAULT_ZOOM_LEVEL);

            map.getContainer().style.backgroundColor = '#aadaff'; // Map background color

            // Tooltip positioning on mouse move
            map.on('mousemove', function (e) {
                if (currentMapView === 'world' && infoTooltipGlobalDiv.style.display === 'block') {
                    infoTooltipGlobalDiv.style.left = (e.containerPoint.x + 15) + 'px';
                    infoTooltipGlobalDiv.style.top = (e.containerPoint.y + 15) + 'px';
                } else if (currentMapView === 'indonesia' && salesTooltipIndonesiaDiv.style.display === 'block') {
                    salesTooltipIndonesiaDiv.style.left = (e.containerPoint.x + 15) + 'px';
                    salesTooltipIndonesiaDiv.style.top = (e.containerPoint.y + 15) + 'px';
                }
            });
        }

        function initUIElements() {
            const startDateSelect = document.getElementById('start-date-select');
            const endDateSelect = document.getElementById('end-date-select');

            startDateSelect.min = dateRanges.min_date_iso;
            startDateSelect.max = dateRanges.max_date_iso;
            startDateSelect.value = dateRanges.initial_start_date;

            endDateSelect.min = dateRanges.min_date_iso;
            endDateSelect.max = dateRanges.max_date_iso;
            endDateSelect.value = dateRanges.initial_end_date;

            startDateSelect.addEventListener('change', handleFilterChange);
            endDateSelect.addEventListener('change', handleFilterChange);

            const salesChartCanvas = document.getElementById('salesChart');
            if (salesChartCanvas) {
                 salesPieChart = new Chart(salesChartCanvas, {
                    type: 'pie', data: { labels: [], datasets: [] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }

            // Dynamic "Back to World" button
            backToWorldBtnDynamic = document.createElement('a');
            backToWorldBtnDynamic.id = 'back-to-world-btn-dynamic';
            backToWorldBtnDynamic.href = '#';
            backToWorldBtnDynamic.innerHTML = '← Kembali ke Peta Dunia';
            mapUiContainer.appendChild(backToWorldBtnDynamic);
            backToWorldBtnDynamic.addEventListener('click', (e) => {
                e.preventDefault();
                switchToView('world');
            });

            // Dynamic Indonesia Legend
            indonesiaLegendFloating = document.createElement('div');
            indonesiaLegendFloating.id = 'indonesia-legend-floating';
            mapUiContainer.appendChild(indonesiaLegendFloating);
        }

        // Centralize UI visibility logic based on view
        function updateUIVisibilityBasedOnView(viewType) {
            if (internationalStatsContainer) {
                internationalStatsContainer.style.display = (viewType === 'world') ? 'block' : 'none';
            }
            if (backToWorldBtnDynamic) {
                backToWorldBtnDynamic.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            }
            if (indonesiaLegendFloating) {
                indonesiaLegendFloating.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            }
        }


        function switchToView(viewType) {
            const oldView = currentMapView;
            currentMapView = viewType;
            // console.log("Switching to view:", currentMapView);
            showLoading(viewType === 'world' ? 'Memuat Peta Dunia...' : 'Memuat Peta Indonesia...');

            updateUIVisibilityBasedOnView(currentMapView);

            infoTooltipGlobalDiv.style.display = 'none';
            salesTooltipIndonesiaDiv.style.display = 'none';

            // If view type changed, or no data for current view, or geoLayer doesn't match new view
            if (oldView !== viewType ||
                (viewType === 'world' && Object.keys(salesDataGlobal).length === 0) ||
                (viewType === 'indonesia' && Object.keys(superRegionSales).length === 0) ||
                (geoLayer && geoLayer.options && geoLayer.options.viewType !== viewType)
            ) {
                // console.log("switchToView: Conditions met, calling handleFilterChange() to reload map and data for new view");
                handleFilterChange(); // This will also call updateDashboardPanels after data load
            } else {
                // console.log("switchToView: Conditions NOT met, adjusting existing map for view:", viewType);
                 if (viewType === 'world') {
                    if (geoLayer && geoLayer.options && geoLayer.options.viewType === 'world') {
                        map.options.minZoom = WORLD_MIN_MAX_ZOOM;
                        map.options.maxZoom = WORLD_MIN_MAX_ZOOM;
                        map.setView([20, 0], WORLD_DEFAULT_ZOOM_LEVEL);
                        map.setMaxBounds([[-85.0511, -Infinity], [85.0511, Infinity]]); // Restrict vertical pan for world
                        geoLayer.setStyle(styleFeatureMap);
                    } else {
                         handleFilterChange(); return; // Force reload if geoLayer mismatch
                    }
                } else if (viewType === 'indonesia') {
                     if (geoLayer && geoLayer.options && geoLayer.options.viewType === 'indonesia') {
                        const defaultIndonesiaCenter = [-2.5, 118];
                        map.options.minZoom = INDONESIA_MIN_ZOOM;
                        map.options.maxZoom = INDONESIA_MAX_ZOOM;
                        const bounds = geoLayer.getBounds();
                        if (bounds.isValid()) {
                            map.fitBounds(bounds.pad(0.1)); // Fit to features with a little padding
                            map.setMaxBounds(bounds.pad(0.3)); // Restrict panning to a slightly larger area
                        } else {
                            map.setView(defaultIndonesiaCenter, INDONESIA_DEFAULT_ZOOM_LEVEL); // Fallback
                            map.setMaxBounds(null); // Or set some default reasonable bounds
                        }
                        geoLayer.setStyle(styleFeatureMap);
                    } else {
                         handleFilterChange(); return; // Force reload
                    }
                }
                updateDashboardPanels();
                hideLoading();
            }
        }

        async function loadAndDisplayMapData(url, viewType, cacheKey = null) {
            let topoData;
            if (cacheKey) {
                try {
                    const cached = localStorage.getItem(cacheKey);
                    if (cached) {
                        // console.log(`Using ${viewType} map data from localStorage.`);
                        topoData = JSON.parse(cached);
                    }
                } catch (e) {
                    console.error('Error reading or parsing cached data:', e);
                    localStorage.removeItem(cacheKey); // Clear corrupted cache
                }
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
                                // console.log(`${viewType} map data cached.`);
                            } else {
                                console.warn(`${viewType} map data too large to cache.`);
                            }
                        } catch (e) {
                             console.error('Error stringifying or caching data:', e);
                        }
                    }
                } catch (error) {
                    console.error(`Gagal memuat TopoJSON ${viewType}:`, error);
                    alert(`Gagal memuat peta ${viewType}. Error: ${error.message}`);
                    hideLoading();
                    // If Indonesia fails to load, attempt to switch back to world view if that's not what failed
                    if (viewType === 'indonesia' && currentMapView === 'indonesia') switchToView('world');
                    return; // Important to stop further processing
                }
            }
            await processTopoJSONAndRender(topoData, viewType);
        }

        async function processTopoJSONAndRender(topojsonInputData, viewType) {
            // console.log("Processing TopoJSON for view:", viewType);
            showLoading(`Memproses data peta ${viewType}...`);

            if (!topojsonInputData || typeof topojsonInputData.objects !== 'object' || Object.keys(topojsonInputData.objects).length === 0) {
                console.error("Invalid TopoJSON data structure", topojsonInputData);
                alert("Gagal memproses data peta: Struktur tidak valid.");
                hideLoading(); return;
            }

            // Try to find a sensible object name within the TopoJSON
            let objectName = Object.keys(topojsonInputData.objects).find(key =>
                key.toLowerCase().includes('countries') || key.toLowerCase().includes('states') ||
                key.toLowerCase().includes('provinces') || key.toLowerCase().includes('adm2') || // More specific first
                key.toLowerCase().includes('adm') || key.toLowerCase().includes('boundaries')
            ) || Object.keys(topojsonInputData.objects)[0]; // Fallback to the first object

            if (!topojsonInputData.objects[objectName]) {
                 console.error(`Invalid TopoJSON: object '${objectName}' not found. Available: ${Object.keys(topojsonInputData.objects).join(', ')}`);
                 hideLoading();
                 alert(`Gagal memproses peta: objek '${objectName}' tidak ditemukan.`);
                 return;
            }

            const geojson = window.topojson.feature(topojsonInputData, topojsonInputData.objects[objectName]);

            if (!geojson || !geojson.features) {
                console.error("Failed to convert TopoJSON or GeoJSON has no features.", geojson);
                alert("Gagal mengkonversi data peta atau tidak ada fitur ditemukan.");
                hideLoading();
                return;
            }


            if (viewType === 'world') {
                calculateWorldMinMaxSales();
            } else if (viewType === 'indonesia') {
                seedRegionData = []; // Clear previous seed data
                featureCentroidCache.clear(); // Clear centroid cache
                 geojson.features.forEach(f => {
                    const cleanedName = getCleanedShapeNameFromProps(f.properties);
                    const superRegName = cityToSuperRegionMap[cleanedName];
                    if (superRegName) {
                        const centroid = getFeatureCentroid(f.geometry);
                        if (centroid) {
                            seedRegionData.push({ centroid: centroid, superRegionName: superRegName });
                        }
                    }
                });
                await calculateNearestSuperRegionsAsync(geojson.features);
            }

            if (geoLayer) map.removeLayer(geoLayer); // Remove old layer if exists
            geoLayer = L.geoJSON(geojson, {
                style: styleFeatureMap,
                onEachFeature: onEachFeatureMap,
                viewType: viewType // Store viewType in layer options for reference
            }).addTo(map);

            // Set map view and bounds based on the view type
            if (viewType === 'world') {
                map.options.minZoom = WORLD_MIN_MAX_ZOOM;
                map.options.maxZoom = WORLD_MIN_MAX_ZOOM;
                map.setView([20, 0], WORLD_DEFAULT_ZOOM_LEVEL);
                map.setMaxBounds([[-85.0511, -Infinity], [85.0511, Infinity]]); // Restrict vertical pan for world
            } else if (viewType === 'indonesia') {
                const defaultIndonesiaCenter = [-2.5, 118];
                map.options.minZoom = INDONESIA_MIN_ZOOM;
                map.options.maxZoom = INDONESIA_MAX_ZOOM;
                const bounds = geoLayer.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds.pad(0.1)); // Fit to features with a little padding
                    map.setMaxBounds(bounds.pad(0.3)); // Restrict panning to a slightly larger area
                } else {
                    map.setView(defaultIndonesiaCenter, INDONESIA_DEFAULT_ZOOM_LEVEL); // Fallback
                    // Optionally set some default max bounds for Indonesia if geoLayer.getBounds() fails
                    // map.setMaxBounds([[-11, 95], [6, 141]]); // Example rough bounds for Indonesia
                }
            }
            hideLoading();
        }

        function getCleanedShapeNameFromProps(properties) {
            if (!properties) return "";
            const potentialNameProps = ['shapeName', 'NAME_2', 'NAME_1', 'name', 'ADM2_EN', 'kabkot', 'ADMIN', 'NAME', 'name_long', 'formal_en', 'COUNTRY'];
            for (const prop of potentialNameProps) {
                if (properties[prop]) {
                    return String(properties[prop]).toLowerCase().trim();
                }
            }
            return ""; // Fallback if no known name property is found
        }

        function getFeatureCentroid(geometry) {
            if (!geometry || !geometry.coordinates) return null;
            // Simple cache key based on type and a snippet of coordinates
            const cacheKey = geometry.type + JSON.stringify(geometry.coordinates.slice(0,1));
            if (featureCentroidCache.has(cacheKey)) {
                return featureCentroidCache.get(cacheKey);
            }

            let centroid;
            if (geometry.type === 'Polygon') {
                const coords = geometry.coordinates[0]; // Exterior ring
                let x = 0, y = 0, count = 0;
                // Simple average for centroid - good enough for many cases
                coords.forEach(c => { x += c[0]; y += c[1]; count++; });
                centroid = count > 0 ? [y / count, x / count] : null; // Leaflet uses [lat, lng]
            } else if (geometry.type === 'MultiPolygon') {
                // For MultiPolygon, find the centroid of the largest polygon (by area approximation)
                let largestPolygonArea = 0;
                let largestPolygonCentroid = null;

                geometry.coordinates.forEach(polygonCoords => {
                    const coords = polygonCoords[0]; // Exterior ring of this polygon part
                    let x = 0, y = 0, count = 0;
                    let currentArea = 0; // Shoelace formula for area

                    for (let i = 0; i < coords.length -1; i++) { // Iterate up to second to last point
                        x += coords[i][0]; y += coords[i][1]; count++;
                        currentArea += (coords[i][0] * coords[i+1][1] - coords[i+1][0] * coords[i][1]);
                    }
                    currentArea = Math.abs(currentArea / 2);

                    if (count > 0 && currentArea > largestPolygonArea) {
                        largestPolygonArea = currentArea;
                        largestPolygonCentroid = [y / count, x / count]; // Centroid of this polygon part
                    }
                });
                centroid = largestPolygonCentroid;
            }
            // Other geometry types (Point, LineString) could be handled if needed

            if (centroid) featureCentroidCache.set(cacheKey, centroid);
            return centroid;
        }

        async function calculateNearestSuperRegionsAsync(features) {
             return new Promise(resolve => {
                let index = 0;
                const totalFeatures = features.length;

                function processBatch() {
                    const batchEndTime = Date.now() + CALCULATION_BATCH_SIZE; // Process for a fixed time slice
                    while (index < totalFeatures && Date.now() < batchEndTime) {
                        const feature = features[index];
                        const cleanedName = getCleanedShapeNameFromProps(feature.properties);

                        // Only calculate if not directly mapped and seed data exists
                        if (!cityToSuperRegionMap[cleanedName] && seedRegionData.length > 0) {
                            const featureCentroid = getFeatureCentroid(feature.geometry);
                            if (featureCentroid) {
                                let minDistance = Infinity;
                                let nearestSuperRegion = null;
                                seedRegionData.forEach(regionSeed => {
                                    // Leaflet's distanceTo is efficient
                                    const dist = L.latLng(featureCentroid).distanceTo(L.latLng(regionSeed.centroid));
                                    if (dist < minDistance) {
                                        minDistance = dist;
                                        nearestSuperRegion = { name: regionSeed.superRegionName, distance: dist };
                                    }
                                });
                                if (nearestSuperRegion) {
                                    feature.properties.calculatedSuperRegion = nearestSuperRegion;
                                }
                            }
                        }
                        index++;
                    }

                    if (index < totalFeatures) {
                        showLoading(`Menghitung region terdekat... (${Math.round((index/totalFeatures)*100)}%)`);
                        requestAnimationFrame(processBatch); // Schedule next batch
                    } else {
                        hideLoading();
                        resolve(); // All features processed
                    }
                }
                processBatch(); // Start the first batch
            });
        }

        function calculateWorldMinMaxSales() {
            const salesValues = Object.values(salesDataGlobal).map(s => Number(s) || 0).filter(s => s > 0);
            if (salesValues.length > 0) {
                worldMinSales = Math.min(...salesValues);
                worldMaxSales = Math.max(...salesValues);
            } else {
                worldMinSales = 0;
                worldMaxSales = 1; // Avoid division by zero if all sales are 0
            }
            // Ensure Indonesia's sales are considered if it's the absolute max or min
            const indonesiaSales = salesDataGlobal["indonesia"] || 0; // Ensure lowercase 'indonesia'
            if (indonesiaSales > worldMaxSales) {
                worldMaxSales = indonesiaSales;
            }
            if (indonesiaSales > 0 && indonesiaSales < worldMinSales) { // only if indonesiaSales is positive
                worldMinSales = indonesiaSales;
            }
             // Further refinement: if min is 0 but max isn't, set min to a small fraction of max for better gradient
             if (worldMinSales === 0 && worldMaxSales > 0) worldMinSales = Math.min(1, worldMaxSales / 10)
             else if (worldMinSales === 0 && worldMaxSales === 0) worldMinSales = 1; // Default if no sales at all to prevent log(0)
        }

        function getWorldFeatureColor(sales) {
            if (sales <= 0) return worldBaseColor;

            // Handle cases where min/max might be equal or min is 0 to avoid issues with log scale
            if (worldMaxSales === worldMinSales) return tinycolor(worldBaseColor).darken(20).toString(); // A slightly darker base if all positive sales are equal

            const logMax = Math.log10(worldMaxSales || 1); // Use || 1 to avoid log(0)
            const logMin = Math.log10(worldMinSales > 0 ? worldMinSales : 1); // Ensure min is > 0 for log scale
            const logSales = Math.log10(sales || 1);

            let intensity = 0.5; // Default intensity if logMax <= logMin
            if (logMax > logMin) { // Avoid division by zero or negative results
                intensity = (logSales - logMin) / (logMax - logMin);
            }
            intensity = Math.max(0, Math.min(1, intensity)); // Clamp intensity between 0 and 1

            // Color gradient from light yellow to dark red
            const startColor = { r: 255, g: 255, b: 204 }; // Light yellow
            const endColor = { r: 128, g: 0, b: 38 };    // Dark red

            const r = Math.round(startColor.r + (endColor.r - startColor.r) * intensity);
            const g = Math.round(startColor.g + (endColor.g - startColor.g) * intensity);
            const b = Math.round(startColor.b + (endColor.b - startColor.b) * intensity);
            return `rgb(${r},${g},${b})`;
        }

        function styleFeatureMap(feature) {
            if (currentMapView === 'world') {
                let name = getCleanedShapeNameFromProps(feature.properties);
                if (name === "united states of america") name = "united states"; // Normalize
                const sales = salesDataGlobal[name] || 0;

                return {
                    fillColor: getWorldFeatureColor(sales),
                    weight: 0.5, opacity: 1, color: '#bbb', fillOpacity: 0.7
                };
            } else if (currentMapView === 'indonesia') {
                const cleanedName = getCleanedShapeNameFromProps(feature.properties);
                let fillColor = regionColors.OTHER_BASE;
                let fillOpacity = 0.70;

                const superRegKeyFromADM = cityToSuperRegionMap[cleanedName];
                if (superRegKeyFromADM && superRegionSales[superRegKeyFromADM]) { // Directly mapped and has sales
                     fillColor = regionColors[superRegKeyFromADM];
                     fillOpacity = 0.85; // More opaque for areas with sales
                } else if (feature.properties.calculatedSuperRegion) { // Estimated region
                    const nearestSuperRegName = feature.properties.calculatedSuperRegion.name;
                    const nearestSuperRegColor = regionColors[nearestSuperRegName];
                    if (nearestSuperRegColor && tinycolor) {
                        if (superRegionSales[nearestSuperRegName]) { // Closest super region has sales (area itself might not be primary part)
                             fillColor = tinycolor(nearestSuperRegColor).setAlpha(0.75).toRgbString(); // Slightly less opaque
                        } else { // Closest super region has no sales
                             fillColor = tinycolor(nearestSuperRegColor).lighten(15).setAlpha(0.60).toRgbString(); // Lighter and less opaque
                        }
                    }
                } else if (superRegKeyFromADM && regionColors[superRegKeyFromADM]) { // Directly mapped but its super region has no sales
                    fillColor = tinycolor(regionColors[superRegKeyFromADM]).lighten(10).setAlpha(0.65).toRgbString(); // Lighter version of region color
                }
                return { weight: 0.5, opacity: 1, color: 'white', fillOpacity: fillOpacity, fillColor: fillColor };
            }
            // Default style if no conditions met (should not happen with current logic)
            return { fillColor: '#ccc', weight: 1, opacity: 1, color: 'white', fillOpacity: 0.7 };
        }

        function onEachFeatureMap(feature, layer) {
             if (currentMapView === 'world') {
                layer.on({
                    mouseover: (e) => {
                        const props = e.target.feature.properties;
                        let name = getCleanedShapeNameFromProps(props);
                        if (name === "united states of america") name = "united states"; // Normalize
                        const sales = salesDataGlobal[name] || 0;
                        const displayName = name.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '); // Capitalize
                        infoTooltipGlobalDiv.innerHTML = `<strong>${displayName || 'Unknown'}</strong><br>Sales: ${sales.toLocaleString()} Ton`;
                        infoTooltipGlobalDiv.style.display = 'block';
                        layer.setStyle({ weight: 1.5, color: '#666' }); // Highlight feature
                    },
                    mouseout: () => {
                        infoTooltipGlobalDiv.style.display = 'none';
                        geoLayer.resetStyle(layer); // Reset style
                    },
                    click: (e) => {
                        const props = e.target.feature.properties;
                        const name = getCleanedShapeNameFromProps(props);
                        if (name === 'indonesia') { // Check for normalized 'indonesia'
                            switchToView('indonesia');
                        }
                    }
                });
            } else if (currentMapView === 'indonesia') {
                layer.on({
                    mouseover: (e) => {
                        const props = e.target.feature.properties;
                        const cleanedName = getCleanedShapeNameFromProps(props);
                        const superRegKey = cityToSuperRegionMap[cleanedName] || props.calculatedSuperRegion?.name;
                        // const salesForThisArea = superRegKey ? (superRegionSales[superRegKey] || 0) : 0; // Sales of the super region
                        const displayRegionName = superRegKey ? superRegKey.replace(/([A-Z])([0-9])/g, '$1 $2') : 'Tidak terpetakan';
                        const displayNameKabKot = cleanedName.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

                        let tooltipText = `<strong>${displayNameKabKot}</strong><br>Super Region: ${displayRegionName}`;
                        if (superRegKey && superRegionSales.hasOwnProperty(superRegKey)) { // Check if the super region itself has sales data
                            tooltipText += `<br>Sales Region Ini: ${(superRegionSales[superRegKey] || 0).toLocaleString()} Ton`;
                        } else if (superRegKey) { // Super region exists but no sales data for it in current filter
                            tooltipText += `<br>Sales Region Ini: 0 Ton`;
                        }

                        if (props.calculatedSuperRegion && !cityToSuperRegionMap[cleanedName]) { // Show estimation only if not directly mapped
                             tooltipText += `<br><small>(Estimasi via kedekatan)</small>`;
                        }

                        salesTooltipIndonesiaDiv.innerHTML = tooltipText;
                        salesTooltipIndonesiaDiv.style.display = 'block';
                        layer.setStyle({ weight: 2, color: '#555' }); // Highlight feature
                    },
                    mouseout: () => {
                        salesTooltipIndonesiaDiv.style.display = 'none';
                        geoLayer.resetStyle(layer); // Reset style
                    }
                    // click: (e) => { /* Optional: Handle clicks on Indonesian regions if needed */ }
                });
            }
        }


        function updateDashboardPanels() {
            updateSuperRegionStatsTable();
            updateInternationalStatsTable(); // This will handle its own visibility based on currentMapView
            updateSalesChart();
            updateLegend();
        }

        function updateSuperRegionStatsTable() {
            const tableBody = document.querySelector('#super-region-stats-table tbody');
            if (!tableBody) return;
            tableBody.innerHTML = ''; // Clear previous content

            if (Object.keys(superRegionSales).length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No super region data for this period.</td></tr>';
                // Clear tfoot as well
                const tfoot = document.querySelector('#super-region-stats-table tfoot');
                if (tfoot) tfoot.innerHTML = '';
                return;
            }

            let totalDispatch = 0;
            let totalBudget = 0;
            const sortedRegionKeys = Object.keys(superRegionSales).sort(); // Sort for consistent order

            for (const regionKey of sortedRegionKeys) {
                const dispatch = superRegionSales[regionKey] || 0;
                // Dummy budget calculation for example
                const budget = (dispatch > 0 ? Math.max(1000, dispatch * (0.8 + Math.random() * 0.4)) : (5000 + Math.random() * 2000));
                const achievement = budget > 0 ? (dispatch / budget * 100) : 0;

                totalDispatch += dispatch;
                totalBudget += budget;

                const row = tableBody.insertRow();
                row.insertCell().textContent = regionKey.replace(/([A-Z])([0-9])/g, '$1 $2'); // Format region name
                row.insertCell().textContent = budget.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
                row.cells[1].classList.add('number-cell');
                row.insertCell().textContent = dispatch.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
                row.cells[2].classList.add('number-cell');
                row.insertCell().textContent = achievement.toFixed(1) + '%';
                row.cells[3].classList.add('number-cell');
            }

            // Footer for Super Region Table
            let tfoot = document.querySelector('#super-region-stats-table tfoot');
            if (!tfoot) { // Create tfoot if it doesn't exist
                tfoot = document.createElement('tfoot');
                document.getElementById('super-region-stats-table').appendChild(tfoot);
            }
            tfoot.innerHTML = ''; // Clear previous footer

            const totalAch = totalBudget > 0 ? (totalDispatch / totalBudget * 100) : 0;
            const totalRow = tfoot.insertRow();
            totalRow.insertCell().textContent = "TOTAL INDONESIA";
            totalRow.cells[0].style.fontWeight = "bold";
            totalRow.insertCell().textContent = totalBudget.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
            totalRow.cells[1].classList.add('number-cell'); totalRow.cells[1].style.fontWeight = "bold";
            totalRow.insertCell().textContent = totalDispatch.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
            totalRow.cells[2].classList.add('number-cell'); totalRow.cells[2].style.fontWeight = "bold";
            totalRow.insertCell().textContent = totalAch.toFixed(1) + '%';
            totalRow.cells[3].classList.add('number-cell'); totalRow.cells[3].style.fontWeight = "bold";
        }

        function updateInternationalStatsTable() {
            const tableBody = document.querySelector('#international-stats-table tbody');
            if (!tableBody) return;
            tableBody.innerHTML = '';

            const tfoot = document.querySelector('#international-stats-table tfoot');
            if (tfoot) tfoot.innerHTML = '';


            if (currentMapView !== 'world' || Object.keys(salesDataGlobal).length === 0) {
                 tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No international export data for this period.</td></tr>';
                return;
            }

            let dataForTable = [];
            // Process export countries (excluding Indonesia)
            const exportCountries = Object.entries(salesDataGlobal)
                .filter(([country, sales]) => country.toLowerCase() !== 'indonesia' && (Number(sales) || 0) > 0)
                .sort(([,a],[,b]) => (Number(b) || 0) - (Number(a) || 0)); // Sort by sales desc

            const maxOtherCountriesInTable = 5; // Show top 5 export countries + "Other Export"
            let otherExportSalesSum = 0;
            let otherExportBudgetSum = 0;

            exportCountries.forEach(([country, sales], index) => {
                const actualSales = Number(sales) || 0;
                const budget = actualSales * (0.8 + Math.random() * 0.3); // Dummy budget
                const achieve = budget > 0 ? (actualSales / budget * 100) : 0;

                if (index < maxOtherCountriesInTable) {
                    dataForTable.push({ country: country, sales: actualSales, budget: budget, achieve: achieve });
                } else {
                    otherExportSalesSum += actualSales;
                    otherExportBudgetSum += budget;
                }
            });

            if (otherExportSalesSum > 0) {
                const achieveOther = otherExportBudgetSum > 0 ? (otherExportSalesSum / otherExportBudgetSum * 100) : 0;
                dataForTable.push({ country: "Other Export", sales: otherExportSalesSum, budget: otherExportBudgetSum, achieve: achieveOther });
            }


            if (dataForTable.length === 0) { // If no export data after filtering Indonesia
                 tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No significant international export sales.</td></tr>';
                return;
            }

            let salesSumForTableFooter = 0;
            let budgetSumForTableFooter = 0;

            dataForTable.forEach(item => {
                const row = tableBody.insertRow();
                row.insertCell().textContent = item.country.charAt(0).toUpperCase() + item.country.slice(1); // Capitalize
                row.insertCell().textContent = item.sales.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
                row.cells[1].classList.add('number-cell');
                row.insertCell().textContent = item.budget.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
                row.cells[2].classList.add('number-cell');
                row.insertCell().textContent = item.achieve.toFixed(1) + '%';
                row.cells[3].classList.add('number-cell');

                salesSumForTableFooter += item.sales;
                budgetSumForTableFooter += item.budget;
            });

            // Total Row for International Export Table
            if (tfoot && salesSumForTableFooter > 0) { // Ensure tfoot exists and there are export sales
                const totalAchExport = budgetSumForTableFooter > 0 ? (salesSumForTableFooter / budgetSumForTableFooter * 100) : 0;
                const totalRow = tfoot.insertRow();
                totalRow.insertCell().textContent = "TOTAL EXPORT"; // Footer title changed
                totalRow.cells[0].style.fontWeight = "bold";
                totalRow.insertCell().textContent = salesSumForTableFooter.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
                totalRow.cells[1].classList.add('number-cell'); totalRow.cells[1].style.fontWeight = "bold";
                totalRow.insertCell().textContent = budgetSumForTableFooter.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
                totalRow.cells[2].classList.add('number-cell'); totalRow.cells[2].style.fontWeight = "bold";
                totalRow.insertCell().textContent = totalAchExport.toFixed(1) + '%';
                totalRow.cells[3].classList.add('number-cell'); totalRow.cells[3].style.fontWeight = "bold";
            }
        }


        function chartTooltipCallback(context) {
            let label = context.label || '';
            if (label) { label += ': '; }
            if (context.parsed !== null) { label += context.parsed.toLocaleString() + ' Ton'; }
            const total = context.dataset.data.reduce((sum, value) => sum + value, 0);
            if (total > 0 && context.parsed !== null) { // ensure context.parsed is a number for percentage calculation
                const percentage = (context.parsed / total * 100).toFixed(1) + '%';
                label += ` (${percentage})`;
            }
            return label;
        }

        function updateSalesChart() {
            const salesChartCanvas = document.getElementById('salesChart');
            if (!salesChartCanvas) { console.warn("Sales chart canvas not found."); return; }
            if (salesPieChart) salesPieChart.destroy(); // Destroy existing chart instance

            const ctx = salesChartCanvas.getContext('2d');
            let chartConfig;

            if (currentMapView === 'world') {
                const indonesiaSales = Number(salesDataGlobal['indonesia']) || 0; // Ensure lowercase 'indonesia'
                // Calculate total global sales from all countries in salesDataGlobal
                const totalGlobalSales = Object.values(salesDataGlobal).reduce((sum, sales) => sum + (Number(sales) || 0), 0);
                const otherExportSales = totalGlobalSales - indonesiaSales; // Export is total minus Indonesia

                let labels = [];
                let data = [];
                let backgroundColors = [];

                if (indonesiaSales > 0) {
                    labels.push('Indonesia');
                    data.push(indonesiaSales);
                    backgroundColors.push('#FF6384'); // Pink for Indonesia
                }
                if (otherExportSales > 0) {
                    labels.push('Global Export'); // Label for all other countries combined
                    data.push(otherExportSales);
                    backgroundColors.push('#36A2EB'); // Blue for Other Export
                }

                if (labels.length === 0) { // If no sales data at all
                    labels.push('No Sales Data');
                    data.push(1); // Dummy data to render an empty-ish chart
                    backgroundColors.push('#CCCCCC');
                }


                chartConfig = {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Global Sales', data: data,
                            backgroundColor: backgroundColors, hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 }, padding: 3 }},
                            title: { display: true, text: 'Sales: Indonesia vs Global Export', font: { size: 13 }, padding: {bottom:8} },
                            tooltip: { callbacks: { label: chartTooltipCallback } }
                        }
                    }
                };
            } else if (currentMapView === 'indonesia') {
                // Sort super regions by sales descending for the chart
                const sortedSuperRegForChart = Object.entries(superRegionSales)
                    .filter(([,sales]) => (Number(sales) || 0) > 0) // Only include regions with sales
                    .sort(([,a],[,b]) => (Number(b) || 0) - (Number(a) || 0));

                let labels_sr = sortedSuperRegForChart.map(([reg]) => reg.replace(/([A-Z])([0-9])/g, '$1 $2'));
                let data_sr = sortedSuperRegForChart.map(([,sales]) => sales);
                let colors_sr = sortedSuperRegForChart.map(([reg]) => regionColors[reg] || '#808080'); // Fallback color

                if (labels_sr.length === 0) { // If no super region sales
                    labels_sr.push('No Super Region Sales');
                    data_sr.push(1);
                    colors_sr.push('#CCCCCC');
                }

                chartConfig = {
                    type: 'pie',
                    data: {
                        labels: labels_sr,
                        datasets: [{
                            label: 'Super Region Sales',
                            data: data_sr,
                            backgroundColor: colors_sr,
                            borderColor: '#fff', borderWidth: 1 // Add a border for better separation
                        }]
                    },
                     options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            title: { display: true, text: 'Sales per Super-Region (ID)', font: {size: 13}, padding: {bottom:8} },
                            legend: { position: 'bottom', labels: { font: {size: 9}, boxWidth:10, padding:5 } }, // Smaller legend for many items
                            tooltip: { callbacks: { label: chartTooltipCallback } }
                        }
                    }
                };
            }
            if (chartConfig) salesPieChart = new Chart(ctx, chartConfig);
        }

        function updateLegend() {
            if (!indonesiaLegendFloating) return;
            indonesiaLegendFloating.innerHTML = ''; // Clear previous legend
            if (currentMapView === 'indonesia') {
                let legendHTML = '<h4>Region Pemasaran</h4>';
                // Show legend for super regions that actually have sales in the current filter
                const regionsWithSales = Object.entries(superRegionSales)
                    .filter(([, sales]) => (Number(sales) || 0) > 0)
                    .map(([regionName]) => regionName)
                    .sort(); // Sort alphabetically

                regionsWithSales.forEach(superRegKey => {
                    if (regionColors[superRegKey]) {
                        let salesVal = superRegionSales[superRegKey] || 0;
                        let salesInfo = (salesVal > 0) ? ` (${salesVal.toLocaleString()})` : "";
                        let displayName = superRegKey.replace(/([A-Z])([0-9])/g, '$1 $2');
                        legendHTML += `<div><i style="background:${regionColors[superRegKey]}"></i> ${displayName}${salesInfo}</div>`;
                    }
                });

                // Add examples for other coloring types
                 const exampleMappedNoSalesColor = (tinycolor && regionColors.REGION1A) ? tinycolor(regionColors.REGION1A).lighten(10).setAlpha(0.65).toRgbString() : '#e0e0e0';
                 legendHTML += `<div><i style="background:${exampleMappedNoSalesColor}"></i> Area S.Region (No Sales)</div>`;

                const otherExampleColor = (tinycolor && regionColors.REGION1A) ? tinycolor(regionColors.REGION1A).lighten(15).setAlpha(0.60).toRgbString() : '#e0e0e0'; // Example for estimated nearby
                legendHTML += `<div><i style="background:${otherExampleColor}"></i> Estimasi Dekat (No Sales)</div>`;

                legendHTML += `<div><i style="background:${regionColors.OTHER_BASE}"></i> Lainnya/Tanpa Data</div>`;


                indonesiaLegendFloating.innerHTML = legendHTML;
                indonesiaLegendFloating.style.display = 'block';
            } else {
                indonesiaLegendFloating.style.display = 'none'; // Hide for world view
            }
        }


        async function handleFilterChange() {
            const startDate = document.getElementById('start-date-select').value;
            const endDate = document.getElementById('end-date-select').value;

            if (!startDate || !endDate) {
                alert("Please select both a start and end date.");
                hideLoading(); // Ensure loading is hidden if we return early
                return;
            }
            if (new Date(startDate) > new Date(endDate)) {
                alert("Start date cannot be after end date.");
                hideLoading();
                return;
            }

            // console.log(`Filter changed: Start ${startDate}, End ${endDate}. View: ${currentMapView}`);
            showLoading('Mengambil data penjualan...');

            try {
                const response = await fetch(`{{ route('api.sales.data') }}?startDate=${startDate}&endDate=${endDate}`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: `HTTP error! status: ${response.status}` }));
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                // Normalize country keys in salesDataGlobal to lowercase and specific cases like "united states"
                salesDataGlobal = {};
                if (data.worldSales) {
                    for (const key in data.worldSales) {
                        let normalizedKey = key.toLowerCase().trim();
                        if (normalizedKey === "united states of america") normalizedKey = "united states";
                        // Add other normalizations if needed, e.g., "south korea" vs "republic of korea"
                        salesDataGlobal[normalizedKey] = data.worldSales[key];
                    }
                }
                superRegionSales = data.indonesiaSuperRegionSales || {};

                const mapUrl = currentMapView === 'world' ? WORLD_TOPOJSON_URL : INDONESIA_TOPOJSON_URL;
                const cacheKey = currentMapView === 'world' ? WORLD_CACHE_KEY : INDONESIA_CACHE_KEY;

                await loadAndDisplayMapData(mapUrl, currentMapView, cacheKey); // This will hide loading upon its completion
                updateDashboardPanels(); // Update all panels with new data

            } catch (error) {
                console.error('Gagal mengambil atau memproses data penjualan:', error);
                alert(`Gagal memuat data penjualan: ${error.message}`);
                salesDataGlobal = {}; // Clear data on error
                superRegionSales = {};
                updateDashboardPanels(); // Refresh panels to show "no data" state
                hideLoading(); // Ensure loading is hidden on error
            }
            // hideLoading() is primarily handled within loadAndDisplayMapData or the catch block now
        }
    </script>

</x-app-layout>