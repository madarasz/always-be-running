{{--meta tags for facebook open graph protocol--}}
<meta property="og:title" content="Always be Running.net"/>
@if (@$tournament)
    <meta property="og:description" content="{!! substr($tournament->title.' - '.strip_tags(Markdown::convertToHtml($tournament->description)),0,300).'...' !!}" />
    @if ($tournament->coverImage())
        <meta property="og:image" content="{{ $tournament->coverImage() }}" />
    @else
        <meta property="og:image" content="https://alwaysberunning.net/ms-icon-310x310.png" />
    @endif
@else
    <meta property="og:description" content="Tournament finder and tournament results for Android: Netrunner card game" />
    <meta property="og:image" content="https://alwaysberunning.net/ms-icon-310x310.png" />
@endif
