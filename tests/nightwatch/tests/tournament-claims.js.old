var tournament = {
    title: 'Test - ' + formatDate(new Date()),
    type: 'online event',
    type_id: '6',
    date: '2001.01.01.',
    players: '20',
    top: 'top 4'
}, claim1_wrong = {
    rank: '4',
    rank_top: 'below top cut',
    runner_deck: 'New Runner Deck',
    corp_deck: 'New Corp Deck, IG'
}, claim2_wrong = {
    rank: '5',
    rank_top: '3',
    runner_deck: 'New Runner Deck',
    corp_deck: 'New Corp Deck, IG'
}, claim3 = {
    rank: '5',
    rank_top: 'below top cut',
    runner_deck: 'New Runner Deck',
    corp_deck: 'New Corp Deck, IG'
};

// TODO: put in module
function formatDate(date) {
    var year = date.getFullYear(),
        month = date.getMonth() + 1, // months are zero indexed
        day = date.getDate(),
        hour = date.getHours(),
        minute = date.getMinutes(),
        minuteFormatted = minute < 10 ? "0" + minute : minute;

    return year + "." + month + "." + day + " " + hour + ":" + minuteFormatted;
}

module.exports = {
    /**
     * NRTM scenarios:
     * - no reg, NRTM import, claim with user, matching claim
     * - reg, NRTM import, claim with user, not mathcing claim
     * - reg, claim with user, NRTM import, matching
     * - claim with user, NRTM import, not matching
     *
     * - remove claim, remove import scenarios
     * - validation errors
     * - creator removing claims from different people
     * - different users claiming the same spot
     */
    /**
     * Claim scenarios:
     * * reg, conclude (with top), claim with validation (below top cut), unclaim, unregister
     */

    /**
     * - create tournament, online, not conluded
     * - verify on tournament details page
     * - register for tournament
     * - verify on tournament details page
     * - conlcude tournament with top cut
     * - verify on tournament details page
     * - claim with validation errors (top swiss+no top cut, below top swiss+top cut), verify errors
     * - claim without errors
     * - verify on tournament details page
     * - remove claim
     * - verify on tournament details page
     * - unregister
     * - verify on tournament details page
     * - delete tournament
     * @param browser
     */
    'Tournament - reg, conclude, claim with validation, unclaim, unregister': function (browser) {

        var regularLogin = browser.globals.regularLogin;

        browser
            .url(browser.launchUrl)
            .log('*** Logging in ***')
            .login(regularLogin.username, regularLogin.password)
            .log('*** Creating Tournament C ***');

        browser.page.mainMenu().selectMenu('organize');

        browser.page.organizePage().clickCommand('create');

        // create tournament: online, not concluded
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                inputs: {
                    title: tournament.title,
                    date: tournament.date
                },
                selects: {
                    tournament_type_id: tournament.type
                },
                checkboxes: {decklist: false, concluded: false}
            })
            .click('@submit_button');

        // verify on tournament detail page
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournament.title,
                ttype: tournament.type,
                creator: regularLogin.username,
                date: tournament.date,
                descriptionSection: false,
                map: false,
                decklist: false,
                editButton: true,
                deleteButton: true,
                conflictWarning: false,
                playerNumbers: false,
                topPlayerNumbers: false,
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
                registerButton: true
            })
            // register for tournament
            .click('@registerButton')
            // verify on tournament detail page
            .assertView({
                editButton: true,
                deleteButton: true,
                conflictWarning: false,
                playerNumbers: false,
                topPlayerNumbers: false,
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
                registerButton: false
            })
            .click('@buttonConclude');

        // conclude on tournament details page, without top-cut
        browser.page.concludeModal()
            .validate(tournament.title)
            .concludeManual({
                players_number: tournament.players,
                top_number: tournament.top
            });

        // verify on tournament detail page
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournament.title,
                editButton: true,
                deleteButton: true,
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
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
                unregisterButton: false,
                registerButton: false
            })
            //claim with validation errors (top swiss+no top cut, below top swiss+top cut), verify errors
            .click('@buttonClaim');

        // top swiss + no top cut
        browser.page.claimModal()
            .validate(tournament.title)
            .claim(claim1_wrong);    // error: top_rank is  below top cut

        browser.page.messages()
            .assertError('The "rank after top cut" must be set')

        // top swiss + no top cut
        browser.page.tournamentView()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournament.title)
            .claim(claim2_wrong);    // error: top_rank is not set

        browser.page.messages()
            .assertError('The "rank after top cut" must be "below top cut"');

        // claim without errors
        browser.page.tournamentView()
            .click('@buttonClaim');

        browser.page.claimModal()
            .validate(tournament.title)
            .claim(claim3);

        // verify on tournament detail page
        browser.page.tournamentView()
            .validate()
            .assertView({
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
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
                registerButton: false
            })
            // remove claim
            .click('@removeClaim')
            // verify on tournament detail page
            .assertView({
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
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
                unregisterButton: false,
                registerButton: false
            })
            // delete tournament
            .click('@deleteButton');

        browser.page.tournamentTable()
            .assertMissingRow('created', tournament.title);

    },

    after: function(browser) {
        browser.end();
    }
};