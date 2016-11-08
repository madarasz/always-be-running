@extends('layout.general')

@section('content')
    <h3 class="page-header">500 - It's a bug!!!</h3>
    <div class="col-xs-12 text-xs-center">
        <img src="/img/error.jpg"/><br/><br/>
        Please help me by sending a bug report to <a href="mailto:error{{'@'}}alwaysberunning.net">error{{'@'}}alwaysberunning.net</a><br/>
        Tell me how you got this error and copy/screenshot the stacktrace below:
        <hr/>
        <div class="stacktrace">
            {{ @$exception }}
        </div>
    </div>
@stop