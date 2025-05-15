<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="{{ asset('assets') }}/sinarmeadow.png">

    <title>Intra SMII - 404 Page not found </title>

	<!-- Vendors Style-->
	<link rel="stylesheet" href="{{ asset('assets') }}/src/css/vendors_css.css">

    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/tailwind.min.css">

    <!-- Style-->
    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/horizontal-menu.css">
    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/style.css">
    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/skin_color.css">
    <link rel="stylesheet" href="{{ asset('assets') }}/src/css/custom.css">

</head>
<body class="hold-transition light-skin theme-primary bg-img" style="background-image: url(../../../images/auth-bg/bg-17.png); background-position: bottom right">

	<section class="error-page h-p100">
		<div class="container h-p100">
		  <div class="grid grid-cols-8 h-p100 items-center justify-center text-center">
			  <div class="col-start-3 col-span-6">
				  <div class="rounded10 p-50">
                    <h1 class="fs-100">@yield('code')</h1>
                    <h1>@yield('message') !</h1>
                    <h3 class="text-fade text-xl font-medium">@yield('message2')</h3>
                    <div class="my-30"><a href="{{ route('dashboard') }}" class="btn btn-primary">@yield('messageurl')</a></div>

                </div>
            </div>
        </div>
    </body>
</html>
