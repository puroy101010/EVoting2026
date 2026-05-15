<!DOCTYPE html>
<html lang="en">

<head>
  <title>E-Voting System{{ isset($title) && $title ? ' | ' . $title : '' }}</title>
  <meta charset="utf-8">
  <link rel="icon" type="image/png" href="{{asset('images/small_logo.png')}}" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">








  <!-- Scripts -->
  <script src="{{ asset('js/app.js') }}" defer></script>
  <script src="{{ asset('js/e-voting/app.js') }}" defer></script>
  <script src="{{ asset('js/jquery-3.5.1.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>












  <!-- Fonts -->
  <link rel="dns-prefetch" href="//fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

  <!-- Chosen -->
  <link href="{{asset('css/chosen.mod.css')}}" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="{{ asset('js/chosen.jquery.min.js') }}" defer></script>


  <!-- Styles -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  <link href="{{ asset('css/e-voting/app.css') }}" rel="stylesheet">

  <script src="{{ asset('js/sweetalert2@9.js') }}"></script>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <!-- <link rel="stylesheet" href="{{asset('css/fontawesome-free/css/all.min.css')}}"> -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css')}}">

  <link rel="stylesheet" href="{{asset('css/admin/customize.css')}}">


  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

  <!-- include summernote css/js -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">


  <style type="text/css">
    :root {
      --corporate-primary: #304c40;
      --corporate-accent: #6b8e4e;
      --corporate-bg: #f7f9f8;
      --corporate-card: #fff;
      --corporate-border: #e0e0e0;
      --corporate-shadow: 0 4px 24px rgba(48, 76, 64, 0.08);
      --corporate-text: #2d3a1f;
      --corporate-light: #8fa572;
    }

    /* Modern Body Styling */
    body {
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--corporate-bg) !important;
      color: var(--corporate-text);
    }

    /* Modern Navbar */
    .main-header.navbar {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf7 100%) !important;
      border-bottom: 1px solid var(--corporate-border);
      box-shadow: 0 2px 8px rgba(107, 142, 78, 0.1);
      backdrop-filter: blur(10px);
      min-height: 48px !important;
      padding: 0.25rem 1rem;
    }

    .navbar-nav .nav-link {
      color: var(--corporate-primary) !important;
      font-weight: 500;
      transition: all 0.3s ease;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.9rem;
    }

    .navbar-nav .nav-link:hover {
      color: var(--corporate-accent) !important;
    }

    /* Ultra Compact Sidebar */
    .sidebar-dark-green {
      background: linear-gradient(180deg, var(--corporate-primary) 0%, #1e3128 100%) !important;
      box-shadow: 4px 0 20px rgba(48, 76, 64, 0.15);
      width: 240px !important;
    }

    .btn-proxyholder-history {
      cursor: pointer;
    }

    .brand-link {
      background: rgba(255, 255, 255, 0.05) !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      color: #ffffff !important;
      font-weight: 700;
      letter-spacing: 0.5px;
      padding: 0.75rem 0.75rem;
      min-height: 48px;
    }

    .brand-text {
      font-size: 1rem !important;
    }

    /* Ultra Compact User Panel */
    .user-panel {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 0.75rem;
      margin: 0.75rem;
      padding: 1rem 0.75rem;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-usertitle-name {
      color: #ffffff;
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 0.15rem;
    }

    .profile-usertitle-job {
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.7rem;
      font-weight: 500;
      letter-spacing: 1px;
      margin-bottom: 0.5rem;
    }

    .user-panel img {
      width: 45px !important;
      height: 45px !important;
    }

    .profile-usertitle {
      text-align: center;
    }

    /* Ultra Compact Navigation */
    .nav-sidebar .nav-item .nav-link {
      color: rgba(255, 255, 255, 0.8) !important;
      border-radius: 0.5rem;
      margin: 0.15rem 0.75rem;
      padding: 0.5rem 0.75rem;
      transition: all 0.3s ease;
      font-weight: 500;
      font-size: 0.85rem;
      line-height: 1.3;
    }

    .nav-sidebar .nav-item .nav-link:hover {
      background: rgba(255, 255, 255, 0.1) !important;
      color: #ffffff !important;
      transform: translateX(2px);
    }

    .nav-sidebar .nav-item .nav-link.active {
      background: linear-gradient(135deg, var(--corporate-accent) 0%, var(--corporate-light) 100%) !important;
      color: #ffffff !important;
      box-shadow: 0 2px 8px rgba(107, 142, 78, 0.3);
    }

    .nav-sidebar .nav-treeview .nav-link {
      margin-left: 1.25rem;
      margin-right: 0.75rem;
      font-size: 0.8rem;
      padding: 0.4rem 0.75rem;
    }

    .nav-sidebar .nav-item .nav-link .nav-icon {
      font-size: 0.9rem !important;
      margin-right: 0.5rem;
      width: 1.2rem;
    }

    .nav-sidebar .nav-item .nav-link p {
      margin: 0;
      font-size: 0.85rem;
    }

    /* Navigation Section Headers */
    .nav-sidebar .nav-header {
      font-size: 0.75rem !important;
      font-weight: 600 !important;
      letter-spacing: 1px !important;
      text-transform: uppercase !important;
      color: rgba(255, 255, 255, 0.6) !important;
      padding: 0.75rem 1rem 0.5rem !important;
      margin-top: 1rem !important;
      margin-bottom: 0.5rem !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      position: relative;
      cursor: pointer;
      transition: all 0.3s ease;
      user-select: none;
    }

    .nav-sidebar .nav-header:hover {
      color: rgba(255, 255, 255, 0.8) !important;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 0.5rem;
    }

    .nav-sidebar .nav-header::before {
      content: '\f078';
      font-family: 'Font Awesome 5 Free';
      font-weight: 900;
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%) rotate(0deg);
      transition: transform 0.3s ease;
      font-size: 0.7rem;
    }

    .nav-sidebar .nav-header.collapsed::before {
      transform: translateY(-50%) rotate(-90deg);
    }

    /* First header shouldn't have top margin */
    .nav-sidebar .nav-header:first-of-type {
      margin-top: 0.5rem !important;
    }

    /* Add subtle gradient line under headers */
    .nav-sidebar .nav-header::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 1rem;
      right: 1rem;
      height: 1px;
      background: linear-gradient(90deg, transparent 0%, rgba(107, 142, 78, 0.4) 50%, transparent 100%);
    }

    /* Navigation group styling */
    .nav-group {
      overflow: hidden;
      transition: max-height 0.3s ease, opacity 0.3s ease;
    }

    .nav-group.collapsed {
      max-height: 0 !important;
      opacity: 0;
      margin: 0;
    }

    .nav-group:not(.collapsed) {
      max-height: 1000px;
      opacity: 1;
    }

    /* Improve spacing between sections */
    .nav-sidebar .nav-item:last-child {
      margin-bottom: 1rem;
    }

    /* Enhanced tree view styling */
    .nav-sidebar .nav-item.has-treeview>.nav-link {
      font-weight: 600;
    }

    .nav-sidebar .nav-treeview {
      background: rgba(0, 0, 0, 0.1);
      border-radius: 0.5rem;
      margin: 0.25rem 0.75rem;
      padding: 0.25rem 0;
    }

    .nav-sidebar .nav-treeview .nav-item .nav-link {
      margin: 0.1rem 0.5rem;
      padding: 0.4rem 0.75rem;
      border-radius: 0.4rem;
      background: rgba(255, 255, 255, 0.05);
    }

    .nav-sidebar .nav-treeview .nav-item .nav-link:hover {
      background: rgba(255, 255, 255, 0.15) !important;
      transform: translateX(4px);
    }

    /* Modern Dropdown */
    .dropdown-menu {
      border: none;
      border-radius: 1rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
      font-size: .8rem !important;
    }

    .dropdown-item {
      /* padding: 0.75rem 1.5rem; */
      border-radius: 0.5rem;
      margin: 0 !important;
      transition: all 0.2s ease;
      font-weight: 500;
      padding: .1rem 1rem !important;
    }

    .dropdown-item:hover {
      background: var(--corporate-bg);
      color: var(--corporate-accent);
    }

    /* Ultra Compact Modal Styling */
    .modal-content {
      border: none;
      border-radius: 1rem;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
      backdrop-filter: blur(10px);
    }

    .modal-header {
      background: linear-gradient(135deg, var(--corporate-primary) 0%, var(--corporate-accent) 100%);
      color: #ffffff;
      border-radius: 1rem 1rem 0 0;
      border-bottom: none;
      padding: 1rem 1.5rem;
    }

    .modal-title {
      font-weight: 700;
      font-size: 1.1rem;
      letter-spacing: 0.5px;
    }

    .modal-body {
      padding: 1.5rem;
    }

    .modal-footer {
      background: var(--corporate-bg);
      border-top: 1px solid var(--corporate-border);
      border-radius: 0 0 1rem 1rem;
      padding: 1rem 1.5rem;
    }

    /* Ultra Compact Form Controls */
    .form-control {
      border-radius: 0.5rem;
      border: 2px solid var(--corporate-border);
      padding: 0.6rem 0.85rem;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }

    .form-control:focus {
      border-color: var(--corporate-accent);
      box-shadow: 0 0 0 2px rgba(107, 142, 78, 0.15);
    }

    /* Ultra Compact Buttons */
    .btn-custom-darkg {
      background: linear-gradient(135deg, var(--corporate-primary) 0%, var(--corporate-accent) 100%);
      color: #ffffff;
      border: none;
      border-radius: 0.5rem;
      padding: 0.6rem 1.25rem;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(48, 76, 64, 0.3);
      font-size: 0.9rem;
    }

    .btn-custom-darkg:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(48, 76, 64, 0.4);
      color: #ffffff;
    }

    /* Password Show/Hide */
    .pass_show {
      position: relative;
    }

    .pass_show .ptxt {
      position: absolute;
      top: 50%;
      right: 15px;
      z-index: 10;
      color: var(--corporate-accent);
      font-weight: 600;
      margin-top: -10px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.85rem;
    }

    .pass_show .ptxt:hover {
      color: var(--corporate-primary);
    }

    /* Content Wrapper */
    .content-wrapper {
      background: var(--corporate-bg) !important;
      min-height: calc(100vh - 48px);
      margin-left: 240px !important;
    }

    /* When sidebar is collapsed/hidden make content full width */
    body.sidebar-collapse .content-wrapper,
    body.layout-navbar-fixed.sidebar-collapse .content-wrapper {
      margin-left: 0 !important;
    }

    /* Ultra Compact for Low Resolution Desktop */
    @media (max-height: 900px) {
      .main-header.navbar {
        min-height: 42px !important;
        padding: 0.2rem 0.75rem;
      }

      .brand-link {
        padding: 0.6rem 0.6rem;
        min-height: 42px;
      }

      .brand-text {
        font-size: 0.9rem !important;
      }

      .user-panel {
        margin: 0.5rem;
        padding: 0.75rem 0.6rem;
        border-radius: 0.5rem;
      }

      .user-panel img {
        width: 35px !important;
        height: 35px !important;
      }

      .profile-usertitle-name {
        font-size: 0.85rem;
        margin-bottom: 0.1rem;
      }

      .profile-usertitle-job {
        font-size: 0.65rem;
        margin-bottom: 0.35rem;
      }

      .nav-sidebar .nav-item .nav-link {
        margin: 0.1rem 0.6rem;
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
        border-radius: 0.4rem;
      }

      .nav-sidebar .nav-treeview .nav-link {
        margin-left: 1rem;
        margin-right: 0.6rem;
        font-size: 0.75rem;
        padding: 0.35rem 0.6rem;
      }

      .nav-sidebar .nav-item .nav-link .nav-icon {
        font-size: 0.8rem !important;
        margin-right: 0.4rem;
        width: 1rem;
      }

      .nav-sidebar .nav-item .nav-link p {
        font-size: 0.7rem;
      }

      .sidebar-dark-green {
        width: 220px !important;
      }

      .content-wrapper {
        margin-left: 220px !important;
        min-height: calc(100vh - 42px);
      }
    }

    /* Extra compact for very low resolution */
    @media (max-height: 768px) {
      .user-panel {
        margin: 0.4rem;
        padding: 0.6rem 0.5rem;
      }

      .user-panel img {
        width: 30px !important;
        height: 30px !important;
      }

      .profile-usertitle-name {
        font-size: 0.8rem;
      }

      .profile-usertitle-job {
        font-size: 0.6rem;
        margin-bottom: 0.25rem;
      }

      .nav-sidebar .nav-item .nav-link {
        margin: 0.05rem 0.5rem;
        padding: 0.35rem 0.5rem;
        font-size: 0.75rem;
      }

      .nav-sidebar .nav-treeview .nav-link {
        margin-left: 0.85rem;
        margin-right: 0.5rem;
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
      }

      .sidebar-dark-green {
        width: 200px !important;
      }

      .content-wrapper {
        margin-left: 200px !important;
      }
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: var(--corporate-bg);
    }

    ::-webkit-scrollbar-thumb {
      background: var(--corporate-accent);
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: var(--corporate-primary);
    }


    /* ----------- iPhone X ----------- */

    /* Portrait and Landscape */
    @media only screen and (min-device-width: 375px) and (max-device-width: 812px) and (-webkit-min-device-pixel-ratio: 3) {

      .navbar-expand .navbar-nav .nav-link {
        padding-right: 2rem;
        padding-left: 0.5rem;
      }

      .dropdown-item {
        padding: 5px;
        clear: both;
        font-weight: 400;
        font-size: 0.8em;
      }
    }
  </style>
  <script>
    const BASE_URL = "{{ asset('/') }}";


    function handleSuccess(xhr, reload = true) {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: xhr.message
      }).then(() => {
        location.reload();
      });
    }

    function handleError(xhr) {

      let icon = 'error';
      let title = 'Error!';
      let message = xhr.responseJSON.message || '';

      if (xhr.status === 403) {
        icon = 'info';
        title = 'Info!';

        message = 'Insufficient privileges';

      }

      if (xhr.status === 500) {

        message = "An unexpected error was encountered. Please contact your administrator if the error persists.";

      }

      Swal.fire({
        icon: icon,
        title: title,
        text: message
      });
    }
  </script>



  @yield('css')
  @yield('head')
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button">
            <i class="fas fa-bars" style="font-size: 1.1rem;"></i>
          </a>
        </li>
      </ul>

      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-user-circle me-2" style="font-size: 1.2rem; margin-right: 0.5rem;"></i>
            <span class="d-none d-md-inline">{{ Auth::user()->stockholder }}</span>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item d-flex align-items-center" id="menu_change_password" href="#">
              <i class="fas fa-key me-2" style="width: 20px; margin-right: 0.75rem;"></i>
              Change Password
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item d-flex align-items-center" href="{{asset('admin/logout')}}">
              <i class="fas fa-sign-out-alt me-2" style="width: 20px; margin-right: 0.75rem;"></i>
              Logout
            </a>
          </div>
        </li>
      </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-green elevation-4">
      <a href="{{asset('admin')}}" class="brand-link d-flex align-items-center justify-content-center">
        <i class="fas fa-vote-yea me-2" style="font-size: 1.5rem; margin-right: 0.5rem;"></i>
        <span class="brand-text font-weight-bold">E-Voting System</span>
      </a>
      <div class="sidebar">
        <div class="user-panel">
          <div>
            <img src="{{asset('images/uni.png')}}" class="img-circle mx-auto d-block m-1" alt="User Image" style="width: 45px; height: 45px;">
          </div>
          <div class="profile-usertitle">
            <div class="profile-usertitle-name">{{ Auth::user()->adminAccount->firstName  }}</div>
            <div class="profile-usertitle-job">{{ Auth::user()->adminAccount->role }}</div>
          </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline d-none">
          <div class="input-group" data-widget="sidebar-search">
            <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
            <div class="input-group-append">
              <button class="btn btn-sidebar">
                <i class="fas fa-search fa-fw"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <!-- DASHBOARD SECTION -->
            <li class="nav-item">
              <a href="{{asset('admin')}}" class="nav-link admin-nav-dashboard text-white">
                <i class="nav-icon fas fa-home"></i>
                <p>Dashboard</p>
              </a>
            </li>

            <!-- VOTING MANAGEMENT SECTION -->
            <li class="nav-header" data-group="voting-management">VOTING MANAGEMENT</li>
            <div class="nav-group" id="voting-management-group">
              <li class="nav-item">
                <a href="{{asset('admin/candidate')}}" class="nav-link admin-nav-candidates text-white">
                  <i class="fas fa-user-tie nav-icon"></i>
                  <p>Candidates</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/agenda')}}" class="nav-link admin-nav-agendas text-white">
                  <i class="fas fa-list-ol nav-icon"></i>
                  <p>Agendas</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/amendment')}}" class="nav-link admin-nav-amendments text-white">
                  <i class="fas fa-file-alt nav-icon"></i>
                  <p>Amendments</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/ballots')}}" class="nav-link admin-nav-ballots text-white">
                  <i class="fas fa-vote-yea nav-icon"></i>
                  <p>Ballots</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/ballots/export')}}" class="nav-link admin-nav-ballots-summary text-white">
                  <i class="fas fa-poll nav-icon"></i>
                  <p>Ballots Summary</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/attendance')}}" class="nav-link admin-nav-attendance text-white">
                  <i class="fas fa-calendar-check nav-icon"></i>
                  <p>Attendance</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{asset('admin/announcement')}}" class="nav-link admin-nav-announcement text-white">
                  <i class="fas fa-bullhorn nav-icon"></i>
                  <p>Announcement</p>
                </a>
              </li>


              <li class="nav-item">
                <a href="{{asset('admin/available-vote-inquiry')}}" class="nav-link admin-nav-available-vote-inquiry text-white">
                  <i class="fas fa-search nav-icon"></i>
                  <p>Available Vote Inquiry</p>
                </a>
              </li>


              @if(Auth::user()->role === 'superadmin')

              <li class="nav-item">
                <a href="{{asset('admin/stock')}}" class="nav-link admin-nav-stock text-white">
                  <i class="fas fa-bullhorn nav-icon"></i>
                  <p>Stocks</p>
                </a>
              </li>
              @endif




            </div>

            <!-- USER MANAGEMENT SECTION -->
            <li class="nav-header" data-group="user-management">USER MANAGEMENT</li>
            <div class="nav-group" id="user-management-group">
              <li class="nav-item">
                <a href="{{asset('admin/stockholder')}}" class="nav-link admin-nav-members text-white">
                  <i class="fas fa-users nav-icon"></i>
                  <p>Stockholders</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/non-member')}}" class="nav-link admin-nav-non-member text-white">
                  <i class="fas fa-user-plus nav-icon"></i>
                  <p>Non-Members</p>
                </a>
              </li>


              <li class="nav-item">
                <a href="{{asset('admin/online-accounts')}}" class="nav-link admin-nav-online-accounts text-white">
                  <i class="fas fa-user-plus nav-icon"></i>
                  <p>Online Accounts</p>
                </a>
              </li>
            </div>



            <!-- PROXY MANAGEMENT SECTION -->
            <li class="nav-header" data-group="proxy-management">PROXY BOD</li>
            <div class="nav-group" id="proxy-management-group">



              <li class="nav-item">
                <a href="{{asset('admin/bod-proxy/masterlist')}}" class="nav-link admin-nav-bod-proxy-holders-masterlist text-white">
                  <i class="fas fa-file-signature nav-icon"></i>
                  <p>Masterlist</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/bod-proxy')}}" class="nav-link admin-nav-bod-proxy-holders text-white">
                  <i class="fas fa-file-signature nav-icon"></i>
                  <p>Active</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/bod-proxy/summary')}}" class="nav-link admin-nav-bod-proxy-holders-summary text-white">
                  <i class="fas fa-chart-pie nav-icon"></i>
                  <p>Summary</p>
                </a>
              </li>

            </div>


            <!-- AMENDMENT MANAGEMENT SECTION -->
            <li class="nav-header" data-group="proxy-management-amendment">PROXY AMENDMENT</li>
            <div class="nav-group" id="proxy-management-amendment-group">



              <li class="nav-item">
                <a href="{{asset('admin/amendment-proxy/masterlist')}}" class="nav-link admin-nav-amendment-proxy-holders-masterlist text-white">
                  <i class="fas fa-file-signature nav-icon"></i>
                  <p>Masterlist</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/amendment-proxy')}}" class="nav-link admin-nav-amendment-proxy-holders text-white">
                  <i class="fas fa-file-signature nav-icon"></i>
                  <p>Active</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/amendment-proxy/summary')}}" class="nav-link admin-nav-amendment-proxy-holders-summary text-white">
                  <i class="fas fa-chart-pie nav-icon"></i>
                  <p>Summary</p>
                </a>
              </li>
            </div>







            <!-- DOCUMENTS SECTION -->
            <li class="nav-header collapsed" data-group="documents">DOCUMENTS</li>
            <div class="nav-group collapsed" id="documents-group">
              <li class="nav-item">
                <a href="{{asset('admin/document')}}" class="nav-link admin-nav-bulletin text-white">
                  <i class="fas fa-folder-open nav-icon"></i>
                  <p>Document Library</p>
                </a>
              </li>
            </div>


            <!-- ADMINISTRATION SECTION -->
            <li class="nav-header" data-group="administration">ADMINISTRATION</li>
            <div class="nav-group" id="administration-group">
              <li class="nav-item">
                <a href="{{asset('admin/admin-account')}}" class="nav-link admin-nav-account text-white">
                  <i class="fas fa-user-cog nav-icon"></i>
                  <p>Admin Accounts</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/role')}}" class="nav-link admin-nav-role-and-permissions text-white">
                  <i class="fas fa-user-shield nav-icon"></i>
                  <p>Roles</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/setting')}}" class="nav-link admin-nav-setting text-white">
                  <i class="fas fa-cogs nav-icon"></i>
                  <p>Settings</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{asset('admin/activity')}}" class="nav-link admin-nav-activity text-white">
                  <i class="fas fa-clipboard-list nav-icon"></i>
                  <p>Activity Logs</p>
                </a>
              </li>
            </div>

          </ul>
        </nav>

      </div>
    </aside>
    @yield('content')
    <aside class="control-sidebar control-sidebar-dark"></aside>
  </div>

  <!-- Change Password Modal -->
  <div class="modal fade" id="changePasswordModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title d-flex align-items-center">
            <i class="fas fa-key me-2" style="margin-right: 0.75rem;"></i>
            Change Password
          </h4>
          <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.8;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <!-- Modal body -->
        <form method="POST" id="change_password_form">
          <div class="modal-body">
            <div class="container-fluid">
              <div class="alert alert-danger rounded" role="alert" id="error_message" style="display: none; border-radius: 0.75rem;"></div>

              <div class="form-group mb-4">
                <label class="form-label fw-semibold mb-2">Current Password</label>
                <div class="pass_show">
                  <input type="password" name="current_password" class="form-control" placeholder="Enter your current password" required minlength="6" maxlength="20">
                </div>
              </div>

              <div class="form-group mb-4">
                <label class="form-label fw-semibold mb-2">New Password</label>
                <div class="pass_show">
                  <input type="password" name="new_password" class="form-control" placeholder="Enter your new password" required minlength="6" maxlength="20">
                </div>
              </div>

              <div class="form-group mb-4">
                <label class="form-label fw-semibold mb-2">Confirm Password</label>
                <div class="pass_show">
                  <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your new password" required minlength="6" maxlength="20">
                </div>
              </div>
            </div>
          </div>
          <!-- Modal footer -->
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-dismiss="modal" style="border-radius: 0.75rem; padding: 0.75rem 1.5rem; font-weight: 500;">Cancel</button>
            <button type="submit" class="btn btn-custom-darkg">
              <i class="fas fa-save me-1" style="margin-right: 0.5rem;"></i>
              Change Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- RIGHT SIDE SLIDING MODAL (NO OVERLAY) -->
  <div class="corporate-right-slide-modal d-flex flex-column" id="corporateRightSlideModal" style="height:100vh;">
    <!-- Modal Header -->
    <div class="corporate-right-slide-modal-header">
      <div class="d-flex align-items-center">
        <div class="corporate-right-slide-modal-icon">
          <i id="corporateRightSlideModalIcon" class="fas fa-cog"></i>
        </div>
        <div class="ml-3">
          <h6 class="corporate-right-slide-modal-title" id="corporateRightSlideModalTitle">Modal <Title></Title>
          </h6>
          <small class="corporate-right-slide-modal-subtitle" id="corporateRightSlideModalSubtitle">Modal subtitle</small>
        </div>
      </div>
      <button class="corporate-right-slide-modal-close" id="corporateRightSlideModalClose" type="button" data-dismiss="modal" aria-label="Close">
        <i class="fas fa-times"></i>
      </button>

    </div>

    <!-- Modal Body -->
    <div class="corporate-right-slide-modal-body modal-body overflow-auto flex-grow-1" style="max-height:unset;" id="corporateRightSlideModalBody">
      <!-- Actual Content (Hidden initially) -->
      <div class="corporate-right-modal-content" id="corporateRightModalContent">
      </div>
    </div>

    <!-- Modal Footer -->
    <div class="corporate-right-slide-modal-footer" id="corporateRightSlideModalFooter">
      <!-- Actual Footer Content -->
    </div>
  </div>
  <!-- END RIGHT SIDE SLIDING MODAL -->

  <!-- overlayScrollbars -->
  <script src="{{asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>


  <!-- AdminLTE App -->
  <script src="{{asset('dist/js/adminlte.js')}}"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      $('.pass_show').append('<span class="ptxt">Show</span>');
    });

    $(document).on('click', '#menu_change_password', function() {
      $('#change_password_form')[0].reset();
      $('#change_password_form input[type=text]').attr('type', 'password');
      $('#change_password_form .ptxt').text('Show');
      $('#changePasswordModal').modal('show');
    })

    $(document).on('submit', '#change_password_form', function(e) {
      e.preventDefault();
      $.ajax({
        url: "{{asset('admin/password/change')}}",
        method: "POST",
        dataType: 'json',
        data: $(this).serialize(),
        beforeSend: function() {
          let newPassword = $('[name=new_password]').val();
          let confirmPassword = $('[name=confirm_password]').val();
          if (newPassword !== confirmPassword) {
            $('#error_message').text('New password and confirm password field did not match!').show();
            this.abort();
          }
        },
        statusCode: {

          200: function(data) {

            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: data.message,
            }).then(() => {
              $('#change_password_form')[0].reset();
              $('#changePasswordModal').modal('hide');
            })

          },

          400: function(data) {

            alert(data["responseJSON"]["message"]);

          },

          401: function() {
            alert(UNAUTHORIZED);
          },

          403: function() {
            alert(FORBIDDEN);
          },

          419: function() {

            alert(SESSION_TIMEOUT);

          },

          500: function() {
            alert(SERVER_ERROR);
          }
        }
      })
    })

    $(document).on('click', '.pass_show .ptxt', function() {
      $(this).text($(this).text() == "Show" ? "Hide" : "Show");
      $(this).prev().attr('type', function(index, attr) {
        return attr == 'password' ? 'text' : 'password';
      });
    });

    // Navigation group collapsible functionality
    $(document).on('click', '.nav-header[data-group]', function() {
      const groupName = $(this).data('group');
      const group = $('#' + groupName + '-group');
      const header = $(this);

      if (group.hasClass('collapsed')) {
        // Expand
        group.removeClass('collapsed');
        header.removeClass('collapsed');
      } else {
        // Collapse
        group.addClass('collapsed');
        header.addClass('collapsed');
      }
    });

    // Auto-expand the section containing the active page
    $(document).ready(function() {
      const activeLink = $('.nav-sidebar .nav-link.active');
      if (activeLink.length) {
        const parentGroup = activeLink.closest('.nav-group');
        if (parentGroup.length) {
          const groupId = parentGroup.attr('id');
          const groupName = groupId.replace('-group', '');
          const header = $('.nav-header[data-group="' + groupName + '"]');

          parentGroup.removeClass('collapsed');
          header.removeClass('collapsed');
        }
      }
    });

    // Global cache-busting function for AJAX requests
    $.ajaxSetup({
      cache: false,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        xhr.setRequestHeader('Pragma', 'no-cache');
        xhr.setRequestHeader('Expires', '0');
      }
    });

    // Add timestamp to all AJAX URLs to prevent caching
    $(document).ajaxSend(function(event, jqXHR, options) {
      if (options.url.indexOf('?') === -1) {
        options.url += '?_=' + new Date().getTime();
      } else {
        options.url += '&_=' + new Date().getTime();
      }
    });

    $(document).ready(function() {
      // Intercept Ballots and Ballots Summary sidebar links
      $(document).on('click', '.admin-nav-ballots, .admin-nav-ballots-summary', function(e) {
        e.preventDefault();
        const link = $(this).attr('href');
        Swal.fire({
          title: 'Are you sure?',
          text: 'You are about to leave this page and view Ballots. Continue?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, continue',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = link;
          }
        });
      });
    });
  </script>


  <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
  @yield('js')
</body>

</html>