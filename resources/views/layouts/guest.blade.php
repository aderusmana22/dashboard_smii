<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ url('assets/images/sinarmeadow.png') }}">

    <title>{{ 'INTRA Dashboard SMII' }} - @yield('title')</title>

<!-- Vendors Style-->
<link rel="stylesheet" href="{{ asset('assets') }}/src/css/vendors_css.css">
<link rel="stylesheet" type="text/css" href="{{ asset('assets') }}/src/css/font-awesome-6.4.css">


{{-- <script src="{{ asset('assets') }}/3.4.3"></script> --}}

<link rel="stylesheet" href="{{ asset('assets') }}/src/css/tailwind.min.css">

<!-- Style-->
<link rel="stylesheet" href="{{ asset('assets') }}/src/css/horizontal-menu.css">
<link rel="stylesheet" href="{{ asset('assets') }}/src/css/style.css">
<link rel="stylesheet" href="{{ asset('assets') }}/src/css/skin_color.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="hold-transition theme-primary bg-img bg-fixed" style="background-image: url({{ asset('frontend/assets/images/logo/pic.jpg') }}); background-size: 100% 100%; height: 100vh; width: 100vw;">

	<div class="px-4 md:px-0">
			<div class="grid grid-cols-1 m-0">
				{{ $slot }}
			</div>

			<script type="text/javascript">

			</script>
			<!-- latest jquery-->
			<script type="text/javascript" src="{{ asset('assets') }}/ajax/libs/jQuery-slimScroll/1.3.8/jquery-3.7.1.min.js">
		</div>


	<!-- Vendor JS -->
	<script src="{{ asset('assets') }}/src/js/vendors.min.js"></script>

</body>

</html>
