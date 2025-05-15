<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Intra Dashboard SMII</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />




    {{-- /* css */ --}}

    <link rel="shortcut icon" href="{{ asset('frontend/assets') }}/images/logo/sinarmeadow.png" type="image/png" />

    <link rel="stylesheet" href="{{ asset('frontend/assets') }}/css/animate.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets') }}/css/tiny-slider.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets') }}/css/glightbox.min.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets') }}/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets') }}/css/style.css" />


    <style>
        .link {
            width: 300px;
            height: 300px;
            transition: transform 0.5s ease;
            transform: scale(1);

            &:hover {
                transform: scale(1.05);
            }
        }

        .website {
            width: 600px;
            height: 600px;
        }

        .single-services {
            width: 600px;
            height: 400px;
        }

        .icon-hover svg {
            fill: currentColor;
            /* Warna default */
            transition: fill 0.3s ease;
            /* Transisi halus */
        }

        .icon-hover:hover svg {
            color: #c0a01f;
            /* Warna gold saat di-hover */
        }

        .services-titles a {
            color: black; /* Warna default dari elemen teks */
            text-decoration: none; /* Menghapus garis bawah default pada link */
            transition: color 0.3s ease; /* Transisi halus untuk warna */
        }

        .services-titles a:hover {
            color: #c0a01f; /* Warna emas saat di-hover */
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
</head>

<body>

    <div class="preloader">
        <div class="loader">
            <div class="spinner">
                <div class="spinner-container">
                    <div class="spinner-rotator">
                        <div class="spinner-left">
                            <div class="spinner-circle"></div>
                        </div>
                        <div class="spinner-right">
                            <div class="spinner-circle"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <header class="header-area">
        <div class="navbar-area">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <nav class="navbar navbar-expand-lg">
                            <a class="navbar-brand" href="/">
                                <img src="{{ asset('frontend/assets') }}/images/logo/logohitam.png" alt="Logo"
                                    width="200" />
                            </a>
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="toggler-icon"> </span>
                                <span class="toggler-icon"> </span>
                                <span class="toggler-icon"> </span>
                            </button>
                            <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                                <ul id="nav" class="navbar-nav ms-auto">
                                    <li class="nav-item">
                                        <a class="page-scroll active" href="#home">Home</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll" href="#features">Website</a>
                                    </li>
                                    @if (Route::has('login'))
                                        <div class="navbar-btn ">
                                            @auth
                                                <a class="main-btn" data-scroll-nav="0" href="{{ url('/dashboard') }}"
                                                    rel="nofollow">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline me-1"
                                                        width="1.2rem" height="1.2rem" fill="currentColor"
                                                        viewBox="0 0 576 512">
                                                        <circle cx="176" cy="416" r="16"
                                                            style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px">
                                                        </circle>
                                                        <circle cx="400" cy="416" r="16"
                                                            style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px">
                                                        </circle>
                                                        <path
                                                            d="M280.4 148.3L96 300.1V464a16 16 0 0 0 16 16l112.1-.3a16 16 0 0 0 15.9-16V368a16 16 0 0 1 16-16h64a16 16 0 0 1 16 16v95.6a16 16 0 0 0 16 16.1L464 480a16 16 0 0 0 16-16V300L295.7 148.3a12.2 12.2 0 0 0 -15.3 0zM571.6 251.5L488 182.6V44.1a12 12 0 0 0 -12-12h-56a12 12 0 0 0 -12 12v72.6L318.5 43a48 48 0 0 0 -61 0L4.3 251.5a12 12 0 0 0 -1.6 16.9l25.5 31A12 12 0 0 0 45.2 301l235.2-193.7a12.2 12.2 0 0 1 15.3 0L530.9 301a12 12 0 0 0 16.9-1.6l25.5-31a12 12 0 0 0 -1.7-16.9z" />

                                                    </svg>
                                                    Dashboard
                                                </a>
                                            @else
                                                <a class="main-btn" data-scroll-nav="0" href="{{ route('login') }}"
                                                    rel="nofollow">
                                                    <svg xmlns="http://www.w3.org/2000/svg"class="inline me-1"
                                                        width="1.2rem" height="1.2rem" fill="currentColor"
                                                        viewBox="0 0 448 512">
                                                        <circle cx="176" cy="416" r="16"
                                                            style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px">
                                                        </circle>
                                                        <circle cx="400" cy="416" r="16"
                                                            style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px">
                                                        </circle>
                                                        <path
                                                            d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z" />
                                                    </svg> Login
                                                </a>
                                            </div>
                                        @endif
                                    @endauth
                                </ul>
                            </div>

                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div id="home" class="header-hero bg_cover"
            style="background-image: url({{ asset('frontend/assets') }}/images/header/banner-bg.svg)">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="header-hero-content text-center">
                            <h3 class="header-sub-title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.2s">
                                Welcome to <br>
                            </h3>
                            <h2 class="header-title wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.5s"
                                style="color: #e01b1b">
                                Test SMII </h2>

                            <p class="text wow fadeInUp" data-wow-duration="1.3s" data-wow-delay="0.8s">
                                Website Internal PT . Sinar Meadow International Indonesia <br>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class=" text-center wow fadeIn" data-wow-duration="1.3s" data-wow-delay="1.4s">
                            <img src="{{ asset('frontend/assets') }}/images/header/hero.png" alt="hero"
                                width="1000" />
                        </div>
                    </div>
                </div>
            </div>

            <div id="particles-1" class="particles"></div>
        </div>
    </header>

    <div class="brand-area pt-90">
        <div class="container">
            <div class="row">
                <div class="brand-logo d-flex align-items-center justify-content-center justify-content-md-between">
                    <div class="single-logo mt-30 wow fadeIn" data-wow-duration="1s" data-wow-delay="0.2s">
                        <img src="{{ asset('frontend/assets/images/brands/gb.jpeg') }}" alt="brand">
                    </div>
                    <div class="single-logo mt-30 wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.2s">
                        <img src="{{ asset('frontend/assets/images/brands/gb2.jpeg') }}" alt="brand">
                    </div>
                    <div class="single-logo mt-30 wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.3s">
                        <img src="{{ asset('frontend/assets/images/brands/gb3.jpeg') }}" alt="brand">
                    </div>
                    <div class="single-logo mt-30 wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.4s">
                        <img src="{{ asset('frontend/assets/images/brands/gb4.jpeg') }}" alt="brand">
                    </div>
                    <div class="single-logo mt-30 wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.5s">
                        <img src="{{ asset('frontend/assets/images/brands/gb5.jpeg') }}" alt="brand">
                    </div>
                    <div class="single-logo mt-30 wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.5s">
                        <img src="{{ asset('frontend/assets/images/brands/gb6.jpeg') }}" alt="brand">
                    </div>
                    <div class="single-logo mt-30 wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.5s">
                        <img src="{{ asset('frontend/assets/images/brands/gb7.jpeg') }}" alt="brand">
                    </div>
                    <div class="single-logo mt-30 wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.5s">
                        <img src="{{ asset('frontend/assets/images/brands/gb8.jpeg') }}" alt="brand">
                    </div>
                    <div class="single-logo mt-30 wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.5s">
                        <img src="{{ asset('frontend/assets/images/brands/gb9.jpeg') }}" alt="brand">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section id="features" class="services-area pt-120">
        <div class="container ">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="section-title text-center pb-40">
                        <div class="line m-auto"></div>
                        <h3 class="title">
                            Our Website
                        </h3>
                    </div>
                </div>
                <div class="col-lg-4 col-md-7 col-sm-8 website">
                    <div class="single-services text-center mt-30 wow fadeIn" data-wow-duration="1s"
                        data-wow-delay="0.2s">
                        <div class="services-icon">
                            <img class="link" src="{{ asset('frontend/assets') }}/images/website/sinarmeadow.png"
                                alt="link" />
                        </div>
                        <div class="services-content">
                            <h4 class="services-titles">
                                <a href="https://sinarmeadow.com" target="_blank">Sinar Meadow Official</a>
                            </h4>
                            {{-- <p class="text text-xs">
                                Official Website PT . Sinar Meadow International Indonesia
                            </p>
                            <a class="more" href="https://sinarmeadow.com" target="_blank">Visit<svg
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-arrow-right-short" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8" />
                                </svg>
                            </a> --}}
                        </div>
                    </div>
                    <div class="single-services text-center mt-30 wow fadeIn" data-wow-duration="1s"
                        data-wow-delay="0.5s">
                        <div class="services-icon">
                            <img class="link" src="{{ asset('frontend/assets') }}/images/website/hris.png"
                                alt="link" />
                        </div>
                        <div class="services-content">
                            <h4 class="services-titles">
                                <a href="http://hris.sinarmeadow.com:8081/" target="_blank">Andal Linkage</a>
                            </h4>
                            {{-- <p class="text text-xs">
                                Website HCD, aplikasi berbasis web yang berfokus pada karyawan.
                            </p>
                            <a class="more" href="http://hris.sinarmeadow.com:8081/">Visit <svg
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-arrow-right-short" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8" />
                                </svg>
                            </a> --}}
                        </div>
                    </div>
                    <div class="single-services text-center mt-30 wow fadeIn" data-wow-duration="1s"
                        data-wow-delay="0.8s">
                        <div class="services-icon">
                            <img class="link" src="{{ asset('frontend/assets') }}/images/website/frontend.png"
                                alt="link" />

                        </div>
                        <div class="services-content">
                            <h4 class="services-titles">
                                <a href="https://frontend.sinarmeadow.com/smii/login.php" target="_blank">FrontEnd</a>
                            </h4>
                            {{-- <p class="text text-xs">
                                Website yang berfokus pada Sales, mulai dari penjualan, distibutor hingga tracking
                                kunjungan sales.
                            </p>
                            <a class="more" href="https://frontend.sinarmeadow.com/smii/login.php"
                                target="_blank">Visit <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                    height="16" fill="currentColor" class="bi bi-arrow-right-short"
                                    viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8" />
                                </svg>
                            </a> --}}
                        </div>
                    </div>
                    <div class="single-services text-center mt-30 wow fadeIn" data-wow-duration="1s"
                        data-wow-delay="0.8s">
                        <div class="services-icon">
                            <img class="link" src="{{ asset('frontend/assets') }}/images/website/wotix.png"
                                alt="link" />

                        </div>
                        <div class="services-content">
                            <h4 class="services-titles">
                                <a href="https://wotix.sinarmeadow.com/login.php" target="_blank">Wotix</a>
                            </h4>
                            {{-- <p class="text text-xs">
                                Website permintaan maintenance dan barang khusus pada IT.
                            </p>
                            <a class="more" href="https://wotix.sinarmeadow.com/login.php" target="_blank">Visit
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-arrow-right-short" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8" />
                                </svg>
                            </a> --}}
                        </div>
                    </div>
                    <div class="single-services text-center mt-30 wow fadeIn" data-wow-duration="1s"
                        data-wow-delay="0.8s">
                        <div class="services-icon">
                            <img class="link" src="{{ asset('frontend/assets') }}/images/website/emgmt.png"
                                alt="link" />

                        </div>
                        <div class="services-content">
                            <h4 class="services-titles">
                                <a href="https://emgmt.sinarmeadow.com/loginPage?next=/" target="_blank">EMGMT</a>
                            </h4>
                            {{-- <p class="text">
                                Website Helpdesk System.
                            </p>
                            <a class="more" href="https://emgmt.sinarmeadow.com/loginPage?next=/"
                                target="_blank">Visit <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                    height="16" fill="currentColor" class="bi bi-arrow-right-short"
                                    viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8" />
                                </svg>
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer id="footer" class="footer-area pt-120">
        <div class="container">
            <div class="subscribe-area wow fadeIn" data-wow-duration="1s" data-wow-delay="0.5s">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="subscribe-content mt-45">
                            <h2 class="subscribe-title text-center" style="color: #c0a01f;">
                                Together, we innovate, excel, and achieve unparalleled success.
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-widget pb-50 mt-45">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-8">
                        <div class="footer-about mt-50 wow fadeIn" data-wow-duration="1s" data-wow-delay="0.2s">
                            <a class="logo" href="/">
                                <img src="{{ asset('frontend/assets') }}/images/logo/logohitam.png" alt="logo"
                                    width="300" />
                            </a>
                            <p class="text">
                                Sinar Meadow yang merupakan hasil kerja sama dari Sinar Mas Grup dan Goodman
                                Fielder adalah perusahaan yang terdepan di Indonesia dalam bidang penghasil lemak nabati
                                (edible fat).
                            </p>
                            <ul class="social">
                                <li>
                                    <a href="https://www.facebook.com/sinarmeadow/" target="_blank"
                                        class="icon-hover">
                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30"
                                            height="30" viewBox="0 0 50 50">
                                            <path
                                                d="M25,3C12.85,3,3,12.85,3,25c0,11.03,8.125,20.137,18.712,21.728V30.831h-5.443v-5.783h5.443v-3.848 c0-6.371,3.104-9.168,8.399-9.168c2.536,0,3.877,0.188,4.512,0.274v5.048h-3.612c-2.248,0-3.033,2.131-3.033,4.533v3.161h6.588 l-0.894,5.783h-5.694v15.944C38.716,45.318,47,36.137,47,25C47,12.85,37.15,3,25,3z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.instagram.com/goldbullion.id/" target="_blank"
                                        class="icon-hover">
                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30"
                                            height="30" viewBox="0 0 50 50">
                                            <path
                                                d="M 16 3 C 8.83 3 3 8.83 3 16 L 3 34 C 3 41.17 8.83 47 16 47 L 34 47 C 41.17 47 47 41.17 47 34 L 47 16 C 47 8.83 41.17 3 34 3 L 16 3 z M 37 11 C 38.1 11 39 11.9 39 13 C 39 14.1 38.1 15 37 15 C 35.9 15 35 14.1 35 13 C 35 11.9 35.9 11 37 11 z M 25 14 C 31.07 14 36 18.93 36 25 C 36 31.07 31.07 36 25 36 C 18.93 36 14 31.07 14 25 C 14 18.93 18.93 14 25 14 z M 25 16 C 20.04 16 16 20.04 16 25 C 16 29.96 20.04 34 25 34 C 29.96 34 34 29.96 34 25 C 34 20.04 29.96 16 25 16 z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://x.com/sinarmeadow" target="_blank" class="icon-hover">
                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30"
                                            height="30" viewBox="0 0 50 50">
                                            <path
                                                d="M 11 4 C 7.134 4 4 7.134 4 11 L 4 39 C 4 42.866 7.134 46 11 46 L 39 46 C 42.866 46 46 42.866 46 39 L 46 11 C 46 7.134 42.866 4 39 4 L 11 4 z M 13.085938 13 L 21.023438 13 L 26.660156 21.009766 L 33.5 13 L 36 13 L 27.789062 22.613281 L 37.914062 37 L 29.978516 37 L 23.4375 27.707031 L 15.5 37 L 13 37 L 22.308594 26.103516 L 13.085938 13 z M 16.914062 15 L 31.021484 35 L 34.085938 35 L 19.978516 15 L 16.914062 15 z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://id.linkedin.com/company/pt-smii" target="_blank"
                                        class="icon-hover">
                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30"
                                            height="30" viewBox="0 0 50 50">
                                            <path
                                                d="M41,4H9C6.24,4,4,6.24,4,9v32c0,2.76,2.24,5,5,5h32c2.76,0,5-2.24,5-5V9C46,6.24,43.76,4,41,4z M17,20v19h-6V20H17z M11,14.47c0-1.4,1.2-2.47,3-2.47s2.93,1.07,3,2.47c0,1.4-1.12,2.53-3,2.53C12.2,17,11,15.87,11,14.47z M39,39h-6c0,0,0-9.26,0-10 c0-2-1-4-3.5-4.04h-0.08C27,24.96,26,27.02,26,29c0,0.91,0,10,0,10h-6V20h6v2.56c0,0,1.93-2.56,5.81-2.56 c3.97,0,7.19,2.73,7.19,8.26V39z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.youtube.com/channel/UC730Ejz5xjFuZXe9c7zcgBg" target="_blank"
                                        class="icon-hover">
                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30"
                                            height="30" viewBox="0 0 50 50">
                                            <path
                                                d="M 44.898438 14.5 C 44.5 12.300781 42.601563 10.699219 40.398438 10.199219 C 37.101563 9.5 31 9 24.398438 9 C 17.800781 9 11.601563 9.5 8.300781 10.199219 C 6.101563 10.699219 4.199219 12.199219 3.800781 14.5 C 3.398438 17 3 20.5 3 25 C 3 29.5 3.398438 33 3.898438 35.5 C 4.300781 37.699219 6.199219 39.300781 8.398438 39.800781 C 11.898438 40.5 17.898438 41 24.5 41 C 31.101563 41 37.101563 40.5 40.601563 39.800781 C 42.800781 39.300781 44.699219 37.800781 45.101563 35.5 C 45.5 33 46 29.398438 46.101563 25 C 45.898438 20.5 45.398438 17 44.898438 14.5 Z M 19 32 L 19 18 L 31.199219 25 Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </a>
                                </li>
                            </ul>

                        </div>
                    </div>
                    <div class="col-lg-5 col-md-7 col-sm-12">
                        <div class="footer-link d-flex mt-50 justify-content-sm-between">
                            <div class="link-wrapper wow fadeIn" data-wow-duration="1s" data-wow-delay="0.4s">

                            </div>

                            <div class="link-wrapper wow fadeIn" data-wow-duration="1s" data-wow-delay="0.6s">
                                <div class="footer-title">
                                    <h4 class="title">Quick Link
                                </div>
                                <ul class="link">
                                    <li><a href="https://sinarmeadow.com" target="_blank">SinarMeadow
                                            Official</a></li>
                                    <li><a href="http://hris.sinarmeadow.com:8081/" target="_blank">Andal
                                            Linkage</a></li>
                                    <li><a href="https://emgmt.sinarmeadow.com/loginPage?next=/" target="_blank">EMGMT
                                            WEB</a></li>
                                    <li><a href="https://wotix.sinarmeadow.com/login.php" target="_blank">WOTIX
                                            WEB</a></li>
                                    <li><a href="https://frontend.sinarmeadow.com/smii/login.php"
                                            target="_blank">FRONTEND WEB</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-5 col-sm-12">
                        <div class="footer-contact mt-50 wow fadeIn" data-wow-duration="1s" data-wow-delay="0.8s">
                            <div class="footer-title">
                                <h4 class="title">Contact Us</h4>
                            </div>
                            <ul class="contact">
                                <li> <svg xmlns="http://www.w3.org/2000/svg" class="inline-block me-1" width="1.2rem"
                                        height="1.2rem" viewbox="0 0 512 512">
                                        <path fill="currentColor"
                                            d="M451,374c-15.88-16-54.34-39.35-73-48.76C353.7,313,351.7,312,332.6,326.19c-12.74,9.47-21.21,17.93-36.12,14.75s-47.31-21.11-75.68-49.39-47.34-61.62-50.53-76.48,5.41-23.23,14.79-36c13.22-18,12.22-21,.92-45.3-8.81-18.9-32.84-57-48.9-72.8C119.9,44,119.9,47,108.83,51.6A160.15,160.15,0,0,0,83,65.37C67,76,58.12,84.83,51.91,98.1s-9,44.38,23.07,102.64,54.57,88.05,101.14,134.49S258.5,406.64,310.85,436c64.76,36.27,89.6,29.2,102.91,23s22.18-15,32.83-31a159.09,159.09,0,0,0,13.8-25.8C465,391.17,468,391.17,451,374Z"
                                            style="fill:none;stroke:currentColor;stroke-miterlimit:10;stroke-width:32px">
                                        </path>
                                    </svg>
                                    Telp (+62-21) 4602981-85 / (+62-21) 4601935</li>
                                <li>
                                    <a href="mailto:cs@sinarmeadow.com" class="text-white"> <svg
                                            xmlns="http://www.w3.org/2000/svg" class="inline-block me-1"
                                            width="1.2rem" height="1.2rem" viewbox="0 0 512 512">
                                            <rect fill="currentColor" x="48" y="96" width="416" height="320"
                                                rx="40" ry="40"
                                                style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px">
                                            </rect>
                                            <polyline fill="currentColor" points="112 160 256 272 400 160"
                                                style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px">
                                            </polyline>
                                        </svg>
                                        cs@sinarmeadow.com</a>
                                </li>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block me-1" width="1.2rem"
                                        height="1.2rem" viewbox="0 0 512 512">
                                        <path fill="currentColor"
                                            d="M256,48c-79.5,0-144,61.39-144,137,0,87,96,224.87,131.25,272.49a15.77,15.77,0,0,0,25.5,0C304,409.89,400,272.07,400,185,400,109.39,335.5,48,256,48Z"
                                            style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px">
                                        </path>
                                        <circle fill="currentColor" cx="256" cy="192" r="48"
                                            style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px">
                                        </circle>
                                    </svg>
                                    Kawasan Industri Pulogadung, <br>
                                    Jl. Pulo Ayang I No. 6. Jakarta 13260
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-copyright">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="copyright d-sm-flex justify-content-between">
                            <div class="copyright-content">
                                <p class="text">
                                    &copy; PT. SINAR MEADOW | MIS | All rights reserved.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="particles-2"></div>
    </footer>

    <a href="#" class="back-to-top"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="up-arrow"><g><path d="M12 22A10 10 0 1 0 2 12a10 10 0 0 0 10 10zM8.31 10.14l3-2.86a.49.49 0 0 1 .15-.1.54.54 0 0 1 .16-.1.94.94 0 0 1 .76 0 1 1 0 0 1 .33.21l3 3a1 1 0 0 1-1.42 1.42L13 10.41V16a1 1 0 0 1-2 0v-5.66l-1.31 1.25a1 1 0 0 1-1.38-1.45z"></path></g></svg>
    </a>
    <script src="{{ asset('frontend/assets') }}/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('frontend/assets') }}/js/wow.min.js"></script>
    <script src="{{ asset('frontend/assets') }}/js/glightbox.min.js"></script>
    <script src="{{ asset('frontend/assets') }}/js/tiny-slider.js"></script>
    <script src="{{ asset('frontend/assets') }}/js/count-up.min.js"></script>
    <script src="{{ asset('frontend/assets') }}/js/particles.min.js"></script>
    <script src="{{ asset('frontend/assets') }}/js/main.js"></script>
    <script>
        var slider = tns({
            container: '.brand-logo',
            items: 5,
            slideBy: 'page',
            autoplay: true,
            autoplayButtonOutput: false,
            mouseDrag: true,
            swipeAngle: false,
            speed: 400,
            controls: false,
            nav: false,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 5
                }
            }
        });
    </script>

    <script>
        var slider = tns({
            container: '.website',
            items: 3,
            slideBy: 'page',
            autoplay: true,
            autoplayButtonOutput: false,
            mouseDrag: true,
            swipeAngle: false,
            speed: 100,
            controls: false,
            nav: false,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 3
                }
            }
        });
    </script>


</body>

</html>
