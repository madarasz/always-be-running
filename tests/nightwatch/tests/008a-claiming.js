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

        tournamentOnlineConcluded.title = browser.currentTest.module.substring(0,4) + "|" +
            browser.currentTest.name.substring(0,28) + "|" + tournamentOnlineConcluded.title.substring(0, 16);

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
            .removeJson()
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

    }

};
