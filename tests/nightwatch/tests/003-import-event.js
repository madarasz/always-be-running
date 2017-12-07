module.exports = {

    beforeEach: function (browser) {
        browser.deleteCookies().windowMaximize('current');
    },

    after: function (browser) {
        browser.end();
    },

    'Import from NRTM.json (no top-cut)': function (browser) {

        var regularLogin = browser.globals.regularLogin,
            adminLogin = browser.globals.adminLogin,
            tournamentNrtmJsonWithoutTopCut = JSON.parse(JSON.stringify(browser.globals.tournamentNrtmJsonWithoutTopCut)); // clone

        tournamentNrtmJsonWithoutTopCut.title = browser.currentTest.module.substring(0,3) + "|" +
            browser.currentTest.name.substring(0,29) + "|" + tournamentNrtmJsonWithoutTopCut.title.substring(0, 16);

        // Open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // Navigate to Organize page
        browser.log('* Navigate to Organize page *');
        browser.page.mainMenu()
            .selectMenu('organize');

        // Login with NRDB (regular user)
        browser.log('* Login with NRDB (regular user) *');
        browser.login(regularLogin.username, regularLogin.password);

        // Validate login, click Create from Result
        browser.log('* Validate login, click Create from Result *');
        browser.page.organizePage()
            .validate(true)
            .click('@createFromResults');

        // Validate Conclude modal, upload NRTM.json
        browser.log('* Validate Conclude modal, upload NRTM.json *');
        browser.page.concludeModal()
            .validate('use this for new concluded tournaments');
        browser.page.mainMenu().acceptCookies(); // cookies info is in the way
        browser.page.concludeModal()
            .concludeNrtmJson('nrtm-without-topcut.json');

        // Validate imported form values, fill remaining fields, create tournament
        browser.log('* Validate imported form values, fill remaining fields, create tournament *');
        browser.page.tournamentForm()
            .validate()
            .click('@hide_non_required')
            .assertForm({
                visible: ['overlay_recurring'],
                not_visible: ['overlay_location', 'overlay_conclusion', 'overlay_cardpool'],
                inputs: {
                    title: tournamentNrtmJsonWithoutTopCut.old_title,
                    players_number: tournamentNrtmJsonWithoutTopCut.players_number
                },
                selects: {
                    top_number: tournamentNrtmJsonWithoutTopCut.top_number
                }
            })
            .fillForm({
                inputs: {
                    title: tournamentNrtmJsonWithoutTopCut.title,
                    date: tournamentNrtmJsonWithoutTopCut.date
                },
                selects: {
                    tournament_type_id: tournamentNrtmJsonWithoutTopCut.type,
                    cardpool_id: tournamentNrtmJsonWithoutTopCut.cardpool
                }
            });

        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');

        // Validate tournament details page with results
        browser.log('* Validate tournament details page with results *');
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
                registeredPlayers: false,
                noRegisteredPlayers: true,
                unregisterButton: false,
                registerButton: false,
                revertButton: true,
                showMatches: true,
                showPoints: true,
                chartRunnerIds: true,
                chartCorpIds: true
            })
            .assertImport(tournamentNrtmJsonWithoutTopCut.imported_results);

        // Validate matches information and points
        browser.log('* Validate matches information and points *');
        browser.page.tournamentView()
            .getLocationInView('@showMatches').click('@showMatches')
            .validateMatches(tournamentNrtmJsonWithoutTopCut.imported_results)
            .getLocationInView('@showPoints').click('@showPoints')
            .validatePoints(tournamentNrtmJsonWithoutTopCut.imported_results);

        // Verify concluded tournament on Results page
        browser.log('* Verify concluded tournament on Results page *');
        browser.page.mainMenu()
            .selectMenu('results');
        browser.page.tournamentTable()
            .assertTable('results', tournamentNrtmJsonWithoutTopCut.title, {
                texts: [tournamentNrtmJsonWithoutTopCut.date, tournamentNrtmJsonWithoutTopCut.cardpool, 'online',
                    tournamentNrtmJsonWithoutTopCut.players_number]
            })
            .click('@toBeConcludedTab')
            .assertMissingRow('to-be-concluded', tournamentNrtmJsonWithoutTopCut.title);

        // Verify concluded tournament on Organize page
        browser.log('* Verify concluded tournament on Organize page *');
        browser.page.mainMenu()
            .selectMenu('organize');
        browser.page.tournamentTable()
            .assertTable('created', tournamentNrtmJsonWithoutTopCut.title, {
                texts: [tournamentNrtmJsonWithoutTopCut.date, tournamentNrtmJsonWithoutTopCut.cardpool, 'online',
                    tournamentNrtmJsonWithoutTopCut.players_number],
                labels: ['concluded']
            });

        // data cleanup, delete tournament
        browser.sqlDeleteTournament(tournamentNrtmJsonWithoutTopCut.title, browser.globals.database.connection);

    }
};