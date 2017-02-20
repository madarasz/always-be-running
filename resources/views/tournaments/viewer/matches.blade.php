{{--Tournament mathces--}}
<div class="bracket">
    <h5>
        <i class="fa fa-handshake-o" aria-hidden="true"></i>
        Matches
        <div class="pull-right">
            <button class="btn btn-primary btn-xs disabled" id="button-showmatches" disabled onclick="displayMatches({{ $tournament->id }}, true)">
                <i class="fa fa-eye" aria-hidden="true"></i>
                show
            </button>
            <button class="btn btn-primary btn-xs hidden-xs-up" id="button-hidematches" onclick="displayMatches({{ $tournament->id }}, false)">
                <i class="fa fa-eye-slash" aria-hidden="true"></i>
                hide
            </button>
        </div>
    </h5>
    <div id="content-matches" class="hidden-xs-up">
        <div id="loader-content" class="hidden-xs-up loader">loading</div>
        {{--Top cut--}}
        <h6 class="hidden-xs-up" id="header-top">
            Top-cut
        </h6>
        {{--Missing top--}}
        <div class="alert alert-warning view-indicator hidden-xs-up" id="warning-matches-top">
            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
            Elimination match data is missing.
        </div>
        <div id="tree-top"></div>
        {{--double elimination tree iframe, to avoid nasty CSS clashes--}}
        <iframe src="/elimination" id="iframe-tree"></iframe>
        <table id="table-matches-top" class="table-match hidden-xs-up m-b-2">
        </table>
        {{--Swiss rounds--}}
        <h6>Swiss rounds</h6>
        {{--Missing swiss--}}
        <div class="alert alert-warning view-indicator hidden-xs-up" id="warning-matches-swiss">
            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
            Swiss match data is missing.
        </div>
        <table id="table-matches-swiss" class="table-match">
        </table>
    </div>
</div>