module.exports = {

    beforeEach: function (browser) {
        browser.deleteCookies().windowMaximize('current');
    },

    after: function (browser) {
        browser.end();
    },

    /**
     * Login with NRDB (regular user)
     * Navigate to Organize page, create from results
     * Fill out form with multi-day, concluded, online tournament data
     * Save tournament
     * Add claim with published decklists
     * Validate tournament details page, validate claim
     * Import nrtm results (conflicting), validate conflicts
     * Remove claim, remove imported entries
     * Add claim with IDs
     * Validate tournament details page, validate claim
     * Import nrtm results (conflicting), validate conflicts
     * Remove claim, remove imported entries
     * Add claim with other user's deck
     * Validate tournament details page, validate claim
     * Import NRTM results (conflicting), validate conflicts
     * Go to organize, validate conflict and match data icons
     * Go to tournament details, remove claim, validate tournament page, conflict is gone
     * Claim again with published decks, validate conflict
     * Remove claim
     * Add claim with IDs, validate conflict
     * Remove claim
     * Add claim with other user's deck, validate conflict
     * Remove conflicting imported entry, validate conflict is gone
     * Go to Personal page, validate tournament entry with claimed status
     */
    'Claiming, no top-cut, NRTM import, conflicts': function (browser) {

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

        // Add claim with published decklists
        browser.log('* Add claim with published decklists *');
        browser.page.tournamentView()
            .validate()
            .removeJson()
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

        // Remove claim, remove imported entries
        browser.log('* Remove claim, remove imported entries *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Add claim with IDs
        browser.log('* Add claim with IDs *');
        browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentNrtmJsonWithoutTopCut.title,
            tournamentNrtmJsonWithoutTopCut.players_number, tournamentNrtmJsonWithoutTopCut.top_number)
            .claimWithID(claim1, false);

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
            .assertIDClaim(
                regularLogin.username,
                claim1.rank, claim1.rank_top,
                false, false,
                claim1.runner_id, claim1.corp_id
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
            .assertIDClaim(
                regularLogin.username,
                claim1.rank, claim1.rank_top,
                true, false,
                claim1.runner_id, claim1.corp_id
            );

        // Remove claim, remove imported entries
        browser.log('* Remove claim, remove imported entries *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Add claim with other user's deck
        browser.log("* Add claim with other user's deck *");
        browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentNrtmJsonWithoutTopCut.title,
            tournamentNrtmJsonWithoutTopCut.players_number, tournamentNrtmJsonWithoutTopCut.top_number)
            .claimWithDeckID(claim1);

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

        // Claim again with published decks, validate conflict
        browser.log('* Claim again with published decks, validate conflict *');
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

        // Remove claim
        browser.log(' * Remove claim *');
        browser.page.tournamentView()
            .click('@removeClaim');

        // Add claim with IDs, validate conflict
        browser.log('* Add claim with IDs *');browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentNrtmJsonWithoutTopCut.title,
            tournamentNrtmJsonWithoutTopCut.players_number, tournamentNrtmJsonWithoutTopCut.top_number)
            .claimWithID(claim1, false);

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
            .assertIDClaim(
            regularLogin.username,
            claim1.rank, claim1.rank_top,
            true, false,
            claim1.runner_id, claim1.corp_id
        );

        // Remove claim
        browser.log('* Remove claim * ');
        browser.page.tournamentView()
            .click('@removeClaim');

        // Add claim with other user's deck, validate conflict
        browser.log("* Add claim with other user's deck, validate conflict *");
        browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentNrtmJsonWithoutTopCut.title,
            tournamentNrtmJsonWithoutTopCut.players_number, tournamentNrtmJsonWithoutTopCut.top_number)
            .claimWithDeckID(claim1);

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
    }
};
