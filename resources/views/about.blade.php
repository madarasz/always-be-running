@extends('layout.general')

@section('content')
    <h4 class="page-header">About</h4>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            {{--Contact--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-envelope" aria-hidden="true"></i>
                    Contact
                </h5>
                <p>This site is created by <strong>Necro</strong>.</p>
                <p>
                    You can contact me via:
                    <ul>
                        <li>
                            <i class="fa fa-slack" aria-hidden="true"></i>
                            Stimhack Slack <a href="#" onclick="goToSlackChannel()">#abr</a> channel
                        </li>
                        <li>
                            <i class="fa fa-envelope-o" aria-hidden="true"></i> alwaysberunning (at) gmail.com
                        </li>
                        <li>
                            <i class="fa fa-twitter" aria-hidden="true"></i>
                            <a href="https://twitter.com/alwaysberunnin">{{'@'}}alwaysberunnin</a>
                        </li>
                        <li>
                            <i class="fa fa-facebook-official" aria-hidden="true"></i>
                            <a href="https://www.facebook.com/alwaysberunning/">/alwaysberunning</a>
                        </li>
                        <li>
                            <i class="fa fa-github" aria-hidden="true"></i>
                            bugs and feature requests: <a href="https://github.com/madarasz/always-be-running">Github</a>
                        </li>
                    </ul>
                </p>
                <p>All ideas, suggestions, feedback are welcome.</p>
            </div>
            {{--Admins--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-globe" aria-hidden="true"></i>
                    Admins
                </h5>
                <div class="m-b-1">Check out their profiles for contact information.</div>
                <ul>
                    <li>
                        <strong>UK</strong>
                        <ul>
                            <li><a href="/profile/6966" class="admin">lpoulter</a></li>
                            <li><a href="/profile/1221" class="admin">johno</a></li>
                            <li><a href="/profile/28653" class="admin">Blue Haired Hacker Girl</a> (NISEI)</li>
                        </ul>
                    </li>
                    <li>
                        <strong>US</strong>
                        <ul>
                            <li><a href="/profile/9571" class="admin">Murphy</a></li>
                            <li><a href="/profile/6075" class="admin">jase2224</a></li>
                            <li><a href="/profile/16051" class="admin">JDC_Wolfpack</a></li>
                            <li><a href="/profile/3527" class="admin">icecoldjazz</a> (NISEI)</li>
                        </ul>
                    </li>
                    <li>
                        <strong>New Zealand / Australia:</strong>
                        <ul>
                            <li><a href="/profile/491" class="admin">Guv_bubbs</a></li>
                            <li><a href="/profile/7806" class="admin">Inactivist</a> (NISEI)</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Germany</strong>
                        <ul>
                            <li><a href="/profile/1149" class="admin">SpaceHonk</a></li>
                            <li><a href="/profile/18163" class="admin">5N00P1</a></li>
                        </ul>
                    </li>
                    <li>
                        <strong>Sweden</strong>
                        <ul>
                            <li><a href="/profile/7664" class="admin">Gejben</a></li>
                            <li><a href="/profile/1361" class="admin">hnautsch</a></li>
                        </ul>
                    </li>
                    <li>
                        <strong>Benelux region + Greece</strong>
                        <ul>
                            <li><a href="/profile/14808" class="admin">Kelfecil</a></li>
                        </ul>
                    </li>
                    <li>
                        <strong>Spain</strong>
                        <ul>
                            <li><a href="/profile/1139" class="admin">vesper</a> (NISEI)</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Central Europe</strong>
                        <ul>
                            <li><a href="/profile/9871" class="admin">TwadaCZ</a></li>
                            <li><a href="/profile/1276" class="admin">Necro</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            {{--Developers--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-laptop" aria-hidden="true"></i>
                    Developers
                </h5>
                <ul>
                    <li><a href="/profile/1276" class="admin">Necro</a> - site owner, main developer</li>
                    <li><a href="/profile/1221" class="admin">johno</a> - youtube tournament videos</li>
                </ul>
            </div>
            {{--Helpers--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-heart" aria-hidden="true"></i>
                    Much appreciated
                </h5>
                Thank you to these awesome people for their technical help, sending ideas or bug reports:
                <ul class="p-t-1">
                    @foreach($helpers as $helper)
                        <li><a href="profile/{{$helper->id}}" class="{{$helper->linkClass()}}">{{$helper->displayUsername()}}</a></li>
                    @endforeach
                </ul>
            </div>
            {{--Acknowledgements--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-child" aria-hidden="true"></i>
                    Acknowledgements
                </h5>
                <p>
                    Thank you to the wonderful people for making these software available:
                    <ul>
                        <li><a href="https://netrunnerdb.com">NetrunnerDB</a></li>
                        <li><a href="https://laravel.com/">Laravel</a></li>
                        <li><a href="https://github.com/haleks/laravel-markdown">Laravel Markdown</a></li>
                        <li><a href="https://github.com/webpatser/laravel-countries">Laravel Countries</a></li>
                        <li><a href="https://github.com/eternicode/bootstrap-datepicker">Datepicker for Bootstrap</a></li>
                        <li><a href="http://www.codrops.com">jquery.calendario.js</a></li>
                        <li><a href="https://github.com/Lusitanian/PHPoAuthLib">PHPoAuthLib</a></li>
                        <li><a href="http://www.aropupu.fi/bracket">jQuery Bracket</a></li>
                        <li><a href="http://fontawesome.io/">Font Awesome</a></li>
                    </ul>
                </p>
            </div>
        </div>
    </div>
@stop

