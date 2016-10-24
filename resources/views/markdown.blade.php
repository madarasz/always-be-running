@extends('layout.general')

@section('content')
    <h4 class="page-header">Templates for tournament descriptions</h4>
    <div class="row">
        <div class="col-md-10 col-xs-12 offset-md-1">
            <div class="bracket">
                <p>
                    Example tournament description in <strong>markdown</strong>:
                </p>
                <div class="help-markdown">
                    ![Image](/img/banner1.jpg)<br/>
                    <br/>
                    **Sign up**: 10:30<br/>
                    **Tournament start**: 10:45<br/>
                    **Price of admission**: 5,- €<br/>
                    <br/>
                    ---<br/>
                    <br/>
                    **Prizes**: [2016 Summer Kit](https://www.fantasyflightgames.com/en/news/2016/3/4/order-your-summer-2016-tournament-kits/)<br/>
                    * One set of **acrylic power tokens**<br/>
                    * Two copies of an alternate art **Hayley Kaplan**<br/>
                    * Seventeen copies of the alternate art card, **Bank Job**
                </div>
                <p class="p-t-2">
                    This looks like:
                </p>
                <div class="row">
                    <div class="col-md-10 offset-md-1 col-xs-12">
                        <div id="tournament-description" class="markdown-content">
                            <p><img src="/img/banner1.jpg" alt="Image" /></p>
                            <p><strong>Sign up</strong>: 10:30<br />
                                <strong>Tournament start</strong>: 10:45<br />
                                <strong>Price of admission</strong>: 5,- €</p>
                            <hr />
                            <p><strong>Prizes</strong>: <a href="https://www.fantasyflightgames.com/en/news/2016/3/4/order-your-summer-2016-tournament-kits/">2016 Summer Kit</a></p>
                            <ul>
                                <li>One set of <strong>acrylic power tokens</strong>
                                </li>
                                <li>Two copies of an alternate art <strong>Hayley Kaplan</strong>
                                </li>
                                <li>Seventeen copies of the alternate art card, <strong>Bank Job</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

