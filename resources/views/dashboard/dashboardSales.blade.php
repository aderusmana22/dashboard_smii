{{-- resources/views/dashboard/map-sales.blade.php --}}
<x-app-layout>

    @section('title')
    Dashboard Sales (Data Dinamis)
    @endsection

    {{-- Tambahkan CSRF token jika diperlukan untuk AJAX request (meskipun GET tidak selalu butuh) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* ... CSS Styles Anda ... */
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; }

        #map-ui-container {
            position: relative;
            width: 100%;
            height: calc(100vh - 57px); /* Sesuaikan 57px jika tinggi header default x-app-layout Anda berbeda */
            overflow: hidden;
            background-color: #f0f0f0;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        .info-tooltip-global {
            padding: 6px 8px; font: 14px/16px Arial, Helvetica, sans-serif;
            background: rgba(255, 255, 255, 0.85); box-shadow: 0 0 15px rgba(0,0,0,0.2);
            border-radius: 5px; white-space: nowrap; pointer-events: none;
            position: absolute; z-index: 10002; display: none;
        }
        #sales-tooltip-indonesia {
            position: absolute; background-color: rgba(0,0,0,0.8); color: white;
            padding: 8px 12px; border-radius: 4px; font-size: 13px;
            pointer-events: none; z-index: 10002; white-space: normal;
            max-width: 250px; display: none; line-height: 1.4;
        }
        #sales-tooltip-indonesia strong { display: block; margin-bottom: 3px; font-size: 14px; }
        #sales-tooltip-indonesia hr { border: 0; border-top: 1px solid #555; margin: 5px 0;}

        .loading {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%); background: rgba(255, 255, 255, 0.9);
            padding: 20px; border-radius: 10px; z-index: 10005;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        .geoboundaries-watermark {
            position: absolute; bottom: 10px; right: 10px;
            background: rgba(255, 255, 255, 0.85); padding: 6px 10px;
            font-size: 13px; border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); z-index: 705;
        }
        .geoboundaries-watermark a { color: #0077cc; text-decoration: none; font-weight: bold; }
        .geoboundaries-watermark a:hover { text-decoration: underline; }

        #chart-container {
            position: absolute; bottom: 10px; left: 10px; width: 280px; height: 280px;
            background: rgba(255, 255, 255, 0.92); padding: 15px; border-radius: 10px;
            z-index: 705;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        #stats-card {
            position: absolute; top: 70px; left: 10px;
            background: rgba(255, 255, 255, 0.92);
            padding: 15px; border-radius: 8px; z-index: 710;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); font-size: 14px; width: 260px;
        }
        #stats-card h3 { margin-top: 0; margin-bottom: 10px; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        #stats-card p { margin: 8px 0; overflow: hidden; }
        #stats-card .value { font-weight: bold; float: right; }
        #stats-card .label { color: #555; }

        #filter-menu {
            position: absolute; top: 15px; right: 15px;
            background: rgba(255, 255, 255, 0.92); padding: 10px 15px;
            border-radius: 8px; z-index: 710;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            display: flex; gap: 10px; align-items: center;
        }
        #filter-menu label { font-size: 14px; margin-right: 5px; }
        #filter-menu select { padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; }

        #back-to-world-btn-dynamic {
            position: absolute; top: 15px; left: 15px;
            background-color: rgba(0, 123, 255, 0.9); color: white;
            padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer;
            font-size: 14px; font-weight: bold; text-decoration: none;
            box-shadow: 0 1px 5px rgba(0,0,0,0.2); z-index: 720;
            display: none;
        }
        #back-to-world-btn-dynamic:hover { background-color: #0056b3; }

        #indonesia-legend-floating {
            position: absolute; bottom: 45px; right: 10px;
            background: rgba(255, 255, 255, 0.9); padding: 8px 10px;
            border-radius: 5px; box-shadow: 0 1px 7px rgba(0,0,0,0.15);
            z-index: 705;
            max-width: 200px; font-size: 11px;
            display: none;
        }
        #indonesia-legend-floating h4 { margin-top: 0; margin-bottom: 6px; font-size: 12px; }
        #indonesia-legend-floating i { width: 16px; height: 16px; float: left; margin-right: 6px; border: 1px solid #ccc; opacity: 0.9; }
        #indonesia-legend-floating div { line-height: 17px; clear: both; margin-bottom: 1px;}
    </style>

    <div id="map-ui-container">
        <div id="map"></div>
        <div id="loading" class="loading">Memuat Peta...</div>

        <div class="geoboundaries-watermark">
            This map uses boundaries from <a href="https://www.geoboundaries.org" target="_blank"
                rel="noopener noreferrer">geoBoundaries</a>.
        </div>

        <div id="stats-card">
            <h3 id="stats-title-main">Sales Summary</h3>
            <p><span class="label">Total Export (Ton):</span> <span class="value" id="total-export-ton">0</span></p>
            <p><span class="label">Global Achieve %:</span> <span class="value" id="global-achieve-percent">0%</span></p>
            <hr id="stats-hr-main" style="border: 0; border-top: 1px dashed #ccc; margin: 8px 0;">
            <p><span class="label">Total Indo (Ton):</span> <span class="value" id="total-indo-ton">0</span></p>
            <p><span class="label">Indo Achieve %:</span> <span class="value" id="indo-achieve-percent">0%</span></p>
        </div>

        <div id="filter-menu">
            <label for="month-select">Month:</label>
            <select id="month-select"></select>
            <label for="year-select">Year:</label>
            <select id="year-select"></select>
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
        let currentMapView = 'world';
        let map, geoLayer;

        const WORLD_TOPOJSON_URL = '{{ asset('maps/dunia.topojson') }}';
        const INDONESIA_TOPOJSON_URL = '{{ asset('maps/indo.topojson') }}';
        const WORLD_CACHE_KEY = 'world-custom-topojson-v2-dynamic';
        const INDONESIA_CACHE_KEY = 'indonesia-adm2-topojson-v14-dynamic';
        const MAX_CACHE_SIZE_MB = 5;
        const CALCULATION_BATCH_SIZE = 50;
        const INDONESIA_DEFAULT_ZOOM_LEVEL = 6; // <--- LEVEL ZOOM INDONESIA YANG DIINGINKAN
        const INDONESIA_MIN_ZOOM = 6;
        const INDONESIA_MAX_ZOOM = 18;


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
            "KEYACCOUNT": "#d9d9d9", "COMMERCIAL": "#f0f0f0",
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

        const infoTooltipGlobalDiv = document.getElementById('info-box');
        const salesTooltipIndonesiaDiv = document.getElementById('sales-tooltip-indonesia');

        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            initUIElements();
            handleFilterChange();
        });

        function initMap() {
            map = L.map('map', {
                zoomControl: true,
                worldCopyJump: true,
                // Set min/max zoom global jika perlu, tapi akan dioverride per view
            }).setView([20, 0], 3);

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
            const monthSelect = document.getElementById('month-select');
            const yearSelect = document.getElementById('year-select');
            const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth();

            months.forEach((month, index) => {
                const opt = new Option(month, index + 1);
                if (index === currentMonth) opt.selected = true;
                monthSelect.add(opt);
            });
            for (let i = currentYear - 5; i <= currentYear + 2; i++) {
                const opt = new Option(i, i);
                if (i === currentYear) opt.selected = true;
                yearSelect.add(opt);
            }
            monthSelect.addEventListener('change', handleFilterChange);
            yearSelect.addEventListener('change', handleFilterChange);

            const salesChartCanvas = document.getElementById('salesChart');
            if (salesChartCanvas) {
                 salesPieChart = new Chart(salesChartCanvas, {
                    type: 'pie', data: { labels: [], datasets: [] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }

            backToWorldBtnDynamic = document.createElement('a');
            backToWorldBtnDynamic.id = 'back-to-world-btn-dynamic';
            backToWorldBtnDynamic.href = '#';
            backToWorldBtnDynamic.innerHTML = '← Kembali ke Peta Dunia';
            mapUiContainer.appendChild(backToWorldBtnDynamic);
            backToWorldBtnDynamic.addEventListener('click', (e) => {
                e.preventDefault();
                switchToView('world');
            });

            indonesiaLegendFloating = document.createElement('div');
            indonesiaLegendFloating.id = 'indonesia-legend-floating';
            mapUiContainer.appendChild(indonesiaLegendFloating);
        }

        function switchToView(viewType) {
            const oldView = currentMapView;
            currentMapView = viewType;
            console.log("Switching to view:", currentMapView); // DEBUG
            showLoading(viewType === 'world' ? 'Memuat Peta Dunia...' : 'Memuat Peta Indonesia...');

            backToWorldBtnDynamic.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            indonesiaLegendFloating.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            infoTooltipGlobalDiv.style.display = 'none';
            salesTooltipIndonesiaDiv.style.display = 'none';

            if (oldView !== viewType ||
                (viewType === 'world' && Object.keys(salesDataGlobal).length === 0) ||
                (viewType === 'indonesia' && Object.keys(superRegionSales).length === 0) ||
                (geoLayer && geoLayer.options && geoLayer.options.viewType !== viewType)
            ) {
                console.log("switchToView: Conditions met, calling handleFilterChange()"); // DEBUG
                handleFilterChange();
            } else {
                console.log("switchToView: Conditions NOT met, adjusting existing map for view:", viewType); // DEBUG
                if (viewType === 'world') {
                    if (geoLayer && geoLayer.options && geoLayer.options.viewType === 'world') {
                        map.options.minZoom = 3;
                        map.options.maxZoom = 3;
                        map.setView([20, 0], 3);
                        map.setMaxBounds(null);
                        geoLayer.setStyle(styleFeatureMap);
                    } else {
                        console.warn("switchToView: World view requested, but layer is missing or incorrect. Forcing reload via handleFilterChange.");
                        handleFilterChange(); // Seharusnya jarang terjadi di sini
                        return;
                    }
                } else if (viewType === 'indonesia') {
                     if (geoLayer && geoLayer.options && geoLayer.options.viewType === 'indonesia') {
                        const defaultIndonesiaCenter = [-2.5, 118];
                        console.log("SWITCHTOVIEW (EXISTING): Setting Indonesia view. Center:", defaultIndonesiaCenter, "Zoom Level:", INDONESIA_DEFAULT_ZOOM_LEVEL); // DEBUG
                        map.options.minZoom = INDONESIA_MIN_ZOOM;
                        map.options.maxZoom = INDONESIA_MAX_ZOOM;
                        map.setView(defaultIndonesiaCenter, INDONESIA_DEFAULT_ZOOM_LEVEL);
                        // const bounds = geoLayer.getBounds(); // SEMENTARA KOMENTARI UNTUK TES
                        // if (bounds.isValid()) {
                        //     map.setMaxBounds(bounds.pad(0.5));
                        // } else {
                        //     map.setMaxBounds(null);
                        // }
                        geoLayer.setStyle(styleFeatureMap);
                    } else {
                         console.warn("switchToView: Indonesia view requested, but layer is missing or incorrect. Forcing reload via handleFilterChange.");
                         handleFilterChange(); // Seharusnya jarang terjadi di sini
                         return;
                    }
                }
                updateDashboardPanels();
                hideLoading();
            }
        }

        async function loadAndDisplayMapData(url, viewType, cacheKey = null) {
            // ... (fungsi loadAndDisplayMapData tetap sama) ...
            let topoData;
            if (cacheKey) {
                try {
                    const cached = localStorage.getItem(cacheKey);
                    if (cached) {
                        console.log(`Using ${viewType} map data from localStorage.`);
                        topoData = JSON.parse(cached);
                    }
                } catch (e) {
                    console.error('Error reading or parsing cached data:', e);
                    localStorage.removeItem(cacheKey);
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
                                console.log(`${viewType} map data cached.`);
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
                    if (viewType === 'indonesia' && currentMapView === 'indonesia') switchToView('world');
                    return;
                }
            }
            await processTopoJSONAndRender(topoData, viewType);
        }

        async function processTopoJSONAndRender(topojsonInputData, viewType) {
            console.log("Processing TopoJSON for view:", viewType); // DEBUG
            showLoading(`Memproses data peta ${viewType}...`);
            // ... (validasi topojsonInputData) ...
            if (!topojsonInputData || typeof topojsonInputData.objects !== 'object' || Object.keys(topojsonInputData.objects).length === 0) {
                console.error("Invalid TopoJSON data structure", topojsonInputData);
                hideLoading(); return;
            }

            let objectName = Object.keys(topojsonInputData.objects).find(key =>
                key.toLowerCase().includes('countries') || key.toLowerCase().includes('states') ||
                key.toLowerCase().includes('provinces') || key.toLowerCase().includes('adm2') ||
                key.toLowerCase().includes('adm') || key.toLowerCase().includes('boundaries')
            ) || Object.keys(topojsonInputData.objects)[0];

            if (!topojsonInputData.objects[objectName]) {
                 console.error(`Invalid TopoJSON: object '${objectName}' not found. Available: ${Object.keys(topojsonInputData.objects).join(', ')}`);
                 hideLoading();
                 alert(`Gagal memproses peta: objek '${objectName}' tidak ditemukan.`);
                 return;
            }

            const geojson = window.topojson.feature(topojsonInputData, topojsonInputData.objects[objectName]);

            if (!geojson || !geojson.features) {
                console.error("Failed to convert TopoJSON or GeoJSON has no features.", geojson);
                hideLoading();
                return;
            }


            if (viewType === 'world') {
                calculateWorldMinMaxSales();
            } else if (viewType === 'indonesia') {
                seedRegionData = [];
                featureCentroidCache.clear();
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
                await calculateNearestSuperRegionsAsync(geojson.features); // Ini mungkin butuh waktu
            }

            if (geoLayer) map.removeLayer(geoLayer);
            geoLayer = L.geoJSON(geojson, {
                style: styleFeatureMap,
                onEachFeature: onEachFeatureMap,
                viewType: viewType
            }).addTo(map);

            if (viewType === 'world') {
                map.options.minZoom = 3;
                map.options.maxZoom = 3;
                map.setView([20, 0], 3);
                map.setMaxBounds(null); // Hapus batasan geser untuk dunia
            } else if (viewType === 'indonesia') {
                const defaultIndonesiaCenter = [-2.5, 118];
                console.log("PROCESS: Setting Indonesia view. Center:", defaultIndonesiaCenter, "Zoom Level:", INDONESIA_DEFAULT_ZOOM_LEVEL); // DEBUG
                map.options.minZoom = INDONESIA_MIN_ZOOM;
                map.options.maxZoom = INDONESIA_MAX_ZOOM;
                map.setView(defaultIndonesiaCenter, INDONESIA_DEFAULT_ZOOM_LEVEL);
                // const bounds = geoLayer.getBounds(); // SEMENTARA KOMENTARI UNTUK TES
                // if (bounds.isValid()) {
                //    map.setMaxBounds(bounds.pad(0.5)); // Opsional: batasi panning
                // } else {
                //    map.setMaxBounds(null);
                // }
            }
            hideLoading();
        }

        // ... (fungsi styleFeatureMap, onEachFeatureMap, dll tetap sama) ...
        function styleFeatureMap(feature) {
            if (currentMapView === 'world') {
                const name = feature.properties.shapeName?.trim();
                const sales = salesDataGlobal[name] || 0;
                return {
                    fillColor: getWorldFeatureColor(sales),
                    weight: 0.5, opacity: 1, color: '#bbb', fillOpacity: 0.7
                };
            } else if (currentMapView === 'indonesia') {
                const cleanedName = getCleanedShapeNameFromProps(feature.properties);
                let fillColor = regionColors.OTHER_BASE;
                let fillOpacity = 0.70;

                const superRegFromADM = cityToSuperRegionMap[cleanedName];
                if (superRegFromADM && superRegionSales[superRegFromADM]) {
                     fillColor = regionColors[superRegFromADM];
                     fillOpacity = 0.85;
                } else if (feature.properties.calculatedSuperRegion) {
                    const nearestSuperRegName = feature.properties.calculatedSuperRegion.name;
                    const nearestSuperRegColor = regionColors[nearestSuperRegName];
                    if (nearestSuperRegColor && tinycolor) {
                        if (superRegionSales[nearestSuperRegName]) {
                             fillColor = tinycolor(nearestSuperRegColor).setAlpha(0.75).toRgbString();
                        } else {
                             fillColor = tinycolor(nearestSuperRegColor).lighten(15).setAlpha(0.60).toRgbString();
                        }
                    }
                } else if (superRegFromADM && regionColors[superRegFromADM]) {
                    fillColor = tinycolor(regionColors[superRegFromADM]).lighten(10).setAlpha(0.65).toRgbString();
                }
                return { weight: 0.5, opacity: 1, color: 'white', fillOpacity: fillOpacity, fillColor: fillColor };
            }
            return { fillColor: '#ccc', weight: 1, opacity: 1, color: 'white', fillOpacity: 0.7 };
        }

        function onEachFeatureMap(feature, layer) {
            if (currentMapView === 'world') {
                const name = feature.properties.shapeName?.trim();
                layer.on({
                    mouseover: function(e) {
                        const sales = salesDataGlobal[name] || 0;
                        infoTooltipGlobalDiv.style.display = 'block';
                        infoTooltipGlobalDiv.innerHTML = `<strong>${name || 'Unknown Country'}</strong><br>Sales: ${sales.toLocaleString()} Ton`;
                        layer.setStyle({ weight: 1.5, color: '#333', fillOpacity: 0.9 }).bringToFront();
                    },
                    mouseout: function(e) {
                        infoTooltipGlobalDiv.style.display = 'none';
                        if (geoLayer) geoLayer.resetStyle(layer);
                    },
                    click: function(e) {
                        if (name === "Indonesia") {
                            switchToView('indonesia');
                        } else {
                            if (e.target.getBounds && e.target.getBounds().isValid()) {
                                map.fitBounds(e.target.getBounds());
                            }
                        }
                    }
                });
            } else if (currentMapView === 'indonesia') {
                const props = feature.properties;
                const rawShapeName = props.NAME_2 || props.name_2 || props.NAME_1 || props.name_1 || props.name || props.Name || props.shapeName || "Unknown Area";
                const cleanedName = getCleanedShapeNameFromProps(props);
                const superRegKey = cityToSuperRegionMap[cleanedName];

                let makeInteractive = false;
                if (superRegKey && superRegionSales[superRegKey]) {
                    makeInteractive = true;
                } else if (props.calculatedSuperRegion && superRegionSales[props.calculatedSuperRegion.name]) {
                    makeInteractive = true;
                }

                if (makeInteractive) {
                    layer.on({
                        mouseover: (e_mouse) => {
                            layer.setStyle({ weight: 2.5, color: '#333', fillOpacity: 1 }).bringToFront();
                            let superRegForSalesDisplay = superRegKey;
                            let actualSuperRegForSalesData = superRegKey;

                            if (!superRegForSalesDisplay && props.calculatedSuperRegion) {
                                superRegForSalesDisplay = `~${props.calculatedSuperRegion.name} (terdekat)`;
                                actualSuperRegForSalesData = props.calculatedSuperRegion.name;
                            }

                            let tooltipContent = `<strong>${rawShapeName}</strong>`;
                            if (superRegForSalesDisplay) {
                                tooltipContent += `<br>Region: ${superRegForSalesDisplay.replace('~','')}`;
                            }
                            if (actualSuperRegForSalesData && superRegionSales[actualSuperRegForSalesData] !== undefined) {
                                tooltipContent += `<hr>Sales Region ${actualSuperRegForSalesData}: <b>${(superRegionSales[actualSuperRegForSalesData] || 0).toLocaleString()} Ton</b>`;
                            } else {
                                tooltipContent += `<br>Sales Region: 0 Ton`;
                            }
                            salesTooltipIndonesiaDiv.innerHTML = tooltipContent;
                            salesTooltipIndonesiaDiv.style.display = 'block';
                        },
                        mouseout: (e_mouse) => {
                            if (geoLayer) geoLayer.resetStyle(layer);
                            salesTooltipIndonesiaDiv.style.display = 'none';
                        },
                        click: (e_click) => {
                            if (e_click.target.getBounds && e_click.target.getBounds().isValid()){
                                map.fitBounds(e_click.target.getBounds(), {
                                    maxZoom: Math.min(map.getZoom() + 2, 12), 
                                    padding: [30,30]
                                });
                            }
                            let popupContent = `<strong>${rawShapeName}</strong>`;
                             let actualSuperRegForSalesData = superRegKey;
                             if (!actualSuperRegForSalesData && props.calculatedSuperRegion) {
                                actualSuperRegForSalesData = props.calculatedSuperRegion.name;
                                popupContent += `<br>Area dekat dengan Region: ${actualSuperRegForSalesData}`;
                            } else if (actualSuperRegForSalesData) {
                                popupContent += `<br>Region: ${actualSuperRegForSalesData}`;
                            }

                            if (actualSuperRegForSalesData && superRegionSales[actualSuperRegForSalesData] !== undefined) {
                                popupContent += `<hr>Sales Region ${actualSuperRegForSalesData}: <b>${(superRegionSales[actualSuperRegForSalesData] || 0).toLocaleString()} Ton</b>`;
                            } else {
                                popupContent += `<br>Sales Region: 0 Ton`;
                            }
                            L.popup().setLatLng(e_click.latlng).setContent(popupContent).openOn(map);
                            L.DomEvent.stopPropagation(e_click); 
                        }
                    });
                } else { layer.options.interactive = false; }
            }
        }

        function calculateWorldMinMaxSales() {
            const values = Object.values(salesDataGlobal).filter(v => typeof v === 'number' && v > 0);
            worldMaxSales = values.length > 0 ? Math.max(...values) : 1;
            worldMinSales = values.length > 0 ? Math.min(...values) : 0;
        }
        function getWorldFeatureColor(sales) {
            if (!sales || sales <= 0) return worldBaseColor;
            if (worldMaxSales === worldMinSales && worldMaxSales > 0) return `rgb(255, ${255 - 100}, 0)`;
            if (worldMaxSales <= worldMinSales) return worldBaseColor;
            const ratio = (sales - worldMinSales) / (worldMaxSales - worldMinSales);
            const r = 255;
            const g = Math.max(0, Math.floor(255 - ratio * 200));
            const b = 0;
            return `rgb(${r},${g},${b})`;
        }

        function updateDashboardPanels() {
            updateStatsCard();
            updateSalesChart();
            updateLegend();
        }

        function updateStatsCard() {
            const titleEl = document.getElementById('stats-title-main');
            const totalExportTonEl = document.getElementById('total-export-ton');
            const globalAchieveEl = document.getElementById('global-achieve-percent');
            const totalIndoTonEl = document.getElementById('total-indo-ton');
            const indoAchieveEl = document.getElementById('indo-achieve-percent');
            const hrEl = document.getElementById('stats-hr-main');

            if (!titleEl || !totalExportTonEl || !globalAchieveEl || !totalIndoTonEl || !indoAchieveEl || !hrEl) {
                console.warn("One or more stats card elements not found. Skipping update.");
                return;
            }

            if (currentMapView === 'world') {
                titleEl.textContent = "Global Sales Summary";
                const totalGlobalSales = Object.values(salesDataGlobal).reduce((sum, val) => sum + (Number(val) || 0), 0);
                const indoSalesGlobal = salesDataGlobal["Indonesia"] || 0;

                totalExportTonEl.textContent = totalGlobalSales.toLocaleString();
                globalAchieveEl.textContent = `${(Math.random() * 10 + 88).toFixed(1)}%`;
                totalIndoTonEl.textContent = indoSalesGlobal.toLocaleString();
                indoAchieveEl.textContent = `${(Math.random() * 10 + 89).toFixed(1)}%`;

                totalIndoTonEl.closest('p').style.display = '';
                indoAchieveEl.closest('p').style.display = '';
                hrEl.style.display = '';
                totalExportTonEl.previousElementSibling.textContent = "Total Export (Ton):";
                globalAchieveEl.previousElementSibling.textContent = "Global Achieve %:";

            } else if (currentMapView === 'indonesia') {
                titleEl.textContent = "Indonesia Sales Summary";
                const totalIndoSuperRegionSales = Object.values(superRegionSales).reduce((sum, val) => sum + (Number(val) || 0), 0);

                totalExportTonEl.textContent = totalIndoSuperRegionSales.toLocaleString();
                globalAchieveEl.textContent = `${(Math.random() * 5 + 90).toFixed(1)}%`;

                totalIndoTonEl.closest('p').style.display = 'none';
                indoAchieveEl.closest('p').style.display = 'none';
                hrEl.style.display = 'none';
                totalExportTonEl.previousElementSibling.textContent = "Total Sales Super Region (Ton):";
                globalAchieveEl.previousElementSibling.textContent = "Nasional Achieve %:";
            }
        }

        function updateSalesChart() {
            const salesChartCanvas = document.getElementById('salesChart');
            if (!salesChartCanvas) {
                console.warn("Sales chart canvas not found. Skipping update.");
                return;
            }
            if (salesPieChart) salesPieChart.destroy();

            const ctx = salesChartCanvas.getContext('2d');
            let chartConfig;

            if (currentMapView === 'world') {
                const indoSales = salesDataGlobal["Indonesia"] || 0;
                const restOfWorldSales = Object.entries(salesDataGlobal)
                    .filter(([countryShapeName]) => countryShapeName !== "Indonesia")
                    .reduce((sum, [, sales]) => sum + (Number(sales) || 0), 0);

                chartConfig = {
                    type: 'pie',
                    data: {
                        labels: ['Indonesia', 'Rest of World'],
                        datasets: [{
                            label: 'Global Sales', data: [indoSales, restOfWorldSales],
                            backgroundColor: ['#FF6384', '#36A2EB'], hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 }}},
                            title: { display: true, text: 'Sales: Indonesia vs Global', font: { size: 13 }, padding: {bottom:8} },
                            tooltip: { callbacks: { label: chartTooltipCallback } }
                        }
                    }
                };
            } else if (currentMapView === 'indonesia') {
                const sortedSuperRegForChart = Object.entries(superRegionSales)
                    .filter(([,sales]) => (Number(sales) || 0) > 0)
                    .sort(([,a],[,b]) => (Number(b) || 0) - (Number(a) || 0));

                chartConfig = {
                    type: 'pie',
                    data: {
                        labels: sortedSuperRegForChart.map(([reg]) => reg),
                        datasets: [{
                            label: 'Super Region Sales',
                            data: sortedSuperRegForChart.map(([,sales]) => sales),
                            backgroundColor: sortedSuperRegForChart.map(([reg]) => regionColors[reg] || '#808080'),
                            borderColor: '#fff', borderWidth: 1
                        }]
                    },
                     options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            title: { display: true, text: 'Sales per Super-Region (ID)', font: {size: 13}, padding: {bottom:8} },
                            legend: { position: 'bottom', labels: { font: {size: 9}, boxWidth:10, padding:5 } },
                            tooltip: { callbacks: { label: chartTooltipCallback } }
                        }
                    }
                };
            }
            if (chartConfig) salesPieChart = new Chart(ctx, chartConfig);
        }

        function chartTooltipCallback(context) {
            let label = context.label || '';
            if (label) label += ': ';
            if (context.parsed !== null && typeof context.parsed === 'number') {
                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + (Number(b) || 0), 0);
                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                label += `${context.parsed.toLocaleString()} Ton (${percentage}%)`;
            }
            return label;
        }

        function updateLegend() {
            if (!indonesiaLegendFloating) return;
            indonesiaLegendFloating.innerHTML = '';
            if (currentMapView === 'indonesia') {
                let legendHTML = '<h4>Region Pemasaran</h4>';
                const regionsWithSales = Object.entries(superRegionSales)
                    .filter(([, sales]) => (Number(sales) || 0) > 0)
                    .map(([regionName]) => regionName)
                    .sort();

                regionsWithSales.forEach(superReg => {
                    if (regionColors[superReg]) {
                        let salesVal = superRegionSales[superReg] || 0;
                        let salesInfo = (salesVal > 0) ? ` (${salesVal.toLocaleString()})` : "";
                        legendHTML += `<div><i style="background:${regionColors[superReg]}"></i> ${superReg}${salesInfo}</div>`;
                    }
                });

                const otherExampleColor = (tinycolor && regionColors.REGION1A) ? tinycolor(regionColors.REGION1A).lighten(10).setAlpha(0.75).toRgbString() : '#e0e0e0';
                legendHTML += `<div><i style="background:${otherExampleColor}"></i> Area Dekat (Sales)</div>`;
                legendHTML += `<div><i style="background:${regionColors.OTHER_BASE}"></i> Lainnya/Tanpa Sales</div>`;
                indonesiaLegendFloating.innerHTML = legendHTML;
                indonesiaLegendFloating.style.display = 'block';
            } else {
                indonesiaLegendFloating.style.display = 'none';
            }
        }

        function getCleanedShapeNameFromProps(featureProperties) {
            if (!featureProperties) return undefined;
            let rawName = (
                featureProperties.NAME_2 || featureProperties.name_2 ||
                featureProperties.NAME_1 || featureProperties.name_1 ||
                featureProperties.name || featureProperties.Name ||
                featureProperties.shapeName
            )?.trim();

            if (rawName) {
                const prefixes = ["Kota ", "Kabupaten ", "Kab. "];
                for (const prefix of prefixes) {
                    if (rawName.toLowerCase().startsWith(prefix.toLowerCase())) {
                        rawName = rawName.substring(prefix.length); break;
                    }
                }
                return rawName.toLowerCase();
            }
            return undefined;
        }
        function getFeatureCentroid(featureGeometry) {
            if (!featureGeometry) return null;
            if (featureCentroidCache.has(featureGeometry)) return featureCentroidCache.get(featureGeometry);
            try {
                const layer = L.geoJSON({type: "Feature", geometry: featureGeometry});
                const bounds = layer.getBounds();
                if (bounds.isValid()) {
                    const centroid = bounds.getCenter();
                    featureCentroidCache.set(featureGeometry, centroid);
                    return centroid;
                }
            } catch (e) {
                console.error("Error calculating centroid for geometry:", featureGeometry, e);
            }
            return null;
        }
        async function calculateNearestSuperRegionsAsync(geojsonFeatures) {
            showLoading(`Menghitung kedekatan wilayah (0%)...`);
            if (seedRegionData.length === 0) {
                console.log("No seed region data for nearest super region calculation.");
                return;
            }

            const otherFeatures = geojsonFeatures.filter(f => f.geometry && !cityToSuperRegionMap[getCleanedShapeNameFromProps(f.properties)]);
            const totalOtherFeatures = otherFeatures.length;
            if (totalOtherFeatures === 0) {
                console.log("No 'other' features to calculate nearest super regions for.");
                return;
            }
            let processedCount = 0;

            for (let i = 0; i < totalOtherFeatures; i++) {
                const feature = otherFeatures[i];
                if (!feature.properties) feature.properties = {};
                const featureCentroid = getFeatureCentroid(feature.geometry);
                if (!featureCentroid) continue;

                let minDistance = Infinity, nearestSuperReg = null;
                seedRegionData.forEach(seed => {
                    if (seed.centroid) {
                        const distance = featureCentroid.distanceTo(seed.centroid);
                        if (distance < minDistance) { minDistance = distance; nearestSuperReg = seed.superRegionName; }
                    }
                });
                if (nearestSuperReg) feature.properties.calculatedSuperRegion = { name: nearestSuperReg, distance: minDistance };
                processedCount++;
                if (i % CALCULATION_BATCH_SIZE === 0 || i === totalOtherFeatures - 1) {
                    showLoading(`Menghitung kedekatan wilayah (${Math.round((processedCount/totalOtherFeatures)*100)}%)...`);
                    await new Promise(resolve => setTimeout(resolve, 0)); 
                }
            }
            console.log("Nearest super regions calculation complete.");
        }
        function showLoading(message = "Memuat...") { if (loadingDiv) { loadingDiv.textContent = message; loadingDiv.style.display = 'block'; } }
        function hideLoading() { if (loadingDiv) loadingDiv.style.display = 'none'; }

        async function handleFilterChange() {
            const selectedMonth = document.getElementById('month-select').value;
            const selectedYear = document.getElementById('year-select').value;
            console.log(`Filter changed: Month ${selectedMonth}, Year ${selectedYear}. View: ${currentMapView}`);

            showLoading('Mengambil data penjualan...');

            try {
                const response = await fetch(`{{ route('api.sales.data') }}?year=${selectedYear}&month=${selectedMonth}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: `HTTP error! status: ${response.status}` }));
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                salesDataGlobal = data.worldSales || {};
                superRegionSales = data.indonesiaSuperRegionSales || {};

                const mapUrl = currentMapView === 'world' ? WORLD_TOPOJSON_URL : INDONESIA_TOPOJSON_URL;
                const cacheKey = currentMapView === 'world' ? WORLD_CACHE_KEY : INDONESIA_CACHE_KEY;

                await loadAndDisplayMapData(mapUrl, currentMapView, cacheKey);
                updateDashboardPanels();

            } catch (error) {
                console.error('Gagal mengambil atau memproses data penjualan:', error);
                alert(`Gagal memuat data penjualan: ${error.message}`);
                salesDataGlobal = {};
                superRegionSales = {};
                updateDashboardPanels();
            } finally {
                hideLoading();
            }
        }
    </script>
    @push('scripts')
    {{-- Additional scripts if needed --}}
    @endpush

</x-app-layout>