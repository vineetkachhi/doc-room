<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <title>Doctor Room</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

</head>

<body>

    <div id="root">
        @yield('content')
    </div>
    <script src="{{ asset('js/app.js') }}" type="text/javascript"></script>
</body>

</html>
