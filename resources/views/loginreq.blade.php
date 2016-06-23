@extends('layout.general')

@section('content')
    <h4 class="page-header">Login required</h4>
    <div class="row">
        <div class="col-xs-12 text-xs-center">
            Please <a href="/oauth2/redirect">login via NetrunnerDB</a> to access this page.
        </div>
    </div>
@stop

