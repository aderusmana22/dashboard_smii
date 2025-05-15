<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="{{ url('assets/images/sinarmeadow.png') }}">

    <title>INTRA SMII - Lockscreen </title>

	<!-- Vendors Style-->
	<link rel="stylesheet" href="{{ asset('assets') }}/src/css/vendors_css.css">


	<link rel="stylesheet" href="{{ asset('assets') }}/src/css/tailwind.min.css">

	<!-- Style-->
	<link rel="stylesheet" href="{{ asset('assets') }}/src/css/horizontal-menu.css">
	<link rel="stylesheet" href="{{ asset('assets') }}/src/css/style.css">
	<link rel="stylesheet" href="{{ asset('assets') }}/src/css/skin_color.css">

</head>
<body class="hold-transition theme-primary bg-img bg-fixed" style="background-image: url({{ asset('frontend/assets/images/logo/bg-baru.png') }}); background-size: 100% 100%; height: 100vh; width: 100vw;">

	<div class="px-4 md:px-0">
			<div class="row m-0">
				<div class="col-12 p-0">
					<div class="login-card login-dark">
						<div>
						<div class="bg-white bg-opacity-70 rounded10 shadow-lg" style="background: rgba(255, 255, 255, 0.651); backdrop-filter: blur(10px); border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
							<div class="content-top-agile items-center justify-center px-100 pt-40 pb-0">
								@if (Auth::user()->avatar)
                                    <img src="{{ Storage::url('public/user_avatars/' . Auth::user()->avatar) }}" class="mx-auto rounded-full" alt="" width="100" height="100">
                                @else
                                    <img src="{{ asset('assets') }}/images/sinarmeadow.png" alt="User Image" class="bg-light rounded-full mx-auto" width="100" height="100">
                                @endif
								<h2 class="text-primary text-2xl font-semibold text-blue-600 mt-6 mb-3">{{ Auth()->user()->name }}</h2>
								<p class="mb-5 text-fade">Input Password to Back to your Page</p>
							</div>

							<div class="px-40 pb-40 pt-20">
                                 <!-- Validation Errors -->
							   @if ($errors->any())
							   <div class="mb-4">
								   <div class="font-medium text-red-600">
									   {{ __('Whoops! Something went wrong.') }}
								   </div>

								   <ul class="mt-3 list-disc list-inside text-sm text-red-600">
									   @foreach ($errors->all() as $error)
										   <li>{{ $error }}</li>
									   @endforeach
								   </ul>
							   </div>
						   @endif
								<form method="POST">
                                    @csrf
									<div class="form-group">
											<label class="font-medium block mb-1  text-gray-700" for="password">
										Password
									</label>
									<div class="relative w-full">
										<input class=" border-1 rounded w-full  px-3 leading-tight border-gray-300 bg-gray-100 focus:outline-none focus:border-indigo-700 focus:bg-white text-gray-700 pr-16 font-mono js-password" id="password" type="password" name="password" required="required" autocomplete="current-password" placeholder="Input password" value="">
									</div>
										<!-- <div class="input-group mb-3">
											<span class="input-group-text bg-transparent"><i class="text-fade ti-lock"></i></span>
											<input type="password" class="form-control ps-15 bg-transparent" placeholder="Password">
										</div> -->
									</div>
									  <div class="row">
										<div class="col-12 text-center">
										  <button type="submit" class="btn btn-primary w-p100 mt-10">UNLOCK</button>
										</div>
										<!-- /.col -->
									  </div>
								</form>


							</div>
						</div>


				</div>
			</div>
		</div>
		</div>
	</div>


	<!-- Vendor JS -->
	<script src="{{ asset('assets') }}/src/js/vendors.min.js"></script>
	<script src="{{ asset('assets') }}/src/js/pages/chat-popup.js"></script>
    <script src="{{ asset('assets') }}/icons/feather-icons/feather.min.js"></script>
    <script>
        // Mengatur bahwa jika pengguna mencoba kembali ke halaman sebelumnya, mereka akan diarahkan ke lock screen jika belum terotentikasi.
        history.pushState(null, null, document.URL);
        window.addEventListener('popstate', function () {
            history.pushState(null, null, document.URL);
            showLockScreen(); // Panggil fungsi untuk menampilkan kembali lock screen
        });

        // Fungsi untuk menampilkan kembali halaman lock screen
        function showLockScreen() {
            document.querySelector('.lock-screen').style.display = 'block'; // Atur tampilan lock screen menjadi visible
            document.body.style.overflow = 'hidden'; // Mencegah scroll di halaman belakang lock screen
        }
    </script>

</body>
</html>
