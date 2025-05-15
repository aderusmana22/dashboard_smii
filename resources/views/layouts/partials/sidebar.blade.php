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
                        <li><a href="{{ route('department.index') }}"
                                class="{{ request()->is('department*') ? 'current' : '' }}"><i class="icon-Commit"><span
                                        class="path1"></span><span class="path2"></span></i>Departments</a></li>
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
    </ul>
</nav>
