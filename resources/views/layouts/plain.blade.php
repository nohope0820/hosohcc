<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Tờ khai nhà đất')</title>
<style>
@include('partials.style')
</style>
</head>
<body>
<div class="wrap"><div class="main" style="max-width:1100px">
  @include('partials.nav')
  <h1>@yield('title')</h1>
  <p class="sub">@yield('sub')</p>
  @yield('content')
</div></div>
@yield('scripts')
</body>
</html>
