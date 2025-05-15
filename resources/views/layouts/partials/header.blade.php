<header class="main-header">
    <div class="inside-header">
        <div class="flex items-center logo-box justify-start">
            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="logo">
                <!-- logo-->
                <div class="logo-lg mt-5">
                    <span class="light-logo"><img src="{{ asset('assets') }}/images/logoblack.png" width="320"
                            alt="logo" style=""></span>
                    <span class="dark-logo"><img src="{{ asset('assets') }}/images/logoblack.png" width="320"
                            alt="logo"></span>
                </div>
            </a>
        </div>
        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <div class="float-left">
            </div>

            <div class="navbar-custom-menu r-side inline-flex items-center float-right">
                <ul class="nav navbar-nav inline-flex items-center">
                    <li class="dropdown notifications-menu inline-flex rounded-md">
                        <label class="switch">
                            <a class="waves-effect waves-light btn-primary-light svg-bt-icon">
                                <input type="checkbox" data-mainsidebarskin="toggle" id="toggle_left_sidebar_skin">
                                <span class="switch-on"><i data-feather="moon"></i></span>
                                <span class="switch-off"><i data-feather="sun"></i></span>
                            </a>
                        </label>
                    </li>
                    {{-- <li class="dropdown notifications-menu btn-group ">
                        <a id="dropdownDefaultButton" data-dropdown-toggle="dropdown"
                            class="btn-primary-light svg-bt-icon hover:text-white hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-3 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            title="Notifications" type="button">
                            <div id="notificationCount"
                                class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full top-[0.7rem] end-[0.7rem] dark:border-gray-900">
                                {{ auth()->user()->unreadNotifications->count() ?? 0 }}</div>
                            <i data-feather="bell"></i>
                            <div class="pulse-wave"></div>
                        </a>

                        <!-- Dropdown menu -->
                        <div id="dropdown"
                            class="dropdown-menu z-10 bg-white divide-y divide-gray-100 rounded-lg shadow! w-max dark:bg-gray-700"
                            style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate(-245px, 58px); min-width: 450px;">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownDefaultButton">
                                <li class="header">
                                    <div class="p-20 border-b">
                                        <div class="flexbox">
                                            <div>
                                                <div class="text-xl mb-0 mt-0">Notifications</div>
                                            </div>
                                            @if (auth()->user()->hasRole('super-admin'))
                                                <div>
                                                    <a href="#" class="text-white hover:bg-red-500"
                                                        id="clearAllNotifications">Clear
                                                        All</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <div class="slimScrollDiv"
                                        style="position: relative; overflow: hidden; width: auto; height: 450px;">
                                        <ul class="menu sm-scrol"
                                            style="overflow-y: scroll; width: auto; height: 450px;">
                                            @foreach (auth()->user()->unreadNotifications as $notification)
                                                <li class="border-b flex justify-between items-center">
                                                    <a href="#"
                                                        class="p-3 block m-0 overflow-hidden text-base whitespace-nowrap text-ellipsis flex-grow">
                                                        <i class="fa fa-bell text-info"></i>
                                                        {{ $notification->data['data']['message'] }}
                                                    </a>
                                                    <button class="mark-as-read mr-2 hover:text-blue-600"
                                                        data-id="{{ $notification->id }}">Mark as Read</button>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="slimScrollBar"
                                            style="background: rgb(228, 230, 239); width: 4px; position: absolute; top: 0px; opacity: 0.8; display: none; border-radius: 7px; z-index: 99; right: 3px; height: 207.641px;">
                                        </div>
                                        <div class="slimScrollRail"
                                            style="width: 4px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51); opacity: 0.2; z-index: 90; right: 3px;">
                                        </div>
                                    </div>
                                </li>
                                <li class="footer p-3 text-center border-t">
                                    <button class="mark-as-read-all hover:text-blue-600">Mark as Read All</button>
                                </li>
                            </ul>
                        </div>
                    </li> --}}
                    <!-- Icon atau elemen yang akan digunakan untuk toggle fullscreen -->
                    <li class="inline-flex rounded-md nav-item max-[1199px]:hidden min-[1200px]:inline-flex">
                        <a href="#" id="fullscreenButton"
                            class="waves-effect waves-light nav-link btn-primary-light svg-bt-icon" title="Full Screen">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-maximize">
                                <path
                                    d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3">
                                </path>
                            </svg>
                        </a>
                    </li>
                    {{-- <!-- Control Sidebar Toggle Button -->
                    <li class="inline-flex rounded-md nav-item max-[1199px]:hidden min-[1200px]:inline-flex">
                        <a href="#" data-toggle="control-sidebar" title="Setting"
                            class="waves-effect waves-light nav-link btn-primary-light svg-bt-icon">
                            <i data-feather="sliders"></i>
                        </a>
                    </li> --}}

                    <!-- User Account-->
                    <li class="btn-group d-xl-inline-flex d-none">
                        <a href="#" id="dropdownDividerButton" data-dropdown-toggle="dropdownDivider-2"
                            class="justify-center btn-primary-light hover:text-white svg-bt-icon hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm !px-px !py-px text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            type="button">
                            @if (Auth::user()->avatar)
                                <img src="{{ Storage::url('public/user_avatars/' . Auth::user()->avatar) }}"
                                    class=" !h-11 !w-11 mt-1" alt="" style="width: 100px; height: 100px;">
                            @else
                                <img src="{{ asset('assets') }}/images/sinarmeadow.png"
                                    class="avatar rounded-full !h-11 !w-11 mt-1" alt="">
                            @endif
                        </a>

                        <!-- Dropdown menu -->
                        <div id="dropdownDivider-2"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-72 dark:bg-gray-700 dark:divide-gray-600">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200 drop-shadow-lg"
                                aria-labelledby="dropdownDividerButton">
                                <li>
                                    <p
                                        class="items-center m-0 text-base flex px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                        <i class="fa fa-user-circle-o me-3 text-xl" aria-hidden="true"> </i>
                                        {{ Auth::user()->name }} - {{ Auth::user()->position->position_name }}
                                    </p>
                                </li>
                                <li>
                                    <p
                                        class="items-center m-0 text-base flex px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                        <i class="fa fa-briefcase me-3 text-xl" aria-hidden="true"> </i>
                                        Department {{ Auth::user()->department->department_name }}
                                    </p>
                                </li>
                                {{-- <li>
                                    <a href="{{ route('locked') }}"
                                        class="items-center m-0 text-base flex px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"><i
                                            class="fa fa-lock me-3 text-xl" aria-hidden="true"> </i>
                                        Lock Screen</a>
                                </li> --}}
                                <li>
                                    <a href="{{ route('profile.edit') }}"
                                        class="items-center m-0 text-base flex px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"><i
                                            class="fa fa-cog me-3 text-xl" aria-hidden="true"> </i>
                                        My Profile</a>
                                </li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <li>
                                        <a :href="route('logout')"
                                            onclick="event.preventDefault();
                                                            this.closest('form').submit();"
                                            class="items-center m-0 text-base flex px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer"><i
                                                class=" fa fa-sign-out  me-3 text-xl"> </i>
                                            {{ __('Log Out') }}
                                        </a>

                                    </li>
                                </form>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
@push('scripts')
    <script>
        function showSuccessMessage(message) {
            Swal.fire({
                title: 'Success!',
                text: message,
                icon: 'success',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi untuk memperbarui jumlah notifikasi
            // function updateNotificationCount() {
            //     fetch('{{ route('notifications.count') }}')
            //         .then(response => response.json())
            //         .then(data => {
            //             document.getElementById('notificationCount').textContent = data.count;
            //         })
            //         .catch(error => console.error('Error:', error));
            // }

            // Event listener untuk tombol "Mark as Read"
            // document.querySelectorAll('.mark-as-read').forEach(button => {
            //     button.addEventListener('click', function() {
            //         const notificationId = this.getAttribute('data-id');
            //         fetch('{{ route('notifications.markAsRead') }}', {
            //                 method: 'POST',
            //                 headers: {
            //                     'Content-Type': 'application/json',
            //                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //                 },
            //                 body: JSON.stringify({
            //                     notification_id: notificationId
            //                 })
            //             })
            //             .then(response => response.json())
            //             .then(data => {
            //                 if (data.success) {
            //                     this.closest('li').remove();
            //                     updateNotificationCount();
            //                 } else {
            //                     alert('Failed to mark notification as read.');
            //                 }
            //             })
            //             .catch(error => console.error('Error:', error));
            //     });
            // });

            // Event listener untuk tombol "Mark as Read All"
            // document.querySelector('.mark-as-read-all').addEventListener('click', function() {
            //     fetch('{{ route('notifications.markAllAsRead') }}', {
            //             method: 'POST',
            //             headers: {
            //                 'Content-Type': 'application/json',
            //                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //             }
            //         })
            //         .then(response => response.json())
            //         .then(data => {
            //             if (data.success) {
            //                 document.querySelectorAll('.menu li').forEach(li => li.remove());
            //                 updateNotificationCount();
            //             } else {
            //                 alert('Failed to mark all notifications as read.');
            //             }
            //         })
            //         .catch(error => console.error('Error:', error));
            // });

            // Event listener untuk tombol "Clear All Notifications"
            // const clearAllNotificationsButton = document.getElementById('clearAllNotifications');
            // if (clearAllNotificationsButton) {
            //     clearAllNotificationsButton.addEventListener('click', function(e) {
            //         e.preventDefault();

            //         Swal.fire({
            //             title: 'Are you sure?',
            //             text: 'This action will delete all notifications.',
            //             icon: 'warning',
            //             showCancelButton: true,
            //             confirmButtonColor: '#d33',
            //             cancelButtonColor: '#3085d6',
            //             confirmButtonText: 'Yes, delete all!',
            //             cancelButtonText: 'Cancel'
            //         }).then((result) => {
            //             if (result.isConfirmed) {
            //                 // Kirim request untuk menghapus semua notifikasi
            //                 $.ajax({
            //                     type: 'DELETE',
            //                     url: '{{ route('notifications.clear') }}', // Sesuaikan dengan route yang sesuai
            //                     headers: {
            //                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //                     },
            //                     success: function(response) {
            //                         showSuccessMessage('Semua notifikasi telah dihapus.');
            //                         // Refresh halaman atau tindakan lain yang diinginkan
            //                         window.location.reload(); // Contoh: reload halaman setelah penghapusan
            //                     },
            //                     error: function(xhr, status, error) {
            //                         console.error(xhr.responseText);
            //                         Swal.fire(
            //                             'Error!',
            //                             'Failed to delete notifications.',
            //                             'error'
            //                         );
            //                     }
            //                 });
            //             }
            //         });
            //     });
            // }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const toggleSwitch = document.getElementById('toggle_left_sidebar_skin');

            // Fungsi untuk mengatur mode gelap
            const setDarkMode = (darkModeOn) => {
                body.classList.toggle('dark-skin', darkModeOn);
                body.classList.toggle('light-skin', !darkModeOn);
                if (toggleSwitch) {
                    toggleSwitch.checked = darkModeOn;
                }
                // Simpan status mode gelap ke localStorage
                localStorage.setItem('darkMode', darkModeOn);
            };

            // Muat status mode gelap dari localStorage saat halaman dimuat
            const loadDarkMode = () => {
                const darkModeOn = localStorage.getItem('darkMode') === 'true';
                setDarkMode(darkModeOn);
            };

            // Jalankan fungsi untuk memuat status mode gelap
            loadDarkMode();

            // Toggle mode gelap saat tombol toggle diklik
            if (toggleSwitch) {
                toggleSwitch.addEventListener('change', () => {
                    setDarkMode(toggleSwitch.checked);
                });
            }
        });



        // Fungsi untuk mengaktifkan dan menonaktifkan mode fullscreen saat tombol diklik
        document.getElementById('fullscreenButton').addEventListener('click', function() {
            var elem = document.documentElement;
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                } else if (elem.mozRequestFullScreen) {
                    elem.mozRequestFullScreen();
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                }
            }
        });
    </script>
@endpush
