<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

{{--Navigation bar--}}
<nav class="navbar navbar-dark bg-inverse navbar-fixed-top" role="navigation" style="background-color: #222;">
    <div class="container">
        <a class="navbar-brand" href="/#">
            <img src="/img/logo_small.png" class="navbar-brand-image"/>
            Always be Running.net
        </a>
        <button class="navbar-toggler hidden-sm-up" type="button" data-toggle="collapse" data-target="#navbar-collapse-1">
            &#9776;
        </button>
        <div class="collapse navbar-toggleable-xs" id="navbar-collapse-1">
            <ul class="nav navbar-nav pull-left">
                <li class="nav-item"><a class="nav-link" href="/discover">Discover</a></li>
                <li class="nav-item"><a class="nav-link" href="/results">Results</a></li>
                <li class="nav-item"><a class="nav-link" href="/organize">Organize</a></li>
                @if (Auth::check() && Auth::user()->admin == 1)
                    <li class="nav-item"><a class="nav-link" href="/admin">Admin</a></li>
                @endif
            </ul>
            <ul class="nav navbar-nav pull-right">
                @if (Auth::check())
                    <li class="nav-item"><a class="nav-link" href="/personal">{{ Auth::user()->name }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="/logout">Logout</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="/oauth2/redirect">Login via NetrunnerDB</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>