<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>RiftBets</title>

    <!-- Styles -->
    <link href="/css/app.css" rel="stylesheet">

    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
    <script>
        let store = {
            state: {
                user: {
                    name: {!! Auth::user() ? '\'' . Auth::user()->name . '\'' : 'null' !!},
                    email: {!! Auth::user() ? '\'' . Auth::user()->email . '\'' : 'null' !!},
                    credits: {{ Auth::user() ? Auth::user()->credits : 'null' }},
                    loggedIn: {{ Auth::check() == true ? 'true' : 'false'}},
                    token: null,
                }
            },
        };

        function  deleteCookie(name) {
            document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }

        function setState(user) {
            console.log('state set');
            store.state.user = user;
        }
    </script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        RiftBets
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <navbar></navbar>
                </div>
            </div>
        </nav>
        @yield('content')
    </div>
    <script src="/js/app.js"></script>
    <!-- Scripts -->
</body>
</html>
