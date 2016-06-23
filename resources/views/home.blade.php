@extends('layout.general')

@section('content')
    <h4 class="page-header">Welcome</h4>
    @include('partials.message')
    <div class="row">
        <div class="col-md-3 col-xs-6">
            <div class="bracket">
                <h5>
                    <i class="fa fa-search" aria-hidden="true"></i>
                    Discover
                </h5>
            </div>
        </div>
        <div class="col-md-3 col-xs-6">
            <div class="bracket">
                <h5>
                    Results
                </h5>
            </div>
        </div>
        <div class="col-md-3 col-xs-6">
            <div class="bracket">
                <h5>
                    Organize
                </h5>
            </div>
        </div>
        <div class="col-md-3 col-xs-6">
            <div class="bracket">
                <h5>
                    Personal
                </h5>
            </div>
        </div>
    </div>
@stop

