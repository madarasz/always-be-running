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
                <p>You can contact me via: alwaysberunning (at) gmail.comt</p>
                <p>All ideas, suggestions, feedback are welcome.</p>
                <p>Bugs and feaure requests: <a href="https://github.com/madarasz/always-be-running">Github</a></p>
            </div>
            {{--Admins--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-globe" aria-hidden="true"></i>
                    Admins
                </h5>
                <ul>
                    <li>
                        <strong>UK</strong>
                        <ul>
                            <li><a href="/profile/6966">lpoulter</a></li>
                            <li><a href="/profile/1221">johno</a></li>
                        </ul>
                    </li>
                    <li>
                        <strong>US</strong>
                        <ul>
                            <li><a href="/profile/9571">Murphy</a></li>
                            <li><a href="/profile/6075">jase2224</a></li>
                        </ul>
                    </li>
                    <li>
                        <strong>New Zealand / Australia:</strong>
                        <ul>
                            <li><a href="/profile/491">Guv_bubbs</a></li>
                        </ul>
                    </li>
                    <li>
                        <strong>Germany</strong>
                        <ul>
                            <li><a href="/profile/1149">SpaceHonk</a></li>
                            <li><a href="/profile/18163">5N00P1</a></li>
                        </ul>
                    </li>
                    <li>
                        <strong>Sweden</strong>
                        <ul>
                            <li><a href="/profile/7664">Gejben</a></li>
                            <li><a href="/profile/1361">hnautsch</a></li>
                        </ul>
                    </li>
                    <li>
                        <strong>Central Europe</strong>
                        <ul>
                            <li><a href="/profile/9871">TwadaCZ</a></li>
                            <li><a href="/profile/1361">Necro</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            {{--Developers--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-laptop" aria-hidden="true"></i>
                    Developers
                </h5>
                <ul>
                    <li><a href="/profile/1276">Necro</a> - site owner, main developer</li>
                    <li><a href="/profile/1221">johno</a> - youtube tournament videos</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            {{--Helpers--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-heart" aria-hidden="true"></i>
                    Much appreciated
                </h5>
                Thank you to these awesome people for their technical help, sending ideas or bug reports:
                <ul class="p-t-1">
                    @foreach($helpers as $helper)
                        <li><a href="profile/{{$helper->id}}">{{$helper->displayUsername()}}</a></li>
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

