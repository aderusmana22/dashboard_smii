<nav class="main-nav" role="navigation">

    <!-- Mobile menu toggle button (hamburger/x icon) -->
    <input id="main-menu-state" type="checkbox">
    <label class="main-menu-btn" for="main-menu-state">
        <span class="main-menu-btn-icon"></span> Toggle main menu visibility
    </label>

    <!-- Sample menu definition -->
    <ul id="main-menu" class="sm sm-blue">
        @can('view dashboard')
        <li class="{{ request()->is('dashboard/*') ? 'current' : '' }}"><a href="{{ route('dashboard') }}"
                style="font-size: 18px;"><i data-feather="home" style="width: 18px; height: 18px;"><span
                        class="path1"></span><span class="path2"></span></i>Dashboard</a>
            <ul>
                @can('view production dashboard')
                <li><a href="{{ route('dashboard.dashboardProduction') }}"
                        class="{{ request()->is('dashboard/dashboard-production') ? 'current' : '' }}"><i
                            class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dashboard
                        Production</a></li>
                @endcan
                @can('view sales dashboard')
                <li><a href="{{ route('dashboard.dashboardSales') }}"
                        class="{{ request()->is('dashboard/dashboard-sales') ? 'current' : '' }}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Dashboard Sales</a></li>
                @endcan
                @can('view warehouse dashboard')
                <li><a href="{{ route('dashboard.dashboardWarehouse') }}"
                        class="{{ request()->is('dashboard/dashboard-warehouse') ? 'current' : '' }}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Dashboard Warehouse</a></li>
                @endcan
            </ul>
        </li>
        @endcan
        @can('view data dashboard')
        <li><a href="#" style="font-size: 18px;" class="{{ request()->is(['dashboard/inventory', 'dashboard/standard-production', 'dashboard/standard-warehouse']) ? 'current' : '' }}"><i
                    data-feather="database" style="width: 18px; height: 18px;"></i>Data Dashboard</a>
            <ul>
                {{-- @can('view sales dashboard')
                <li><a href="{{ route('dashboard.sales') }}"
                        class="{{ request()->is('dashboard/sales') ? 'current' : '' }}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Sales Dashboard </a></li>
                @endcan --}}
                @can('view inventory dashboard')
                <li><a href="{{ route('dashboard.inventory') }}"
                        class="{{ request()->is('dashboard/inventory') ? 'current' : '' }}"><i
                            class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Inventory
                        Dashboard</a></li>
                @endcan
                @can('view production dashboard')
                <li><a href="{{ route('data.production') }}"
                        class="{{ request()->is('data.production') ? 'current' : '' }}"><i
                            class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Data Production
                        </a></li>
                @endcan
                @can('view standard production dashboard')
                <li><a href="{{ route('dashboard.production.standard') }}"
                        class="{{ request()->is('dashboard/standard-production/') ? 'current' : '' }}"><i
                            class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Standard
                        Production</a></li>
                @endcan
                @can('view standard warehouse dashboard')
                <li><a href="{{ route('dashboard.warehouseindex') }}"
                        class="{{ request()->is('dashboard/standard-warehouse') ? 'current' : '' }}"><i
                            class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Standard
                        Warehouse</a></li>
                @endcan
                
                @can('view standard shipment dashboard')
                <li><a href="{{ route('data.sales') }}"
                        class="{{ request()->is('sales') ? 'current' : '' }}"><i
                            class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Data
                        Sales</a></li>
                @endcan
                @can('view standard shipment dashboard')
                <li><a href="{{ route('standard-budgets.index') }}"
                class="{{ request()->is('standard-budgets.index') ? 'current' : '' }}">

                <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                Standard Budgets</a></li>
                @endcan
            </ul>
        </li>
        @endcan
            @can('view user management')
            <li
                class="{{ request()->is('users*') || request()->is('department*') || request()->is('position*') || request()->is('level*') || request()->is('roles*') || request()->is('permissions*') || request()->is('get.master*') ? 'current' : '' }}">
                <a href="#" style="font-size: 18px;">
                    <i data-feather="users" style="width: 18px; height: 18px;"></i>
                    User Management
                </a>
                <ul>
                    @can('view user')
                        <li><a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'current' : '' }}"><i
                                    class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Users</a>
                        </li>
                    @endcan
                    @can('view department')
                                                <li>
                            <a href="{{ route('department.index') }}"
                            class="{{ request()->routeIs('department.index') ? 'current' : '' }}">
                                <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                                Departments
                            </a>
                        </li>
                    @endcan
                    @can('view department')
                                <li>
                        <a href="{{ route('department-approvers.index') }}"
                        class="{{ request()->routeIs('department-approvers.index') ? 'current' : '' }}">
                            <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                            Departments Approver
                        </a>
                    </li>
                    @endcan
                    @can('view position')
                        <li><a href="{{ route('position.index') }}" class="{{ request()->is('position*') ? 'current' : '' }}"><i
                                    class="icon-Commit"><span class="path1"></span><span
                                        class="path2"></span></i>Positions</a></li>
                    @endcan
                    @can('view level')
                        <li><a href="{{ route('level.index') }}" class="{{ request()->is('level*') ? 'current' : '' }}"><i
                                    class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Levels</a>
                        </li>
                    @endcan
                    @can('view role')
                        <li><a href="{{ route('roles.index') }}" class="{{ request()->is('roles*') ? 'current' : '' }}"><i
                                    class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Roles</a>
                        </li>
                    @endcan
                    @can('view permission')
                        <li><a href="{{ route('permissions.index') }}"
                                class="{{ request()->is('permissions*') ? 'current' : '' }}"><i class="icon-Commit"><span
                                        class="path1"></span><span class="path2"></span></i>Permission</a></li>
                    @endcan
                    {{-- @can('get master data') --}}
                        <li><a href="{{ route('get.master') }}" class="{{ request()->is('get.master*') ? 'current' : '' }}"><i
                                    class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Get Data
                                Master </a></li>
                    {{-- @endcan --}}
                </ul>
            </li>
            @endcan
            </li>


{{-- Kanban --}}
{{-- Ganti 'view kanban management' dengan permission yang sesuai jika ada --}}
{{-- @can('view kanban management') --}}
<li class="{{ request()->is('kanban*') ? 'current' : '' }}">
    <a href="#" style="font-size: 18px;" class="has-submenu" id="sm-kanban-management-menu" aria-haspopup="true" aria-controls="sm-kanban-management-submenu">
        {{-- Ikon untuk Manajemen Kanban, misalnya 'briefcase' atau 'layers' --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers" style="width: 18px; height: 18px;"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
        Kanban {{-- <--- GANTI NAMA INI JIKA PERLU --}}
        <i class="sub-arrow fa fa-angle-right"></i> {{-- Panah submenu --}}
    </a>
    <ul id="sm-kanban-management-submenu" role="group" aria-hidden="true" aria-labelledby="sm-kanban-management-menu" class="sm-nowrap" style="z-index: 10000; width: auto; min-width: 10em; display: none; max-width: 20em; top: auto; left: 0px; margin-left: 1px; margin-top: 0px;">

        {{-- Sub-item untuk Papan Kanban --}}
        {{-- @can('view kanban board') --}}
        <li class="{{ request()->is('kanban') ? 'current' : '' }}"> {{-- Kelas 'current' di sini juga untuk sub-item --}}
            <a href="{{ route('page.kanban.index') }}"> {{-- Hapus style font-size dari sub-item jika tidak perlu --}}
                <i class="icon-Layout-4-blocks"><span class="path1"></span><span class="path2"></span></i> {{-- Contoh ikon, sesuaikan --}}
                Papan Kanban
            </a>
        </li>
        {{-- @endcan --}}

        {{-- Sub-item untuk Laporan Kanban (yang juga punya sub-sub-menu jika diperlukan, atau link langsung) --}}
        {{-- Untuk kasus ini, Laporan Kanban akan menjadi sub-item yang membawa ke halaman laporan, bukan membuka sub-sub-menu --}}
        {{-- @can('view kanban reports') --}}
        <li class="{{ Str::startsWith(request()->path(), 'reports/tasks') ? 'current' : '' }}">
            <a href="{{ route('reports.tasks.list') }}">
                <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                Laporan Daftar Tugas
            </a>
        </li>
        {{-- @endcan --}}

    </ul>
</li>
{{-- @endcan --}}
    </ul>
</nav>
