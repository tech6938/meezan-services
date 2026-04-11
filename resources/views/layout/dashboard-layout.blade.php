<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Otika - Admin Dashboard Template</title>
    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <!-- Custom style CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel='shortcut icon' type='image/x-icon' href='{{ asset('assets/img/favicon.ico') }}' />
    @yield('css')
</head>
<!-- index.html  21 Nov 2019 03:44:50 GMT -->

<body>
    <div class="loader"></div>
    <x-sweet-alert />
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg
									collapse-btn">
                                <i data-feather="align-justify"></i></a></li>
                        <li><a href="#" class="nav-link nav-link-lg fullscreen-btn">
                                <i data-feather="maximize"></i>
                            </a></li>
                        <li>
                            {{-- <form class="form-inline mr-auto">
                                <div class="search-element">
                                    <input class="form-control" type="search" placeholder="Search" aria-label="Search"
                                        data-width="200">
                                    <button class="btn" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form> --}}
                        </li>
                    </ul>
                </div>
                <ul class="navbar-nav navbar-right">
                    <li class="p-2">
                        <a href="{{ route('chatsList') }}" class="btn btn-primary text-white"><i
                                data-feather="message-circle"></i></a>
                    </li>
                    <li class="dropdown"><a href="#" data-toggle="dropdown"
                            class="nav-link dropdown-toggle nav-link-lg nav-link-user"> <img alt="image"
                                src="{{ asset('assets/img/user.png') }}" class="user-img-radious-style"> <span
                                class="d-sm-none d-lg-inline-block"></span></a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <a href="#" class="dropdown-item has-icon"> <i class="fas fa-cog"></i>
                                Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('logout') }}" onclick="return confirm('Are you sure you want to logout?')"
                                class="dropdown-item has-icon text-danger"> <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <a href="{{ route('dashboard') }}"> <img alt="image"
                                src="{{ asset('assets/img/logo.png') }}" class="header-logo" /> <span
                                class="logo-name">Otika</span>
                        </a>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">Main</li>
                        <li class="dropdown  {{ request()->routeIs('dashboard') ? 'active' : ' ' }}">
                            <a href="{{ route('dashboard') }}" class="nav-link"><i
                                    data-feather="monitor"></i><span>Dashboard</span></a>
                        </li>
                        <li
                            class="dropdown {{ request()->routeIs('main-categories.index') || request()->routeIs('sub-categories.index') ? 'active' : ' ' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="layers"></i><span>Skill Categories</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('main-categories.index') }}">Main Categories</a>
                                </li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('sub-categories.index') }}">Sub Categories</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown  {{ request()->routeIs('chatsList') ? 'active' : ' ' }}">
                            <a href="{{ route('chatsList') }}" class="nav-link"><i
                                    data-feather="message-circle"></i><span>Chat List</span></a>
                        </li>
                        <li class="dropdown {{ request()->routeIs('userList') ? 'active' : ' ' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="user"></i><span>Users</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('userList') }}">Users List</a></li>
                            </ul>
                        </li>
                        <li
                            class="dropdown  {{ request()->routeIs('allProviders') || request()->routeIs('approvedProviders') || request()->routeIs('suspendedProviders') || request()->routeIs('blockedProviders') || request()->routeIs('pendingProviders') ? 'active' : ' ' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="users"></i><span>Providers</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('allProviders') }}">All Providers</a></li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('approvedProviders') }}">Approved</a></li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('suspendedProviders') }}">Suspended</a></li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('blockedProviders') }}">Blocked</a></li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('pendingProviders') }}">Pending</a></li>
                            </ul>
                        </li>
                        <li class="dropdown  {{ request()->routeIs('allRequest') ? 'active' : ' ' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i class="fas  fa-tasks"></i>
                                <span>Requests</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('allRequest') }}">All Request</a></li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('pendingRequest') }}">Pending Request</a></li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('approvedRequest') }}">Approved Request</a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="dropdown  {{ request()->routeIs('pendingBooking') ? 'active' : ' ' }} {{ request()->routeIs('startBooking') ? 'active' : ' ' }}   {{ request()->routeIs('cancelBooking') ? 'active' : ' ' }} {{ request()->routeIs('allBookings') ? 'active' : ' ' }} {{ request()->routeIs('endBooking') ? 'active' : ' ' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    class="fas fa-calendar-alt"></i>
                                <span>Booking</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('allBookings') }}"> All Bookings</a></li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('pendingBooking') }}">Pending Bookings</a>
                                </li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('startBooking') }}">Start Bookings</a></li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('endBooking') }}">Complete Bookings</a></li>
                            </ul>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('cancelBooking') }}">Cancel Bookings</a></li>
                            </ul>
                        </li>
                        <li
                            class="dropdown  {{ request()->routeIs('shopkeepers') || request()->routeIs('shops') ? 'active' : ' ' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="shopping-cart"></i>

                                <span>Shop</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('shopkeepers') }}">ShopKeepers</a></li>
                            </ul>
                        </li>
                        <li
                            class="dropdown {{ request()->routeIs('commission.*') || request()->routeIs('appUrl.*') ? 'active' : '' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="settings"></i>
                                <span>Setting</span>
                            </a>

                            <ul class="dropdown-menu">

                                <!-- Commission -->
                                <li class="{{ request()->routeIs('commission.*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('commission.index') }}">
                                        Commission
                                    </a>
                                </li>

                                <!-- App URL -->
                                <li class="{{ request()->routeIs('appUrl.*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('appUrl.index') }}">
                                        App URL
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="menu-header">OFF LOAD</li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>
                        <li class="dropdown">
                            <a href="#" id="logoutBtn" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>

                        </li>
                    </ul>
                </aside>
            </div>
            <!-- Main Content -->
            <!-- Main Content -->
            <!-- Main Content -->
            @yield('content')
            <!-- Main Content -->
            <!-- Main Content -->
            <!-- Main Content -->
        </div>
    </div>
    <!-- Sweet Alerts Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <!-- General JS Scripts -->
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <!-- JS Libraies -->
    <script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
    <!-- Page Specific JS File -->
    <script src="{{ asset('assets/js/page/index.js') }}"></script>
    <!-- Template JS File -->
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <!-- Custom JS File -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <!-- js  -->
    @yield('js')
    <!-- js  -->
    <script>
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault(); // prevent default link action

            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit(); // submit hidden form
                }
            });
        });
    </script>
</body>
<!-- index.html  21 Nov 2019 03:47:04 GMT -->

</html>
