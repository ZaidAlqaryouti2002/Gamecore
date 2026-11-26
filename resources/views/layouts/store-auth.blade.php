<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GameCore | @yield('title')</title>
  <link rel="icon" href="{{ asset('gamecore-favicon.svg') }}" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/gamecore-auth.css') }}">
</head>
<body class="auth-theme-soft">
  <div class="auth-backdrop" aria-hidden="true">
    <span><i class="bi bi-controller"></i></span>
    <span><i class="bi bi-headset"></i></span>
    <span><i class="bi bi-lightning"></i></span>
    <span><i class="bi bi-joystick"></i></span>
  </div>
  @yield('content')
  <script src="{{ asset('js/password-toggle.js') }}" defer></script>
</body>
</html>
