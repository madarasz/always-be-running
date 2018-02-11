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
     * Click claim, validate claim modal, add claim of published decklists
     * Validate tournament details page, validate claim
     * Import Cobra results (no-confilect), validate absence of conflicts
     * Remove user claim, remove imported claims
     * Import Cobra results, add claim of published decklist, validate absence of conflict
     */
    'Claiming, import (Cobr.ai), top-cut, no conflicts': function (browser) {

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

        // Claim with published decklists
        browser.log('* Claim with published decklists *');
        browser.page.tournamentView()
            .validate()
            .removeJson()
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

        // Import Cobra results (no-conflict), validate absence of conflicts
        browser.log('* Import Cobra results (no-conflict), validate absence of conflicts *');
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

        // Claim with IDs
        browser.log('* Claim with IDs *');
        browser.page.tournamentView()
            .validate()
            .removeJson()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentCobraJsonWithTopCut.title,
            tournamentCobraJsonWithTopCut.players_number, tournamentCobraJsonWithTopCut.top_number)
            .claimWithID(claim2, false);

        // Import Cobra results (no-conflict), validate absence of conflicts
        browser.log('* Import Cobra results (no-conflict), validate absence of conflicts *');
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
            .assertIDClaim(
                regularLogin.username,
                claim2.rank, claim2.rank_top,
                false, false,
                claim2.runner_id, claim2.cord_id_validate
            );

        // Remove user claim, remove imported claims
        browser.log('* Remove user claim, remove imported claims *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Add claim with other user's deck
        browser.log("* Add claim with other user's deck *");
        browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .claimWithDeckID(claim2);

        // Import Cobra results (no-conflict), validate absence of conflicts
        browser.log('* Import Cobra results (no-conflict), validate absence of conflicts *');
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

        // Import Cobra results, add claim with published decklist, validate absence of conflict
        browser.log('* Import Cobra results, add claim with published decklist, validate absence of conflict *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');
        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        browser.page.tournamentView()
            .click('@buttonClaim');
        browser.page.claimModal()
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

        // Remove user claim, remove imported claims
        browser.log('* Remove user claim, remove imported claims *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Import Cobra results, add claim with IDs, validate absence of conflict
        browser.log('* Import Cobra results, add claim with IDs, validate absence of conflict *');
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
            .claimWithID(claim2, true);

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
            .assertIDClaim(
                regularLogin.username,
                claim2.rank, claim2.rank_top,
                false, false,
                claim2.runner_id, claim2.cord_id_validate
            );

        // Remove user claim, remove imported claims
        browser.log('* Remove user claim, remove imported claims *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Import Cobra results, add claim with other user's deck, validate absence of conflict
        browser.log("* Import Cobra results, add claim with other user's deck, validate absence of conflict *");
        browser.page.tournamentView()
            .click('@buttonNRTMimport');
        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        browser.page.tournamentView()
            .click('@buttonClaim');
        browser.page.claimModal()
            .claimWithDeckID(claim2);

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
