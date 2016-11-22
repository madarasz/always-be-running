@extends('layout.general')

@section('content')
    <h4 class="page-header">Frequently asked questions</h4>
    <div class="row">
        <div class="col-md-10 col-xs-12 offset-md-1">
            <div class="bracket">
                <p>
                    <a href="#why-ndb">Why do I have to login with my NetrunnerDB user?</a><br>
                    <a href="#ndb-sharing">I cannot see my decks when I am claiming my spot at a tournament.</a><br>
                    <a href="#other-deckbuilders">Do you plan to integrate with other deckbuilders?</a><br>
                    <a href="#nrtm">How do I import the tournament results?</a><br>
                    <a href="#more-questions">I have more questions or ideas.</a><br>
                </p>
                <hr/>
                <p>
                    <a name="why-ndb"></a><strong>Why do I have to login with my NetrunnerDB user?</strong>
                </p>
                <p>
                    Because you will be able to link the decks created at NetrunnerDB with the tournaments you participated at.
                    Also I can identify you as a user with your NetrunnerDB account. You won't need a separate registration for
                    this site.
                </p>
                <p>
                    This is done via the <a href="https://en.wikipedia.org/wiki/OAuth">OAuth</a> protocol. You have
                    seen sites with the <em>"login with Facebook / Gmail"</em> option, this is the same thing. You are
                    being logged in on NetrunnerDB and then redirected back.
                </p>
                <p class="p-t-2">
                    <a name="ndb-sharing"></a><strong>I cannot see my decks when I am claiming my spot at a tournament.</strong>
                </p>
                <p>
                    By default I can only access your <em>published</em> decks.
                </p>
                <p>
                    If you want to use your <em>private</em> decks here, please enable the <strong>Share your decks</strong>
                    option in your NetrunnerDB <a href="https://netrunnerdb.com/en/user/profile">account settings</a>.
                    After this is done, <strong>relogin into Always be Running.net</strong>, so changes take effect.
                </p>
                <p class="p-t-2">
                    <a name="other-deckbuilders"></a><strong>Do you plan to integrate with other deckbuilders?</strong>
                </p>
                <p>
                    This is not planned. Though the possibility is there if the deckbuilder site has the needed API
                    endpoints and has a wide userbase.
                </p>
                <p class="p-t-2">
                    <a name="nrtm"></a><strong>How do I import the tournament results?</strong>
                </p>
                <p>
                    You can either do it by <strong>NRTM</strong> or a <strong>CSV</strong> file that you prepare.
                </p>
                <ul>
                    <li>
                        <p>
                            <a href="https://itunes.apple.com/us/app/nrtm/id695468874?mt=8">NRTM</a> is a Netrunner tournament
                            manager app for iOS.</p>
                        <p>
                            First, download the identity names
                            from NetrunnerDB by going to <strong>Settings</strong> >> <strong>Edit Names</strong> >>
                            <strong>Download from NetrunnerDB.com</strong>. Then you can proceed with exporting the results:
                        </p>
                        <ul>
                            <li>
                                <img src="img/faq-nrtm1.png"/><br/>
                                After the tournament is finished, go to tab <strong>More...</strong> >> <strong>Export</strong>.
                                Click upload icon on top.<br/><br/>
                                <img src="img/faq-nrtm2.png"/><br/>
                                Select <strong>Upload to alwaysberunning.net</strong>.<br/><br/>
                                <img src="img/faq-nrtm3.png"/><br/>
                                After NRTM uploads the results, you will receive a <strong>conclusion code.</strong><br/><br/>
                                <img src="img/faq-nrtm4.png"/><br/>
                                When you are concluding the tournament on AlwaysBeRunning.net, provide this code.

                            </li>
                        </ul>
                        <p class="p-t-1">
                            Alternatively you can upload the NRTM.json results file. <br/>
                            <em>(JSON export can be enabled by <strong>Settings</strong> >> <strong>Data Export</strong> >>
                            switch on <strong>Add JSON Data to Export</strong>.)</em>
                        </p>
                    </li>
                    <li>
                        <p>
                            If you are uploading a CSV file, you have to follow this format:<br/>
                        <blockquote class="help-markdown">name;swiss-rank;topcut-rank;runnerID;corpID</blockquote><br/>
                        If there were no top-cut or the player did not reach top-cut, use "0" (zero)
                        in the <em>top-cut rank</em> field. The ID fields should be the (substring of the)
                        official card name. Example:
                        <blockquote class="help-markdown">
                            Pete;1;0;Kit;Near-Earth Hub<br/>
                            Tristan;2;0;Hayley;Engineering the Future<br/>
                            Alice;3;0;Omar;Controlling the Message<br/>
                            Ed;4;0;Khan;Architects of Tomorrow<br/>
                        </blockquote>
                        </p>
                    </li>
                </ul>
                <p class="p-t-2">
                    <a name="more-questions"></a><strong>I have more questions or ideas.</strong>
                </p>
                <p>
                    You can contact me via: info (at) alwaysberunning.net
                </p>
            </div>
        </div>
    </div>
@stop

