<x-app-layout>

    @section('title')
    Dashboard Sales (Data Dinamis)
    @endsection

    <meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* ... (CSS Anda yang lain tetap sama) ... */

    html::-webkit-scrollbar {
        display: none;
    }

    html {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    :root {
        /* Light Theme (Default) */
        --main-bg-color: #f0f0f0;
        --map-ui-bg: #f0f0f0;
        --map-bg: #aadaff;
        --text-color-primary: #333;
        --text-color-secondary: #555;
        --text-color-labels: #333;
        --panel-bg: rgba(255, 255, 255, 0.97);
        --panel-bg-solid: #fff;
        --panel-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        --border-color-light: #ccc;
        --border-color-medium: #bbb;
        --border-color-dark: #ddd;
        --input-bg: #fff;
        --input-border: #ccc;
        --button-bg: #e9e9e9;
        --button-border: #bbb;
        --button-hover-bg: #dcdcdc;
        --table-header-bg: #f2f2f2;
        --tooltip-global-bg: rgba(0, 0, 0, 0.8);
        --tooltip-global-text: white;
        --tooltip-indonesia-bg: rgba(255, 255, 255, 0.95);
        --tooltip-indonesia-text: #333;
        --tooltip-indonesia-border: #ccc;
        --watermark-bg: rgba(255, 255, 255, 0.7);
        --watermark-text: #555;
        --watermark-link: #337ab7;
        --chart-bg: rgba(255, 255, 255, 0.92);
        --link-color: #337ab7;
        --back-to-world-bg: #fff;
        --world-feature-base-color: #e0e0e0;
    }

    .dark-mode {
        --main-bg-color: #0d1a2e;
        --map-ui-bg: #12213c;
        --map-bg: #1a2b41;
        --text-color-primary: #e0e6eb;
        --text-color-secondary: #b0b8c0;
        --text-color-labels: #c0c8d0;
        --panel-bg: rgba(28, 44, 68, 0.97);
        --panel-bg-solid: #1c2c44;
        --panel-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        --border-color-light: #4a5b78;
        --border-color-medium: #5a6b88;
        --border-color-dark: #3a4b68;
        --input-bg: #1c2c44;
        --input-border: #4a5b78;
        --button-bg: #2c3c54;
        --button-border: #4a5b78;
        --button-hover-bg: #3c4c64;
        --table-header-bg: #2a3b58;
        --tooltip-global-bg: rgba(220, 220, 230, 0.85);
        --tooltip-global-text: #111;
        --tooltip-indonesia-bg: rgba(30, 45, 70, 0.95);
        --tooltip-indonesia-text: #e0e6eb;
        --tooltip-indonesia-border: #4a5b78;
        --watermark-bg: rgba(0, 0, 0, 0.5);
        --watermark-text: #aaa;
        --watermark-link: #8ab4f8;
        --chart-bg: rgba(28, 44, 68, 0.92);
        --link-color: #8ab4f8;
        --back-to-world-bg: #1c2c44;
        --world-feature-base-color: #2a3b58;
    }


    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--main-bg-color);
        color: var(--text-color-primary);
        font-size: 1.6vh;
    }

    #map-ui-container {
        position: relative;
        width: 100%;
        height: calc(100vh - 57px);
        overflow: hidden;
        background-color: var(--map-ui-bg);
        display: block;
    }

    .leaflet-control-zoom { display: none !important; }

    #map {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        height: 100%; width: 100%;
        background-color: var(--map-bg);
        z-index: 500;
    }

    .info-tooltip-global,
    #sales-tooltip-indonesia {
        position: absolute; padding: 1vh 1.2vw; border-radius: 4px;
        font-size: 1.5vh; z-index: 800; pointer-events: none;
        display: none; box-shadow: var(--panel-shadow);
    }
    .info-tooltip-global { background: var(--tooltip-global-bg); color: var(--tooltip-global-text); }
    #sales-tooltip-indonesia { background: var(--tooltip-indonesia-bg); color: var(--tooltip-indonesia-text); border: 1px solid var(--tooltip-indonesia-border); }

    .loading {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.7); color: white;
        padding: 2.5vh 3vw; border-radius: 8px;
        z-index: 10000; font-size: 2vh;
        text-align: center; display: none;
    }

    .geoboundaries-watermark {
        position: absolute; bottom: 0.5vh; right: 1vw;
        font-size: 1.1vh; color: var(--watermark-text);
        background-color: var(--watermark-bg);
        padding: 0.3vh 0.6vw; border-radius: 3px;
        z-index: 700;
    }
    .geoboundaries-watermark a { color: var(--watermark-link); }

    /* --- Filter Menu (Top Bar) --- */
    #filter-menu {
        position: absolute; top: 1.5vh;
        right: 1vw;
        width: auto;
        max-width: calc(120%);
        background: var(--panel-bg); padding: 0.8vh 1vw;
        border-radius: 6px; z-index: 720;
        box-shadow: var(--panel-shadow);
        display: flex;
        flex-wrap: wrap;
        gap: 0.6vh 0.6vw;
        align-items: center;
        overflow: hidden;
    }
    .date-filter-container {
        display: flex; gap: 0.5vw;
        align-items: center; flex-shrink: 0;
    }
    .date-filter-container > div { display: flex; flex-direction: row; align-items: center; }
    .date-filter-container label {
        font-size: 1.3vh; font-weight: 500;
        margin-right: 0.3vw; color: var(--text-color-labels);
        white-space: nowrap;
    }
    #filter-menu input[type="date"] {
        padding: 0.4vh 0.6vw; border-radius: 3px;
        border: 1px solid var(--input-border);
        background-color: var(--input-bg); color: var(--text-color-primary);
        font-size: 1.4vh; width: 10vw;
        min-width: 90px;
        max-width: 110px;
        height: 3vh; max-height: 23px;
        box-sizing: border-box; flex-shrink: 0;
    }

    .filter-group {
        display: flex; flex-direction: row;
        align-items: center; gap: 0.3vw;
        flex-shrink: 1;
        min-width: 0;
    }
    .filter-group > label {
        font-size: 1.4vh; font-weight: 600;
        white-space: nowrap; color: var(--text-color-labels);
        margin-right: 0.2vw;
    }
    .custom-dropdown-container {
        position: static;
        flex-shrink: 1;
        min-width: 0;
    }
    .custom-dropdown-trigger {
        background-color: var(--input-bg); border: 1px solid var(--input-border);
        color: var(--text-color-primary); border-radius: 3px;
        padding: 0.4vh 1.8vw 0.4vh 0.6vw; font-size: 1.4vh;
        min-width: 10vw;
        max-width: 16vw;
        text-align: left; cursor: pointer;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        position: relative; height: 3vh; max-height: 23px;
        box-sizing: border-box;
        display: flex;
        align-items: center;
    }
    .custom-dropdown-trigger::after {
        content: '▼'; font-size: 0.8em;
        position: absolute; right: 0.6vw; top: 50%;
        transform: translateY(-50%); color: var(--text-color-secondary);
    }
    .custom-dropdown-content {
        display: none;
        position: fixed;
        z-index: 9900;
        box-shadow: var(--panel-shadow);
    }
    .checkbox-list-container {
        overflow-y: auto;
        border: 1px solid var(--input-border);
        padding: 0.8vh; border-radius: 3px;
        background-color: var(--input-bg);
        min-width: 15vw;
        max-width: 25vw;
        max-height: 18vh;
    }
    .checkbox-list-container div { display: flex; align-items: center; margin-bottom: 0.3vh; }
    .checkbox-list-container input[type="checkbox"] { margin-right: 0.5vw; }
    .checkbox-list-container label {
        font-size: 1.3vh; font-weight: normal; cursor: pointer;
        user-select: none; color: var(--text-color-primary);
    }


    .filter-actions {
        margin-left: auto;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }

    #reset-all-filters {
        padding: 0.5vh 1vw;
        font-size: 1.4vh;
        background-color: var(--button-bg); border: 1px solid var(--button-border);
        color: var(--text-color-primary); border-radius: 3px;
        cursor: pointer;
        height: 3vh; max-height: 23px;
        box-sizing: border-box; flex-shrink: 0;
        white-space: nowrap;
        width: auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #reset-all-filters:hover { background-color: var(--button-hover-bg); }

    #left-column-stats-container {
        position: absolute; top: 8.1vh; right: 1.5vw;
        z-index: 709; width: 38vw; max-width: 500px;
        display: flex; flex-direction: column;
        gap: 1vh;
        max-height: calc(100vh - 57px - 8.1vh - 2vh);
        padding-bottom: 1vh;
        overflow-y: auto;
    }

    #super-region-stats-container {
        background: var(--panel-bg); padding: 1.5vh 1vw;
        border-radius: 8px; box-shadow: var(--panel-shadow);
        font-size: 1.2vh;
        width: 100%;
        color: var(--text-color-primary);
        overflow-x: hidden;
        flex-shrink: 0;
        /* For consistent spacing like indonesia zone, ensure it can clip if tbody is too large */
        overflow-y: hidden; /* Added to ensure consistency if its tbody is miscalculated */
    }

    #indonesia-zone-summary-container {
        position: absolute;
        bottom: 10vh;
        right: 1.5vw;
        width: 38vw; max-width: 500px;
        max-height: 28vh;
        background: var(--panel-bg);
        padding: 1.5vh 1vw; /* padding-top: 1.5vh, padding-bottom: 1.5vh */
        border-radius: 8px; box-shadow: var(--panel-shadow);
        font-size: 1.2vh;
        color: var(--text-color-primary);
        overflow: hidden; /* Clips content exceeding max-height, respects padding */
        z-index: 709;
        display: none;
        flex-shrink: 0;
    }


    /* --- Panel Title Styling --- */
    .panel-title-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0;
        margin-bottom: 0.8vh;
    }

    #super-region-stats-container h3,
    #indonesia-zone-summary-container .panel-title-container h3,
    #international-stats-container h3,
    #chart-container #chart-title-dynamic,
    #indonesia-legend-floating h4 {
        margin-top: 0;
        margin-bottom: 0;
        font-size: 1.6vh;
        border-bottom: none;
        padding-bottom: 0;
        color: var(--text-color-primary);
        flex-grow: 1;
    }
    #indonesia-legend-floating .panel-title-container {
         margin-bottom: 0.5vh;
    }


    .download-button {
        background-color: var(--button-bg);
        border: 1px solid var(--button-border);
        color: var(--text-color-primary);
        padding: 0.3vh 0.6vw;
        font-size: 1.2vh;
        border-radius: 3px;
        cursor: pointer;
        margin-left: 0.5vw;
        white-space: nowrap;
        height: 2.5vh;
        max-height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .download-button:hover {
        background-color: var(--button-hover-bg);
    }

    table th button.sort-button {
        background: none;
        border: none;
        color: inherit;
        font-weight: inherit;
        font-size: inherit;
        padding: 0;
        margin: 0;
        cursor: pointer;
        text-align: left;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    table th button.sort-button:hover {
        color: var(--link-color);
    }
    table th button.sort-button .sort-arrow {
        font-size: 0.9em;
        margin-left: 0.3vw;
        display: inline-block;
        width: 1em;
        text-align: right;
        color: var(--text-color-secondary);
    }


    #super-region-stats-table,
    #indonesia-zone-summary-table,
    #international-stats-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    #super-region-stats-table thead, #indonesia-zone-summary-table thead, #international-stats-table thead,
    #super-region-stats-table tfoot tr, #indonesia-zone-summary-table tfoot tr, #international-stats-table tfoot tr {
        display: table;
        width: 100%;
        table-layout: fixed;
        box-sizing: border-box;
    }
    #super-region-stats-table tbody,
    #indonesia-zone-summary-table tbody,
    #international-stats-table tbody {
        display: block;
        overflow-y: auto;
        width: 100%;
        box-sizing: border-box;
    }

    #super-region-stats-table tbody {
      max-height: 15vh;
    }

    #indonesia-zone-summary-table tbody {
        max-height: 11.5vh;
    }
    #international-stats-table tbody {
        max-height: 16.5vh;
    }


    #super-region-stats-table tbody tr, #indonesia-zone-summary-table tbody tr, #international-stats-table tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    #super-region-stats-table th, #super-region-stats-table td,
    #indonesia-zone-summary-table th, #indonesia-zone-summary-table td,
    #international-stats-table th, #international-stats-table td {
        border: 1px solid var(--border-color-dark);
        padding: 0.4vh 0.4vw;
        text-align: left;
        font-size: 1.1vh;
        color: var(--text-color-primary);
        word-wrap: break-word;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #super-region-stats-table th,
    #indonesia-zone-summary-table th,
    #international-stats-table th {
        background-color: var(--table-header-bg);
        font-weight: bold;
        color: var(--text-color-primary);
    }
    #super-region-stats-table td.number-cell,
    #indonesia-zone-summary-table td.number-cell,
    #international-stats-table td.number-cell { text-align: right; }

    /* Column widths for Super Region Table */
    #super-region-stats-table .col-region { width: 22%; }
    #super-region-stats-table .col-budget { width: 16%; }
    #super-region-stats-table .col-dispatch { width: 16%; }
    #super-region-stats-table .col-achieve { width: 13%; }
    #super-region-stats-table .col-lastyear { width: 18%; }
    #super-region-stats-table .col-margin-percent { width: 15%; }

    /* Column widths for Indonesia Zone Summary Table */
    #indonesia-zone-summary-table .col-zone { width: 22%; }
    #indonesia-zone-summary-table .col-budget { width: 16%; }
    #indonesia-zone-summary-table .col-dispatch { width: 16%; }
    #indonesia-zone-summary-table .col-achieve { width: 13%; }
    #indonesia-zone-summary-table .col-lastyear { width: 18%; }
    #indonesia-zone-summary-table .col-margin-percent { width: 15%; }

    /* Column widths for International Stats Table */
    #international-stats-table .col-country { width: 25%; }
    #international-stats-table .col-sales { width: 15%; }
    #international-stats-table .col-budget { width: 15%; }
    #international-stats-table .col-achieve { width: 13%; }
    #international-stats-table .col-lastyear { width: 17%; }
    #international-stats-table .col-margin-percent { width: 15%; }

    /* Highlight for low dispatch */
    .highlight-low-dispatch td {
        background-color: #ffdddd !important; /* Light red */
        color: #a00000 !important; /* Darker red text for contrast */
        font-weight: bold !important;
    }

    .dark-mode .highlight-low-dispatch td {
        background-color: #581818 !important; /* Darker, less saturated red for dark mode */
        color: #ffc0c0 !important; /* Lighter red text for dark mode */
    }


    #international-stats-container {
        position: absolute; top: 8.1vh; left: 1vw;
        z-index: 709; background: var(--panel-bg); padding: 1.5vh 1vw;
        border-radius: 8px; box-shadow: var(--panel-shadow);
        font-size: 1.5vh; width: 38vw; max-width: 500px;
        max-height: 25vh;
        overflow: hidden;
        display: none;
        color: var(--text-color-primary);
    }
    #back-to-world-btn-dynamic {
        position: absolute; top: 8.1vh; left: 1.5vw;
        background: var(--back-to-world-bg); color: var(--link-color);
        padding: 1vh 1.2vw; border-radius: 5px;
        text-decoration: none; font-size: 1.6vh;
        box-shadow: var(--panel-shadow); z-index: 720; display: none;
    }


    #indonesia-legend-floating {
        position: absolute;
        bottom: calc(20vh + 220px + 1vh);
        left: 1vw;
        background: var(--panel-bg); padding: 1.5vh 1vw;
        border-radius: 5px; box-shadow: var(--panel-shadow);
        z-index: 700;
        width: 20vw;
        max-width: 250px;
        display: none;
    }

    .legend-items-scroll-container {
        max-height: 10vh;
        overflow-y: auto; font-size: 1.4vh;
        color: var(--text-color-primary);
    }
    .legend-items-scroll-container div { margin-bottom: 0.3vh; display: flex; align-items: center; }
    .legend-items-scroll-container i {
        width: 1.2vh; height: 1.2vh;
        margin-right: 0.5vw; border: 1px solid var(--border-color-light);
        flex-shrink: 0;
    }

   #chart-container {
    position: absolute;
    bottom: 12vh;
    left: 1vw;
    width: 20vw;
    max-width: 250px;
    aspect-ratio: 1 / 1;
    background: var(--chart-bg);
    padding: 1.5vh 1vw;
    border-radius: 8px;
    box-shadow: var(--panel-shadow);
    z-index: 710;
    display: flex;
    flex-direction: column;
}


    #chart-container .panel-title-container {
        flex-shrink: 0;
    }

#chart-container canvas {
    flex-grow: 1;
    min-height: 0;
    width: auto;
    height: auto;
    aspect-ratio: 1/1;
}


    #back-to-world-btn-dynamic:hover { background-color: var(--button-hover-bg); }


    /* --- Responsive Adjustments --- */

    @media (max-width: 1200px) {
        body { font-size: 1.5vh; }
        #filter-menu {
            gap: 0.6vw; top: 1vh; padding: 0.7vh 0.8vw;
        }
        #filter-menu input[type="date"] { width: 9vw; min-width: 80px; max-width: 90px; }
        .custom-dropdown-trigger { min-width: 10vw; max-width: 15vw; }

        #left-column-stats-container {
            width: 40vw; max-width: 450px;
            top: calc(1vh + 3vh + 0.7vh + 1.5vh);
            max-height: calc(100vh - 57px - (1vh + 3vh + 0.7vh + 1.5vh) - 2vh );
        }
        #super-region-stats-table tbody {
            max-height: 14vh;
        }

        #indonesia-zone-summary-container {
            width: 40vw; max-width: 450px;
            max-height: 22vh;
        }
        #indonesia-zone-summary-table tbody {
            max-height: 13.5vh;
        }

        #international-stats-container {
            width: 40vw; max-width: 450px;
            top: calc(1vh + 3vh + 0.7vh + 1.5vh);
            max-height: 23vh;
        }
        #international-stats-table tbody {
             max-height: 14.5vh;
        }


        #indonesia-legend-floating {
            width: 20vw; max-width: 250px;
            bottom: calc(10vh + 200px + 1vh);
             max-height: 15vh;
        }
        .legend-items-scroll-container { max-height: 8vh; }

        #chart-container {
            width: 26vw; max-width: 260px;
            min-height: 18vh; max-height: 200px;
            bottom: 10vh;
        }
    }

    @media (max-width: 991px) {
        body { font-size: 1.6vh; }
        #map-ui-container {
            display: flex; flex-direction: column;
            height: auto; min-height: calc(100vh - 57px);
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 2vh;
        }
        #filter-menu {
            position: relative; order: 1;
            top: auto; right: auto; left: auto;
            width: 100%; max-width: 100%;
            border-radius: 0; box-shadow: none;
            border-bottom: 1px solid var(--border-color-dark);
            padding: 1.5vh 2vw; margin-bottom: 1.5vh;
            flex-wrap: wrap;
        }
        .date-filter-container { flex-basis: auto; justify-content: flex-start; }
        #filter-menu input[type="date"] { width: 35vw; max-width: 130px; font-size: 1.6vh; height: 3.5vh; max-height: 28px; }
        .filter-group { flex-basis: auto; }
        .custom-dropdown-trigger { min-width: 30vw; max-width: none; font-size: 1.6vh; height: 3.5vh; max-height: 28px; }

        .custom-dropdown-container {
             position: relative;
        }
        .custom-dropdown-content {
            position: absolute;
            z-index: 9900;
        }


        .filter-actions {
            margin-left: 0;
            width: 100%;
            order: 99;
            margin-top: 1vh;
        }
        #reset-all-filters {
            width: 100%;
            margin-left: 0;
            padding: 0.8vh 1vw;
            height: 3.5vh; max-height: 28px;
        }


        #back-to-world-btn-dynamic { order: 2; position: relative; top: auto; left: auto; width: max-content; margin: 0 auto 1.5vh auto; font-size: 2vh; }

        #left-column-stats-container,
        #indonesia-zone-summary-container,
        #international-stats-container {
            position: relative; width: calc(100% - 4vw);
            margin: 0 auto 1.5vh auto; top: auto; right: auto; bottom:auto; left: auto;
            max-height: none;
            padding-bottom: 0;
            overflow-y: visible;
        }
        #left-column-stats-container { order: 3; }
        #indonesia-zone-summary-container { order: 4; overflow-x: auto; }
        #international-stats-container { order: 5; overflow-x: auto; }

        #indonesia-legend-floating {
            order: 6;
            position: relative; width: calc(100% - 4vw);
            margin: 0 auto 1.5vh auto; bottom: auto; right: auto; left: auto;
            max-height: 25vh;
            overflow-y: auto;
        }
        #chart-container {
            order: 7;
            position: relative; width: calc(100% - 4vw);
            min-height: 200px;
            max-height: 280px;
            margin: 0 auto 1.5vh auto; bottom: auto; left: auto;
        }

        #super-region-stats-container {
             margin-top: 0;
             overflow-x: auto;
             overflow-y: visible;
        }


        #super-region-stats-table,
        #indonesia-zone-summary-table,
        #international-stats-table {
            min-width: 450px;
        }
        #super-region-stats-table th, #super-region-stats-table td,
        #indonesia-zone-summary-table th, #indonesia-zone-summary-table td,
        #international-stats-table th, #international-stats-table td {
            font-size: 1.2vh;
        }
        #super-region-stats-table tbody { max-height: 25vh; }
        #indonesia-zone-summary-table tbody { max-height: 20vh; }
        #international-stats-table tbody { max-height: 30vh; }


        .legend-items-scroll-container { max-height: 20vh; }

        #map { order: 8; position: relative; width: 100%; height: 50vh; min-height: 300px; z-index: 1; }
        .geoboundaries-watermark { order: 9; position: relative; text-align: center; width: 100%; bottom: auto; right: auto; padding: 0.5vh 0; font-size: 1.2vh; background-color: transparent; z-index: 2; }
    }

    @media (max-width: 575px) {
        body { font-size: 1.7vh; }
        #filter-menu { padding: 1vh 2vw; gap: 1vh 1vw; }
        .date-filter-container > div { flex-basis: 100%; margin-bottom: 0.8vh; }
        #filter-menu input[type="date"] { width: calc(100% - 10vw); max-width: none; }
        .filter-group { flex-basis: 100%; }
        .custom-dropdown-trigger { min-width: calc(100% - 10vw); }

        #map { height: 45vh; min-height: 250px; }
        #left-column-stats-container,
        #indonesia-zone-summary-container,
        #international-stats-container { font-size: 1.5vh; }

        #super-region-stats-table tbody { max-height: 20vh; }
        #indonesia-zone-summary-table tbody { max-height: 18vh; }
        #international-stats-table tbody { max-height: 25vh; }

        #super-region-stats-table th, #super-region-stats-table td,
        #indonesia-zone-summary-table th, #indonesia-zone-summary-table td,
        #international-stats-table th, #international-stats-table td {
            font-size: 1.1vh;
            padding: 0.3vh 0.3vw;
        }
        #super-region-stats-table,
        #indonesia-zone-summary-table,
        #international-stats-table { min-width: 300px; }
        #chart-container {
            min-height: 170px;
            max-height: 240px;
        }
        .legend-items-scroll-container { font-size: 1.3vh; max-height: 15vh; }
        #indonesia-legend-floating h4 { font-size: 1.5vh; }
        .download-button { font-size: 1.1vh; padding: 0.2vh 0.4vw; height: 2.2vh; max-height: 18px;}
    }
</style>

    <div id="map-ui-container">
        <div id="map"></div>
        <div id="loading" class="loading">Memuat Peta...</div>
        <div class="geoboundaries-watermark"> Boundaries from <a href="https://www.geoboundaries.org" target="_blank" rel="noopener noreferrer">geoBoundaries</a>.</div>

        <div id="filter-menu">
            <div class="date-filter-container">
                <div><label for="start-date-select">Start:</label><input type="date" id="start-date-select"></div>
                <div><label for="end-date-select">End:</label><input type="date" id="end-date-select"></div>
            </div>

            <div class="filter-group">
                <label>Brand:</label>
                <div class="custom-dropdown-container">
                    <button type="button" id="brand-dropdown-trigger" class="custom-dropdown-trigger" data-controls="brand-filter-list" aria-haspopup="true" aria-expanded="false">All Brands</button>
                    <div id="brand-filter-list" class="custom-dropdown-content" role="listbox">
                        <div class="checkbox-list-container">
                           <!-- Populated by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-group">
                <label>Region/Code:</label>
                <div class="custom-dropdown-container">
                    <button type="button" id="code-cmmt-dropdown-trigger" class="custom-dropdown-trigger" data-controls="code-cmmt-filter-list" aria-haspopup="true" aria-expanded="false">All Regions/Codes</button>
                    <div id="code-cmmt-filter-list" class="custom-dropdown-content" role="listbox">
                         <div class="checkbox-list-container">
                            <!-- Populated by JS -->
                         </div>
                    </div>
                </div>
            </div>

            <div class="filter-group">
                <label>City:</label>
                <div class="custom-dropdown-container">
                    <button type="button" id="city-dropdown-trigger" class="custom-dropdown-trigger" data-controls="city-filter-list" aria-haspopup="true" aria-expanded="false">All Cities</button>
                    <div id="city-filter-list" class="custom-dropdown-content" role="listbox">
                        <div class="checkbox-list-container">
                           <!-- Populated by JS -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button id="reset-all-filters">Reset Filters</button>
            </div>
        </div>

        <a href="#" id="back-to-world-btn-dynamic">← Kembali ke Peta Dunia</a>

        <div id="left-column-stats-container">
            <div id="super-region-stats-container">
                <div class="panel-title-container">
                    <h3>Indonesia Region Sales</h3>
                    <button class="download-button" data-target-id="super-region-stats-container" data-filename="indonesia-region-sales.png">Unduh</button>
                </div>
                <table id="super-region-stats-table">
                    <thead><tr>
                        <th class="col-region"><button class="sort-button" data-sort-col="region" title="Sort by Region">Region <span class="sort-arrow"></span></button></th>
                        <th class="col-budget"><button class="sort-button" data-sort-col="budget" title="Sort by Budget">Budget (Ton) <span class="sort-arrow"></span></button></th>
                        <th class="col-dispatch"><button class="sort-button" data-sort-col="dispatch" title="Sort by Dispatch">Dispatch (Ton) <span class="sort-arrow"></span></button></th>
                        <th class="col-achieve"><button class="sort-button" data-sort-col="achieve" title="Sort by Achieve %">Achieve % <span class="sort-arrow"></span></button></th>
                        <th class="col-lastyear"><button class="sort-button" data-sort-col="lastyear" title="Sort by Dispatch LY">Dispatch LY (Ton) <span class="sort-arrow"></span></button></th>
                        <th class="col-margin-percent"><button class="sort-button" data-sort-col="margin" title="Sort by Achieve Margin %">Achieve Margin % <span class="sort-arrow"></span></button></th>
                    </tr></thead>
                    <tbody></tbody>
                    <tfoot><tr>
                        <td class="col-region"></td>
                        <td class="col-budget number-cell"></td>
                        <td class="col-dispatch number-cell"></td>
                        <td class="col-achieve number-cell"></td>
                        <td class="col-lastyear number-cell"></td>
                        <td class="col-margin-percent number-cell"></td>
                    </tr></tfoot>
                </table>
            </div>
        </div>

        <div id="indonesia-zone-summary-container">
            <div class="panel-title-container">
                <h3 class="">Indonesia Zone & Category Sales</h3>
                <button class="download-button" data-target-id="indonesia-zone-summary-container" data-filename="indonesia-zone-summary.png">Unduh</button>
            </div>
            <table id="indonesia-zone-summary-table">
                <thead>
                    <tr>
                        <th class="col-zone"><button class="sort-button" data-sort-col="zone" title="Sort by Zone / Category">Zone / Category <span class="sort-arrow"></span></button></th>
                        <th class="col-budget"><button class="sort-button" data-sort-col="budget" title="Sort by Budget">Budget (Ton) <span class="sort-arrow"></span></button></th>
                        <th class="col-dispatch"><button class="sort-button" data-sort-col="dispatch" title="Sort by Dispatch">Dispatch (Ton) <span class="sort-arrow"></span></button></th>
                        <th class="col-achieve"><button class="sort-button" data-sort-col="achieve" title="Sort by Achievement %">Achieve % <span class="sort-arrow"></span></button></th>
                        <th class="col-lastyear"><button class="sort-button" data-sort-col="lastyear" title="Sort by Dispatch LY">Dispatch LY (Ton) <span class="sort-arrow"></span></button></th>
                        <th class="col-margin-percent"><button class="sort-button" data-sort-col="margin" title="Sort by Achieve Margin %">Achieve Margin % <span class="sort-arrow"></span></button></th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot><tr>
                    <td class="col-zone"></td>
                    <td class="col-budget number-cell"></td>
                    <td class="col-dispatch number-cell"></td>
                    <td class="col-achieve number-cell"></td>
                    <td class="col-lastyear number-cell"></td>
                    <td class="col-margin-percent number-cell"></td>
                </tr></tfoot>
            </table>
        </div>


        <div id="international-stats-container">
            <div class="panel-title-container">
                <h3>International Export Sales</h3>
                <button class="download-button" data-target-id="international-stats-container" data-filename="international-export-sales.png">Unduh</button>
            </div>
            <table id="international-stats-table">
                <thead><tr>
                    <th class="col-country"><button class="sort-button" data-sort-col="country" title="Sort by Country">Country <span class="sort-arrow"></span></button></th>
                    <th class="col-sales"><button class="sort-button" data-sort-col="sales" title="Sort by Dispatch">Dispatch (Ton) <span class="sort-arrow"></span></button></th>
                    <th class="col-budget"><button class="sort-button" data-sort-col="budget" title="Sort by Budget">Budget (Ton) <span class="sort-arrow"></span></button></th>
                    <th class="col-achieve"><button class="sort-button" data-sort-col="achieve" title="Sort by Achieve %">Achieve % <span class="sort-arrow"></span></button></th>
                    <th class="col-lastyear"><button class="sort-button" data-sort-col="lastyear" title="Sort by Dispatch LY">Dispatch LY (Ton) <span class="sort-arrow"></span></button></th>
                    <th class="col-margin-percent"><button class="sort-button" data-sort-col="margin" title="Sort by Margin %">Margin % <span class="sort-arrow"></span></button></th>
                </tr></thead>
                <tbody></tbody>
                <tfoot><tr>
                    <td class="col-country"></td>
                    <td class="col-sales number-cell"></td>
                    <td class="col-budget number-cell"></td>
                    <td class="col-achieve number-cell"></td>
                    <td class="col-lastyear number-cell"></td>
                    <td class="col-margin-percent number-cell"></td>
                </tr></tfoot>
            </table>
        </div>

        <div id="indonesia-legend-floating">
            <div class="panel-title-container">
                 <h4>Legend</h4>
            </div>
            <div class="legend-items-scroll-container"></div>
        </div>

        <div id="chart-container">
            <div class="panel-title-container">
                <h4 id="chart-title-dynamic">Sales Chart</h4>
                <button class="download-button" data-target-id="chart-container" data-filename="sales-chart.png">Unduh</button>
            </div>
            <canvas id="salesChart"></canvas>
        </div>


        <div id="info-box" class="info-tooltip-global"></div>
        <div id="sales-tooltip-indonesia"></div>

    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/topojson/3.0.2/topojson.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://unpkg.com/tinycolor2"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


    <script>
        // --- Variabel Global dan Konfigurasi Awal ---
        let currentMapView = 'world';
        let map, geoLayer, previousIndonesiaZoom = null;
        const INDIA_CENTER = [20.5937, 78.9629];
        const WORLD_TOPOJSON_URL = '{{ asset('maps/dunia.topojson') }}';
        const INDONESIA_TOPOJSON_URL = '{{ asset('maps/indo.topojson') }}';
        const WORLD_CACHE_KEY = 'world-custom-topojson-v7-dynamic';
        const INDONESIA_CACHE_KEY = 'indonesia-adm2-topojson-v19-dynamic';
        const MAX_CACHE_SIZE_MB = 5;
        const CALCULATION_BATCH_SIZE = 50;

        const INDONESIA_DEFAULT_ZOOM_LEVEL = 5;
        const INDONESIA_MIN_ZOOM = 4.5;
        const INDONESIA_MAX_ZOOM = 7.75;
        const WORLD_DEFAULT_ZOOM_LEVEL = 3;
        const WORLD_MIN_ZOOM = 2.5;
        const WORLD_MAX_ZOOM = 6;

        let salesDataGlobal = {};
        let superRegionSales = {};
        let cityMarkersData = [];
        let internationalCityMarkersData = [];
        let cityMarkersLayerGroup = L.layerGroup();
        let superRegionPolygonLayers = {};

        const superRegionDefinitions = {
          "REGION1A": ["Pontianak", "Kalimantan Barat", "Serang", "Tangerang", "Lampung"],
          "REGION1B": ["Bandung", "Tasikmalaya", "Cirebon"],
          "REGION1C": ["Jakarta Timur", "Jakarta Pusat", "Jakarta Utara", "Jakarta Barat", "Jakarta Selatan", "Jakarta", "DKI Jakarta", "DKI Jakarta Raya", "Adm. Jakarta Selatan", "Depok", "Kota Depok", "Kota Jakarta Timur", "Kota Jakarta Pusat", "Kota Jakarta Utara", "Kota Jakarta Barat", "Kota Jakarta Selatan"],
          "REGION1D": ["Karawang", "Bekasi", "Bogor", "Kota Bekasi", "Kabupaten Bekasi"],
          "REGION2A": ["Semarang", "Kudus", "Tegal", "Pekalongan", "Bojonegoro"],
          "REGION2B": ["Malang", "Surabaya", "Jember", "Madura", "Banyuwangi", "Pasuruan", "Sidoarjo", "Gresik", "Probolinggo", "Kediri"],
          "REGION2C": ["Bali", "Flores", "Kupang", "Lombok", "Mataram", "Alok", "Kota Denpasar", "Dompu"],
          "REGION2D": ["Solo", "Purwokerto", "Magelang", "Yogyakarta", "D.I. Yogyakarta", "DI Yogyakarta", "Sleman", "Tulungagung", "Tulung Agung", "Madiun"],
          "REGION3A": ["Palembang", "Bangka", "Pangkalpinang", "Jambi", "Bungo"],
          "REGION3B": ["Padang", "Kota Pekanbaru", "Pekan Baru", "Batam", "Duri", "Bintan"],
          "REGION3C": ["Medan", "Aceh", "Banda Aceh", "Nias", "Deli Serdang", "Tebing Tinggi", "Kaban Jahe Karo"],
          "REGION4A": ["Sorong", "Gorontalo", "Ternate", "Jayapura", "Manado", "Manokwari", "Manokwari Papua", "Timika", "Merauke", "Ambon", "Papua Barat"],
          "REGION4B": ["Tarakan", "Palu", "Samarinda", "Baubau", "Banjarmasin", "Palangkaraya", "Sampit", "Pangkalanbun", "Makassar", "Kendari", "Balikpapan", "Kotawaringin", "Kabupaten Kutai Kartanegara", "Kota Bontang", "Kota Tarakan", "Kota Palangkaraya", "Kota Makassar", "Kota Samarinda"],
          "KEYACCOUNT": [], "COMMERCIAL": []
        };
        const cityToSuperRegionMap = {};
        for (const superReg in superRegionDefinitions) {
            superRegionDefinitions[superReg].forEach(cityTopoJsonName => {
                cityToSuperRegionMap[cityTopoJsonName.toLowerCase().trim()] = superReg;
            });
        }

        const indonesiaZoneDefinitions = {
            "WEST ZONE": ["REGION1A", "REGION1B", "REGION1C", "REGION1D", "REGION3A", "REGION3B", "REGION3C"],
            "EAST ZONE": ["REGION2A", "REGION2B", "REGION2C", "REGION2D", "REGION4A", "REGION4B"]
        };

        const regionColors = { "REGION1A": "#8dd3c7", "REGION1B": "#ffffb3", "REGION1C": "#bebada", "REGION1D": "#fb8072", "REGION2A": "#80b1d3", "REGION2B": "#fdb462", "REGION2C": "#b3de69", "REGION2D": "#fccde5", "REGION3A": "#bc80bd", "REGION3B": "#ccebc5", "REGION3C": "#ffed6f", "REGION4A": "#99cce0", "REGION4B": "#f7cac9", "KEYACCOUNT": "#d9d9d9", "COMMERCIAL": "#a9a9a9", "OTHER_BASE": "#cccccc" };
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
        let indonesiaZoneSummaryContainer;
        let superRegionContainer;
        let leftColContainer;
        const infoTooltipGlobalDiv = document.getElementById('info-box');
        const salesTooltipIndonesiaDiv = document.getElementById('sales-tooltip-indonesia');

        const dateRanges = @json($dateRanges);
        const initialFilterValues = @json($filterValues ?? ['brands' => [], 'cities' => [], 'code_cmmts' => []]);

        let currentSuperRegionSort = { column: 'region', direction: 'asc' };
        let currentZoneSort = { column: 'dispatch', direction: 'asc' };
        let currentInternationalSort = { column: 'sales', direction: 'asc' };


        const lightThemeVarsConfig = {
            '--main-bg-color': '#f0f0f0',
            '--map-ui-bg': '#f0f0f0',
            '--text-color-primary': '#333333',
            '--text-color-secondary': '#555555',
            '--text-color-labels': '#333333',
            '--panel-bg': 'rgba(255, 255, 255, 0.97)',
            '--panel-bg-solid': '#ffffff',
            '--border-color-light': '#cccccc',
            '--border-color-medium': '#bbbbbb',
            '--border-color-dark': '#dddddd',
            '--table-header-bg': '#f2f2f2',
            '--chart-bg': '#ffffff',
            '--button-bg': '#e9e9e9',
            '--button-border': '#bbbbbb',
        };

        function positionDropdown(triggerElement, dropdownContent) {
            const triggerRect = triggerElement.getBoundingClientRect();
            const initialDisplay = dropdownContent.style.display;
            if (initialDisplay === 'none') {
                dropdownContent.style.visibility = 'hidden';
                dropdownContent.style.display = 'block';
            }
            const contentRect = dropdownContent.getBoundingClientRect();
            if (initialDisplay === 'none') {
                dropdownContent.style.display = 'none';
                dropdownContent.style.visibility = 'visible';
            }

            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const scrollY = window.scrollY;

            let top = triggerRect.bottom + scrollY + 2;
            let left = triggerRect.left;

            if (top + contentRect.height > viewportHeight + scrollY) {
                top = triggerRect.top + scrollY - contentRect.height - 2;
            }
            if (top < scrollY) {
                top = scrollY + 5;
            }
            if (left + contentRect.width > viewportWidth) {
                left = viewportWidth - contentRect.width - 10;
            }
            if (left < 0) {
                left = 10;
            }

            dropdownContent.style.top = top + 'px';
            dropdownContent.style.left = left + 'px';
        }

        function openDropdown(triggerElement, dropdownContent) {
            document.querySelectorAll('.custom-dropdown-content.active').forEach(openContent => {
                if (openContent !== dropdownContent) {
                    openContent.style.display = 'none';
                    openContent.classList.remove('active');
                    const otherTrigger = document.querySelector(`[data-controls="${openContent.id}"]`);
                    if (otherTrigger) otherTrigger.setAttribute('aria-expanded', 'false');
                }
            });

            const isOpening = dropdownContent.style.display !== 'block';

            if (isOpening) {
                dropdownContent.style.display = 'block';
                dropdownContent.classList.add('active');
                triggerElement.setAttribute('aria-expanded', 'true');
                positionDropdown(triggerElement, dropdownContent);

                const clickOutsideHandler = (event) => {
                    if (!dropdownContent.contains(event.target) && !triggerElement.contains(event.target)) {
                        dropdownContent.style.display = 'none';
                        dropdownContent.classList.remove('active');
                        triggerElement.setAttribute('aria-expanded', 'false');
                        document.removeEventListener('click', clickOutsideHandler, true);
                    }
                };
                setTimeout(() => {
                    document.addEventListener('click', clickOutsideHandler, true);
                }, 0);

            } else {
                dropdownContent.style.display = 'none';
                dropdownContent.classList.remove('active');
                triggerElement.setAttribute('aria-expanded', 'false');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            internationalStatsContainer = document.getElementById('international-stats-container');
            indonesiaZoneSummaryContainer = document.getElementById('indonesia-zone-summary-container');
            superRegionContainer = document.getElementById('super-region-stats-container');
            leftColContainer = document.getElementById('left-column-stats-container');
            indonesiaLegendContainer = document.getElementById('indonesia-legend-floating');
            if (indonesiaLegendContainer) legendItemsScrollContainer = indonesiaLegendContainer.querySelector('.legend-items-scroll-container');

            initMap();
            initAppDarkMode();
            populateFilterDropdowns(initialFilterValues.brands, initialFilterValues.code_cmmts, initialFilterValues.cities, [], [], []);
            initUIElements();
            updateAllDropdownTriggers();

            updateUIVisibilityBasedOnView(currentMapView);
            handleFilterChange();

            window.addEventListener('resize', debounce(() => {
                document.querySelectorAll('.custom-dropdown-content.active').forEach(content => {
                    const triggerId = content.id.replace('-filter-list', '-dropdown-trigger');
                    const trigger = document.getElementById(triggerId);
                    if (trigger) {
                        positionDropdown(trigger, content);
                    }
                });
                adjustTableHeadersAndFooters('super-region-stats-table');
                adjustTableHeadersAndFooters('international-stats-table');
                adjustTableHeadersAndFooters('indonesia-zone-summary-table');
            }, 150));
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function applyCurrentThemeStyles() {
            const isDarkMode = document.body.classList.contains('dark-mode');
            const newGlobalFontColor = isDarkMode
                                        ? lightThemeVarsConfig['--text-color-primary']
                                        : getComputedStyle(document.documentElement).getPropertyValue('--text-color-primary').trim();

            if (typeof Chart !== 'undefined' && Chart.defaults) {
                Chart.defaults.color = newGlobalFontColor;
                Chart.defaults.font.size = 12;
            }

            if (salesPieChart) {
                updateSalesChart();
            }
            if (geoLayer) {
                geoLayer.setStyle(styleFeatureMap);
            }
            updateLegend();
            updateCityMarkers();

            adjustTableHeadersAndFooters('super-region-stats-table');
            adjustTableHeadersAndFooters('international-stats-table');
            adjustTableHeadersAndFooters('indonesia-zone-summary-table');
        }

        function initAppDarkMode() {
            const darkModeCheckbox = document.getElementById('toggle_left_sidebar_skin');

            const setInitialTheme = () => {
                const prefersDark = localStorage.getItem('darkMode') === 'true';
                if (prefersDark) {
                    document.body.classList.add('dark-mode');
                    if (darkModeCheckbox) darkModeCheckbox.checked = true;
                } else {
                    document.body.classList.remove('dark-mode');
                    if (darkModeCheckbox) darkModeCheckbox.checked = false;
                }
                applyCurrentThemeStyles();
            };

            if (darkModeCheckbox) {
                darkModeCheckbox.addEventListener('change', () => {
                    if (darkModeCheckbox.checked) {
                        document.body.classList.add('dark-mode');
                        localStorage.setItem('darkMode', 'true');
                    } else {
                        document.body.classList.remove('dark-mode');
                        localStorage.setItem('darkMode', 'false');
                    }
                    applyCurrentThemeStyles();
                });
            } else {
                console.warn("Dark mode toggle checkbox (e.g., ID 'toggle_left_sidebar_skin') not found. Dark mode may not sync.");
            }

            setInitialTheme();
        }

        function showLoading(message = 'Memuat...') { if (loadingDiv) { loadingDiv.textContent = message; loadingDiv.style.display = 'block'; } }
        function hideLoading() { if (loadingDiv) { loadingDiv.style.display = 'none'; } }

        function initMap() {
            map = L.map('map', { worldCopyJump: true, zoomControl: false, zoomSnap: 0.25, zoomDelta: 0.25 }).setView(INDIA_CENTER, WORLD_DEFAULT_ZOOM_LEVEL);
            map.on('mousemove', e => {
                if (currentMapView === 'world' && infoTooltipGlobalDiv.style.display === 'block') { infoTooltipGlobalDiv.style.left = (e.containerPoint.x + 15) + 'px'; infoTooltipGlobalDiv.style.top = (e.containerPoint.y + 15) + 'px'; }
                else if (currentMapView === 'indonesia' && salesTooltipIndonesiaDiv.style.display === 'block') { salesTooltipIndonesiaDiv.style.left = (e.containerPoint.x + 15) + 'px'; salesTooltipIndonesiaDiv.style.top = (e.containerPoint.y + 15) + 'px'; }
            });
            map.on('zoomend', () => { if (currentMapView === 'indonesia') previousIndonesiaZoom = map.getZoom(); });
        }

        function updateDropdownTriggerText(triggerId, contentId, singularName, pluralName, defaultTextPrefix = "All") {
            const trigger = document.getElementById(triggerId);
            const contentWrapper = document.getElementById(contentId);
            if (!trigger || !contentWrapper) return;

            const checkboxContainer = contentWrapper.querySelector('.checkbox-list-container');
            if (!checkboxContainer) return;


            const checkedBoxes = checkboxContainer.querySelectorAll('input[type="checkbox"]:checked');
            let newText = "";
            if (checkedBoxes.length === 0) {
                newText = `${defaultTextPrefix} ${pluralName}`;
            } else if (checkedBoxes.length === 1) {
                const labelElement = checkedBoxes[0].closest('div').querySelector('label');
                if (labelElement) {
                    newText = labelElement.textContent.trim();
                    if (newText.length > 15) {
                        newText = newText.substring(0, 12) + "...";
                    }
                } else {
                     newText = `${checkedBoxes.length} ${pluralName} selected`;
                }
            } else {
                newText = `${checkedBoxes.length} ${pluralName} selected`;
            }
            trigger.textContent = newText;
            trigger.title = newText;
        }


        function updateAllDropdownTriggers() {
            updateDropdownTriggerText('brand-dropdown-trigger', 'brand-filter-list', 'Brand', 'Brands');
            updateDropdownTriggerText('code-cmmt-dropdown-trigger', 'code-cmmt-filter-list', 'Region/Code', 'Regions/Codes');
            updateDropdownTriggerText('city-dropdown-trigger', 'city-filter-list', 'City', 'Cities');
        }

        function createCheckboxFilterGroup(containerId, items, filterType, currentSelections = []) {
            const dropdownContentElement = document.getElementById(containerId);
            if (!dropdownContentElement) {
                console.error(`Dropdown content element with ID '${containerId}' not found.`);
                return;
            }
            const container = dropdownContentElement.querySelector('.checkbox-list-container');
            if (!container) {
                console.error(`Checkbox list container not found within ID '${containerId}'. Check HTML structure.`);
                return;
            }
            container.innerHTML = '';


            items.forEach(item => {
                let value, text;
                if (typeof item === 'object' && item.value !== undefined && item.text !== undefined) {
                    value = String(item.value);
                    text = String(item.text);
                } else {
                    value = String(item);
                    text = String(item).split(/[\s_]+/).map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
                    if (filterType === 'code_cmmt' && value.match(/^REGION[0-9][A-Z]$/)) {
                        text = value.replace(/([A-Z]+)([0-9]+[A-Z]*)/g, '$1 $2');
                    }
                }

                const div = document.createElement('div');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.id = `${filterType}-checkbox-${value.replace(/[^a-zA-Z0-9]/g, '_')}`;
                checkbox.value = value;
                checkbox.name = `${filterType}_filter_checkbox`;
                if (currentSelections.includes(value)) {
                    checkbox.checked = true;
                }
                checkbox.addEventListener('change', () => {
                    handleFilterChange();
                });

                const label = document.createElement('label');
                label.htmlFor = checkbox.id;
                label.textContent = ` ${text}`;
                div.appendChild(checkbox);
                div.appendChild(label);
                container.appendChild(div);
            });
        }

        function populateFilterDropdowns(availableBrands, availableCodeCmmts, availableCities, currentSelectedBrands, currentSelectedCodeCmmts, currentSelectedCities) {
            createCheckboxFilterGroup('brand-filter-list', availableBrands || [], 'brand', currentSelectedBrands);
            createCheckboxFilterGroup('code-cmmt-filter-list', availableCodeCmmts || [], 'code_cmmt', currentSelectedCodeCmmts);
            createCheckboxFilterGroup('city-filter-list', availableCities || [], 'city', currentSelectedCities);
            updateAllDropdownTriggers();
        }

        function getSelectedCheckboxValues(contentListId) {
            const dropdownContentElement = document.getElementById(contentListId);
            if (!dropdownContentElement) return [];
            const container = dropdownContentElement.querySelector('.checkbox-list-container');
            if (!container) return [];

            const checkedBoxes = container.querySelectorAll('input[type="checkbox"]:checked');
            return Array.from(checkedBoxes).map(cb => cb.value);
        }

      function downloadElementAsPNG(elementId, filename) {
            const elementToCapture = document.getElementById(elementId);
            if (!elementToCapture) {
                console.error(`Element with ID '${elementId}' not found for download: ${elementId}`);
                alert(`Could not find element to download: ${elementId}`);
                return;
            }

            showLoading('Preparing high-resolution download...');

            let isChartContainer = elementId === 'chart-container';
            let isTableContainer = elementToCapture.querySelector('table') !== null && !isChartContainer;

            const finalBgColor = isChartContainer ? lightThemeVarsConfig['--chart-bg'] : lightThemeVarsConfig['--panel-bg-solid'];

            html2canvas(elementToCapture, {
                backgroundColor: finalBgColor,
                useCORS: true,
                logging: false,
                scale: window.devicePixelRatio * 2,
                onclone: (clonedDoc) => {
                    const clonedCapturedElement = clonedDoc.getElementById(elementToCapture.id);
                    if (!clonedCapturedElement) return;

                    clonedDoc.body.classList.remove('dark-mode');
                    for (const [varName, varValue] of Object.entries(lightThemeVarsConfig)) {
                        clonedDoc.documentElement.style.setProperty(varName, varValue);
                    }
                    clonedCapturedElement.style.backgroundColor = finalBgColor;

                    const downloadButtonsInClone = clonedCapturedElement.querySelectorAll('.download-button');
                    downloadButtonsInClone.forEach(btn => btn.style.display = 'none');

                    const allTextNodes = clonedCapturedElement.querySelectorAll('h3, h4, th, td, label, span, div:not(.download-button)');
                    allTextNodes.forEach(node => {
                        node.style.color = lightThemeVarsConfig['--text-color-primary'];
                    });
                    const panelTitlesInClone = clonedCapturedElement.querySelectorAll('.panel-title-container h3, .panel-title-container h4, #chart-title-dynamic');
                    panelTitlesInClone.forEach(titleNode => {
                        titleNode.style.color = lightThemeVarsConfig['--text-color-primary'];
                        const parentTitleContainer = titleNode.closest('.panel-title-container');
                        if(parentTitleContainer) {
                            parentTitleContainer.style.borderBottom = 'none';
                        }
                    });

                    if (isTableContainer) {
                        clonedCapturedElement.style.width = '900px';
                        clonedCapturedElement.style.padding = '15px';
                        clonedCapturedElement.style.boxShadow = 'none';

                        const tableElement = clonedCapturedElement.querySelector('table');
                        if (tableElement) {
                            const clonedThead = tableElement.querySelector('thead');
                            const clonedTbody = tableElement.querySelector('tbody');
                            const clonedTfoot = tableElement.querySelector('tfoot');

                            tableElement.style.display = 'table';
                            tableElement.style.width = '100%';
                            tableElement.style.minWidth = '0';
                            tableElement.style.tableLayout = 'auto';
                            tableElement.style.borderCollapse = 'collapse';

                            const tableHeaderBg = lightThemeVarsConfig['--table-header-bg'];
                            const tableBorderColor = lightThemeVarsConfig['--border-color-dark'];
                            const textColorPrimary = lightThemeVarsConfig['--text-color-primary'];

                            if (clonedThead) {
                                clonedThead.style.display = 'table-header-group';
                                clonedThead.style.width = '100%';
                                clonedThead.style.position = 'static';
                                Array.from(clonedThead.querySelectorAll('th')).forEach(th => {
                                    th.style.backgroundColor = tableHeaderBg;
                                    th.style.color = textColorPrimary;
                                    th.style.border = `1px solid ${tableBorderColor}`;
                                    th.style.fontWeight = 'bold';
                                    th.style.padding = '5px';
                                    th.style.verticalAlign = 'middle';
                                    th.style.textAlign = 'center';
                                    const sortButton = th.querySelector('.sort-button');
                                    if (sortButton) {
                                        const buttonText = sortButton.textContent.replace(/\s*[▼▲↕]\s*$/, '').trim();
                                        th.textContent = buttonText;
                                    }
                                });
                            }
                            if (clonedTbody) {
                                clonedTbody.style.display = 'table-row-group';
                                clonedTbody.style.maxHeight = 'none';
                                clonedTbody.style.overflowY = 'visible';
                                clonedTbody.style.height = 'auto';
                                clonedTbody.style.width = '100%';
                                Array.from(clonedTbody.querySelectorAll('tr')).forEach(tr => {
                                    tr.style.display = 'table-row';
                                    Array.from(tr.querySelectorAll('td')).forEach(td => {
                                        td.style.display = 'table-cell';
                                        td.style.color = textColorPrimary;
                                        td.style.border = `1px solid ${tableBorderColor}`;
                                        td.style.padding = '5px';
                                        td.style.verticalAlign = 'middle';
                                        td.style.overflow = 'visible';
                                        td.style.textOverflow = 'clip';
                                        td.style.whiteSpace = 'normal';
                                        if (td.classList.contains('number-cell')) {
                                            td.style.textAlign = 'right';
                                        } else {
                                            td.style.textAlign = 'left';
                                        }
                                    });
                                });
                            }
                            if (clonedTfoot) {
                                clonedTfoot.style.display = 'table-footer-group';
                                clonedTfoot.style.width = '100%';
                                clonedTfoot.style.position = 'static';
                                const clonedTfootTr = clonedTfoot.querySelector('tr');
                                if(clonedTfootTr){
                                    clonedTfootTr.style.display = 'table-row';
                                    Array.from(clonedTfootTr.querySelectorAll('td')).forEach(td => {
                                        td.style.color = textColorPrimary;
                                        td.style.border = `1px solid ${tableBorderColor}`;
                                        td.style.fontWeight = 'bold';
                                        td.style.padding = '5px';
                                        td.style.verticalAlign = 'middle';
                                        if (td.classList.contains('number-cell')) {
                                            td.style.textAlign = 'right';
                                        } else {
                                            td.style.textAlign = 'left';
                                        }
                                    });
                                }
                            }
                        }
                    } else if (isChartContainer) {
                        const clonedTitle = clonedCapturedElement.querySelector('#chart-title-dynamic');
                        if (clonedTitle) {
                             clonedTitle.style.color = lightThemeVarsConfig['--text-color-primary'];
                        }
                        clonedCapturedElement.style.height = 'auto';
                        clonedCapturedElement.style.minHeight = '0';
                        clonedCapturedElement.style.maxHeight = 'none';
                        clonedCapturedElement.style.overflow = 'visible';
                    }
                }
            }).then(canvas => {
                const image = canvas.toDataURL('image/png', 1.0);
                const link = document.createElement('a');
                link.href = image;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                hideLoading();
            }).catch(err => {
                console.error('Error capturing element for download:', err);
                alert('An error occurred while trying to download the image.');
                hideLoading();
            });
        }

        function handleTableSort(e, tableId, sortState, updateFunction) {
            e.stopPropagation();
            const button = e.currentTarget;
            const sortCol = button.dataset.sortCol;

            if (sortState.column === sortCol) {
                sortState.direction = sortState.direction === 'desc' ? 'asc' : 'desc';
            } else {
                sortState.column = sortCol;
                sortState.direction = 'asc';
            }
            updateFunction();
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
            startDateSelect.value = dateRanges.default_start_date_iso || todayISO;
            endDateSelect.value = dateRanges.default_end_date_iso || todayISO;

            [startDateSelect, endDateSelect].forEach(el => el.addEventListener('change', handleFilterChange));

            document.getElementById('reset-all-filters').addEventListener('click', () => {
                startDateSelect.value = dateRanges.default_start_date_iso || todayISO;
                endDateSelect.value = dateRanges.default_end_date_iso || todayISO;
                ['brand-filter-list', 'code-cmmt-filter-list', 'city-filter-list'].forEach(contentId => {
                    const dropdownContentElement = document.getElementById(contentId);
                    if (dropdownContentElement) {
                        const container = dropdownContentElement.querySelector('.checkbox-list-container');
                        if (container) {
                            container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                 cb.checked = false;
                            });
                        }
                        dropdownContentElement.style.display = 'none';
                        dropdownContentElement.classList.remove('active');
                        const trigger = document.querySelector(`[data-controls="${contentId}"]`);
                        if (trigger) trigger.setAttribute('aria-expanded', 'false');
                    }
                });
                handleFilterChange();
            });

            document.querySelectorAll('.custom-dropdown-trigger').forEach(trigger => {
                trigger.addEventListener('click', function(event) {
                    event.stopPropagation();
                    const targetId = this.dataset.controls;
                    const content = document.getElementById(targetId);
                    if (content) {
                        openDropdown(this, content);
                    }
                });
            });

            backToWorldBtnDynamic = document.getElementById('back-to-world-btn-dynamic');
            if (backToWorldBtnDynamic) backToWorldBtnDynamic.addEventListener('click', (e) => { e.preventDefault(); switchToView('world'); });
            else console.error("back-to-world-btn-dynamic element not found.");

            document.querySelectorAll('#super-region-stats-table th button.sort-button').forEach(button => {
                button.addEventListener('click', (e) => handleTableSort(e, 'super-region-stats-table', currentSuperRegionSort, updateSuperRegionStatsTable));
            });
            document.querySelectorAll('#indonesia-zone-summary-table th button.sort-button').forEach(button => {
                button.addEventListener('click', (e) => handleTableSort(e, 'indonesia-zone-summary-table', currentZoneSort, updateIndonesiaZoneSummaryTable));
            });
            document.querySelectorAll('#international-stats-table th button.sort-button').forEach(button => {
                button.addEventListener('click', (e) => handleTableSort(e, 'international-stats-table', currentInternationalSort, updateInternationalStatsTable));
            });

            const zoneTableContainerElement = document.getElementById('indonesia-zone-summary-container');
            if(zoneTableContainerElement) {
                document.addEventListener('click', (event) => {
                    if (zoneTableContainerElement && !zoneTableContainerElement.contains(event.target)) {
                        if (currentZoneSort.column !== 'dispatch' || currentZoneSort.direction !== 'asc') {
                            let inDropdownTrigger = false;
                            document.querySelectorAll('.custom-dropdown-trigger').forEach(trigger => {
                                if (trigger.contains(event.target)) inDropdownTrigger = true;
                            });
                            const openDropdownContent = document.querySelector('.custom-dropdown-content.active');
                            if (inDropdownTrigger || (openDropdownContent && openDropdownContent.contains(event.target))) return;

                            currentZoneSort = { column: 'dispatch', direction: 'asc' };
                            updateIndonesiaZoneSummaryTable();
                        }
                    }
                }, true);
            }

            document.querySelectorAll('.download-button').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.dataset.targetId;
                    const filename = this.dataset.filename;
                    downloadElementAsPNG(targetId, filename);
                });
            });
        }

        function updateUIVisibilityBasedOnView(viewType) {
            if (internationalStatsContainer) internationalStatsContainer.style.display = (viewType === 'world') ? 'block' : 'none';
            if (backToWorldBtnDynamic) backToWorldBtnDynamic.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            if (leftColContainer) leftColContainer.style.display = (viewType === 'indonesia' || viewType === 'world') ? 'flex' : 'none';
            if (superRegionContainer) superRegionContainer.style.display = 'block';
            if (indonesiaZoneSummaryContainer) indonesiaZoneSummaryContainer.style.display = (viewType === 'indonesia') ? 'block' : 'none';
            if (indonesiaLegendContainer) indonesiaLegendContainer.style.display = (viewType === 'indonesia') ? 'block' : 'none';
        }


        function switchToView(viewType) {
            infoTooltipGlobalDiv.style.display = 'none'; salesTooltipIndonesiaDiv.style.display = 'none';
            showLoading(viewType === 'world' ? 'Memuat Peta Dunia...' : 'Memuat Peta Indonesia...');
            currentMapView = viewType;
            if (cityMarkersLayerGroup) { cityMarkersLayerGroup.clearLayers(); if (map.hasLayer(cityMarkersLayerGroup)) map.removeLayer(cityMarkersLayerGroup); }
            superRegionPolygonLayers = {};
            updateUIVisibilityBasedOnView(currentMapView);
            handleFilterChange();
        }

        async function loadAndDisplayMapData(url, viewType, cacheKey = null) {
            let topoData;
            if (cacheKey) { try { const cached = localStorage.getItem(cacheKey); if (cached) topoData = JSON.parse(cached); } catch (e) { console.error('Cache read error:', e); localStorage.removeItem(cacheKey); } }
            if (!topoData) {
                try {
                    showLoading(`Mengunduh data peta ${viewType}...`);
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status} for ${url}`);
                    topoData = await response.json();
                    if (cacheKey) { try { const dataString = JSON.stringify(topoData); if (new TextEncoder().encode(dataString).length / (1024 * 1024) < MAX_CACHE_SIZE_MB) localStorage.setItem(cacheKey, dataString); else console.warn(`${viewType} map data too large to cache.`); } catch (e) { console.error('Cache write error:', e); } }
                } catch (error) {
                    console.error(`Gagal memuat TopoJSON ${viewType}:`, error); alert(`Gagal memuat peta ${viewType}. Error: ${error.message}`); hideLoading();
                    if (viewType === 'indonesia' && currentMapView === 'indonesia') { currentMapView = 'world'; switchToView('world'); }
                    return;
                }
            }
            await processTopoJSONAndRender(topoData, viewType);
        }

        async function processTopoJSONAndRender(topojsonInputData, viewType) {
            infoTooltipGlobalDiv.style.display = 'none'; salesTooltipIndonesiaDiv.style.display = 'none';
            showLoading(`Memproses data peta ${viewType}...`);
            if (!topojsonInputData || typeof topojsonInputData.objects !== 'object' || Object.keys(topojsonInputData.objects).length === 0) { console.error("Invalid TopoJSON structure", topojsonInputData); alert("Gagal memproses peta: Struktur tidak valid."); hideLoading(); return; }
            let objectName = Object.keys(topojsonInputData.objects).find(key => key.toLowerCase().includes('countries') || key.toLowerCase().includes('states') || key.toLowerCase().includes('provinces') || key.toLowerCase().includes('adm2') || key.toLowerCase().includes('adm') || key.toLowerCase().includes('boundaries')) || Object.keys(topojsonInputData.objects)[0];
            if (!topojsonInputData.objects[objectName]) { console.error(`TopoJSON object '${objectName}' not found.`); alert(`Gagal memproses peta: objek '${objectName}' tidak ditemukan.`); hideLoading(); return; }
            const geojson = window.topojson.feature(topojsonInputData, topojsonInputData.objects[objectName]);
            if (!geojson || !geojson.features) { console.error("Failed to convert TopoJSON or no features.", geojson); alert("Gagal konversi data peta."); hideLoading(); return; }

            superRegionPolygonLayers = {};
            if (viewType === 'world') {
                calculateWorldMinMaxSales();
            } else if (viewType === 'indonesia') {
                seedRegionData = []; featureCentroidCache.clear();
                geojson.features.forEach(feature => {
                    const cleanedName = getCleanedShapeNameFromProps(feature.properties);
                    const superRegionKeyFromMap = cityToSuperRegionMap[cleanedName];
                    if (superRegionKeyFromMap) {
                        const centroid = getFeatureCentroid(feature.geometry);
                        if (centroid) seedRegionData.push({ centroid, superRegionName: superRegionKeyFromMap });
                        feature.properties.superRegionKey = superRegionKeyFromMap;
                    }
                });
                await calculateNearestSuperRegionsAsync(geojson.features);
            }

            if (geoLayer) map.removeLayer(geoLayer);
            geoLayer = L.geoJSON(geojson, { style: styleFeatureMap, onEachFeature: onEachFeatureMap }).addTo(map);

            if (viewType === 'world') {
                map.options.minZoom = WORLD_MIN_ZOOM;
                map.options.maxZoom = WORLD_MAX_ZOOM;
                map.setView(INDIA_CENTER, WORLD_DEFAULT_ZOOM_LEVEL);
                map.setMaxBounds(null);
            } else if (viewType === 'indonesia') {
                map.options.minZoom = INDONESIA_MIN_ZOOM;
                map.options.maxZoom = INDONESIA_MAX_ZOOM;
                const bounds = geoLayer.getBounds();

                if (bounds.isValid()) {
                    map.fitBounds(bounds.pad(0.05));
                    map.setMaxBounds(bounds.pad(0.2));

                    if (previousIndonesiaZoom !== null &&
                        previousIndonesiaZoom >= INDONESIA_MIN_ZOOM &&
                        previousIndonesiaZoom <= INDONESIA_MAX_ZOOM) {
                        map.setZoom(previousIndonesiaZoom);
                    } else {
                        let zoomAfterFit = map.getZoom();
                        if (zoomAfterFit < INDONESIA_DEFAULT_ZOOM_LEVEL) {
                            map.setZoom(INDONESIA_DEFAULT_ZOOM_LEVEL);
                        }
                    }
                } else {
                    map.setView([-2.5, 118], INDONESIA_DEFAULT_ZOOM_LEVEL);
                    map.setMaxBounds(null);
                }
            }
            hideLoading();
        }

        function getCleanedShapeNameFromProps(properties) { if (!properties) return ""; const potentialNameProps = ['shapeName', 'NAME_2', 'NAME_1', 'name', 'ADM2_EN', 'kabkot', 'ADMIN', 'NAME', 'name_long', 'formal_en', 'COUNTRY', 'NAME_EN', 'NAME_0']; for (const prop of potentialNameProps) { if (properties[prop]) return String(properties[prop]).toLowerCase().trim(); } return "";}
        function getFeatureCentroid(geometry) { if (!geometry || !geometry.coordinates) return null; const cacheKey = geometry.type + JSON.stringify(geometry.coordinates.slice(0,1)); if (featureCentroidCache.has(cacheKey)) return featureCentroidCache.get(cacheKey); let centroid; try { if (geometry.type === 'Polygon') { const coords = geometry.coordinates[0]; let x=0,y=0,count=0; coords.forEach(c => { if (c && c.length === 2) { x += c[0]; y += c[1]; count++; }}); centroid = count > 0 ? [y / count, x / count] : null; } else if (geometry.type === 'MultiPolygon') { let largestPolygonArea = 0, largestPolygonCentroid = null; geometry.coordinates.forEach(polygonCoords => { const coords = polygonCoords[0]; let x=0,y=0,count=0,currentArea=0; for (let i=0; i<coords.length-1; i++) { if (coords[i] && coords[i].length === 2 && coords[i+1] && coords[i+1].length === 2) { x+=coords[i][0]; y+=coords[i][1]; count++; currentArea+=(coords[i][0]*coords[i+1][1] - coords[i+1][0]*coords[i][1]); } } currentArea=Math.abs(currentArea/2); if(count>0 && currentArea > largestPolygonArea){ largestPolygonArea=currentArea; largestPolygonCentroid=[y/count,x/count]; } }); centroid = largestPolygonCentroid; } } catch (error) { console.error("Error calculating centroid:", error, geometry); return null; } if (centroid) featureCentroidCache.set(cacheKey, centroid); return centroid;}

        async function calculateNearestSuperRegionsAsync(features) {
             return new Promise(resolve => { let index = 0; const totalFeatures = features.length; function processBatch() { const batchStartTime = Date.now(); while (index < totalFeatures && (Date.now() - batchStartTime) < CALCULATION_BATCH_SIZE) { const feature = features[index]; if (!feature.properties.superRegionKey && seedRegionData.length > 0) { const featureCentroid = getFeatureCentroid(feature.geometry); if (featureCentroid) { let minDistance = Infinity, nearestSuperRegionInfo = null; seedRegionData.forEach(regionSeed => { const dist = L.latLng(featureCentroid).distanceTo(L.latLng(regionSeed.centroid)); if (dist < minDistance) { minDistance = dist; nearestSuperRegionInfo = { name: regionSeed.superRegionName, distance: dist }; } }); if (nearestSuperRegionInfo) { feature.properties.calculatedSuperRegion = nearestSuperRegionInfo; feature.properties.superRegionKey = nearestSuperRegionInfo.name; } } } index++; } if (index < totalFeatures) { showLoading(`Menghitung region... (${Math.round((index/totalFeatures)*100)}%)`); requestAnimationFrame(processBatch); } else { hideLoading(); resolve(); } } if (totalFeatures > 0) processBatch(); else resolve(); });
        }

        function calculateWorldMinMaxSales() { const salesValues = Object.values(salesDataGlobal).map(data => Number(data.sales) || 0).filter(s => s > 0); if(salesValues.length > 0){ worldMinSales = Math.min(...salesValues); worldMaxSales = Math.max(...salesValues); } else { worldMinSales = 0; worldMaxSales = 1; } const indonesiaData = salesDataGlobal["Indonesia"]; if (indonesiaData) { const indonesiaSales = Number(indonesiaData.sales) || 0; if(indonesiaSales > worldMaxSales) worldMaxSales = indonesiaSales; if(indonesiaSales > 0 && (worldMinSales === 0 || indonesiaSales < worldMinSales) ) { worldMinSales = indonesiaSales; } } if(worldMinSales === 0 && worldMaxSales > 0) worldMinSales = Math.min(1, worldMaxSales / 1000); else if(worldMinSales === 0 && worldMaxSales === 0) { worldMinSales = 0; worldMaxSales = 1; } if (worldMinSales >= worldMaxSales && worldMaxSales > 0) worldMinSales = worldMaxSales / 2; if (worldMaxSales === 0) worldMaxSales = 1;}

        function getWorldFeatureColor(salesAmount) {
            const sales = Number(salesAmount) || 0;
            const isDarkMode = document.body.classList.contains('dark-mode');

            const baseClr = getComputedStyle(document.documentElement).getPropertyValue('--world-feature-base-color').trim() || (isDarkMode ? '#2a3b58' : '#e0e0e0');

            if (sales <= 0) return baseClr;
            if (worldMaxSales <= worldMinSales || worldMaxSales === 0) {
                return tinycolor(baseClr).darken(isDarkMode ? 5 : 10).toString();
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

            const startColorLight = {r:255,g:255,b:204};
            const endColorLight = {r:128,g:0,b:38};

            const startColorDark = {r:50,g:70,b:100};
            const endColorDark = {r:220,g:90,b:90};

            const startColor = isDarkMode ? startColorDark : startColorLight;
            const endColor = isDarkMode ? endColorDark : endColorLight;

            const r = Math.round(startColor.r + (endColor.r - startColor.r) * intensity);
            const g = Math.round(startColor.g + (endColor.g - startColor.g) * intensity);
            const b = Math.round(startColor.b + (endColor.b - startColor.b) * intensity);

            return `rgb(${r},${g},${b})`;
        }


        function styleFeatureMap(feature) {
            const isDarkMode = document.body.classList.contains('dark-mode');
            const defaultBorderColor = isDarkMode ? 'var(--border-color-medium)' : 'white';
            const highlightBorderColor = isDarkMode ? 'var(--text-color-primary)' : '#222';
            const hoverBorderColor = isDarkMode ? 'var(--text-color-secondary)' : '#444';
            const worldBorderColor = isDarkMode ? 'var(--border-color-light)' : '#bbb';

            if (feature.properties.isHighlightedSuperRegion) {
                return {
                    weight: 1.5,
                    color: highlightBorderColor,
                    opacity: 1,
                    fillOpacity: 0.9,
                    fillColor: feature.properties.originalFillColor || (isDarkMode ? getComputedStyle(document.documentElement).getPropertyValue('--map-ui-bg').trim() : regionColors.OTHER_BASE)
                };
            }
            if (feature.properties.isInHoveredSuperRegion) {
                return {
                    weight: 0.8,
                    color: hoverBorderColor,
                    opacity: 0.9,
                    fillOpacity: 0.85,
                    fillColor: feature.properties.originalFillColor || (isDarkMode ? getComputedStyle(document.documentElement).getPropertyValue('--map-ui-bg').trim() : regionColors.OTHER_BASE)
                };
            }

            if (currentMapView === 'world') {
                let name = getCleanedShapeNameFromProps(feature.properties);
                if (name === "united states of america") name = "united states";
                const countryKey = Object.keys(salesDataGlobal).find(k => k.toLowerCase() === name.toLowerCase());
                const countryData = countryKey ? salesDataGlobal[countryKey] : null;
                const sales = countryData ? (countryData.sales || 0) : 0;
                return {
                    fillColor: getWorldFeatureColor(sales),
                    weight: 0.5,
                    opacity: 1,
                    color: worldBorderColor,
                    fillOpacity: sales > 0 ? 0.85 : 0.7
                };
            }
            else if (currentMapView === 'indonesia') {
                let fillColor = isDarkMode ? getComputedStyle(document.documentElement).getPropertyValue('--map-bg').trim() : regionColors.OTHER_BASE;
                let fillOpacity = isDarkMode ? 0.50 : 0.45;
                const effectiveSRKey = feature.properties.superRegionKey;

                if (effectiveSRKey && regionColors[effectiveSRKey]) {
                    let baseRegionColor = tinycolor(regionColors[effectiveSRKey]);
                    if (isDarkMode) {
                        fillColor = baseRegionColor.isLight() ? baseRegionColor.darken(20).desaturate(10).toString()
                                                              : baseRegionColor.lighten(25).desaturate(15).toString();
                    } else {
                        fillColor = regionColors[effectiveSRKey];
                    }

                    const regionData = superRegionSales[effectiveSRKey];
                    if (regionData && regionData.sales > 0) {
                        fillOpacity = isDarkMode ? 0.80 : 0.75;
                    } else {
                        fillOpacity = isDarkMode ? 0.65 : 0.60;
                    }
                }
                feature.properties.originalFillColor = fillColor;
                return {
                    weight: 0.5,
                    opacity: 1,
                    color: defaultBorderColor,
                    fillOpacity: fillOpacity,
                    fillColor: fillColor
                };
            }
            return {
                fillColor: (isDarkMode ? getComputedStyle(document.documentElement).getPropertyValue('--map-ui-bg').trim() : '#ccc'),
                weight: 1,
                opacity: 1,
                color: defaultBorderColor,
                fillOpacity: 0.7
            };
        }


        function onEachFeatureMap(feature, layer) {
            const isDarkMode = document.body.classList.contains('dark-mode');
            const styles = getComputedStyle(document.documentElement);
            const worldHighlightWeight = 1.5;
            const worldHighlightColor = styles.getPropertyValue('--text-color-secondary').trim();
            const indonesiaHighlightWeight = 2;
            const indonesiaHighlightColor = styles.getPropertyValue('--text-color-labels').trim();


            if (currentMapView === 'indonesia') { const srk = feature.properties.superRegionKey; if (srk) { if (!superRegionPolygonLayers[srk]) superRegionPolygonLayers[srk] = []; superRegionPolygonLayers[srk].push(layer); } }
            if (currentMapView === 'world') { layer.on({ mouseover: (e)=>{ let p=e.target.feature.properties; let name=getCleanedShapeNameFromProps(p); if(name==="united states of america") name="united states"; const countryKey = Object.keys(salesDataGlobal).find(k => k.toLowerCase() === name.toLowerCase()); const countryData = countryKey ? salesDataGlobal[countryKey] : null; const sales = countryData ? (countryData.sales || 0) : 0; const budget = countryData ? (countryData.budget || 0) : 0; const lastYearSales = countryData ? (countryData.lastYearSales || 0) : 0; const displayName = countryKey || name.split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' '); let tooltipText = `<strong>${displayName}</strong>`; tooltipText += `<br>Sales: ${sales.toLocaleString(undefined, {maximumFractionDigits:0})} Ton`; if (budget > 0) tooltipText += `<br>Budget: ${budget.toLocaleString(undefined, {maximumFractionDigits:0})} Ton`; if (lastYearSales > 0) tooltipText += `<br>Sales LY: ${lastYearSales.toLocaleString(undefined, {maximumFractionDigits:0})} Ton`; infoTooltipGlobalDiv.innerHTML = tooltipText; infoTooltipGlobalDiv.style.display='block'; e.target.setStyle({weight:worldHighlightWeight,color:worldHighlightColor,fillOpacity: 0.9}); }, mouseout: (e)=>{ infoTooltipGlobalDiv.style.display='none'; if (geoLayer) geoLayer.resetStyle(e.target); }, click: (e)=>{ const p=e.target.feature.properties; const n=getCleanedShapeNameFromProps(p); if(n==='indonesia') switchToView('indonesia'); } }); }
            else if (currentMapView === 'indonesia') { layer.on({ mouseover: (e) => { const hoveredLayer = e.target; const p = hoveredLayer.feature.properties; const cN = getCleanedShapeNameFromProps(p); const sRK = p.superRegionKey; const dNKK = cN.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' '); let displayNameSuperRegion = sRK ? formatRegionKeyForDisplay(sRK) : 'Tidak terpetakan'; let tT = `<strong>${dNKK}</strong><br>Super Region: ${displayNameSuperRegion}`; if (sRK && superRegionSales[sRK]) { const srData = superRegionSales[sRK]; tT += `<br>Total Sales (Region): ${(srData.sales || 0).toLocaleString(undefined, {maximumFractionDigits:0})} Ton`; } if (p.calculatedSuperRegion && !cityToSuperRegionMap[cN.toLowerCase().trim()]) tT += `<br><small>(Estimasi via kedekatan)</small>`; salesTooltipIndonesiaDiv.innerHTML = tT; salesTooltipIndonesiaDiv.style.display = 'block'; if (sRK && superRegionPolygonLayers[sRK]) { superRegionPolygonLayers[sRK].forEach(l => { l.feature.properties.isInHoveredSuperRegion = true; if (l === hoveredLayer) l.feature.properties.isHighlightedSuperRegion = true; l.setStyle(styleFeatureMap(l.feature)); if (l !== hoveredLayer) l.bringToFront(); }); hoveredLayer.bringToFront(); } else { hoveredLayer.setStyle({ weight: indonesiaHighlightWeight, color: indonesiaHighlightColor, fillOpacity: 0.95 }); } }, mouseout: (e) => { salesTooltipIndonesiaDiv.style.display = 'none'; const hoveredLayer = e.target; const sRK = hoveredLayer.feature.properties.superRegionKey; if (sRK && superRegionPolygonLayers[sRK]) { superRegionPolygonLayers[sRK].forEach(l => { delete l.feature.properties.isHighlightedSuperRegion; delete l.feature.properties.isInHoveredSuperRegion; geoLayer.resetStyle(l); }); } else { geoLayer.resetStyle(hoveredLayer); } } }); }
        }

        function formatRegionKeyForDisplay(regionKey) { if (!regionKey) return 'N/A'; return String(regionKey).replace(/([A-Z]+)([0-9]+[A-Z]*)/g, '$1 $2').replace(/([A-Z])([A-Z]+)/g, (match, p1, p2) => p1 + p2.toLowerCase()).replace(/\b(Keyaccount|Commercial)\b/gi, m => m.charAt(0).toUpperCase() + m.slice(1).toLowerCase());}

        function updateDashboardPanels() {
            updateSuperRegionStatsTable();
            updateIndonesiaZoneSummaryTable();
            updateInternationalStatsTable();
            updateSalesChart();
            updateLegend();
        }

        function adjustTableHeadersAndFooters(tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const tbody = table.querySelector('tbody');
            const thead = table.querySelector('thead');
            const tfootTr = table.querySelector('tfoot tr');

            if (!tbody || !thead || !tfootTr) return;

            const hasScrollbar = tbody.scrollHeight > tbody.clientHeight;
            const scrollbarWidth = hasScrollbar ? (tbody.offsetWidth - tbody.clientWidth) : 0;

            if (hasScrollbar) {
                thead.style.width = `calc(100% - ${scrollbarWidth}px)`;
                tfootTr.style.width = `calc(100% - ${scrollbarWidth}px)`;
            } else {
                thead.style.width = '100%';
                tfootTr.style.width = '100%';
            }
        }

        function updateIndonesiaZoneSummaryTable() {
            const tableBody = document.querySelector('#indonesia-zone-summary-table tbody');
            const tfootRow = document.querySelector('#indonesia-zone-summary-table tfoot tr');
            if (!tableBody || !tfootRow) return;

            tableBody.innerHTML = '';
            Array.from(tfootRow.cells).forEach(cell => cell.textContent = '');

            document.querySelectorAll('#indonesia-zone-summary-table th button.sort-button').forEach(btn => {
                const arrowSpan = btn.querySelector('.sort-arrow');
                btn.classList.remove('active-sort');
                arrowSpan.textContent = '↕';
                if (btn.dataset.sortCol === currentZoneSort.column) {
                    btn.classList.add('active-sort');
                    arrowSpan.textContent = currentZoneSort.direction === 'desc' ? '▼' : '▲';
                }
            });

            if (currentMapView !== 'indonesia') {
                if (indonesiaZoneSummaryContainer) indonesiaZoneSummaryContainer.style.display = 'none';
                adjustTableHeadersAndFooters('indonesia-zone-summary-table');
                return;
            }
            if (indonesiaZoneSummaryContainer) indonesiaZoneSummaryContainer.style.display = 'block';


            const zoneData = {
                "WEST ZONE": { sales: 0, budget: 0, lastYearSales: 0, sales_value: 0, margin_value: 0 },
                "EAST ZONE": { sales: 0, budget: 0, lastYearSales: 0, sales_value: 0, margin_value: 0 }
            };
            for (const zoneName in indonesiaZoneDefinitions) {
                indonesiaZoneDefinitions[zoneName].forEach(regionKey => {
                    const region = superRegionSales[regionKey];
                    if (region) {
                        zoneData[zoneName].sales += (region.sales || 0);
                        zoneData[zoneName].budget += (region.budget || 0);
                        zoneData[zoneName].lastYearSales += (region.lastYearSales || 0);
                        zoneData[zoneName].sales_value += (region.sales_value || 0);
                        zoneData[zoneName].margin_value += (region.margin_value || 0);
                    }
                });
            }
            const keyAccountData = superRegionSales["KEYACCOUNT"] || { sales: 0, budget: 0, lastYearSales: 0, sales_value: 0, margin_value: 0 };
            const commercialData = superRegionSales["COMMERCIAL"] || { sales: 0, budget: 0, lastYearSales: 0, sales_value: 0, margin_value: 0 };
            let totalExportSales = 0, totalExportBudget = 0, totalExportLYSales = 0, totalExportSalesValue = 0, totalExportMarginValue = 0;
            Object.entries(salesDataGlobal).forEach(([country, data]) => {
                if (country.toLowerCase() !== 'indonesia') {
                    totalExportSales += (data.sales || 0); totalExportBudget += (data.budget || 0); totalExportLYSales += (data.lastYearSales || 0); totalExportSalesValue += (data.sales_value || 0); totalExportMarginValue += (data.margin_value || 0);
                }
            });
            const exportData = { sales: totalExportSales, budget: totalExportBudget, lastYearSales: totalExportLYSales, sales_value: totalExportSalesValue, margin_value: totalExportMarginValue };

            let dataRows = [
                { name: "WEST ZONE", data: zoneData["WEST ZONE"] },
                { name: "EAST ZONE", data: zoneData["EAST ZONE"] },
                { name: "KEY ACCOUNT", data: keyAccountData },
                { name: "COMMERCIAL", data: commercialData },
                { name: "TOTAL EXPORT", data: exportData }
            ].map(item => ({
                ...item,
                achievePercent: (item.data.budget || 0) > 0 ? ((item.data.sales || 0) / (item.data.budget || 1) * 100) : ((item.data.sales || 0) > 0 ? 100 : 0),
                marginPercent: (item.data.sales_value || 0) > 0 ? ((item.data.margin_value || 0) / (item.data.sales_value || 1) * 100) : 0,
            }));


            dataRows.sort((a, b) => {
                let valA, valB;
                switch (currentZoneSort.column) {
                    case 'zone': valA = a.name; valB = b.name; return currentZoneSort.direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
                    case 'budget': valA = a.data.budget || 0; valB = b.data.budget || 0; break;
                    case 'dispatch': valA = a.data.sales || 0; valB = b.data.sales || 0; break;
                    case 'achieve': valA = a.achievePercent; valB = b.achievePercent; break;
                    case 'lastyear': valA = a.data.lastYearSales || 0; valB = b.data.lastYearSales || 0; break;
                    case 'margin': valA = a.marginPercent; valB = b.marginPercent; break;
                    default: return 0;
                }
                return currentZoneSort.direction === 'asc' ? valA - valB : valB - valA;
            });

            let grandTotalDispatch = 0, grandTotalBudget = 0, grandTotalLYDispatch = 0, grandTotalSalesValue = 0, grandTotalMarginValue = 0;
            dataRows.forEach(item => {
                const row = tableBody.insertRow();
                row.insertCell().textContent = item.name; row.cells[0].classList.add('col-zone');
                row.insertCell().textContent = (item.data.budget || 0).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[1].classList.add('number-cell', 'col-budget');
                row.insertCell().textContent = (item.data.sales || 0).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[2].classList.add('number-cell', 'col-dispatch');
                row.insertCell().textContent = item.achievePercent.toFixed(1) + '%'; row.cells[3].classList.add('number-cell', 'col-achieve');
                row.insertCell().textContent = (item.data.lastYearSales || 0).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[4].classList.add('number-cell', 'col-lastyear');
                row.insertCell().textContent = item.marginPercent.toFixed(1) + '%'; row.cells[5].classList.add('number-cell', 'col-margin-percent');

                const dispatchValue = item.data.sales || 0;
                const targetRowsForHighlight = ["WEST ZONE", "EAST ZONE", "TOTAL EXPORT","KEY ACCOUNT","COMMERCIAL"];

                if (targetRowsForHighlight.includes(item.name) && dispatchValue < 40) {
                    row.classList.add('highlight-low-dispatch');
                } else {
                    row.classList.remove('highlight-low-dispatch');
                }

                if (item.name !== "TOTAL EXPORT") {
                    grandTotalDispatch += (item.data.sales || 0); grandTotalBudget += (item.data.budget || 0); grandTotalLYDispatch += (item.data.lastYearSales || 0); grandTotalSalesValue += (item.data.sales_value || 0); grandTotalMarginValue += (item.data.margin_value || 0);
                }
            });
            if (grandTotalDispatch > 0 || grandTotalBudget > 0 || grandTotalLYDispatch > 0 || grandTotalSalesValue > 0) {
                const grandTotalAchievement = grandTotalBudget > 0 ? (grandTotalDispatch / grandTotalBudget * 100) : (grandTotalDispatch > 0 ? 100 : 0);
                const grandTotalMarginPercent = grandTotalSalesValue > 0 ? (grandTotalMarginValue / grandTotalSalesValue * 100) : 0;
                tfootRow.cells[0].textContent = "TOTAL"; tfootRow.cells[0].style.fontWeight = "bold";
                tfootRow.cells[1].textContent = grandTotalBudget.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[1].style.fontWeight = "bold";
                tfootRow.cells[2].textContent = grandTotalDispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[2].style.fontWeight = "bold";
                tfootRow.cells[3].textContent = grandTotalAchievement.toFixed(1) + '%'; tfootRow.cells[3].style.fontWeight = "bold";
                tfootRow.cells[4].textContent = grandTotalLYDispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[4].style.fontWeight = "bold";
                tfootRow.cells[5].textContent = grandTotalMarginPercent.toFixed(1) + '%'; tfootRow.cells[5].style.fontWeight = "bold";
            } else if (dataRows.length > 0 && dataRows.some(item => item.name === "TOTAL EXPORT" && item.data.sales > 0)) {
                 tfootRow.cells[0].textContent = "";
            } else {
                 tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No summary data for current filters.</td></tr>';
            }
            adjustTableHeadersAndFooters('indonesia-zone-summary-table');
        }


        function updateSuperRegionStatsTable() {
            const tableBody = document.querySelector('#super-region-stats-table tbody');
            const tfootRow = document.querySelector('#super-region-stats-table tfoot tr');
            if (!tableBody || !tfootRow) return;
            tableBody.innerHTML = '';
            Array.from(tfootRow.cells).forEach(cell => cell.textContent = '');

            document.querySelectorAll('#super-region-stats-table th button.sort-button').forEach(btn => {
                const arrowSpan = btn.querySelector('.sort-arrow');
                btn.classList.remove('active-sort');
                arrowSpan.textContent = '↕';
                if (btn.dataset.sortCol === currentSuperRegionSort.column) {
                    btn.classList.add('active-sort');
                    arrowSpan.textContent = currentSuperRegionSort.direction === 'desc' ? '▼' : '▲';
                }
            });

            let regionEntries = Object.entries(superRegionSales)
                .filter(([key]) => key !== "KEYACCOUNT" && key !== "COMMERCIAL")
                .map(([key, data]) => ({
                    key,
                    ...data,
                    regionDisplay: formatRegionKeyForDisplay(key),
                    achievePercent: (data.budget || 0) > 0 ? ((data.sales || 0) / (data.budget || 1) * 100) : ((data.sales || 0) > 0 ? 100 : 0),
                    marginPercent: (data.sales_value || 0) > 0 ? ((data.margin_value || 0) / (data.sales_value || 1) * 100) : 0
                }));

            regionEntries.sort((a, b) => {
                let valA, valB;
                switch (currentSuperRegionSort.column) {
                    case 'region': valA = a.regionDisplay; valB = b.regionDisplay; return currentSuperRegionSort.direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
                    case 'budget': valA = a.budget || 0; valB = b.budget || 0; break;
                    case 'dispatch': valA = a.sales || 0; valB = b.sales || 0; break;
                    case 'achieve': valA = a.achievePercent; valB = b.achievePercent; break;
                    case 'lastyear': valA = a.lastYearSales || 0; valB = b.lastYearSales || 0; break;
                    case 'margin': valA = a.marginPercent; valB = b.marginPercent; break;
                    default: return 0;
                }
                return currentSuperRegionSort.direction === 'asc' ? valA - valB : valB - valA;
            });

            let totalDispatch = 0, totalBudget = 0, totalLastYearDispatch = 0, totalSalesValueForMarginCalc = 0, grandTotalMarginValue = 0;
            regionEntries.forEach(regionEntry => {
                if (regionEntry.sales === 0 && regionEntry.budget === 0 && regionEntry.lastYearSales === 0 && regionEntry.margin_value === 0 && regionEntry.sales_value === 0) {
                    return;
                }
                totalDispatch += (regionEntry.sales || 0); totalBudget += (regionEntry.budget || 0); totalLastYearDispatch += (regionEntry.lastYearSales || 0); grandTotalMarginValue += (regionEntry.margin_value || 0); totalSalesValueForMarginCalc += (regionEntry.sales_value || 0);
                const row = tableBody.insertRow();
                row.insertCell().textContent = regionEntry.regionDisplay; row.cells[0].classList.add('col-region');
                row.insertCell().textContent = (regionEntry.budget || 0).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[1].classList.add('number-cell', 'col-budget');
                row.insertCell().textContent = (regionEntry.sales || 0).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[2].classList.add('number-cell', 'col-dispatch');
                row.insertCell().textContent = regionEntry.achievePercent.toFixed(1) + '%'; row.cells[3].classList.add('number-cell', 'col-achieve');
                row.insertCell().textContent = (regionEntry.lastYearSales || 0).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[4].classList.add('number-cell', 'col-lastyear');
                row.insertCell().textContent = regionEntry.marginPercent.toFixed(1) + '%'; row.cells[5].classList.add('number-cell', 'col-margin-percent');

                const dispatchValue = regionEntry.sales || 0;
                if (dispatchValue < 40) {
                    row.classList.add('highlight-low-dispatch');
                } else {
                    row.classList.remove('highlight-low-dispatch');
                }
            });

            if (tableBody.rows.length === 0) {
                if ( (Object.keys(superRegionSales).length > 0 && Object.values(superRegionSales).some(d => d.sales > 0 || d.budget > 0)) || currentMapView === 'indonesia' ) {
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No Indonesia region sales data for current filters.</td></tr>';
                } else if (currentMapView === 'world') {
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Indonesia region data not applicable or no sales.</td></tr>';
                }
            } else if (totalDispatch > 0 || totalBudget > 0 || totalLastYearDispatch > 0 || totalSalesValueForMarginCalc > 0) {
                const totalAchievement = totalBudget > 0 ? (totalDispatch / totalBudget * 100) : (totalDispatch > 0 ? 100 : 0);
                const totalMarginPercent = totalSalesValueForMarginCalc > 0 ? (grandTotalMarginValue / totalSalesValueForMarginCalc * 100) : 0;
                tfootRow.cells[0].textContent = "TOTAL REGIONS (ID)"; tfootRow.cells[0].style.fontWeight = "bold";
                tfootRow.cells[1].textContent = totalBudget.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[1].style.fontWeight = "bold";
                tfootRow.cells[2].textContent = totalDispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[2].style.fontWeight = "bold";
                tfootRow.cells[3].textContent = totalAchievement.toFixed(1) + '%'; tfootRow.cells[3].style.fontWeight = "bold";
                tfootRow.cells[4].textContent = totalLastYearDispatch.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[4].style.fontWeight = "bold";
                tfootRow.cells[5].textContent = totalMarginPercent.toFixed(1) + '%'; tfootRow.cells[5].style.fontWeight = "bold";
            }
            adjustTableHeadersAndFooters('super-region-stats-table');
        }


        function updateInternationalStatsTable() {
            const tableBody = document.querySelector('#international-stats-table tbody');
            const tfootRow = document.querySelector('#international-stats-table tfoot tr');
            if(!tableBody || !tfootRow) return;
            tableBody.innerHTML = '';
            Array.from(tfootRow.cells).forEach(cell => cell.textContent = '');

            document.querySelectorAll('#international-stats-table th button.sort-button').forEach(btn => {
                const arrowSpan = btn.querySelector('.sort-arrow');
                btn.classList.remove('active-sort');
                arrowSpan.textContent = '↕';
                if (btn.dataset.sortCol === currentInternationalSort.column) {
                    btn.classList.add('active-sort');
                    arrowSpan.textContent = currentInternationalSort.direction === 'desc' ? '▼' : '▲';
                }
            });

            if (currentMapView !== 'world' || Object.keys(salesDataGlobal).length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No export data for world view or current filters.</td></tr>';
                adjustTableHeadersAndFooters('international-stats-table');
                return;
            }

            let exportCountriesData = Object.entries(salesDataGlobal)
                .filter(([country, data]) => country.toLowerCase() !== 'indonesia' &&
                    ((data.sales || 0) > 0 || (data.budget || 0) > 0 || (data.lastYearSales || 0) > 0 || (data.margin_value || 0) > 0  || (data.sales_value || 0) > 0)
                )
                .map(([country, data]) => ({
                    country, ...data,
                    achievePercent: (data.budget || 0) > 0 ? ((data.sales || 0) / (data.budget || 1) * 100) : ((data.sales || 0) > 0 ? 100: 0),
                    marginPercent: (data.sales_value || 0) > 0 ? ((data.margin_value || 0) / (data.sales_value || 1) * 100) : 0
                }));

            exportCountriesData.sort((a, b) => {
                let valA, valB;
                switch(currentInternationalSort.column) {
                    case 'country': valA = a.country; valB = b.country; return currentInternationalSort.direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
                    case 'sales': valA = a.sales || 0; valB = b.sales || 0; break;
                    case 'budget': valA = a.budget || 0; valB = b.budget || 0; break;
                    case 'achieve': valA = a.achievePercent; valB = b.achievePercent; break;
                    case 'lastyear': valA = a.lastYearSales || 0; valB = b.lastYearSales || 0; break;
                    case 'margin': valA = a.marginPercent; valB = b.marginPercent; break;
                    default: return 0;
                }
                return currentInternationalSort.direction === 'asc' ? valA - valB : valB - valA;
            });


            let dataForTable = [];
            const maxCountriesInTable = 7;
            let otherSalesSum = 0, otherBudgetSum = 0, otherLYSalesSum = 0, otherMarginValueSum = 0, otherSalesValueForMarginCalcSum = 0;

            exportCountriesData.forEach((item, index) => {
                if (index < maxCountriesInTable || exportCountriesData.length <= maxCountriesInTable) {
                    dataForTable.push(item);
                } else if (index === maxCountriesInTable && exportCountriesData.length > maxCountriesInTable) {
                    otherSalesSum += (item.sales || 0); otherBudgetSum += (item.budget || 0); otherLYSalesSum += (item.lastYearSales || 0); otherMarginValueSum += (item.margin_value || 0); otherSalesValueForMarginCalcSum += (item.sales_value || 0);
                } else if (index > maxCountriesInTable) {
                    otherSalesSum += (item.sales || 0); otherBudgetSum += (item.budget || 0); otherLYSalesSum += (item.lastYearSales || 0); otherMarginValueSum += (item.margin_value || 0); otherSalesValueForMarginCalcSum += (item.sales_value || 0);
                }
            });

            if (exportCountriesData.length > maxCountriesInTable && (otherSalesSum > 0 || otherBudgetSum > 0 || otherLYSalesSum > 0)) {
                dataForTable.splice(maxCountriesInTable);
                dataForTable.push({
                    country: "Other Exports",
                    sales: otherSalesSum, budget: otherBudgetSum, lastYearSales: otherLYSalesSum,
                    margin_value: otherMarginValueSum, sales_value: otherSalesValueForMarginCalcSum,
                    achievePercent: otherBudgetSum > 0 ? (otherSalesSum / otherBudgetSum * 100) : (otherSalesSum > 0 ? 100 : 0),
                    marginPercent: otherSalesValueForMarginCalcSum > 0 ? (otherMarginValueSum / otherSalesValueForMarginCalcSum * 100) : 0
                });
            }


            if (dataForTable.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No significant export sales for current filters.</td></tr>';
                adjustTableHeadersAndFooters('international-stats-table');
                return;
            }

            let totalSalesFooter = 0, totalBudgetFooter = 0, totalLYSalesFooter = 0, totalSalesValueForMarginCalcFooter = 0, grandTotalMarginValueExport = 0;
            dataForTable.forEach(item => {
                const row = tableBody.insertRow();
                row.insertCell().textContent = item.country; row.cells[0].classList.add('col-country');
                row.insertCell().textContent = (item.sales || 0).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[1].classList.add('number-cell','col-sales');
                row.insertCell().textContent = (item.budget || 0).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[2].classList.add('number-cell','col-budget');
                row.insertCell().textContent = item.achievePercent.toFixed(1) + '%'; row.cells[3].classList.add('number-cell','col-achieve');
                row.insertCell().textContent = (item.lastYearSales || 0).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); row.cells[4].classList.add('number-cell','col-lastyear');
                row.insertCell().textContent = item.marginPercent.toFixed(1) + '%'; row.cells[5].classList.add('number-cell','col-margin-percent');

                const dispatchValue = item.sales || 0;
                if (dispatchValue < 40) {
                    row.classList.add('highlight-low-dispatch');
                } else {
                    row.classList.remove('highlight-low-dispatch');
                }

                totalSalesFooter += (item.sales || 0); totalBudgetFooter += (item.budget || 0); totalLYSalesFooter += (item.lastYearSales || 0); grandTotalMarginValueExport += (item.margin_value || 0); totalSalesValueForMarginCalcFooter += (item.sales_value || 0);
            });

            if (tfootRow && (totalSalesFooter > 0 || totalBudgetFooter > 0 || totalLYSalesFooter > 0 || totalSalesValueForMarginCalcFooter > 0)) {
                const totalAchExport = totalBudgetFooter > 0 ? (totalSalesFooter / totalBudgetFooter * 100) : (totalSalesFooter > 0 ? 100:0);
                const totalMarginPercentExport = totalSalesValueForMarginCalcFooter > 0 ? (grandTotalMarginValueExport / totalSalesValueForMarginCalcFooter * 100) : 0;
                tfootRow.cells[0].textContent = "TOTAL EXPORT"; tfootRow.cells[0].style.fontWeight = "bold";
                tfootRow.cells[1].textContent = totalSalesFooter.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[1].style.fontWeight="bold";
                tfootRow.cells[2].textContent = totalBudgetFooter.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[2].style.fontWeight="bold";
                tfootRow.cells[3].textContent = totalAchExport.toFixed(1) + '%'; tfootRow.cells[3].style.fontWeight="bold";
                tfootRow.cells[4].textContent = totalLYSalesFooter.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); tfootRow.cells[4].style.fontWeight="bold";
                tfootRow.cells[5].textContent = totalMarginPercentExport.toFixed(1) + '%'; tfootRow.cells[5].style.fontWeight="bold";
            } else {
                 tfootRow.cells[0].textContent = "";
            }
            adjustTableHeadersAndFooters('international-stats-table');
        }

        function chartTooltipCallback(context) { let label = context.label || ''; if (label) { label += ': '; } if (context.parsed !== null && typeof context.parsed !== 'undefined') { label += context.parsed.toLocaleString(undefined, {maximumFractionDigits:0}) + ' Ton'; const total = context.dataset.data.reduce((s, v) => s + v, 0); if (total > 0) { const percentage = (context.parsed / total * 100).toFixed(1) + '%'; label += ` (${percentage})`; } } return label;}

        function updateSalesChart() {
            const sCC = document.getElementById('salesChart');
            const chartTitleElement = document.getElementById('chart-title-dynamic');
            if (!sCC || !chartTitleElement) return;

            if (salesPieChart) salesPieChart.destroy();
            const ctx = sCC.getContext('2d');

            const isDarkModeDisplay = document.body.classList.contains('dark-mode');
            const displayChartTextColor = isDarkModeDisplay ? lightThemeVarsConfig['--text-color-primary'] : getComputedStyle(document.documentElement).getPropertyValue('--text-color-primary').trim();
            const displayChartTooltipBgColor = isDarkModeDisplay ? lightThemeVarsConfig['--panel-bg-solid'] : getComputedStyle(document.documentElement).getPropertyValue('--panel-bg-solid').trim();
            const displayChartBorderColor = isDarkModeDisplay ? lightThemeVarsConfig['--map-ui-bg'] : getComputedStyle(document.documentElement).getPropertyValue('--map-ui-bg').trim();
            const displayChartLegendBorderColor = isDarkModeDisplay ? lightThemeVarsConfig['--border-color-light'] : getComputedStyle(document.documentElement).getPropertyValue('--border-color-light').trim();

            Chart.defaults.color = displayChartTextColor;

            let chartConfig;
            let dynamicTitleText = "Sales Chart";

            const commonPieOptions = {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            font: { size: 9 },
                            padding: 2,
                            color: displayChartTextColor
                        }
                    },
                    title: { display: false },
                    tooltip: {
                        callbacks: { label: chartTooltipCallback },
                        backgroundColor: displayChartTooltipBgColor,
                        titleColor: displayChartTextColor,
                        bodyColor: displayChartTextColor,
                        borderColor: displayChartLegendBorderColor,
                        borderWidth: 1
                    }
                }
            };


            if (currentMapView === 'world') {
                const indonesiaData = salesDataGlobal['Indonesia'];
                const iS = indonesiaData ? (indonesiaData.sales || 0) : 0;
                let tES = 0;
                Object.entries(salesDataGlobal).forEach(([country, data]) => {
                    if (country.toLowerCase() !== 'indonesia') tES += (data.sales || 0);
                });
                let L = [], D = [], B = [];
                const indonesiaColor = isDarkModeDisplay ? '#B71C1C' : '#FF6384';
                const exportColor = isDarkModeDisplay ? '#0D47A1' : '#36A2EB';
                const noDataColor = isDarkModeDisplay ? getComputedStyle(document.documentElement).getPropertyValue('--border-color-medium').trim() : '#CCCCCC';


                if (iS > 0) { L.push('Indonesia'); D.push(iS); B.push(indonesiaColor); }
                if (tES > 0) { L.push('Global Export'); D.push(tES); B.push(exportColor); }
                if (L.length === 0) { L.push('No Sales Data'); D.push(1); B.push(noDataColor); }

                dynamicTitleText = 'Sales Indonesia vs Global';
                chartConfig = {
                    type: 'pie',
                    data: { labels: L, datasets: [{ label: 'Global Sales', data: D, backgroundColor: B, hoverOffset: 4, borderColor: displayChartBorderColor, borderWidth: 1 }] },
                    options: commonPieOptions
                };
            } else if (currentMapView === 'indonesia') {
                const sSRFC = Object.entries(superRegionSales).filter(([, data]) => (data.sales || 0) > 0).sort(([, a], [, b]) => (b.sales || 0) - (a.sales || 0));
                let l_sr = sSRFC.map(([r]) => formatRegionKeyForDisplay(r));
                let d_sr = sSRFC.map(([, data]) => data.sales);
                let c_sr = sSRFC.map(([r]) => {
                    let color = regionColors[r] || (isDarkModeDisplay ? getComputedStyle(document.documentElement).getPropertyValue('--text-color-secondary').trim() : '#808080');
                    if (isDarkModeDisplay && regionColors[r]) {
                        let tinyRegionColor = tinycolor(regionColors[r]);
                        color = tinyRegionColor.isLight() ? tinyRegionColor.darken(20).desaturate(15).toString()
                                                          : tinyRegionColor.lighten(25).desaturate(10).toString();
                    }
                    return color;
                });

                if (l_sr.length === 0) {
                    l_sr.push('No Super Region Sales');
                    d_sr.push(1);
                    c_sr.push(isDarkModeDisplay ? getComputedStyle(document.documentElement).getPropertyValue('--border-color-medium').trim() : '#CCCCCC');
                }
                dynamicTitleText = 'Sales per Super-Region (ID)';
                chartConfig = {
                    type: 'pie',
                    data: { labels: l_sr, datasets: [{ label: 'Super Region Sales (Indonesia)', data: d_sr, backgroundColor: c_sr, borderColor: displayChartBorderColor, borderWidth: 1 }] },
                    options: {
                        ...commonPieOptions,
                        plugins: {
                            ...commonPieOptions.plugins,
                            legend: {
                                ...commonPieOptions.plugins.legend,
                                labels: {
                                    ...commonPieOptions.plugins.legend.labels,
                                    font: { size: 7 },
                                    boxWidth: 7,
                                    generateLabels: function (chart) {
                                        const data = chart.data;
                                        if (data.labels.length && data.datasets.length) {
                                            const dataset = data.datasets[0];
                                            const sortedLabels = data.labels.map((label, i) => ({ label, value: dataset.data[i], color: dataset.backgroundColor[i] })).sort((a, b) => b.value - a.value);
                                            const currentChartTextColor = chart.options.plugins.legend.labels.color;
                                            const legendItems = sortedLabels.slice(0, 4).map(item => ({ text: `${item.label}`, fillStyle: item.color, hidden: false, index: data.labels.indexOf(item.label.split(' (')[0]), fontColor: currentChartTextColor }));
                                            if (sortedLabels.length > 4) { legendItems.push({ text: 'Others...', fillStyle: isDarkModeDisplay ? getComputedStyle(document.documentElement).getPropertyValue('--text-color-secondary').trim() : '#ccc', hidden: false, index: -1, fontColor: currentChartTextColor }); }
                                            return legendItems;
                                        } return [];
                                    }
                                }
                            }
                        }
                    }
                };
            }

            chartTitleElement.textContent = dynamicTitleText;

            if (chartConfig) {
                salesPieChart = new Chart(ctx, chartConfig);
            }
        }

        function updateLegend() {
            if (!legendItemsScrollContainer || currentMapView !== 'indonesia') {
                if(legendItemsScrollContainer) legendItemsScrollContainer.innerHTML = '';
                 if(indonesiaLegendContainer) indonesiaLegendContainer.style.display = 'none';
                return;
            }
             if(indonesiaLegendContainer) indonesiaLegendContainer.style.display = 'block';

            legendItemsScrollContainer.innerHTML = '';
            let legendHTML = '';
            const isDarkMode = document.body.classList.contains('dark-mode');
            const styles = getComputedStyle(document.documentElement);

            const legendOrder = Object.keys(regionColors).filter(k => k !== "OTHER_BASE" && k !== "KEYACCOUNT" && k !== "COMMERCIAL").sort();
            legendOrder.forEach(superRegKey => {
                if (regionColors[superRegKey] && typeof superRegionSales[superRegKey] !== 'undefined') {
                    const regionData = superRegionSales[superRegKey];
                    const salesVal = regionData ? (regionData.sales || 0) : 0;

                    let salesInfo = (salesVal > 0) ? ` (${salesVal.toLocaleString(undefined, {maximumFractionDigits:0})} Ton)` : "";
                    let displayName = formatRegionKeyForDisplay(superRegKey);

                    let legendColorHex = regionColors[superRegKey];
                    if(isDarkMode) {
                        let tinyRegionColor = tinycolor(legendColorHex);
                        legendColorHex = tinyRegionColor.isLight() ? tinyRegionColor.darken(20).desaturate(15).toString()
                                                                 : tinyRegionColor.lighten(25).desaturate(10).toString();
                    }
                    legendHTML += `<div><i style="background:${legendColorHex}"></i> ${displayName}${salesInfo}</div>`;
                }
            });

            const exampleBaseColor = regionColors.REGION1A || '#8dd3c7';
            let exampleMappedNoSalesColor, otherExampleColor;

            if (isDarkMode) {
                exampleMappedNoSalesColor = tinycolor(exampleBaseColor).darken(20).desaturate(15).setAlpha(0.65).toRgbString();
                otherExampleColor = tinycolor(exampleBaseColor).darken(20).desaturate(15).setAlpha(0.60).toRgbString();
            } else {
                exampleMappedNoSalesColor = tinycolor(exampleBaseColor).setAlpha(0.60).toRgbString();
                otherExampleColor = tinycolor(exampleBaseColor).setAlpha(0.55).toRgbString();
            }

            legendHTML += `<div><i style="background:${exampleMappedNoSalesColor}"></i> Area S.Region (No Sales)</div>`;
            legendHTML += `<div><i style="background:${otherExampleColor}"></i> Estimasi Dekat (No Sales)</div>`;

            const otherBaseLegendColor = isDarkMode ? styles.getPropertyValue('--map-bg').trim() : regionColors.OTHER_BASE;
            legendHTML += `<div><i style="background:${otherBaseLegendColor}"></i> Lainnya/Tanpa Data</div>`;
            legendItemsScrollContainer.innerHTML = legendHTML;
        }


        function updateCityMarkers() {
            cityMarkersLayerGroup.clearLayers();
            const isDarkMode = document.body.classList.contains('dark-mode');

            const markerIconUrl = '{{ asset("maps/marker.svg") }}';

            const commonMarkerIcon = L.icon({
                iconUrl: markerIconUrl,
                iconSize: [28, 28],
                iconAnchor: [14, 28],
                popupAnchor: [0, -28]
            });


            if (currentMapView === 'indonesia' && cityMarkersData && cityMarkersData.length > 0) {
                cityMarkersData.forEach(city => {
                    if (city.lat && city.lng && city.sales > 0) {
                        const marker = L.marker([city.lat, city.lng], { icon: commonMarkerIcon });
                        const salesFormatted = (city.sales || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        const popupContent = `<strong>${city.name} (ID)</strong><br>Sales (Kota): ${salesFormatted} Ton`;
                        marker.bindPopup(popupContent).addTo(cityMarkersLayerGroup);
                    }
                });
            }
            else if (currentMapView === 'world' && internationalCityMarkersData && internationalCityMarkersData.length > 0) {
                internationalCityMarkersData.forEach(city => {
                    if (city.lat && city.lng && city.sales > 0) {
                        const marker = L.marker([city.lat, city.lng], { icon: commonMarkerIcon });
                        const salesFormatted = (city.sales || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        const popupContent = `<strong>${city.name} (${city.country || ''})</strong><br>Sales (Kota): ${salesFormatted} Ton`;
                        marker.bindPopup(popupContent).addTo(cityMarkersLayerGroup);
                    }
                });
            }

            if (cityMarkersLayerGroup.getLayers().length > 0) {
                if (!map.hasLayer(cityMarkersLayerGroup)) cityMarkersLayerGroup.addTo(map);
            } else {
                if (map.hasLayer(cityMarkersLayerGroup)) map.removeLayer(cityMarkersLayerGroup);
            }
        }

        async function handleFilterChange() {
            currentSuperRegionSort = { column: 'region', direction: 'asc' };
            currentZoneSort = { column: 'dispatch', direction: 'asc' };
            currentInternationalSort = { column: 'sales', direction: 'asc' };


            document.querySelectorAll('.custom-dropdown-content.active').forEach(content => {
                content.style.display = 'none';
                content.classList.remove('active');
                const trigger = document.querySelector(`[data-controls="${content.id}"]`);
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            });

            infoTooltipGlobalDiv.style.display = 'none';
            salesTooltipIndonesiaDiv.style.display = 'none';

            const startDate = document.getElementById('start-date-select').value;
            const endDate = document.getElementById('end-date-select').value;
            const currentSelectedBrands = getSelectedCheckboxValues('brand-filter-list');
            const currentSelectedCodeCmmts = getSelectedCheckboxValues('code-cmmt-filter-list');
            const currentSelectedCities = getSelectedCheckboxValues('city-filter-list');

            if (!startDate || !endDate) { alert("Please select valid start and end dates."); hideLoading(); return; }
            if (new Date(startDate) > new Date(endDate)) { alert("Start date cannot be after end date."); hideLoading(); return; }

            showLoading('Mengambil data penjualan...');
            if(cityMarkersLayerGroup && map.hasLayer(cityMarkersLayerGroup)) cityMarkersLayerGroup.clearLayers();

            try {
                const params = new URLSearchParams();
                params.append('startDate', startDate);
                params.append('endDate', endDate);
                currentSelectedBrands.forEach(brand => params.append('brands[]', brand));
                currentSelectedCodeCmmts.forEach(code => params.append('code_cmmts[]', code));
                currentSelectedCities.forEach(city => params.append('cities[]', city));

                const response = await fetch(`{{ route('api.sales.data') }}?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(()=>({message:`HTTP error! Status: ${response.status}`}));
                    throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
                }
                const data = await response.json();

                if (data.availableFilterOptions) {
                     populateFilterDropdowns(
                        data.availableFilterOptions.brands,
                        data.availableFilterOptions.code_cmmts,
                        data.availableFilterOptions.cities,
                        currentSelectedBrands,
                        currentSelectedCodeCmmts,
                        currentSelectedCities
                     );
                }

                salesDataGlobal = {};
                if (data.worldSales) {
                    for (const countryName in data.worldSales) {
                        salesDataGlobal[countryName] = {
                            sales: Number(data.worldSales[countryName].sales) || 0,
                            budget: Number(data.worldSales[countryName].budget) || 0,
                            lastYearSales: Number(data.worldSales[countryName].lastYearSales) || 0,
                            sales_value: Number(data.worldSales[countryName].sales_value) || 0,
                            margin_value: Number(data.worldSales[countryName].margin_value) || 0
                        };
                    }
                }

                superRegionSales = {};
                if (data.indonesiaSuperRegionSales) {
                    for (const regionKey in data.indonesiaSuperRegionSales) {
                        superRegionSales[regionKey] = {
                            sales: Number(data.indonesiaSuperRegionSales[regionKey].sales) || 0,
                            budget: Number(data.indonesiaSuperRegionSales[regionKey].budget) || 0,
                            lastYearSales: Number(data.indonesiaSuperRegionSales[regionKey].lastYearSales) || 0,
                            sales_value: Number(data.indonesiaSuperRegionSales[regionKey].sales_value) || 0,
                            margin_value: Number(data.indonesiaSuperRegionSales[regionKey].margin_value) || 0
                        };
                    }
                }

                cityMarkersData = data.cityMarkers || [];
                internationalCityMarkersData = data.internationalCityMarkers || [];

                const mapUrl = currentMapView === 'world' ? WORLD_TOPOJSON_URL : INDONESIA_TOPOJSON_URL;
                const cacheKey = currentMapView === 'world' ? WORLD_CACHE_KEY : INDONESIA_CACHE_KEY;
                await loadAndDisplayMapData(mapUrl, currentMapView, cacheKey);

                updateDashboardPanels();
                updateCityMarkers();

            } catch (error) {
                console.error('Gagal memproses data:', error);
                alert(`Gagal memuat data: ${error.message}`);
                salesDataGlobal = {}; superRegionSales = {}; cityMarkersData = []; internationalCityMarkersData = [];
                populateFilterDropdowns(
                    initialFilterValues.brands,
                    initialFilterValues.code_cmmts,
                    initialFilterValues.cities,
                    currentSelectedBrands,
                    currentSelectedCodeCmmts,
                    currentSelectedCities
                );
                updateDashboardPanels();
                updateCityMarkers();
            } finally {
                hideLoading();
                updateAllDropdownTriggers();
            }
        }
    </script>
</x-app-layout>