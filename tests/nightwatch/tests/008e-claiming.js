module.exports = {

    beforeEach: function (browser) {
        browser.deleteCookies().windowMaximize('current');
    },

    after: function (browser) {
        browser.end();
    },

    /**
     * Login with NRDB (user without decks)
     * Navigate to Organize page, create from results
     * Fill out form with concluded, online tournament data
     * Save tournament
     * Open claim modal, validate modal
     * Add other user's runner deck ID, validate modal
     * Add other user's corp deck ID, validate modal
     * Clear deck IDs, validate modal
     * Add deck IDs again, validate modal, submit claim
     * Validate tournament page and claim
     * @param browser
     */
    'Claiming with user without decks': function (browser) {

        var util = require('util');
        var emptyLogin = browser.globals.accounts.emptyLogin,
            claim1 = browser.globals.claims.claim1,
            tournamentNrtmJsonWithoutTopCut =
                JSON.parse(JSON.stringify(browser.globals.tournaments.tournamentNrtmJsonWithoutTopCut)); // clone

        tournamentNrtmJsonWithoutTopCut.title = browser.currentTest.module.substring(0, 4) + "|" +
            browser.currentTest.name.substring(0, 28) + "|" + tournamentNrtmJsonWithoutTopCut.title.substring(0, 16);

        // Open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // Login with NRDB (user without decks)
        browser.log('* Login with NRDB (user without decks) *');
        browser.login(emptyLogin.username, emptyLogin.password);

        // Navigate to Organize page, create from results
        browser.log('* Navigate to Organize page, create from results *');
        browser.page.mainMenu()
            .selectMenu('organize');
        browser.page.organizePage()
            .validate(true)
            .clickCommand('createTournament');

        // Fill out form with concluded, online tournament data
        browser.log('* Fill out form with concluded, online tournament data *');
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

        // Save tournament
        browser.log('* Save tournament *');
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');

        // Open claim modal, validate modal
        browser.log('* Open claim modal, validate modal *');
        browser.page.tournamentView()
            .validate()
            .removeJson()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournamentNrtmJsonWithoutTopCut.title,
            tournamentNrtmJsonWithoutTopCut.players_number, tournamentNrtmJsonWithoutTopCut.top_number)
            .assertModal({
                warningNoRunnerDecks: true,
                warningNoCorpDecks: true,
                warningUsingOtherRunner: false,
                warningUsingOtherCorp: false,
                inputCorpDeck: false,
                inputRunnerDeck: false,
                warningNetrunnerDB: false,
                warningPublishing: false,
                submit: 'not found'
            });

        // Add other user's runner deck ID, validate modal
        browser.log("* Add other user's runner deck ID, validate modal *");
        browser.page.claimModal()
            .click('@moreOptions')
            .click('@inputRunnerDeckID')
            .setValue('@inputRunnerDeckID', claim1.runner_deck_id)
            .assertModal({
                warningNoRunnerDecks: true,
                warningNoCorpDecks: true,
                warningUsingOtherRunner: true,
                warningUsingOtherCorp: false,
                inputCorpDeck: false,
                inputRunnerDeck: false,
                warningNetrunnerDB: false,
                warningPublishing: false,
                submit: 'not found'
            });

        // Add other user's corp deck ID, validate modal
        browser.log("* Add other user's corp deck ID, validate modal *");
        browser.page.claimModal()
            .click('@inputCorpDeckID')
            .setValue('@inputCorpDeckID', claim1.corp_deck_id)
            .assertModal({
                warningNoRunnerDecks: true,
                warningNoCorpDecks: true,
                warningUsingOtherRunner: true,
                warningUsingOtherCorp: true,
                inputCorpDeck: false,
                inputRunnerDeck: false,
                warningNetrunnerDB: false,
                warningPublishing: false,
                submit: true
            });

        // Clear deck IDs, validate modal
        browser.log('* Clear deck IDs, validate modal *');
        browser.page.claimModal()
            .clearValue('@inputCorpDeckID')
            .clearValue('@inputRunnerDeckID')
            .click('@inputCorpDeckID').api.keys('1').keys(browser.Keys.BACK_SPACE) // to trigger onchange
        browser.page.claimModal()
            .click('@inputRunnerDeckID').api.keys('1').keys(browser.Keys.BACK_SPACE); // to trigger onchange
        browser.page.claimModal()
            .assertModal({
                warningNoRunnerDecks: true,
                warningNoCorpDecks: true,
                warningUsingOtherRunner: false,
                warningUsingOtherCorp: false,
                inputCorpDeck: false,
                inputRunnerDeck: false,
                warningNetrunnerDB: false,
                warningPublishing: false,
                submit: 'not found'
            });

        // Add deck IDs again, validate modal, submit claim
        browser.log('* Add deck IDs again, validate modal, submit claim *');
        browser.page.claimModal()
            .click('@inputCorpDeckID')
            .setValue('@inputCorpDeckID', claim1.corp_deck_id)
            .click('@inputRunnerDeckID')
            .setValue('@inputRunnerDeckID', claim1.runner_deck_id)
            .assertModal({
                warningNoRunnerDecks: true,
                warningNoCorpDecks: true,
                warningUsingOtherRunner: true,
                warningUsingOtherCorp: true,
                inputCorpDeck: false,
                inputRunnerDeck: false,
                warningNetrunnerDB: false,
                warningPublishing: false,
                submit: true
            })
            .click('@submit');

        // Validate tournament page and claim
        browser.log('* Validate tournament page and claim *');
        browser.page.tournamentView()
            .validate()
            .assertView({
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
                showPoints: false
            })
            .assertClaim(
                emptyLogin.username,
                1, 0,
                false, false,
                claim1.runner_deck, claim1.corp_deck
            );

        // data cleanup, delete tournament
        browser.sqlDeleteTournament(tournamentNrtmJsonWithoutTopCut.title, browser.globals.database.connection);
    }
};
