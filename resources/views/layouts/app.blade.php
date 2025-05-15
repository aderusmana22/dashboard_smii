<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <!-- PWA  -->
    <meta name="theme-color" content="#6777ef" />
    <link rel="apple-touch-icon" href="{{ asset('assets/images/sinarmeadow.png') }}">

    <link rel="manifest" href="{{ asset('/manifest.json') }}"> --}}
    <link rel="icon" href="{{ url('assets/images/sinarmeadow.png') }}">

    <title>{{ 'INTRA Dashboard SMII' }} - @yield('title')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vendors Style-->
    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/vendors_css.css">

    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/tailwind.min.css">

    <!-- Style-->
    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/horizontal-menu.css">
    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/style.css">
    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/skin_color.css">
    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/custom.css">
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor_components/datatables/datatables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.3.3/css/searchPanes.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.1.0/css/select.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.4.3/css/scroller.dataTables.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.jqueryui.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.3.1/css/searchPanes.jqueryui.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.jqueryui.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/searchbuilder/1.7.1/css/searchBuilder.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.2/css/dataTables.dateTime.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.min.css" rel="stylesheet">

    @stack('css')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="layout-top-nav light-skin theme-primary fixed-manu">

    <div class="wrapper">
        <div id="loader"></div>
        @include('layouts.partials.header')

        @include('layouts.partials.sidebar')
        <div class="content-wrapper">
            <div class="px-4 md:px-0">
                @include('sweetalert::alert')
                {{ $slot }}
            </div>
        </div>


        @include('layouts.partials.footer')

    </div>




     <script type="text/javascript" src="{{ asset('assets') }}/ajax/libs/jQuery-slimScroll/1.3.8/jquery-3.7.1.min.js">
    </script>
    <script type="text/javascript" src="{{ asset('assets') }}/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js">
    </script>
    <!-- Vendor JS -->
    <script src="{{ asset('assets') }}/src/js/vendors.min.js"></script>
    <script src="{{ asset('assets') }}/icons/feather-icons/feather.min.js"></script>

    <script src="{{ asset('assets') }}/src/js/tailwind.min.js"></script>
    <script src="{{ asset('assets/datepicker/jquery-ui.min.js') }}"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script src="{{ asset('assets') }}/vendor_components/datatables/datatables.min.js"></script>
    <script src="{{ asset('assets') }}/vendor_components/echarts/dist/echarts-en.min.js"></script>
    <script src="{{ asset('assets') }}/vendor_components/jquery-toast-plugin-master/src/jquery.toast.js"></script>
    <script src="{{ asset('assets') }}/vendor_components/sweetalert/sweetalert.min.js"></script>
    <script src="{{ asset('assets') }}/vendor_components/sweetalert/jquery.sweet-alert.custom.js"></script>

    <script src="{{ asset('assets') }}/ajax/libs/moment.js/2.24.0/moment-with-locales.min.js"></script>
    <script src="{{ asset('assets') }}/vendor_components/raphael/raphael.min.js"></script>
    <script src="{{ asset('assets') }}/vendor_components/morris.js/morris.min.js"></script>


    <!-- Warehouse App -->
    <script src="{{ asset('assets') }}/src/js/demo.js"></script>
    <script src="{{ asset('assets') }}/src/js/jquery.smartmenus.js"></script>
    <script src="{{ asset('assets') }}/src/js/menus.js"></script>
    <script src="{{ asset('assets') }}/src/js/template.js"></script>
    <script src="{{ asset('assets') }}/src/js/pages/dashboard2.js"></script>
    <script src="
            https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.all.min.js
            "></script>
    <script src="{{ asset('assets') }}/vendor_components/sweetalert/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.jqueryui.js"></script>
    <script src="https://cdn.datatables.net/searchpanes/2.3.3/js/dataTables.searchPanes.js"></script>
    <script src="https://cdn.datatables.net/searchpanes/2.3.3/js/searchPanes.jqueryui.js"></script>
    <script src="https://cdn.datatables.net/select/2.1.0/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.1.0/js/select.jqueryui.js"></script>
    <script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.js"></script>
    <script src="https://cdn.datatables.net/scroller/2.4.3/js/scroller.dataTables.js"></script>
    <script src="https://cdn.datatables.net/searchbuilder/1.8.1/js/dataTables.searchBuilder.js"></script>
    <script src="https://cdn.datatables.net/searchbuilder/1.8.1/js/searchBuilder.dataTables.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.5.4/js/dataTables.dateTime.min.js"></script>
    @stack('scripts')


</body>

</html>
