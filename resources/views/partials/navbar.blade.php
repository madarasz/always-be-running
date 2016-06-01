<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

{{--Navigation bar--}}
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="/#"><img src="" /></a>
            <a class="navbar-brand" href="/#">Always be Running.net</a>
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
                <li><a href="/#">Upcoming</a></li>
                @if (Auth::check())
                    <li><a href="/tournaments/create">Create</a></li>
                    <li><a href="/my">My Tournaments</a></li>
                    @if (Auth::user()->admin == 1)
                        <li><a href="/admin">Admin</a></li>
                    @endif
                    <li><a href="/logout">Logout</a></li>
                @else
                    <li><a href="/oauth2/redirect">Login via NetrunnerDB</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>