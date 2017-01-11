{{--Tournament description--}}
<div class="bracket">
    @if (strlen($tournament->description) > 1000)
        <div class="more-container collapse" id="more-collapse">
            <div class="more-overlay" id="more-overlay"></div>
            <a name="more-top"/>
            <div id="tournament-description" class="markdown-content">
                {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->description)) !!}
            </div>
        </div>
        <div id="more-more">
            <i class="fa fa-caret-right" aria-hidden="true"></i>
            <a data-toggle="collapse" href="#more-collapse" aria-expanded="false" aria-controls="more-collapse">
                more...
            </a>
        </div>
        <div id="more-less" class="hidden-xs-up">
            <i class="fa fa-caret-up" aria-hidden="true"></i>
            <a data-toggle="collapse" href="#more-collapse" aria-expanded="false" aria-controls="more-collapse">
                less...
            </a>
        </div>
        <script type="text/javascript">
            $('#more-collapse').on('shown.bs.collapse', function () {
                $('#more-collapse').css({
                    'max-height': 'none'
                });
                $('#more-overlay').addClass('hidden-xs-up');
                $('#more-more').addClass('hidden-xs-up');
                $('#more-less').removeClass('hidden-xs-up');
            });
            $('#more-collapse').on('hidden.bs.collapse', function () {
                $('#more-collapse').css({
                    'max-height': '400px'
                });
                $('#more-overlay').removeClass('hidden-xs-up');
                $('#more-more').removeClass('hidden-xs-up');
                $('#more-less').addClass('hidden-xs-up');
                location.hash = "#more-top"; location.hash = "#";
            })
        </script>
    @else
        <div id="tournament-description" class="markdown-content">
            {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->description)) !!}
        </div>
    @endif
</div>