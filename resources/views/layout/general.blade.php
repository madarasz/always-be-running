<!DOCTYPE html>
<html>
<head>
    <title>Tournaments - Decklists - Game of Thrones</title>
    <link rel="stylesheet" href="css/all.css">

</head>
<body>

    <!--[if lt IE 8]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->

    {{--Navigation bar--}}
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="/#"><img src="" /></a>
                <a class="navbar-brand" href="/#">GoT Tournaments</a>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse navbar-right" id="navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="/#">Home</a></li>
                    <li><a href="/#">Results</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Upcoming<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="/">List</a>
                            </li>
                            <li>
                                <a href="/">Calendar</a>
                            </li>
                        </ul>
                    </li>
                    <li><a href="/create">Create</a></li>
                    <li><a href="/">My Tournaments</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
    @yield('content')
    </div>

    <script type="text/javascript" src="js/all.js"></script>
</body>
</html>
