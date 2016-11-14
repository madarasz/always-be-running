@extends('layout.general')

@section('content')
    <h4 class="page-header">Badges</h4>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <p>
                    <strong>For all players:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 2000 && $badge->order < 3000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
            <div class="bracket">
                <p>
                    <strong>For tournament organizers:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 3000 && $badge->order < 4000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <p>
                    <strong>For competative players:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->year)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
            <div class="bracket">
                <p>
                    <strong>For heavy NetrunnerDB users:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 4000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
            <div class="bracket">
                <p>
                    <strong>Other badges:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order < 100)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@stop

