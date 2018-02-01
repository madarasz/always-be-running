module.exports = {

    beforeEach: function (browser) {
        browser.deleteCookies().windowMaximize('current');
    },

    after: function (browser) {
        browser.end();
    },

    /**
     * Navigate to Organize page
     * Login with NRDB (regular user)
     * Validate login, click Create Tournament
     * Fill out tournament form with past tournament data
     * Save tournament, validate tournament details page
     * Click register, validate tournament page
     * Go to Personal page, validate registered tournament entry
     * Go back to tournament details page, unregister, validate tournament details page
     * Register again, conclude tournament, validate tournament details page
     * Go to Personal page, validate claim button
     * Click unregister on Personal page, validate tournament missing on Personal page
     */
    'Registering, un-registering': function (browser) {
        var regularLogin = browser.globals.accounts.regularLogin,
            tournamentOnlineConcluded = JSON.parse(JSON.stringify(browser.globals.tournaments.tournamentOnlineConcluded)); // clone

        tournamentOnlineConcluded.title = browser.currentTest.module.substring(0,3) + "|" +
            browser.currentTest.name.substring(0,29) + "|" + tournamentOnlineConcluded.title.substring(0, 16);

        // open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // navigate to Organize page
        browser.log('* Navigate to Organize page *');
        browser.page.mainMenu()
            .selectMenu('organize');

        // login with NRDB (regular user)
        browser.log('* Login with NRDB (regular user) *');
        browser.login(regularLogin.username, regularLogin.password);

        // validate login, click Create Tournament
        browser.log('* Validate login, click Create Tournament *');
        browser.page.organizePage()
            .validate(true)
            .clickCommand('createTournament');

        // fill out tournament form with past tournament data
        browser.log('* Fill out tournament form with past tournament data *');
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                inputs: {
                    title: tournamentOnlineConcluded.title,
                    date: tournamentOnlineConcluded.date
                },
                selects: {
                    tournament_type_id: tournamentOnlineConcluded.type,
                    cardpool_id: tournamentOnlineConcluded.cardpool
                }
            });

        // save tournament, validate tournament details page
        browser.log('* Save tournament, validate tournament details page *');
        browser.page.mainMenu().acceptCookies(); // cookies info is in the way
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');
        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: false,
                playerNumbers: false,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: false,
                buttonConclude: true,
                playerClaim: false,
                buttonClaim: false,
                removeClaim: false,
                claimError: false,
                topEntriesTable: false,
                swissEntriesTable: false,
                ownClaimInTable: false,
                conflictInTable: false,
                dueWarning: true,
                registeredPlayers: false,
                noRegisteredPlayers: true,
                unregisterButton: false,
                registerButton: true,
                revertButton: false,
                showMatches: false,
                showPoints: false,
                chartRunnerIds: false,
                chartCorpIds: false
            });

        // Click register, validate tournament page
        browser.log('* Click register, validate tournament page *');
        browser.page.tournamentView()
            .click('@registerButton')
            .validate()
            .assertView({
                conflictWarning: false,
                playerNumbers: false,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: false,
                buttonConclude: true,
                playerClaim: false,
                buttonClaim: false,
                removeClaim: false,
                claimError: false,
                topEntriesTable: false,
                swissEntriesTable: false,
                ownClaimInTable: false,
                conflictInTable: false,
                dueWarning: true,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: true,
                registerButton: false,
                revertButton: false,
                showMatches: false,
                showPoints: false,
                chartRunnerIds: false,
                chartCorpIds: false
            });

        // Go to Personal page, validate registered tournament entry
        browser.log('* Go to Personal page, validate registered tournament entry *');
        browser.page.mainMenu()
            .selectMenu('personal');
        browser.page.tournamentTable()
            .assertTable('my-table', tournamentOnlineConcluded.title, {
                texts: [ tournamentOnlineConcluded.date, 'online', tournamentOnlineConcluded.cardpool ],
                labels: [ 'registered' ]
            });

        // Go back to tournament details page, unregister, validate tournament details page
        browser.log('* Go back to tournament details page, unregister, validate tournament details page *');
        browser.page.tournamentTable()
            .selectTournament('my-table', tournamentOnlineConcluded.title);
        browser.page.tournamentView()
            .validate()
            .click('@unregisterButton')
            .validate()
            .assertView({
                conflictWarning: false,
                playerNumbers: false,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: false,
                buttonConclude: true,
                playerClaim: false,
                buttonClaim: false,
                removeClaim: false,
                claimError: false,
                topEntriesTable: false,
                swissEntriesTable: false,
                ownClaimInTable: false,
                conflictInTable: false,
                dueWarning: true,
                registeredPlayers: false,
                noRegisteredPlayers: true,
                unregisterButton: false,
                registerButton: true,
                revertButton: false,
                showMatches: false,
                showPoints: false,
                chartRunnerIds: false,
                chartCorpIds: false
            });

        // Register again, conclude tournament, validate tournament details page
        browser.log('* Register again, conclude tournament, validate tournament details page *');
        browser.page.tournamentView()
            .click('@registerButton')
            .validate()
            .click('@buttonConclude');
        browser.page.concludeModal()
            .validate(tournamentOnlineConcluded.title)
            .concludeManual({
                players_number: tournamentOnlineConcluded.players_number,
                top_number: tournamentOnlineConcluded.top
            });
        browser.page.tournamentView()
            .assertView({
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
                suggestLogin: false,
                buttonNRTMimport: true,
                buttonNRTMclear: false,
                buttonConclude: false,
                playerClaim: false,
                buttonClaim: true,
                removeClaim: false,
                claimError: false,
                topEntriesTable: true,
                swissEntriesTable: true,
                ownClaimInTable: false,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: true,
                registerButton: false,
                revertButton: true,
                showMatches: false,
                showPoints: false,
                chartRunnerIds: true,
                chartCorpIds: true
            });

        // Go to Personal page, validate claim button
        browser.log('* Go to Personal page, validate claim button *');
        browser.page.mainMenu()
            .selectMenu('personal');
        browser.page.tournamentTable()
            .assertTable('my-table', tournamentOnlineConcluded.title, {
                texts: [ tournamentOnlineConcluded.date, 'online', tournamentOnlineConcluded.cardpool ],
                buttons: [ 'claim' ]
            });

        // Click unregister on Personal page, validate tournament missing on Personal page
        browser.log('* Click unregister on Personal page, validate tournament missing on Personal page *');
        browser.page.tournamentTable()
            .selectTournamentAction('my-table', tournamentOnlineConcluded.title, 'unregister')
            .assertMissingRow('my-table', tournamentOnlineConcluded.title);

        // data cleanup, delete tournament
        browser.sqlDeleteTournament(tournamentOnlineConcluded.title, browser.globals.database.connection);

    },

    /**
     * Login with NRDB (regular user)
     * Navigate to Organize page, create from results
     * Fill out form with multi-day, concluded, online tournament data
     * Save tournament
     * Click claim, validate claim modal, add claim of published decklists
     * Validate tournament details page, validate claim
     * Import nrtm results (conflicting), validate conflicts
     * Remove claim, validate tournament page, conflict is gone
     * Claim again, validate conflict
     * Remove conflicting imported entry, validate conflict is gone
     * Go to Personal page, validate tournament entry with claimed status
     */
    'Claiming with published decks, no top-cut, conflicts': function (browser) {

        var regularLogin = browser.globals.accounts.regularLogin,
            claim1 = browser.globals.claims.claim1,
            tournamentNrtmJsonWithoutTopCut = JSON.parse(JSON.stringify(browser.globals.tournaments.tournamentNrtmJsonWithoutTopCut)); // clone

        tournamentNrtmJsonWithoutTopCut.title = browser.currentTest.module.substring(0, 3) + "|" +
            browser.currentTest.name.substring(0, 29) + "|" + tournamentNrtmJsonWithoutTopCut.title.substring(0, 16);

        // Open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // Login with NRDB (regular user)
        browser.log('* Login with NRDB (regular user) *');
        browser.login(regularLogin.username, regularLogin.password);

        // Navigate to Organize page, create from results
        browser.log('* Navigate to Organize page, create from results *');
        browser.page.mainMenu()
            .selectMenu('organize');
        browser.page.organizePage()
            .validate(true)
            .clickCommand('createTournament');

        // Fill out form with multi-day, concluded, online tournament data
        browser.log('* Fill out form with multi-day, concluded, online tournament data *');
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                inputs: {
                    title: tournamentNrtmJsonWithoutTopCut.title,
                    date: tournamentNrtmJsonWithoutTopCut.date,
                },
                selects: {
                    tournament_type_id: tournamentNrtmJsonWithoutTopCut.type,
                    cardpool_id: tournamentNrtmJsonWithoutTopCut.cardpool
                }
            });
        browser.page.mainMenu().acceptCookies(); // cookies info is in the way
        browser.page.tournamentForm()
            .fillForm({
                checkboxes: {
                    concluded: tournamentNrtmJsonWithoutTopCut.conclusion
                },
                inputs: {
                    players_number: tournamentNrtmJsonWithoutTopCut.players_number
                },
                selects: {
                    top_number: tournamentNrtmJsonWithoutTopCut.top
                }
            });

        // save tournament
        browser.log('* Save tournament *');
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');

        // Click claim, validate claim modal, add claim of published decklists
        browser.log('* Click claim, validate claim modal, add claim of published decklists *');
        browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentNrtmJsonWithoutTopCut.title,
                tournamentNrtmJsonWithoutTopCut.players_number, tournamentNrtmJsonWithoutTopCut.top_number)
            .claim(claim1);

        // Validate tournament details page, validate claim
        browser.log('* Validate tournament details page, validate claim *');
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentNrtmJsonWithoutTopCut.title,
                ttype: tournamentNrtmJsonWithoutTopCut.type,
                creator: regularLogin.username,
                date: tournamentNrtmJsonWithoutTopCut.date,
                cardpool: tournamentNrtmJsonWithoutTopCut.cardpool,
                concludedBy: regularLogin.username,
                map: false,
                decklist: false,
                approvalNeed: true,
                editButton: true,
                approveButton: false,
                rejectButton: false,
                deleteButton: true,
                transferButton: true,
                featureButton: false,
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: true,
                buttonNRTMclear: false,
                buttonConclude: false,
                playerClaim: true,
                buttonClaim: false,
                removeClaim: true,
                claimError: false,
                topEntriesTable: false,
                swissEntriesTable: true,
                ownClaimInTable: true,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: false,
                registerButton: false,
                revertButton: true,
                showMatches: false,
                showPoints: false,
            })
            .assertClaim(
                regularLogin.username,
                claim1.rank, claim1.rank_top,
                false, false,
                claim1.runner_deck, claim1.corp_deck
            );

        // Import nrtm results (conflicting), validate conflicts
        browser.log('* Import nrtm results (conflicting), validate conflicts *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');

        browser.page.concludeModal()
            .validate(tournamentNrtmJsonWithoutTopCut.title)
            .concludeNrtmJson('nrtm-without-topcut.json');

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: true,
                playerNumbers: true,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: true,
                buttonConclude: false,
                playerClaim: true,
                buttonClaim: false,
                removeClaim: true,
                claimError: false,
                topEntriesTable: false,
                swissEntriesTable: true,
                ownClaimInTable: true,
                conflictInTable: true,
                dueWarning: false,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: false,
                registerButton: false,
                revertButton: true,
                showMatches: true,
                showPoints: true,
            })
            .assertClaim(
                regularLogin.username,
                claim1.rank, claim1.rank_top,
                true, false,
                claim1.runner_deck, claim1.corp_deck
            );

        // Go to organize, validate conflict and match data icons
        browser.log('* Go to organize, validate conflict and match data icons *');
        browser.page.mainMenu()
            .selectMenu('organize');
        browser.page.tournamentTable()
            .assertTable('created', tournamentNrtmJsonWithoutTopCut.title, {
                icons: ['conflict', 'match data']
            })
            .selectTournament('created', tournamentNrtmJsonWithoutTopCut.title);

        // Go to tournament details, remove claim, validate tournament page, conflict is gone
        browser.log('* Go to tournament details, remove claim, validate tournament page, conflict is gone *');
        browser.page.tournamentView()
            .validate()
            .click('@removeClaim')
            .assertView({
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: true,
                buttonConclude: false,
                playerClaim: false,
                buttonClaim: true,
                removeClaim: false,
                claimError: false,
                topEntriesTable: false,
                swissEntriesTable: true,
                ownClaimInTable: false,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: true,
                registerButton: false,
                revertButton: true,
                showMatches: true,
                showPoints: true,
            });

        // Claim again, validate conflict
        browser.log('* Claim again, validate conflict *');
        browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentNrtmJsonWithoutTopCut.title,
            tournamentNrtmJsonWithoutTopCut.players_number, tournamentNrtmJsonWithoutTopCut.top_number)
            .claim(claim1);

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: true,
                playerNumbers: true,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: true,
                buttonConclude: false,
                playerClaim: true,
                buttonClaim: false,
                removeClaim: true,
                claimError: false,
                topEntriesTable: false,
                swissEntriesTable: true,
                ownClaimInTable: true,
                conflictInTable: true,
                dueWarning: false,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: false,
                registerButton: false,
                revertButton: true,
                showMatches: true,
                showPoints: true,
            });

        // Remove conflicting imported entry, validate conflict is gone
        browser.log('* Remove conflicting imported entry, validate conflict is gone *');
        browser.page.tournamentView()
            .removeAnonym(
                'entries-swiss',
                claim1.rank,
                tournamentNrtmJsonWithoutTopCut.imported_results.swiss[claim1.rank-1].player
            );
        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: true,
                buttonConclude: false,
                playerClaim: true,
                buttonClaim: false,
                removeClaim: true,
                claimError: false,
                topEntriesTable: false,
                swissEntriesTable: true,
                ownClaimInTable: true,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: false,
                registerButton: false,
                revertButton: true,
                showMatches: true,
                showPoints: true
            });

        // Go to Personal page, validate tournament entry with claimed status
        browser.log('* Go to Personal page, validate tournament entry with claimed status *');
        browser.page.mainMenu()
            .selectMenu('personal');
        browser.page.tournamentTable()
            .assertTable('my-table', tournamentNrtmJsonWithoutTopCut.title, {
                texts: [ tournamentNrtmJsonWithoutTopCut.date, 'online', tournamentNrtmJsonWithoutTopCut.cardpool ],
                labels: [ 'claimed' ]
            });

        // data cleanup, delete tournament
        browser.sqlDeleteTournament(tournamentNrtmJsonWithoutTopCut.title, browser.globals.database.connection);
    },

    /**
     * Login with NRDB (regular user)
     * Navigate to Organize page, create from results
     * Fill out form with multi-day, concluded, online tournament data
     * Save tournament
     * Click claim, validate claim modal, add claim of published decklists
     * Validate tournament details page, validate claim
     * Import Cobra results (no-confilect), validate absence of conflicts
     * Remove user claim, remove imported claims
     * Import Cobra results, add claim of published decklist, validate absence of conflict
     */
    'Claiming with published decks, top-cut, no conflicts': function (browser) {

        var regularLogin = browser.globals.accounts.regularLogin,
            claim2 = browser.globals.claims.claim2,
            tournamentCobraJsonWithTopCut = JSON.parse(JSON.stringify(browser.globals.tournaments.tournamentCobraJsonWithTopCut)); // clone

        tournamentCobraJsonWithTopCut.title = browser.currentTest.module.substring(0, 3) + "|" +
            browser.currentTest.name.substring(0, 29) + "|" + tournamentCobraJsonWithTopCut.title.substring(0, 16);

        // Open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // Login with NRDB (regular user)
        browser.log('* Login with NRDB (regular user) *');
        browser.login(regularLogin.username, regularLogin.password);

        // Navigate to Organize page, create from results
        browser.log('* Navigate to Organize page, create from results *');
        browser.page.mainMenu()
            .selectMenu('organize');
        browser.page.organizePage()
            .validate(true)
            .clickCommand('createTournament');

        // Fill out form with multi-day, concluded, online tournament data
        browser.log('* Fill out form with multi-day, concluded, online tournament data *');
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                inputs: {
                    title: tournamentCobraJsonWithTopCut.title,
                    date: tournamentCobraJsonWithTopCut.date,
                },
                selects: {
                    tournament_type_id: tournamentCobraJsonWithTopCut.type,
                    cardpool_id: tournamentCobraJsonWithTopCut.cardpool
                }
            });
        browser.page.mainMenu().acceptCookies(); // cookies info is in the way
        browser.page.tournamentForm()
            .fillForm({
                checkboxes: {
                    concluded: tournamentCobraJsonWithTopCut.conclusion
                },
                inputs: {
                    players_number: tournamentCobraJsonWithTopCut.players_number
                },
                selects: {
                    top_number: tournamentCobraJsonWithTopCut.top
                }
            });

        // save tournament
        browser.log('* Save tournament *');
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');

        // Click claim, validate claim modal, add claim of published decklists
        browser.log('* Click claim, validate claim modal, add claim of published decklists *');
        browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentCobraJsonWithTopCut.title,
            tournamentCobraJsonWithTopCut.players_number, tournamentCobraJsonWithTopCut.top_number)
            .claim(claim2);

        // Validate tournament details page, validate claim
        browser.log('* Validate tournament details page, validate claim *');
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentCobraJsonWithTopCut.title,
                ttype: tournamentCobraJsonWithTopCut.type,
                creator: regularLogin.username,
                date: tournamentCobraJsonWithTopCut.date,
                cardpool: tournamentCobraJsonWithTopCut.cardpool,
                concludedBy: regularLogin.username,
                map: false,
                decklist: false,
                approvalNeed: true,
                editButton: true,
                approveButton: false,
                rejectButton: false,
                deleteButton: true,
                transferButton: true,
                featureButton: false,
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
                suggestLogin: false,
                buttonNRTMimport: true,
                buttonNRTMclear: false,
                buttonConclude: false,
                playerClaim: true,
                buttonClaim: false,
                removeClaim: true,
                claimError: false,
                topEntriesTable: true,
                swissEntriesTable: true,
                ownClaimInTable: true,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: false,
                registerButton: false,
                revertButton: true,
                showMatches: false,
                showPoints: false,
            })
            .assertClaim(
            regularLogin.username,
            claim2.rank, claim2.rank_top,
            false, false,
            claim2.runner_deck, claim2.corp_deck
        );

        // Import Cobra results (no-confilect), validate absence of conflicts
        browser.log('* Import Cobra results (no-confilect), validate absence of conflicts *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');

        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: true,
                buttonConclude: false,
                playerClaim: true,
                buttonClaim: false,
                removeClaim: true,
                claimError: false,
                topEntriesTable: true,
                swissEntriesTable: true,
                ownClaimInTable: true,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: false,
                registerButton: false,
                revertButton: true,
                showMatches: true,
                showPoints: true
            })
            .assertClaim(
                regularLogin.username,
                claim2.rank, claim2.rank_top,
                false, false,
                claim2.runner_deck, claim2.corp_deck
            );

        // Remove user claim, remove imported claims
        browser.log('* Remove user claim, remove imported claims *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Import Cobra results, add claim of published decklist, validate absence of conflict
        browser.log('* Import Cobra results, add claim of published decklist, validate absence of conflict *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');
        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        browser.page.tournamentView()
            .click('@buttonClaim');
        browser.page.claimModal()
            .validate(tournamentCobraJsonWithTopCut.title,
            tournamentCobraJsonWithTopCut.players_number, tournamentCobraJsonWithTopCut.top_number)
            .claim(claim2);

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: true,
                buttonConclude: false,
                playerClaim: true,
                buttonClaim: false,
                removeClaim: true,
                claimError: false,
                topEntriesTable: true,
                swissEntriesTable: true,
                ownClaimInTable: true,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: true,
                noRegisteredPlayers: false,
                unregisterButton: false,
                registerButton: false,
                revertButton: true,
                showMatches: true,
                showPoints: true
            })
            .assertClaim(
                regularLogin.username,
                claim2.rank, claim2.rank_top,
                false, false,
                claim2.runner_deck, claim2.corp_deck
            );

        // data cleanup, delete tournament
        browser.sqlDeleteTournament(tournamentCobraJsonWithTopCut.title, browser.globals.database.connection);
    }
};
