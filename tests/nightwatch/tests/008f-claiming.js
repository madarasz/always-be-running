module.exports = {

    beforeEach: function (browser) {
        browser.deleteCookies().windowMaximize('current');
    },

    after: function (browser) {
        browser.end();
    },

    /***
     * Go to NetrunnerDB, login (regular login)
     * Go to My Decklists, delete all previously published decklist by this test case
     * Go to ABR, login with NRDB (regular user)
     * Create concluded tournament
     * Open claim modal, validate modal
     * Select private and published deck, validate modal
     * Select just published decks, validate modal
     * Select private and published deck, validate modal, submit claim
     * Validate tournament page and claim
     * Go to NetrunnerDB, verify that private deck was published
     * Delete published deck on NetrunnerDB
     * @param browser
     */
    'Claiming with private decks, publishing': function (browser) {

        var util = require('util');
        var regularLogin = browser.globals.accounts.regularLogin,
            claim1 = browser.globals.claims.claim1,
            claim4 = browser.globals.claims.claim4,
            tournamentNrtmJsonWithoutTopCut =
                JSON.parse(JSON.stringify(browser.globals.tournaments.tournamentNrtmJsonWithoutTopCut)); // clone

        tournamentNrtmJsonWithoutTopCut.title = browser.currentTest.module.substring(0, 4) + "|" +
            browser.currentTest.name.substring(0, 28) + "|" + tournamentNrtmJsonWithoutTopCut.title.substring(0, 16);

        // Go to NetrunnerDB, login (regular login)
        browser.log(' * Go to NetrunnerDB, login (regular login) *');
        browser.url("https://netrunnerdb.com");
        browser.page.NetrunnerDB()
            .validate()
            .loginNetrunnerDB(regularLogin);

        // Go to My Decklists, delete all previously published decklist by this test case
        browser.log('* Go to My Decklists, delete all previously published decklist by this test case *');
        browser.page.NetrunnerDB()
            .deleteDecklist(claim4.runner_deck);

        // Go to ABR, login with NRDB (regular user)
        browser.log('* Go to ABR, login with NRDB (regular user) *');
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();
        browser.login(regularLogin.username, regularLogin.password);

        // Create concluded tournament
        browser.log('* Create concluded tournament *');
        browser.page.mainMenu()
            .selectMenu('organize');
        browser.page.organizePage()
            .validate(true)
            .clickCommand('createTournament');
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
            })
            .getLocationInView('@submit_button').click('@submit_button');

        // Open claim modal, validate modal
        browser.log('* Open claim modal, validate modal *');
        browser.page.tournamentView()
            .validate()
            .removeJson()
            .click('@buttonClaim');

        browser.pause(500);
        browser.page.claimModal()
            .validate(tournamentNrtmJsonWithoutTopCut.title,
            tournamentNrtmJsonWithoutTopCut.players_number, tournamentNrtmJsonWithoutTopCut.top_number)
            .assertModal({
                warningNoRunnerDecks: false,
                warningNoCorpDecks: false,
                warningUsingOtherRunner: false,
                warningUsingOtherCorp: false,
                inputCorpDeck: true,
                inputRunnerDeck: true,
                warningNetrunnerDB: false,
                warningPublishing: false,
                submit: true
            });

        // Select private and published deck, validate modal
        browser.log('* Select private deck, validate modal *');
        browser.page.claimModal()
            .click('@inputRunnerDeck')
            .setValue('@inputRunnerDeck', claim4.runner_deck)
            .click('@inputCorpDeck')
            .setValue('@inputCorpDeck', claim4.corp_deck)
            .assertModal({
                warningNoRunnerDecks: false,
                warningNoCorpDecks: false,
                warningUsingOtherRunner: false,
                warningUsingOtherCorp: false,
                inputCorpDeck: true,
                inputRunnerDeck: true,
                warningNetrunnerDB: false,
                warningPublishing: true,
                submit: true
            });

        // Select just published decks, validate modal
        browser.log('* Select just published decks, validate modal *');
        browser.page.claimModal()
            .click('@inputRunnerDeck')
            .setValue('@inputRunnerDeck', claim1.runner_deck)
            .assertModal({
                warningNoRunnerDecks: false,
                warningNoCorpDecks: false,
                warningUsingOtherRunner: false,
                warningUsingOtherCorp: false,
                inputCorpDeck: true,
                inputRunnerDeck: true,
                warningNetrunnerDB: false,
                warningPublishing: false,
                submit: true
            });

        // Select private and published deck, validate modal, submit claim
        browser.log('* Select private deck, validate modal *');
        browser.page.claimModal()
            .click('@inputCorpDeck')
            .setValue('@inputCorpDeck', claim4.corp_deck)
            .click('@inputRunnerDeck')
            .setValue('@inputRunnerDeck', claim4.runner_deck)
            .assertModal({
                warningNoRunnerDecks: false,
                warningNoCorpDecks: false,
                warningUsingOtherRunner: false,
                warningUsingOtherCorp: false,
                inputCorpDeck: true,
                inputRunnerDeck: true,
                warningNetrunnerDB: false,
                warningPublishing: true,
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
                regularLogin.username,
                claim4.rank, claim4.rank_top,
                false, false,
                claim4.runner_deck, claim4.corp_deck
            );

        // Go to NetrunnerDB, verify that private deck was published
        browser.log('* Go to NetrunnerDB, verify that private deck was published *');
        browser.url("https://netrunnerdb.com");
        browser.page.NetrunnerDB().assertDeckExist(claim4.runner_deck);

        // Delete published deck on NetrunnerDB
        browser.log('* Delete published deck on NetrunnerDB *');
        browser.page.NetrunnerDB()
            .deleteDecklist(claim4.runner_deck);

        // data cleanup, delete tournament
        browser.sqlDeleteTournament(tournamentNrtmJsonWithoutTopCut.title, browser.globals.database.connection);
    }
};
