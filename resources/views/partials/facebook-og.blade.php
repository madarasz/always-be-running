{{--meta tags for facebook open graph protocol--}}
<meta property="og:title" content="Always be Running.net"/>
@if (@$tournament)
    <meta property="og:description" content="{!! substr($tournament->title.' - '.strip_tags(Markdown::convertToHtml($tournament->description)),0,300).'...' !!}" />
    <?php $containsImage = preg_match('/!\[([^\]]*)\]\(([^)]+)\)/', $tournament->description); ?>
    @if (!$containsImage)
        <meta property="og:image" content="https://alwaysberunning.net/ms-icon-70x70.png" />
    @endif
@else
    <meta property="og:description" content="Tournament finder and tournament results for Android: Netrunner card game" />
    <meta property="og:image" content="https://alwaysberunning.net/ms-icon-70x70.png" />
@endif
