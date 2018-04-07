<!DOCTYPE HTML>
<html>
<head>
    <title>AlwaysBeRunning.Net - Tournaments - Decklists - Netrunner</title>
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="{{ elixir('css/all.css') }}">
    <script type="text/javascript" src="{{ elixir('js/all.js') }}"></script>
    <!-- Begin Cookie Consent plugin by Silktide - http://silktide.com/cookieconsent -->
    <script type="text/javascript">
        window.cookieconsent_options = {"message":"This website uses cookies to ensure you get the best experience on our website","dismiss":"Got it!","learnMore":"More info","link":null,"theme":"dark-bottom"};
    </script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.10/cookieconsent.min.js"></script>
    <!-- End Cookie Consent plugin -->
    @include('partials.facebook-og')
</head>
<body>

    @include('partials.navbar')

    <div class="container body-container">
    @yield('content')

    @include('partials.footer')
    </div>

    @include('partials.analytics')

    <script type="text/javascript">
        // Javascript to enable selecting tab
        var url = document.location.toString();
        if (url.match('#')) {
            $(".nav-tabs a[href='#"+url.split('#')[1]+"']").tab('show');
        }

        // Change URL hash for tab
        $('.nav-tabs a').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
            window.scrollTo(0,0);
        });
    </script>
</body>
</html>