<!DOCTYPE html>
<html lang="en">

<head>
  <title>Online Voting System</title>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{{asset('images/small_logo.png')}}" />
  <!-- No Cache Meta Tags -->
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  <!-- Modern Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  @php
  $__chapaza_ver = @filemtime(public_path('css/user/Chapaza.ttf')) ?: time();
  $__avenir_black_ver = @filemtime(public_path('css/user/AvenirLTStd-Black.otf')) ?: time();
  $__avenir_roman_ver = @filemtime(public_path('css/user/AvenirLTStd-Roman.otf')) ?: time();
  @endphp

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
</head>

<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Global jQuery AJAX setup to prevent caching and set CSRF + cache headers
  $.ajaxSetup({
    cache: false,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'Cache-Control': 'no-cache, no-store, must-revalidate',
      'Pragma': 'no-cache',
      'Expires': '0'
    },
    beforeSend: function(xhr) {
      xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
      xhr.setRequestHeader('Pragma', 'no-cache');
      xhr.setRequestHeader('Expires', '0');
    }
  });

  // Append a cache-busting timestamp to every AJAX URL (best-effort)
  $(document).ajaxSend(function(event, jqXHR, options) {
    try {
      if (typeof options.url === 'string') {
        if (options.url.indexOf('?') === -1) {
          options.url += '?_=' + new Date().getTime();
        } else {
          options.url += '&_=' + new Date().getTime();
        }
      }
    } catch (e) {
      // ignore
    }
  });
</script>

<style>
  /* Modern CSS Variables */
  :root {
    --primary-green: #5E7C4C;
    --dark-green: #2F4A3C;
    --light-bg: #f8f9fa;
    --text-primary: #2c3e50;
    --text-secondary: #7f8c8d;
    --white: #ffffff;
    --border-light: #e9ecef;
    --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
    --shadow-medium: 0 4px 20px rgba(0, 0, 0, 0.15);
    --border-radius: 12px;
    --transition: all 0.3s ease;
  }

  /* Reset and Base Styles */
  * {
    box-sizing: border-box;
  }

  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background-color: var(--light-bg);
    margin: 0;
    padding: 0;
    line-height: 1.6;
    color: var(--text-primary);
  }

  /* Modern Header */
  .modern-header {
    background: var(--white);
    border-bottom: 1px solid var(--border-light);
    box-shadow: var(--shadow-light);
    position: sticky;
    top: 0;
    z-index: 1000;
  }

  .header-top {
    background: var(--primary-green);
    color: var(--white);
    padding: 0.5rem 0;
    font-size: 0.875rem;
  }

  .header-main {
    padding: 1rem 0;
  }

  .logo-section {
    display: flex;
    align-items: center;
  }

  .logo-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: inherit;
    transition: var(--transition);
  }

  .logo-link:hover {
    text-decoration: none;
    color: inherit;
    opacity: 0.8;
  }

  .logo-link:hover .company-name {
    color: var(--dark-green);
  }

  .company-logo {
    max-height: 60px;
    width: auto;
  }

  .company-info {
    margin-left: 1.5rem;
    flex: 1;
  }

  .company-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-green);
    margin: 0;
  }

  .company-tagline {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin: 0;
  }

  .header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .user-menu {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    background: var(--light-bg);
    border: 1px solid var(--border-light);
    transition: var(--transition);
  }

  .user-menu:hover {
    background: var(--primary-green);
    color: var(--white);
    text-decoration: none;
  }

  .user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--primary-green);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-weight: 600;
    font-size: 0.875rem;
  }

  .logout-btn {
    background: var(--dark-green);
    color: var(--white);
    border: none;
    padding: 0.6rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }

  .logout-btn:hover {
    background: #243a2f;
    color: var(--white);
    text-decoration: none;
    transform: translateY(-1px);
  }

  /* Mobile Header */
  .mobile-header {
    display: none;
    background: var(--primary-green);
    color: var(--white);
    padding: 1rem;
    position: sticky;
    top: 0;
    z-index: 1000;
  }

  .mobile-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .mobile-logo {
    max-height: 40px;
  }

  .mobile-menu-btn {
    background: none;
    border: 2px solid var(--white);
    color: var(--white);
    padding: 0.5rem;
    border-radius: 8px;
    cursor: pointer;
  }

  .mobile-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--white);
    box-shadow: var(--shadow-medium);
    border-radius: 0 0 var(--border-radius) var(--border-radius);
  }

  .mobile-dropdown.show {
    display: block;
  }

  .mobile-menu-item {
    display: block;
    padding: 1rem;
    color: var(--text-primary);
    text-decoration: none;
    border-bottom: 1px solid var(--border-light);
    transition: var(--transition);
  }

  .mobile-menu-item:hover {
    background: var(--light-bg);
    text-decoration: none;
  }

  /* Main Content */
  .main-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
  }

  /* Modern Form Styling */
  .form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(94, 124, 76, 0.25);
  }

  .btn-primary {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
  }

  .btn-primary:hover {
    background-color: var(--dark-green);
    border-color: var(--dark-green);
  }

  .btn-secondary {
    background-color: var(--dark-green);
    border-color: var(--dark-green);
  }

  /* Modern Card Styling */
  .card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-light);
    transition: var(--transition);
  }

  .card:hover {
    box-shadow: var(--shadow-medium);
    transform: translateY(-2px);
  }

  .card-header {
    background: var(--primary-green);
    color: var(--white);
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    font-weight: 600;
  }

  /* Modal Styling */
  .modal-content {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--shadow-medium);
  }

  .modal-header {
    background: var(--primary-green);
    color: var(--white);
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    border-bottom: none;
  }

  /* Custom Checkbox */
  .custom-checkbox {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border: 2px solid var(--border-light);
    border-radius: var(--border-radius);
    transition: var(--transition);
    cursor: pointer;
  }

  .custom-checkbox:hover {
    border-color: var(--primary-green);
    background: rgba(94, 124, 76, 0.05);
  }

  .custom-checkbox input {
    display: none;
  }

  .checkbox-mark {
    width: 20px;
    height: 20px;
    border: 2px solid var(--primary-green);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
  }

  .custom-checkbox input:checked+.checkbox-content .checkbox-mark {
    background: var(--primary-green);
    color: var(--white);
  }

  /* Legacy Support for Old Classes */
  .bg-color {
    background-color: var(--primary-green) !important;
  }

  .bg-new-green {
    background-color: var(--dark-green) !important;
  }

  .btn-custom {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: var(--white);
  }

  .btn-custom:hover {
    background-color: var(--dark-green);
    border-color: var(--dark-green);
    color: var(--white);
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .modern-header {
      display: none;
    }

    .mobile-header {
      display: block;
    }

    .main-content {
      padding: 1rem 0.5rem;
    }

    .company-info {
      margin-left: 1rem;
    }

    .company-name {
      font-size: 1.25rem;
    }

    .header-actions {
      flex-direction: column;
      gap: 0.5rem;
    }
  }

  @media (max-width: 480px) {
    .main-content {
      padding: 0.5rem 0.25rem;
    }

    .user-menu {
      font-size: 0.875rem;
      padding: 0.4rem 0.8rem;
    }

    .logout-btn {
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
    }
  }

  /* Animation Classes */
  .fade-in {
    animation: fadeIn 0.3s ease-in;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Utility Classes */
  .text-primary-green {
    color: var(--primary-green) !important;
  }

  .bg-primary-green {
    background-color: var(--primary-green) !important;
  }

  .border-primary-green {
    border-color: var(--primary-green) !important;
  }
</style>

@yield('head')

<script>
  const BASE_URL = "{{ asset('/') }}";

  function handleSuccess(xhr, reload = true) {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: xhr.message,
      confirmButtonColor: '#5E7C4C'
    }).then(() => {
      if (reload) location.reload();
    });
  }

  function handleError(xhr) {
    let icon = 'error';
    let title = 'Error!';
    let message = xhr.responseJSON?.message || 'An unexpected error occurred';

    if (xhr.status === 400 || xhr.status === 403) {
      icon = 'info';
      title = 'Info!';
    }

    if (xhr.status === 403) {
      message = 'Insufficient privileges';
    }

    if (xhr.status === 500) {
      message = "An unexpected error was encountered. Please contact your administrator if the error persists.";
    }

    Swal.fire({
      icon: icon,
      title: title,
      text: message,
      confirmButtonColor: '#5E7C4C'
    });
  }

  // Mobile menu toggle
  function toggleMobileMenu() {
    const dropdown = document.querySelector('.mobile-dropdown');
    dropdown.classList.toggle('show');
  }

  // Close mobile menu when clicking outside
  document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.mobile-dropdown');
    const btn = document.querySelector('.mobile-menu-btn');

    if (dropdown && btn && !dropdown.contains(event.target) && !btn.contains(event.target)) {
      dropdown.classList.remove('show');
    }
  });
</script>

<body>
  <!-- Modern Desktop Header -->
  <header class="modern-header">
    <div class="header-top">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center">
            <small>Don Celso S. Tuason Ave. • Antipolo City 1870 • Philippines • Tel: 8658 4901-03 • Fax: 8658 4919</small>
          </div>
        </div>
      </div>
    </div>

    <div class="header-main">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-8">
            <div class="logo-section">
              <a href="{{ asset('') }}" class="logo-link">
                <img src="{{asset('images/home_logo.png')}}" alt="Valley Golf Logo" class="company-logo">
                <div class="company-info">
                  <h1 class="company-name">Valley Golf & Country Club</h1>
                  <p class="company-tagline">Online Voting System</p>
                </div>
              </a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="header-actions justify-content-end">
              @if(Auth::check())
              <div class="user-menu">
                <div class="user-avatar">
                  {{ strtoupper(substr(Auth::user()->full_name ?? 'U', 0, 1)) }}
                </div>
                <span class="d-none d-lg-inline">
                  {{ \Illuminate\Support\Str::limit(Auth::user()->full_name ?? 'User', 20) }}
                </span>
              </div>
              <a href="{{ asset('logout') }}" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span class="d-none d-sm-inline">Logout</span>
              </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Mobile Header -->
  <header class="mobile-header">
    <div class="mobile-nav">
      <a href="{{ asset('') }}">
        <img src="{{asset('images/home_logo.png')}}" alt="Valley Golf Logo" class="mobile-logo">
      </a>
      <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
      </button>
    </div>

    <div class="mobile-dropdown">
      @if(Auth::check())
      <div class="mobile-menu-item">
        <i class="fas fa-user"></i> {{ Auth::user()->full_name ?? 'User' }}
      </div>
      <a href="{{ asset('logout') }}" class="mobile-menu-item">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
      @endif
    </div>
  </header>

  <!-- Main Content -->
  <main class="main-content fade-in">
    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @yield('js')
</body>

</html>