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
     * Add claim of published decklist, import Cobra results, validate tournament details, claim and conflict in swiss
     * Import Cobra results, validate conflict in swiss
     * Remove user claim, remove imported claims
     * Import Cobra results, add claim of published decklist, validate tournament details, claim and conflict in swiss
     * Remove user claim, remove imported claims
     * Add claim of published decklist, import Cobra results, validate tournament details, claim and conflict in top cut
     * Remove user claim, remove imported claims
     * Import Cobra results, add claim of published decklist, validate tournament detaild, claim and conflict in top cut
     * Remove user claim, validate imported entries and points
     * Add claim of published deck without conflict
     * Validate tournament details, user's claim, the absence of conflict
     */
    'Claiming, import (Cobr.ai), top-cut, conflicts': function (browser) {

        var regularLogin = browser.globals.accounts.regularLogin,
            claim3 = browser.globals.claims.claim3,
            tournamentCobraJsonWithTopCut = JSON.parse(JSON.stringify(browser.globals.tournaments.tournamentCobraJsonWithTopCut)), // clone
            claim_wrong_top = {
                rank: claim3.rank,
                rank_top: claim3.wrong_rank_top,
                runner_deck: claim3.runner_deck,
                corp_deck: claim3.corp_deck,
                runner_id: claim3.runner_id,
                corp_id: claim3.corp_id,
                runner_deck_id: claim3.runner_deck_id,
                corp_deck_id: claim3.corp_deck_id
            },
            claim_wrong_swiss = {
                rank: claim3.wrong_rank,
                rank_top: claim3.rank_top,
                runner_deck: claim3.runner_deck,
                corp_deck: claim3.corp_deck,
                runner_id: claim3.runner_id,
                corp_id: claim3.corp_id,
                runner_deck_id: claim3.runner_deck_id,
                corp_deck_id: claim3.corp_deck_id
            };

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

        // Save tournament
        browser.log('* Save tournament *');
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');

        // Add claim with published decklists
        browser.log('* Add claim with published decklists *');
        browser.page.tournamentView()
            .validate()
            .removeJson()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentCobraJsonWithTopCut.title,
            tournamentCobraJsonWithTopCut.players_number, tournamentCobraJsonWithTopCut.top_number)
            .claim(claim_wrong_swiss);

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
                claim_wrong_swiss.rank, claim_wrong_swiss.rank_top,
                false, false,
                claim_wrong_swiss.runner_deck, claim_wrong_swiss.corp_deck
            );

        // Import Cobra results, validate claim and conflict in swiss
        browser.log('* Import Cobra results, validate claim and conflict in swiss *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');

        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: true,
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
                conflictInTable: true,
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
                claim_wrong_swiss.rank, claim_wrong_swiss.rank_top,
                true, true,
                claim_wrong_swiss.runner_deck, claim_wrong_swiss.corp_deck
            );

        // Remove user claim, remove imported claims
        browser.log('* Remove user claim, remove imported claims *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Add claim with IDs
        browser.page.tournamentView()
            .click('@buttonClaim');
        browser.page.claimModal()
            .validate(tournamentCobraJsonWithTopCut.title,
            tournamentCobraJsonWithTopCut.players_number, tournamentCobraJsonWithTopCut.top_number)
            .claimWithID(claim_wrong_swiss, false);

        // Import Cobra results, validate claim and conflict in swiss
        browser.log('* Import Cobra results, validate claim and conflict in swiss *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');

        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        browser.pause(10000);

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: true,
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
                conflictInTable: true,
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
                claim_wrong_swiss.rank, claim_wrong_swiss.rank_top,
                true, true,
                claim_wrong_swiss.runner_id, claim_wrong_swiss.corp_id
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
            .claimWithDeckID(claim_wrong_swiss);

        // Import Cobra results, validate claim and conflict in swiss
        browser.log('* Import Cobra results, validate claim and conflict in swiss *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');

        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: true,
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
                conflictInTable: true,
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
                claim_wrong_swiss.rank, claim_wrong_swiss.rank_top,
                true, true,
                claim_wrong_swiss.runner_deck, claim_wrong_swiss.corp_deck
            );

        // Remove user claim, remove imported claims
        browser.log('* Remove user claim, remove imported claims *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Import Cobra results
        browser.log('* Import Cobra results *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');
        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        // Validate imported entries and points
        browser.log('* Validate imported entries and points *');
        browser.page.tournamentView()
            .assertImport(tournamentCobraJsonWithTopCut.imported_results)
            .getLocationInView('@showMatches').click('@showMatches')
            .validateMatches(tournamentCobraJsonWithTopCut.imported_results)
            .getLocationInView('@showPoints').click('@showPoints')
            .validatePoints(tournamentCobraJsonWithTopCut.imported_results);

        // Add claim with published decklists, validate conflict in top
        browser.log('* Add claim with published decklists, validate conflict in top *');
        browser.page.tournamentView()
            .click('@buttonClaim');
        browser.page.claimModal()
            .validate(tournamentCobraJsonWithTopCut.title,
            tournamentCobraJsonWithTopCut.players_number, tournamentCobraJsonWithTopCut.top_number)
            .claim(claim_wrong_top);

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: true,
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
                conflictInTable: true,
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
                claim_wrong_top.rank, claim_wrong_top.rank_top,
                true, true,
                claim_wrong_top.runner_deck, claim_wrong_top.corp_deck
            );

        // Remove user claim
        browser.log('* Remove user claim *');
        browser.page.tournamentView()
            .click('@removeClaim');

        // Add claim with IDs, validate conflict in top cut
        browser.log('* Add claim with IDs, validate conflict in top cut *');
        browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentCobraJsonWithTopCut.title,
            tournamentCobraJsonWithTopCut.players_number, tournamentCobraJsonWithTopCut.top_number)
            .claimWithID(claim_wrong_top, false);

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: true,
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
                conflictInTable: true,
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
                claim_wrong_top.rank, claim_wrong_top.rank_top,
                true, true,
                claim_wrong_top.runner_id, claim_wrong_top.corp_id
            );

        // Remove user claim
        browser.log('* Remove user claim, remove imported claims *');
        browser.page.tournamentView()
            .click('@removeClaim');

        // Add claim with other user's decks, validate conflict in top
        browser.log("* Add claim with other user's decks, validate conflict in top *");
        browser.page.tournamentView()
            .click('@buttonClaim');
        browser.page.claimModal()
            .claimWithDeckID(claim_wrong_top);

        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: true,
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
                conflictInTable: true,
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
                claim_wrong_top.rank, claim_wrong_top.rank_top,
                true, true,
                claim_wrong_top.runner_deck, claim_wrong_top.corp_deck
            );

        // Remove user claim
        browser.log('* Remove user claim *');
        browser.page.tournamentView()
            .click('@removeClaim');

        // Add claim with published deck, no conflict
        browser.log('* Add claim with published deck, no conflict *');
        browser.page.tournamentView()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentCobraJsonWithTopCut.title,
            tournamentCobraJsonWithTopCut.players_number, tournamentCobraJsonWithTopCut.top_number)
            .claim(claim3);

        // Validate tournament details, user's claim, the absence of conflict
        browser.log('* Validate tournament details, user\'s claim, the absence of conflict*');
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
                claim3.rank, claim3.rank_top,
                false, false,
                claim3.runner_deck, claim3.corp_deck
            );

        // Remove user claim, remove imported claims
        browser.log('* Remove user claim, remove imported claims *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Import Cobra results
        browser.log('* Import Cobra results *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');
        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        // Add claim with IDs, no conflict
        browser.log('* Add claim with IDs, no conflict *');
        browser.page.tournamentView()
            .validate()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentCobraJsonWithTopCut.title,
            tournamentCobraJsonWithTopCut.players_number, tournamentCobraJsonWithTopCut.top_number)
            .claimWithID(claim3, false);

        // Validate tournament details, user's claim, the absence of conflict
        browser.log('* Validate tournament details, user\'s claim, the absence of conflict*');
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
                claim3.rank, claim3.rank_top,
                false, false,
                claim3.runner_id, claim3.corp_id
            );

        // Remove user claim, remove imported claims
        browser.log('* Remove user claim, remove imported claims *');
        browser.page.tournamentView()
            .click('@removeClaim')
            .click('@buttonNRTMclear').api.acceptAlert();

        // Import Cobra results
        browser.log('* Import Cobra results *');
        browser.page.tournamentView()
            .click('@buttonNRTMimport');
        browser.page.concludeModal()
            .validate(tournamentCobraJsonWithTopCut.title)
            .concludeNrtmJson('cobra-with-topcut.json');

        // Add claim with other user's decks
        browser.log("* Add claim with other user's decks *");
        browser.page.tournamentView()
            .click('@buttonClaim');
        browser.page.claimModal()
            .claimWithDeckID(claim3);

        // Validate tournament details, user's claim, the absence of conflict
        browser.log('* Validate tournament details, user\'s claim, the absence of conflict*');
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
                claim3.rank, claim3.rank_top,
                false, false,
                claim3.runner_deck, claim3.corp_deck
            );

        // data cleanup, delete tournament
        browser.sqlDeleteTournament(tournamentCobraJsonWithTopCut.title, browser.globals.database.connection);
    }
};
