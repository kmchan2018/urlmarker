<!DOCTYPE html>
<html lang="en">
<head>
    <title>
        @section('title')
            URL Marker
        @show
    </title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{ url('css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ url('css/all.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ url('css/app.css') }}" type="text/css">
</head>
<body id="@yield('id')" data-url="{{ route('action') }}" data-csrf-token="{{ csrf_token() }}"
@section('data')
@show
>
    <section class="header">
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand ml-3" href="{{ route('home') }}">URL Marker</a>
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('markers') }}">Markers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('trashcan') }}">Trashcan</a>
                        </li>
                        @can('admin')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('users') }}">Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('invites') }}">Invites</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('resets') }}">Resets</a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('password_update') }}">Password</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                        </li>
                    @endauth

                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('password_reset') }}">Reset</a>
                        </li>
                    @endguest
                </ul>
            </div>
        </nav>
    </section>

    <section class="content container">
        <div class="row">
            <main class="col-8">
                @yield('main')
            </main>
            <aside class="col-4">
                @yield('aside')
            </aside>
        </div>
    </section>

    <section class="footer py-3 text-muted bg-light">
        <div class="container">
            <div class="row">
                <div class="col-6"><div class="left mx-3">This service is access controlled. Unauthorized access is strictly prohibited.</div></div>
                <div class="col-6"><div class="right">Time: {{ date('Y-m-d H:i:s') }} UTC</div></div>
            </div>
        </div>
    </section>

    <script type="text/javascript" src="{{ url('js/jquery.slim.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('js/bootstrap.bundle.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('js/app.js') }}"></script>
</body>
</html>
