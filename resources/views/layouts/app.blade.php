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
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

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

        function setState(user) {
            store.state.user = user;
        }
    </script>
</head>
<body>
    <div id="app">
        <!-- Navbar.vue Component -->
        <navbar></navbar>

        <!-- Testing schedule -->
        <game-schedule></game-schedule>
        @yield('content')
    </div>
    <script src="{{ mix('/js/app.js') }}"></script>
    <!-- Scripts -->
</body>
</html>
