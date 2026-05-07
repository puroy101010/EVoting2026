<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="icon" type="image/png" href="{{asset('images/golf.png')}}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Web Dashboard</title>


    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">


    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{asset('css/fontawesome-free/css/all.min.css')}}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css')}}">


    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>



    @yield('css')
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#"> WEB ADMIN</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar1" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar1">
        <ul class="navbar-nav ml-auto"> 
          <li class="nav-item">
            <a href="{{asset('web-admin')}}" class="nav-link">SYSTEM LOGS</a>
          </li>
          <li class="nav-item">
            <a href="{{asset('web-admin/email')}}" class="nav-link" href="#">EMAILS</a>
          </li>
          <li class="nav-item">
            <a href="{{asset('web-admin/audit')}}" class="nav-link" href="#">AUDIT</a>
          </li>
  <!--         <li class="nav-item dropdown">
            <a class="nav-link  dropdown-toggle" href="#" data-toggle="dropdown">  Dropdown  </a>
              <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#"> Menu item 1</a></li>
              <li><a class="dropdown-item" href="#"> Menu item 2 </a></li>
              </ul>
          </li> -->
        </ul>
      </div>
  </nav>



    @yield('content')


  <!-- overlayScrollbars -->
  <script src="{{asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>

  <!-- AdminLTE App -->
  <script src="{{asset('dist/js/adminlte.js')}}"></script>
</body>
</html>
