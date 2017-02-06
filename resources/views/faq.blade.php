@extends('layout.general')

@section('content')
    <h4 class="page-header">Frequently asked questions</h4>
    <div class="row">
        <div class="col-md-10 col-xs-12 offset-md-1">
            <div class="bracket">
                <p>
                    <a href="#why-ndb">Why do I have to login with my NetrunnerDB user?</a><br>
                    <a href="#ndb-private">What is the difference between "private" and "public" decks/decklists?</a><br>
                    <a href="#ndb-sharing">I cannot see my decks when I am claiming my spot at a tournament.</a><br>
                    <a href="#other-deckbuilders">Do you plan to integrate with other deckbuilders?</a><br>
                    <a href="#import">How do I import the tournament results?</a><br>
                    <a href="#more-questions">I have more questions or ideas.</a><br>
                </p>
                <hr/>

                <p>
                    <a name="why-ndb" class="anchor"></a><strong>Why do I have to login with my NetrunnerDB user?</strong>
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
                <hr/>

                <p>
                    <a name="ndb-private" class="anchor"></a><strong>What is the difference between "private" and "public" decks/decklists?</strong>
                </p>
                <p>
                    <div class="bracket-mini m-t-0">
                        <img src="img/faq-private1.png"/>
                    </div>
                    <br/>
                    When you create a deck in NetrunnerDB, it is added to your list of
                    <a href="https://netrunnerdb.com/en/decks"><strong>private</strong> decks<a/>.<br/>
                    <div class="bracket-mini">
                        <img src="img/faq-private2.png"/>
                    </div>
                    <br/>
                    In order to create a published copy of your private deck, click the <strong>Publish</strong> button.<br/>
                    <div class="bracket-mini">
                        <img src="img/faq-public.png"/>
                    </div>
                    <br/>
                    The deck is added to your <a href="https://netrunnerdb.com/en/decklists/mine"><strong>published</strong>
                    decklists</a>.
                </p>
                <p>
                    Private decks
                    <ul>
                        <li>mainly for private use</li>
                        <li>cards can be changed</li>
                        <li>
                            can be shared (or claimed with) if you enable the option in your
                            <a href="https://netrunnerdb.com/en/user/profile">NetrunnerDB settings</a>
                        </li>
                        <li>you can have a limited number of private decks</li>
                    </ul>
                </p>
                <p>
                    Published decks
                    <ul>
                        <li>for the public, ideal for sharing</li>
                        <li>cards cannot be changed, it's snapshot</li>
                        <li>another users can like or favorite it, you will get <em>reputation points</em> on NetrunnerDB</li>
                        <li>unlimited number of published decks</li>
                        <li>description can be provided</li>
                    </ul>
                </p>
                <p>
                    For these reasons, I suggest you make claims with your <strong>published</strong> decklists. To make things
                    easier, there is a <strong>publish selected private decks</strong> option when you create a claim. If you
                    select it, AlwaysBeRunning.net will create a published version of the private decks you selected.
                </p>
                <hr/>

                <p>
                    <a name="ndb-sharing" class="anchor"></a><strong>I cannot see my decks when I am claiming my spot at a tournament.</strong>
                </p>
                <p>
                    By default I can only access your <em>published</em> decks.
                </p>
                <p>
                    If you want to use your <em>private</em> decks here, please enable the <strong>Share your decks</strong>
                    option in your NetrunnerDB <a href="https://netrunnerdb.com/en/user/profile">account settings</a>.
                    After this is done, <strong>relogin into Always be Running.net</strong>, so changes can take effect.
                </p>
                <hr/>

                <p>
                    <a name="other-deckbuilders" class="anchor"></a><strong>Do you plan to integrate with other deckbuilders?</strong>
                </p>
                <p>
                    This is not planned. Though the possibility is there if the deckbuilder site has the needed API
                    endpoints and has a wide userbase.
                </p>
                <hr/>

                <p>
                    <a name="import" class="anchor"></a><strong>How do I import the tournament results?</strong>
                </p>
                <p>
                    You can do it in bulk either by <strong><a href="#import-nrtm">NRTM</a></strong> or a
                    <strong><a href="#import-csv">CSV</a></strong> file that you prepare.
                    Or you can <strong><a href="#import-manual">import manually</a></strong>.
                </p>
                <ul>
                    <li>
                        <p>
                            <a name="import-nrtm" class="anchor"></a>
                            <a href="https://itunes.apple.com/us/app/nrtm/id695468874?mt=8">NRTM</a> is a Netrunner tournament
                            manager app for iOS.</p>
                        <p>
                            First, download the identity names
                            from NetrunnerDB by going to <strong>Settings</strong> >> <strong>Edit Names</strong> >>
                            <strong>Download from NetrunnerDB.com</strong>. Assign those identity names to your players.
                            After the tournament finishes, you can proceed with exporting the results:
                        </p>
                        <ul>
                            <li>
                                <div class="bracket-mini">
                                    <img src="img/faq-nrtm1.png"/>
                                </div>
                                <br/>
                                After the tournament is finished, go to tab <strong>More...</strong> >> <strong>Export</strong>.
                                Click upload icon on top.<br/>
                                <div class="bracket-mini">
                                    <img src="img/faq-nrtm2.png"/>
                                </div>
                                <br/>
                                Select <strong>Upload to alwaysberunning.net</strong>.<br/>
                                <div class="bracket-mini">
                                    <img src="img/faq-nrtm3.png"/>
                                </div>
                                <br/>
                                After NRTM uploads the results, you will receive a <strong>conclusion code.</strong><br/>
                                <div class="bracket-mini">
                                    <img src="img/faq-nrtm4.png"/>
                                </div>
                                <br/>
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
                        <a name="import-csv" class="anchor"></a>
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
                    <li>
                        <a name="import-manual" class="anchor"></a>
                        You can also import results manually, adding each player one-by-one.<br/>
                        <div class="bracket-mini">
                            <img src="img/faq-manual1.png"/>
                        </div><br/>
                        Go to your concluded tournament and click <strong>Import manually</strong>.<br/>
                        <div class="bracket-mini">
                            <img src="img/faq-manual2.png"/>
                        </div><br/>
                        You can now add the player names and IDs to the results. You cannot link users or decklists.
                        Your players have to claim themselves to get those in. If you made a mistake you can delete
                        entries with the red trashcan icon next to them.
                    </li>
                </ul>
                <hr/>

                <p>
                    <a name="more-questions" class="anchor"></a><strong>I have more questions or ideas.</strong>
                </p>
                <p>
                    You can contact me via: info (at) alwaysberunning.net
                </p>
            </div>
        </div>
    </div>
@stop

