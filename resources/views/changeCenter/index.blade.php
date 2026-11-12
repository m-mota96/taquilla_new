<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Taquilla TicketLand</title>
    <link rel="stylesheet" href="{{asset('css/bootstrap5.3.0.min.css')}}">
    <link rel="stylesheet" href="{{asset('fontawesome6.4.0/css/all.css')}}">
    <link rel="stylesheet" href="{{asset('css/general_customers.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('heads')
</head>
<body class="bg-body-secondary">
    @include('changeCenter.menuUp')
    {{-- @include('customers.menuDown') --}}
    @yield('content')
    <input type="hidden" id="URL" value="{{asset('')}}">
    <script src="{{asset('js/bootstrap5.3.0.bundle.min.js')}}"></script>
    <script src="{{asset('js/jquery-3.7.0.min.js')}}"></script>
    <script src="{{asset('js/sweetalert2.js')}}"></script>
    @yield('scripts')
</body>
</html>