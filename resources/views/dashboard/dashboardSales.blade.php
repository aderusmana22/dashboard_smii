<x-app-layout>

    @section('title')
    Dashboard Sales (Data Dinamis)
    @endsection

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            margin: 0;
        }
        #map-ui-container {
            position: relative;
            width: 100%;
            height: calc(
                100vh - 57px
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
        }
        .info-tooltip-global {
            background: rgba(0, 0, 0, 0.8);
        }
        #sales-tooltip-indonesia {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
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

        #left-column-stats-container {
            position: absolute;
            top: 65px;
            right: 15px;
            left: auto;
            z-index: 709;
            width: 380px;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 75px - 200px - 20px - 10px);
            padding-bottom: 10px;
        }

        #super-region-stats-container {
            background: rgba(255, 255, 255, 0.92);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 12px;
            width: 100%;
            display: block;
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
            width: 96%;
            table-layout: fixed;
        }
        #super-region-stats-table tbody {
            display: block;
            max-height: 150px;
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
            padding: 5px;
            text-align: left;
            font-size: 11px;
        }
        #super-region-stats-table th {
            background-color: #f2f2f2;
        }
        #super-region-stats-table td.number-cell {
            text-align: right;
        }

        #super-region-stats-table .col-region {
            width: 35%;
        }
        #super-region-stats-table .col-budget {
            width: 23%;
        }
        #super-region-stats-table .col-dispatch {
            width: 22%;
        }
        #super-region-stats-table .col-achieve {
            width: 20%;
        }

        #international-stats-container {
            position: absolute;
            top: 55px;
            left: 10px;
            right: auto;
            z-index: 710;
            background: rgba(255, 255, 255, 0.92);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 12px;
            width: 340px;
            max-height: calc(100vh - 55px - 20px);
            overflow-y: auto;
            display: none;
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
            padding: 5px;
            text-align: left;
            font-size: 11px;
        }
        #international-stats-table th {
            background-color: #f2f2f2;
        }
        #international-stats-table td.number-cell {
            text-align: right;
        }
        #international-stats-table .col-country {
            width: 35%;
        }
        #international-stats-table .col-sales {
            width: 23%;
        }
        #international-stats-table .col-budget-est {
            width: 22%;
        }
        #international-stats-table .col-achieve-est {
            width: 20%;
        }

        #chart-container {
            position: absolute;
            bottom: 80px;
            left: 10px;
            width: 380px;
            height: 180px;
            background: rgba(255, 255, 255, 0.92);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            z-index: 710;
            display: block;
        }
        #chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }

        #filter-menu {
            position: absolute;
            top: 5px;
            right: 15px;
            background: rgba(255, 255, 255, 0.92);
            padding: 10px 15px;
            border-radius: 8px;
            z-index: 710;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            display: flex;
            gap: 10px;
            align-items: center;
        }
        #filter-menu label {
            font-size: 14px;
            margin-right: 3px;
        }
        #filter-menu input[type="date"] {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 13px;
        }

        #back-to-world-btn-dynamic {
            position: absolute;
            top: 5px;
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
            bottom: 80px;
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
        }
        .legend-items-scroll-container i {
            width: 12px;
            height: 12px;
            float: left;
            margin-right: 5px;
            border: 1px solid #ccc;
        }

    </style>

    <div id="map-ui-container">
        <div id="map"></div>
        <div id="loading" class="loading">Memuat Peta...</div>

        <div class="geoboundaries-watermark">
            This map uses boundaries from <a href="https://www.geoboundaries.org" target="_blank"
                rel="noopener noreferrer">geoBoundaries</a>.
        </div>

        <div id="left-column-stats-container">
            <div id="super-region-stats-container">
                <h3>Indonesia Super Region Sales</h3>
                <table id="super-region-stats-table">
                    <thead>
                        <tr>
                            <th class="col-region">Region</th>
                            <th class="col-budget">Budget (Ton)</th>
                            <th class="col-dispatch">Dispatch (Ton)</th>
                            <th class="col-achieve">Achieve %</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td class="col-region"></td>
                            <td class="col-budget"></td>
                            <td class="col-dispatch"></td>
                            <td class="col-achieve"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div id="international-stats-container">
            <h3>International Export Sales</h3>
            <table id="international-stats-table">
                <thead>
                    <tr>
                        <th class="col-country">Country</th>
                        <th class="col-sales">Sales (Ton)</th>
                        <th class="col-budget-est">Budget (Est.)</th>
                        <th class="col-achieve-est">Achieve % (Est.)</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                     <tr>
                        <td class="col-country"></td>
                        <td class="col-sales"></td>
                        <td class="col-budget-est"></td>
                        <td class="col-achieve-est"></td>
                    </tr>
                </tfoot>
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

        <div id="indonesia-legend-floating">
            <h4>Region Pemasaran</h4>
            <div class="legend-items-scroll-container">
                {{-- Legend items will be populated here by JS --}}
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/topojson/3.0.2/topojson.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/tinycolor2"></script>

    <script>
        let currentMapView = 'world';
        let map, geoLayer, previousIndonesiaZoom = null;
        const INDIA_CENTER = [7.8731, 80.7718];

        const WORLD_TOPOJSON_URL = '{{ asset('maps/dunia.topojson') }}';
        const INDONESIA_TOPOJSON_URL = '{{ asset('maps/indo.topojson') }}';
        const WORLD_CACHE_KEY = 'world-custom-topojson-v2-dynamic';
        const INDONESIA_CACHE_KEY = 'indonesia-adm2-topojson-v14-dynamic';
        const MAX_CACHE_SIZE_MB = 5;
        const CALCULATION_BATCH_SIZE = 50;

        const INDONESIA_DEFAULT_ZOOM_LEVEL = 5.75;
        const INDONESIA_MIN_ZOOM = 5.75;
        const INDONESIA_MAX_ZOOM = 7.5;
        const WORLD_DEFAULT_ZOOM_LEVEL = 4;
        const WORLD_MIN_MAX_ZOOM = 4;


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
        let indonesiaLegendContainer;
        let legendItemsScrollContainer;
        let internationalStatsContainer;

        const infoTooltipGlobalDiv = document.getElementById('info-box');
        const salesTooltipIndonesiaDiv = document.getElementById('sales-tooltip-indonesia');

        function showLoading(message = 'Memuat...') {
            if (loadingDiv) { loadingDiv.textContent = message; loadingDiv.style.display = 'block'; }
        }
        function hideLoading() {
            if (loadingDiv) { loadingDiv.style.display = 'none'; }
        }

        const dateRanges = @json($dateRanges);

        document.addEventListener('DOMContentLoaded', () => {
            internationalStatsContainer = document.getElementById('international-stats-container');
            indonesiaLegendContainer = document.getElementById('indonesia-legend-floating');
            legendItemsScrollContainer = indonesiaLegendContainer.querySelector('.legend-items-scroll-container');


            initMap();
            initUIElements();
            updateUIVisibilityBasedOnView(currentMapView);
            handleFilterChange();
        });

        function initMap() {
            map = L.map('map', {
                worldCopyJump: true,
                zoomControl: false,
                 zoomSnap: 0.25,
                zoomDelta: 0.25 
            }).setView(INDIA_CENTER, WORLD_DEFAULT_ZOOM_LEVEL);

            map.getContainer().style.backgroundColor = '#aadaff';

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

            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const todayISO = `${yyyy}-${mm}-${dd}`;

            startDateSelect.min = dateRanges.min_date_iso;
            startDateSelect.max = todayISO;
            startDateSelect.value = todayISO;

            endDateSelect.min = dateRanges.min_date_iso;
            endDateSelect.max = todayISO;
            endDateSelect.value = todayISO;

            startDateSelect.addEventListener('change', handleFilterChange);
            endDateSelect.addEventListener('change', handleFilterChange);

            const salesChartCanvas = document.getElementById('salesChart');
            if (salesChartCanvas) {
                 salesPieChart = new Chart(salesChartCanvas, {
                    type: 'pie', data: { labels: [], datasets: [] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }

            backToWorldBtnDynamic = document.getElementById('back-to-world-btn-dynamic');
             if(!backToWorldBtnDynamic) {
                backToWorldBtnDynamic = document.createElement('a');
                backToWorldBtnDynamic.id = 'back-to-world-btn-dynamic';
                backToWorldBtnDynamic.href = '#';
                backToWorldBtnDynamic.innerHTML = '← Kembali ke Peta Dunia';
                mapUiContainer.appendChild(backToWorldBtnDynamic);
            }
            backToWorldBtnDynamic.addEventListener('click', (e) => {
                e.preventDefault();
                switchToView('world');
            });
        }

        function updateUIVisibilityBasedOnView(viewType) {
            if (internationalStatsContainer) {
                internationalStatsContainer.style.display = (viewType === 'world') ? 'block' : 'none';
            }
            if (backToWorldBtnDynamic) {
                backToWorldBtnDynamic.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            }
            if (indonesiaLegendContainer) {
                indonesiaLegendContainer.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            }
        }

        function switchToView(viewType) {
            const oldView = currentMapView;
            currentMapView = viewType;
            showLoading(viewType === 'world' ? 'Memuat Peta Dunia...' : 'Memuat Peta Indonesia...');
            updateUIVisibilityBasedOnView(currentMapView);
            infoTooltipGlobalDiv.style.display = 'none';
            salesTooltipIndonesiaDiv.style.display = 'none';

            if (viewType === 'indonesia' && oldView === 'world') {
                previousIndonesiaZoom = null;
            }

            if (oldView !== viewType ||
                (viewType === 'world' && Object.keys(salesDataGlobal).length === 0) ||
                (viewType === 'indonesia' && Object.keys(superRegionSales).length === 0) ||
                (geoLayer && geoLayer.options && geoLayer.options.viewType !== viewType)
            ) {
                handleFilterChange();
            } else {
                 if (viewType === 'world') {
                    if (geoLayer && geoLayer.options && geoLayer.options.viewType === 'world') {
                        map.options.minZoom = WORLD_MIN_MAX_ZOOM;
                        map.options.maxZoom = WORLD_MIN_MAX_ZOOM;
                        map.setView(INDIA_CENTER, WORLD_DEFAULT_ZOOM_LEVEL);
                        map.setMaxBounds([[-85.0511, -Infinity], [85.0511, Infinity]]);
                        geoLayer.setStyle(styleFeatureMap);
                    } else { handleFilterChange(); return; }
                } else if (viewType === 'indonesia') {
                     if (geoLayer && geoLayer.options && geoLayer.options.viewType === 'indonesia') {
                        map.options.minZoom = INDONESIA_MIN_ZOOM;
                        map.options.maxZoom = INDONESIA_MAX_ZOOM;
                        const bounds = geoLayer.getBounds();
                        if (bounds.isValid()) {
                            map.fitBounds(bounds.pad(0.1));
                            map.setMaxBounds(bounds.pad(0.3));
                            if (previousIndonesiaZoom !== null && previousIndonesiaZoom >= INDONESIA_MIN_ZOOM && previousIndonesiaZoom <= INDONESIA_MAX_ZOOM) {
                                map.setZoom(previousIndonesiaZoom);
                            }
                        } else {
                            map.setView([-2.5, 118], INDONESIA_DEFAULT_ZOOM_LEVEL);
                            map.setMaxBounds(null);
                        }
                        geoLayer.setStyle(styleFeatureMap);
                    } else { handleFilterChange(); return; }
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
                    if (viewType === 'indonesia' && currentMapView === 'indonesia') switchToView('world');
                    return;
                }
            }
            await processTopoJSONAndRender(topoData, viewType);
        }

        async function processTopoJSONAndRender(topojsonInputData, viewType) {
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
                    const cN = getCleanedShapeNameFromProps(f.properties), sRN = cityToSuperRegionMap[cN];
                    if (sRN) { const cen = getFeatureCentroid(f.geometry); if (cen) seedRegionData.push({ centroid: cen, superRegionName: sRN }); }
                });
                await calculateNearestSuperRegionsAsync(geojson.features);
            }

            if (geoLayer) map.removeLayer(geoLayer);
            geoLayer = L.geoJSON(geojson, { style: styleFeatureMap, onEachFeature: onEachFeatureMap, viewType: viewType }).addTo(map);

            if (viewType === 'world') {
                map.options.minZoom = WORLD_MIN_MAX_ZOOM; map.options.maxZoom = WORLD_MIN_MAX_ZOOM;
                map.setView(INDIA_CENTER, WORLD_DEFAULT_ZOOM_LEVEL);
                map.setMaxBounds([[-85.0511, -Infinity], [85.0511, Infinity]]);
            } else if (viewType === 'indonesia') {
                map.options.minZoom = INDONESIA_MIN_ZOOM; map.options.maxZoom = INDONESIA_MAX_ZOOM;
                const bounds = geoLayer.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds.pad(0.1));
                    map.setMaxBounds(bounds.pad(0.3));
                    if (previousIndonesiaZoom !== null && previousIndonesiaZoom >= INDONESIA_MIN_ZOOM && previousIndonesiaZoom <= INDONESIA_MAX_ZOOM) {
                        map.setZoom(previousIndonesiaZoom);
                    }
                } else {
                    map.setView([-2.5, 118], INDONESIA_DEFAULT_ZOOM_LEVEL);
                    map.setMaxBounds(null);
                }
                previousIndonesiaZoom = null;
            }
            hideLoading();
        }

        function getCleanedShapeNameFromProps(properties) {
            if (!properties) return "";
            const potentialNameProps = ['shapeName', 'NAME_2', 'NAME_1', 'name', 'ADM2_EN', 'kabkot', 'ADMIN', 'NAME', 'name_long', 'formal_en', 'COUNTRY'];
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
            if (geometry.type === 'Polygon') {
                const coords = geometry.coordinates[0]; let x=0,y=0,count=0;
                coords.forEach(c => { x += c[0]; y += c[1]; count++; });
                centroid = count > 0 ? [y / count, x / count] : null;
            } else if (geometry.type === 'MultiPolygon') {
                let largestPolygonArea = 0, largestPolygonCentroid = null;
                geometry.coordinates.forEach(polygonCoords => {
                    const coords = polygonCoords[0]; let x=0,y=0,count=0,currentArea=0;
                    for (let i=0; i<coords.length-1; i++) {
                        x+=coords[i][0]; y+=coords[i][1]; count++;
                        currentArea+=(coords[i][0]*coords[i+1][1] - coords[i+1][0]*coords[i][1]);
                    }
                    currentArea=Math.abs(currentArea/2);
                    if(count>0 && currentArea > largestPolygonArea){
                        largestPolygonArea=currentArea; largestPolygonCentroid=[y/count,x/count];
                    }
                });
                centroid = largestPolygonCentroid;
            }
            if (centroid) featureCentroidCache.set(cacheKey, centroid);
            return centroid;
        }

        async function calculateNearestSuperRegionsAsync(features) {
             return new Promise(resolve => {
                let index = 0; const totalFeatures = features.length;
                function processBatch() {
                    const batchEndTime = Date.now() + CALCULATION_BATCH_SIZE;
                    while (index < totalFeatures && Date.now() < batchEndTime) {
                        const feature = features[index], cleanedName = getCleanedShapeNameFromProps(feature.properties);
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
                processBatch();
            });
        }

        function calculateWorldMinMaxSales() {
            const salesValues = Object.values(salesDataGlobal).map(s=>Number(s)||0).filter(s=>s>0);
            if(salesValues.length>0){ worldMinSales=Math.min(...salesValues); worldMaxSales=Math.max(...salesValues); }
            else { worldMinSales=0; worldMaxSales=1; }
            const indonesiaSales = salesDataGlobal["indonesia"]||0;
            if(indonesiaSales > worldMaxSales) worldMaxSales = indonesiaSales;
            if(indonesiaSales > 0 && indonesiaSales < worldMinSales) worldMinSales = indonesiaSales;
            if(worldMinSales===0 && worldMaxSales > 0) worldMinSales = Math.min(1, worldMaxSales/10);
            else if(worldMinSales===0 && worldMaxSales===0) worldMinSales = 1;
        }

        function getWorldFeatureColor(sales) {
            if (sales <= 0) return worldBaseColor;
            if (worldMaxSales === worldMinSales) return tinycolor(worldBaseColor).darken(20).toString();
            const logMax = Math.log10(worldMaxSales||1), logMin = Math.log10(worldMinSales > 0 ? worldMinSales : 1), logSales = Math.log10(sales||1);
            let intensity = 0.5; if (logMax > logMin) intensity = (logSales - logMin) / (logMax - logMin);
            intensity = Math.max(0, Math.min(1, intensity));
            const sC={r:255,g:255,b:204}, eC={r:128,g:0,b:38};
            const r=Math.round(sC.r+(eC.r-sC.r)*intensity), g=Math.round(sC.g+(eC.g-sC.g)*intensity), b=Math.round(sC.b+(eC.b-sC.b)*intensity);
            return `rgb(${r},${g},${b})`;
        }

        function styleFeatureMap(feature) {
            if (currentMapView === 'world') {
                let name = getCleanedShapeNameFromProps(feature.properties); if (name === "united states of america") name = "united states";
                const sales = salesDataGlobal[name] || 0;
                return { fillColor: getWorldFeatureColor(sales), weight: 0.5, opacity: 1, color: '#bbb', fillOpacity: 0.7 };
            } else if (currentMapView === 'indonesia') {
                const cN=getCleanedShapeNameFromProps(feature.properties); let fC=regionColors.OTHER_BASE,fO=0.70;
                const sRKFA=cityToSuperRegionMap[cN];
                if(sRKFA && superRegionSales[sRKFA]){ fC=regionColors[sRKFA]; fO=0.85; }
                else if(feature.properties.calculatedSuperRegion){
                    const nSRN=feature.properties.calculatedSuperRegion.name, nSRC=regionColors[nSRN];
                    if(nSRC && tinycolor){
                        if(superRegionSales[nSRN]) fC=tinycolor(nSRC).setAlpha(0.75).toRgbString();
                        else fC=tinycolor(nSRC).lighten(15).setAlpha(0.60).toRgbString();
                    }
                } else if(sRKFA && regionColors[sRKFA]){ fC=tinycolor(regionColors[sRKFA]).lighten(10).setAlpha(0.65).toRgbString(); }
                return { weight: 0.5, opacity: 1, color: 'white', fillOpacity:fO, fillColor:fC };
            }
            return { fillColor: '#ccc', weight: 1, opacity: 1, color: 'white', fillOpacity: 0.7 };
        }

        function onEachFeatureMap(feature, layer) {
             if (currentMapView === 'world') {
                layer.on({
                    mouseover: (e)=>{ let p=e.target.feature.properties, n=getCleanedShapeNameFromProps(p); if(n==="united states of america")n="united states"; const s=salesDataGlobal[n]||0, dN=n.split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' '); infoTooltipGlobalDiv.innerHTML=`<strong>${dN||'Unknown'}</strong><br>Sales: ${s.toLocaleString()} Ton`; infoTooltipGlobalDiv.style.display='block'; layer.setStyle({weight:1.5,color:'#666'}); },
                    mouseout: ()=>{ infoTooltipGlobalDiv.style.display='none'; if (geoLayer) geoLayer.resetStyle(layer); },
                    click: (e)=>{ const p=e.target.feature.properties, n=getCleanedShapeNameFromProps(p); if(n==='indonesia')switchToView('indonesia'); }
                });
            } else if (currentMapView === 'indonesia') {
                layer.on({
                    mouseover: (e)=>{ const p=e.target.feature.properties, cN=getCleanedShapeNameFromProps(p), sRK=cityToSuperRegionMap[cN]||p.calculatedSuperRegion?.name, dRN=sRK?sRK.replace(/([A-Z])([0-9])/g,'$1 $2'):'Tidak terpetakan', dNKK=cN.split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' '); let tT=`<strong>${dNKK}</strong><br>Super Region: ${dRN}`; if(sRK && superRegionSales.hasOwnProperty(sRK)) tT+=`<br>Sales Region Ini: ${(superRegionSales[sRK]||0).toLocaleString()} Ton`; else if(sRK) tT+=`<br>Sales Region Ini: 0 Ton`; if(p.calculatedSuperRegion && !cityToSuperRegionMap[cN]) tT+=`<br><small>(Estimasi via kedekatan)</small>`; salesTooltipIndonesiaDiv.innerHTML=tT; salesTooltipIndonesiaDiv.style.display='block'; layer.setStyle({weight:2,color:'#555'}); },
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

        function updateSuperRegionStatsTable() {
            const tableBody = document.querySelector('#super-region-stats-table tbody');
            if (!tableBody) return;
            tableBody.innerHTML = '';

            if (Object.keys(superRegionSales).length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No super region data.</td></tr>';
                const tfoot = document.querySelector('#super-region-stats-table tfoot tr');
                if (tfoot) Array.from(tfoot.cells).forEach(cell => cell.textContent = '');
                return;
            }

            let totalDispatch = 0, totalBudget = 0;
            const sortedRegionKeys = Object.keys(superRegionSales).sort();

            for (const regionKey of sortedRegionKeys) {
                const dispatch = superRegionSales[regionKey]||0;
                const budget = (dispatch>0 ? Math.max(1000, dispatch * (0.8 + Math.random()*0.4)) : (5000 + Math.random()*2000));
                const achievement = budget > 0 ? (dispatch / budget * 100) : 0;
                totalDispatch += dispatch; totalBudget += budget;

                const row = tableBody.insertRow();
                row.insertCell().textContent = regionKey.replace(/([A-Z])([0-9])/g, '$1 $2'); row.cells[0].classList.add('col-region');
                row.insertCell().textContent = budget.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[1].classList.add('number-cell', 'col-budget');
                row.insertCell().textContent = dispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[2].classList.add('number-cell', 'col-dispatch');
                row.insertCell().textContent = achievement.toFixed(1) + '%'; row.cells[3].classList.add('number-cell', 'col-achieve');
            }

            const tfootRow = document.querySelector('#super-region-stats-table tfoot tr');
            if (tfootRow) {
                const totalAch = totalBudget > 0 ? (totalDispatch / totalBudget * 100) : 0;
                tfootRow.cells[0].textContent = "TOTAL INDONESIA"; tfootRow.cells[0].style.fontWeight = "bold";
                tfootRow.cells[1].textContent = totalBudget.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[1].style.fontWeight = "bold"; tfootRow.cells[1].classList.add('number-cell');
                tfootRow.cells[2].textContent = totalDispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[2].style.fontWeight = "bold"; tfootRow.cells[2].classList.add('number-cell');
                tfootRow.cells[3].textContent = totalAch.toFixed(1) + '%'; tfootRow.cells[3].style.fontWeight = "bold"; tfootRow.cells[3].classList.add('number-cell');
            }
        }

        function updateInternationalStatsTable() {
            const tableBody = document.querySelector('#international-stats-table tbody');
            if(!tableBody) return; tableBody.innerHTML = '';

            const tfootRow = document.querySelector('#international-stats-table tfoot tr');
            if(tfootRow) Array.from(tfootRow.cells).forEach(cell => cell.textContent = '');


            if (currentMapView !== 'world' || Object.keys(salesDataGlobal).length === 0) {
                 tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No export data.</td></tr>';
                return;
            }

            let dataForTable = [];
            const exportCountries = Object.entries(salesDataGlobal)
                .filter(([country, sales]) => country.toLowerCase() !== 'indonesia' && (Number(sales) || 0) > 0)
                .sort(([,a],[,b]) => (Number(b) || 0) - (Number(a) || 0));

            const maxOtherCountriesInTable = 5;
            let otherExportSalesSum = 0, otherExportBudgetSum = 0;

            exportCountries.forEach(([country, sales], index) => {
                const actualSales = Number(sales)||0, budget = actualSales*(0.8+Math.random()*0.3), achieve = budget>0?(actualSales/budget*100):0;
                if (index < maxOtherCountriesInTable) dataForTable.push({ country, sales: actualSales, budget, achieve });
                else { otherExportSalesSum += actualSales; otherExportBudgetSum += budget; }
            });

            if (otherExportSalesSum > 0) {
                const achieveOther = otherExportBudgetSum > 0 ? (otherExportSalesSum / otherExportBudgetSum * 100) : 0;
                dataForTable.push({ country: "Other Export", sales: otherExportSalesSum, budget: otherExportBudgetSum, achieve: achieveOther });
            }

            if (dataForTable.length === 0) {
                 tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No significant export sales.</td></tr>'; return;
            }

            let salesSumForTableFooter = 0, budgetSumForTableFooter = 0;
            dataForTable.forEach(item => {
                const row = tableBody.insertRow();
                row.insertCell().textContent = item.country.charAt(0).toUpperCase()+item.country.slice(1); row.cells[0].classList.add('col-country');
                row.insertCell().textContent = item.sales.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[1].classList.add('number-cell','col-sales');
                row.insertCell().textContent = item.budget.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[2].classList.add('number-cell','col-budget-est');
                row.insertCell().textContent = item.achieve.toFixed(1) + '%'; row.cells[3].classList.add('number-cell','col-achieve-est');
                salesSumForTableFooter += item.sales; budgetSumForTableFooter += item.budget;
            });

            if (tfootRow && salesSumForTableFooter > 0) {
                const totalAchExport = budgetSumForTableFooter > 0 ? (salesSumForTableFooter / budgetSumForTableFooter * 100) : 0;
                tfootRow.cells[0].textContent = "TOTAL EXPORT"; tfootRow.cells[0].style.fontWeight = "bold";
                tfootRow.cells[1].textContent = salesSumForTableFooter.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[1].style.fontWeight="bold"; tfootRow.cells[1].classList.add('number-cell');
                tfootRow.cells[2].textContent = budgetSumForTableFooter.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[2].style.fontWeight="bold"; tfootRow.cells[2].classList.add('number-cell');
                tfootRow.cells[3].textContent = totalAchExport.toFixed(1) + '%'; tfootRow.cells[3].style.fontWeight="bold"; tfootRow.cells[3].classList.add('number-cell');
            }
        }

        function chartTooltipCallback(context) {
            let label=context.label||''; if(label)label+=': '; if(context.parsed!==null)label+=context.parsed.toLocaleString()+' Ton';
            const total=context.dataset.data.reduce((s,v)=>s+v,0); if(total>0&&context.parsed!==null){const p=(context.parsed/total*100).toFixed(1)+'%';label+=` (${p})`;} return label;
        }

        function updateSalesChart() {
            const sCC=document.getElementById('salesChart'); if(!sCC)return; if(salesPieChart)salesPieChart.destroy();
            const ctx=sCC.getContext('2d'); let chartConfig;
            if(currentMapView==='world'){
                const iS=Number(salesDataGlobal['indonesia'])||0,tGS=Object.values(salesDataGlobal).reduce((s,sl)=>s+(Number(sl)||0),0),oES=tGS-iS;
                let L=[],D=[],B=[]; if(iS>0){L.push('Indonesia');D.push(iS);B.push('#FF6384');} if(oES>0){L.push('Global Export');D.push(oES);B.push('#36A2EB');}
                if(L.length===0){L.push('No Sales Data');D.push(1);B.push('#CCCCCC');}
                chartConfig={type:'pie',data:{labels:L,datasets:[{label:'Global Sales',data:D,backgroundColor:B,hoverOffset:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:10},padding:3}},title:{display:true,text:'Sales: Indonesia vs Global Export',font:{size:13},padding:{bottom:8}},tooltip:{callbacks:{label:chartTooltipCallback}}}}};
            } else if(currentMapView==='indonesia'){
                const sSRFC=Object.entries(superRegionSales).filter(([,s])=>(Number(s)||0)>0).sort(([,a],[,b])=>(Number(b)||0)-(Number(a)||0));
                let l_sr=sSRFC.map(([r])=>r.replace(/([A-Z])([0-9])/g,'$1 $2')),d_sr=sSRFC.map(([,s])=>s),c_sr=sSRFC.map(([r])=>regionColors[r]||'#808080');
                if(l_sr.length===0){l_sr.push('No Super Region Sales');d_sr.push(1);c_sr.push('#CCCCCC');}
                chartConfig={type:'pie',data:{labels:l_sr,datasets:[{label:'Super Region Sales',data:d_sr,backgroundColor:c_sr,borderColor:'#fff',borderWidth:1}]},options:{responsive:true,maintainAspectRatio:false,plugins:{title:{display:true,text:'Sales per Super-Region (ID)',font:{size:13},padding:{bottom:8}},legend:{position:'bottom',labels:{font:{size:9},boxWidth:10,padding:5}},tooltip:{callbacks:{label:chartTooltipCallback}}}}};
            }
            if(chartConfig)salesPieChart=new Chart(ctx,chartConfig);
        }

        function updateLegend() {
            if (!legendItemsScrollContainer) return;
            legendItemsScrollContainer.innerHTML = '';

            if (currentMapView === 'indonesia') {
                let legendHTML = '';
                const regionsWithSales = Object.entries(superRegionSales)
                    .filter(([, sales]) => (Number(sales) || 0) > 0)
                    .map(([regionName]) => regionName)
                    .sort();

                regionsWithSales.forEach(superRegKey => {
                    if (regionColors[superRegKey]) {
                        let salesVal = superRegionSales[superRegKey] || 0;
                        let salesInfo = (salesVal > 0) ? ` (${salesVal.toLocaleString()})` : "";
                        let displayName = superRegKey.replace(/([A-Z])([0-9])/g, '$1 $2');
                        legendHTML += `<div><i style="background:${regionColors[superRegKey]}"></i> ${displayName}${salesInfo}</div>`;
                    }
                });
                 const exampleMappedNoSalesColor = (tinycolor && regionColors.REGION1A) ? tinycolor(regionColors.REGION1A).lighten(10).setAlpha(0.65).toRgbString() : '#e0e0e0';
                 legendHTML += `<div><i style="background:${exampleMappedNoSalesColor}"></i> Area S.Region (No Sales)</div>`;
                const otherExampleColor = (tinycolor && regionColors.REGION1A) ? tinycolor(regionColors.REGION1A).lighten(15).setAlpha(0.60).toRgbString() : '#e0e0e0';
                legendHTML += `<div><i style="background:${otherExampleColor}"></i> Estimasi Dekat (No Sales)</div>`;
                legendHTML += `<div><i style="background:${regionColors.OTHER_BASE}"></i> Lainnya/Tanpa Data</div>`;

                legendItemsScrollContainer.innerHTML = legendHTML;
            }
        }

        async function handleFilterChange() {
            const startDate = document.getElementById('start-date-select').value;
            const endDate = document.getElementById('end-date-select').value;

            if (currentMapView === 'indonesia' && map) {
                previousIndonesiaZoom = map.getZoom();
            } else {
                previousIndonesiaZoom = null;
            }

            if (!startDate || !endDate) { alert("Please select dates."); hideLoading(); return; }
            if (new Date(startDate) > new Date(endDate)) { alert("Start date after end date."); hideLoading(); return; }

            showLoading('Mengambil data penjualan...');
            try {
                const response = await fetch(`{{ route('api.sales.data') }}?startDate=${startDate}&endDate=${endDate}`, {
                    method: 'GET', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    const eD = await response.json().catch(()=>({message:`HTTP error! status: ${response.status}`}));
                    throw new Error(eD.message || `HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                salesDataGlobal = {};
                if (data.worldSales) {
                    for (const key in data.worldSales) {
                        let nK = key.toLowerCase().trim(); if (nK === "united states of america") nK = "united states";
                        salesDataGlobal[nK] = data.worldSales[key];
                    }
                }
                superRegionSales = data.indonesiaSuperRegionSales || {};
                const mapUrl = currentMapView === 'world' ? WORLD_TOPOJSON_URL : INDONESIA_TOPOJSON_URL;
                const cacheKey = currentMapView === 'world' ? WORLD_CACHE_KEY : INDONESIA_CACHE_KEY;

                await loadAndDisplayMapData(mapUrl, currentMapView, cacheKey);
                updateDashboardPanels();
            } catch (error) {
                console.error('Gagal memproses data:', error); alert(`Gagal memuat data: ${error.message}`);
                salesDataGlobal = {}; superRegionSales = {}; updateDashboardPanels(); hideLoading();
                previousIndonesiaZoom = null;
            }
        }
    </script>

</x-app-layout>