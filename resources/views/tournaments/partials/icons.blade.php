<div class="text-xs-center small-text">
    <button id="show-exp-icons" class="btn btn-primary btn-xs {{ @$profile ? '' : 'pull-right' }} m-b-1"
       onclick="$('#exp-icons').removeClass('hidden-xs-up'); $('#show-exp-icons').addClass('hidden-xs-up')">
        <i class="fa fa-eye" aria-hidden="true"></i>
        explain icons
    </button>
    <br/>&nbsp;
    <div id="exp-icons" class="hidden-xs-up">
        @unless(@$profile)
            <i title="charity" class="fa fa-heart text-danger"></i>
            charity |
            <img class="img-patron-o">
            patreon T.O. |
            <i title="match data" class="fa fa-handshake-o"></i>
            match data, points available |
            <i title="top cut" class="fa fa-scissors"></i>
            top cut |
            <i title="video" class="fa fa-camera"></i>
            has photo |
            <i title="video" class="fa fa-video-camera"></i>
            has video |
            <i title="multiple day event" class="fa fa-plus-circle text-primary"></i>
            multiple day event <br/>
        @endunless
        <span class="tournament-type type-store" title="store championship">S</span> store championship |
        <span class="tournament-type type-regional" title="regional championship">R</span> regional championship |
        <span class="tournament-type type-national" title="national championship">N</span> national championship |
        <span class="tournament-type type-continental" title="continental championship">C</span> continental championship |
        <span class="tournament-type type-world" title="worlds championship">W</span> worlds championship
        <br/>
        <span class="tournament-format type-cache" title="cache refresh">CR</span> cache refresh |
        <span class="tournament-format type-onesies" title="1.1.1.1">1</span> 1.1.1.1 |
        <span class="tournament-format type-draft" title="draft">D</span> draft |
        <span class="tournament-format type-cube-draft" title="cube draft">CD</span> cube draft
    </div>
</div>