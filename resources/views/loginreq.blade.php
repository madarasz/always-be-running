@extends('layout.general')

@section('content')
    <h4 class="page-header">Login required</h4>
    <div class="row">
        <div class="col-xs-12 text-xs-center">
            <div>
                Please <a href="/oauth2/redirect">login via NetrunnerDB</a> to access this page.
            </div>
            <div class="small-text p-t-2">
                You can also tell us about a tournament by sending an email to <strong>alwaysberunning (at) gmail.com</strong>
                <br/>
                But then you will miss all the glory, the fame and the badges.
            </div>
        </div>
    </div>
@stop

