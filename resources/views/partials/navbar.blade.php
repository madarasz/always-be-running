<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

{{--Navigation bar--}}
<nav class="navbar navbar-dark bg-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="nav-main">
            <a class="navbar-brand" href="/#">
                <img src="/favicon-96x96.png" class="navbar-brand-image image-30x30" alt="logo"/>
                <span class="hidden-md-up">ABR</span>
                <span class="hidden-sm-down">Always be Running.net</span>
            </a>
            <button class="navbar-toggler hidden-lg-up pull-right" type="button" data-toggle="collapse" data-target="#navbar-collapse-1">
                &#9776;
            </button>
        </div>
        <div class="collapse navbar-toggleable-md" id="navbar-collapse-1">
            <ul class="nav navbar-nav pull-left">
                <li class="nav-item{{ @$page_section == 'upcoming' ? ' active' : '' }}">
                    <a class="nav-link" href="/#">Upcoming</a>
                </li>
                <li class="nav-item{{ @$page_section == 'results' ? ' active' : '' }}">
                    <a class="nav-link" href="/results">Results</a>
                </li>
                <li class="nav-item{{ @$page_section == 'videos' ? ' active' : '' }}">
                    <a class="nav-link" href="/videos">Videos</a>
                </li>
                <li class="nav-item{{ @$page_section == 'organize' ? ' active' : '' }}">
                    <a class="nav-link" href="/organize">
                        <span id="nav-organize" class="notif-red notif-badge span-nav">Organize</span>
                    </a>
                </li>
                <li class="nav-item{{ @$page_section == 'prizes' ? ' active' : '' }}">
                    <a class="nav-link" href="/prizes">
                        Prizes
                    </a>
                </li>
                @if (Auth::check() && Auth::user()->admin == 1)
                    <li class="nav-item{{ @$page_section == 'admin' ? ' active' : '' }}">
                        <a class="nav-link" href="/admin">
                            <span id="nav-admin" class="notif-red notif-badge span-nav">Admin</span>
                        </a>
                    </li>
                @endif
            </ul>
            <ul class="nav navbar-nav pull-right">
                @if (Auth::check())
                    <li class="nav-item{{ @$page_section == 'personal' ? ' active' : '' }}">
                        <a class="nav-link" href="/personal">
                            <span id="nav-personal" class="notif-red notif-badge span-nav">Personal</span>
                        </a>
                    </li>
                    <li class="nav-item{{ @$page_section == 'profile' ? ' active' : '' }}">
                        <a class="nav-link" href="/profile/{{ Auth::user()->id }}">
                            <span id="nav-profile" class="notif-green notif-badge span-nav">Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout" id="button-logout">
                            <i class="fa fa-power-off" title="Logout"></i>
                            <span class="hidden-lg-up">Logout</span>
                        </a>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="/oauth2/redirect">Login via NetrunnerDB</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>
