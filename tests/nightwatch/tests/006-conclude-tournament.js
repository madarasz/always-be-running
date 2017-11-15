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
     * Conclude tournament manually, assert tournament page
     * Navigate to Results page, validate tournament in table
     * Navigate to tournament view, revert conclusion, validate tournament
     * Logout
     * Login with NRDB (admin user), hard delete tournament
     */
    'Manual Conclusion (with top-cut), revert by creator': function (browser) {
        var regularLogin = browser.globals.regularLogin,
            adminLogin = browser.globals.adminLogin,
            tournamentOnlineConcluded = JSON.parse(JSON.stringify(browser.globals.tournamentOnlineConcluded)); // clone

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
                title: tournamentOnlineConcluded.title,
                ttype: tournamentOnlineConcluded.type,
                creator: regularLogin.username,
                date: tournamentOnlineConcluded.date,
                cardpool: tournamentOnlineConcluded.cardpool,
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
                revertButton: false
            });

        // Conclude tournament manually, validate tournament page
        browser.log('* Conclude tournament manually, assert tournament page *');
        browser.page.tournamentView()
            .click('@buttonConclude');
        browser.page.concludeModal()
            .validate(tournamentOnlineConcluded.title)
            .concludeManual({
                players_number: tournamentOnlineConcluded.players_number,
                top_number: tournamentOnlineConcluded.top
            });
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentOnlineConcluded.title,
                ttype: tournamentOnlineConcluded.type,
                creator: regularLogin.username,
                date: tournamentOnlineConcluded.date,
                cardpool: tournamentOnlineConcluded.cardpool,
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
                playerClaim: false,
                buttonClaim: true,
                removeClaim: false,
                claimError: false,
                topEntriesTable: true,
                swissEntriesTable: true,
                ownClaimInTable: false,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: false,
                noRegisteredPlayers: true,
                unregisterButton: false,
                registerButton: false,
                revertButton: true
            });

        // Navigate to Results page, validate tournament in table
        browser.log('* Navigate to Results page, validate tournament in table *');
        browser.page.mainMenu()
            .selectMenu('results');
        browser.page.tournamentTable()
            .assertTable('results', tournamentOnlineConcluded.title, {
                texts: [tournamentOnlineConcluded.date, tournamentOnlineConcluded.cardpool, 'online',
                    tournamentOnlineConcluded.cardpool, tournamentOnlineConcluded.players_number]
            });

        // Navigate to tournament view, revert conclusion, validate tournament
        browser.log('* Navigate to tournament view, revert conclusion, validate tournament *');
        browser.page.mainMenu()
            .selectMenu('organize'); // TODO: navigate form the Results page
        browser.page.tournamentTable()
            .selectTournament('created', tournamentOnlineConcluded.title);
        browser.page.tournamentView()
            .click('@revertButton')
            .api.acceptAlert(); // TODO: phantomJS workaround
        browser.page.tournamentView()
            .assertView({
                title: tournamentOnlineConcluded.title,
                ttype: tournamentOnlineConcluded.type,
                creator: regularLogin.username,
                date: tournamentOnlineConcluded.date,
                cardpool: tournamentOnlineConcluded.cardpool,
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
                revertButton: false
            });

        // logout
        browser.log('* Logout *');
        browser.page.mainMenu().selectMenu('logout');

        // login as admin, hard delete tournament
        browser.log('* Login with NRDB (admin user), hard delete tournament *');
        browser.login(adminLogin.username, adminLogin.password);
        browser.page.mainMenu().selectMenu('admin');
        browser.page.tournamentTable()
            .assertTable('pending', tournamentOnlineConcluded.title, {
                texts: [tournamentOnlineConcluded.date, tournamentOnlineConcluded.cardpool, 'online'],
                labels: ['pending']
            })
            .selectTournamentAction('pending', tournamentOnlineConcluded.title, 'delete');
        browser.page.tournamentTable()
            .assertTable('deleted', tournamentOnlineConcluded.title, {
                texts: [tournamentOnlineConcluded.date, regularLogin.username],
                buttons: ['conclude']
            })
            .selectTournamentAction('deleted', tournamentOnlineConcluded.title, 'remove');

    }
};
