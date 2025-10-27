<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .overlay-full {
              background-color: rgba(255, 255, 255, 0.1);
              width: 100vw;
              height: 100vh;
              position: absolute;
              top: 0;
              left: 0;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height root">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        @if(Auth::user()->primary_group_id == 0)
                          @if(Auth::user()->role_id == 3)
                            <a href="/super">Home</a>
                          @elseif(Auth::user()->role_id == 2)
                            <a href="/admin">Home</a>
                          @endif
                        @else
                          <a href="/home/{{Auth::user()->primary_group_id}}">Home</a>
                        @endif
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    {{$message}}
                </div>
            </div>
        </div>
    </body>
</html>
