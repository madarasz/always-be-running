var tournamentA = {
    title: 'Test A - ' + formatDate(new Date()),
    type: 'casual',
    type_id: '6',
    cardpool: 'Business First',
    cardpool_id: 'bf',
    description: 'description A',
    date: '2999.01.01.',
    time: '12:40',
    players: '20',
    wrong_players: '3',
    top: 'top 4',
    top_value: '4',
    contact: '+66 666 666',
    location_input: 'Budapest metagame',
    location: 'Hungary, Budapest',
    city: 'Budapest',
    country: 'Hungary',
    store: 'Metagame Kártyabolt',
    address: 'Budapest, Kádár u. 10, 1132 Hungary'
};
var tournamentB = {
    title: 'Test B - ' + formatDate(new Date()),
    type: 'worlds championship',
    type_id: '4',
    cardpool: 'True Colors',
    cardpool_id: 'tc',
    description: 'description B',
    date: '2003.01.01.',
    time: '10:00',
    contact: '+33 333 333',
    wrong_location: 'Spain',
    location: 'Barcelona, Spain',
    city: 'Spain, Barcelona',
    players: '30',
    wrong_players: '8',
    top: 'top 8'
};
var tournamentC = {
    title: 'Test C - ' + formatDate(new Date()),
    type: 'online event',
    type_id: '7',
    date: '2001.01.01.',
    cardpool: '--- not yet known ---',
    cardpool_id: 'unknown'
};

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
     * - create tournament (casual, concluded), validation for missing location, players-top
     * - fill in missing location, save
     * - verify on tournament view page
     * - verify on Organize page table
     * - update tournament (worlds, not concluded), validation for missing city
     * - fill in missing city, save
     * - verify on tournament view page
     * - verify on Organize page table
     * - conclude tournament from Organize page, validation for players-top
     * - conclude tournament from Organize page, with correct values
     * - verify on tournament view page
     * - delete tournament from Organize page
     */
    'Tournament - create, edit, view with validation, conclude': function (browser) {

        var regularLogin = browser.globals.regularLogin;

        browser
            .url(browser.launchUrl)
            .log('*** Logging in ***')
            .login(regularLogin.username, regularLogin.password)
            .log('*** Creating Tournament A ***');

        browser.page.mainMenu().selectMenu('organize');

        browser.page.organizePage().clickCommand('create');

        // creating new tournament
        browser.page.tournamentForm()
            .validate()
            .assertForm({
                visible: ['players_number_disabled', 'map_loaded'],
                not_visible: ['overlay_conclusion', 'overlay_location']
            })
            .fillForm({
                inputs: {
                    title: tournamentA.title,
                    date: tournamentA.date,
                    start_time: tournamentA.time,
                    contact: tournamentA.contact
                },
                textareas: {description: tournamentA.description},
                selects: {
                    tournament_type_id: tournamentA.type,
                    cardpool_id: tournamentA.cardpool
                },
                checkboxes: {decklist: true}
            }).
            fillForm({
                checkboxes: {concluded: true}
            });
        browser.page.tournamentForm()
            .fillForm({
                inputs: { players_number: tournamentA.wrong_players },
                selects: { top_number: tournamentA.top }
            })
            // submit with missing location
            .click('@submit_button')
            .assertForm({
                errors: ['city', 'country', 'Players in top cut']
            })
            // adding location info
            .fillForm({
                location: tournamentA.location_input,
                inputs: { players_number: tournamentA.players }
            })
            .assertForm({
                visible: ['location_country', 'location_city', 'location_store', 'location_address'],
                not_present: ['location_state']
            })
            .click('@submit_button');

        // verify on tournament detail page
        browser.page.tournamentView()
            .assertView({
                title: tournamentA.title,
                ttype: tournamentA.type,
                creator: regularLogin.username,
                description: tournamentA.description,
                date: tournamentA.date,
                time: tournamentA.time,
                cardpool: tournamentA.cardpool,
                city: tournamentA.city,
                store: tournamentA.store,
                address: tournamentA.address,
                contact: tournamentA.contact,
                map: true,
                decklist: true,
                approvalNeed: true,
                editButton: true,
                approveButton: false,
                rejectButton: false,
                deleteButton: true,
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
                suggestLogin: false,
                buttonNRTMimport: true,
                buttonNRTMclear: true,
                buttonConclude: false,
                playerClaim: false,
                createClaimFrom: true,
                submitClaim: true,
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
                registerButton: true
            });

        // verify table on organize page
        browser.page.mainMenu().selectMenu('organize');

        browser.page.tournamentTable()
            .assertTable('created', tournamentA.title, {
                texts: [tournamentA.date, tournamentA.cardpool, tournamentA.players, tournamentA.city],
                labels: ['pending', 'concluded'], texts_missing: []
            })
            .selectTournament('created', tournamentA.title, 'update');

        // update tournament
        browser.page.tournamentForm()
            .validate()
            .assertForm({
                visible: ['location_country', 'location_city', 'location_store', 'location_address'],
                not_present: ['location_state'],
                not_visible: ['overlay_conclusion', 'overlay_location'],
                inputs: {
                    title: tournamentA.title,
                    date: tournamentA.date,
                    start_time: tournamentA.time,
                    contact: tournamentA.contact,
                    players_number: tournamentA.players
                },
                textareas: { description: tournamentA.description },
                selects: {
                    tournament_type_id: tournamentA.type_id,
                    cardpool_id: tournamentA.cardpool_id,
                    top_number: tournamentA.top_value
                },
                checkboxes: {decklist: true, concluded: true}
            })
            .fillForm({
                inputs: {
                    title: tournamentB.title,
                    date: tournamentB.date,
                    start_time: tournamentB.time,
                    contact: tournamentB.contact
                },
                textareas: {description: tournamentB.description},
                selects: {
                    tournament_type_id: tournamentB.type,
                    cardpool_id: tournamentB.cardpool
                },
                checkboxes: {decklist: false},
                location: tournamentB.wrong_location
            })
            .fillForm({
                checkboxes: {concluded: false}
            })
            // submit with missing city location
            .click('@submit_button')
            .assertForm({
                errors: ['city']
            })
            .fillForm({
                location: tournamentB.location
            })
            .click('@submit_button');

        // verify on tournament detail page
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentB.title,
                ttype: tournamentB.type,
                creator: regularLogin.username,
                description: tournamentB.description,
                date: tournamentB.date,
                time: tournamentB.time,
                cardpool: tournamentB.cardpool,
                city: tournamentB.city,
                contact: tournamentB.contact,
                map: true,
                decklist: false,
                approvalNeed: true,
                editButton: true,
                approveButton: false,
                rejectButton: false,
                deleteButton: true,
                conflictWarning: false,
                playerNumbers: false,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: false,
                buttonConclude: true,
                playerClaim: false,
                createClaimFrom: false,
                submitClaim: false,
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
                storeInfo: false,
                addressInfo: false
            });

        // verify table on organize page
        browser.page.mainMenu().selectMenu('organize');

        browser.page.tournamentTable()
            .assertTable('created', tournamentB.title, {
                texts: [tournamentB.date, tournamentB.cardpool, tournamentB.city],
                labels: ['pending']
            })
            .selectTournament('created', tournamentB.title, 'conclude');

        // conclude on organize page, with validation error
        browser.page.concludeModal()
            .validate(tournamentB.title)
            .validate(tournamentB.date)
            .concludeManual({
                players_number: tournamentB.wrong_players,
                top_number: tournamentB.top
            });

        // check for valication error
        browser.page.tournamentForm().assertForm({ errors: ['Players in top cut']});    // validating with different page object

        // conclude on organize page, with correct values
        browser.page.tournamentTable()
            .assertTable('created', tournamentB.title, {
                texts: [tournamentB.date, tournamentB.cardpool, tournamentB.city],
                labels: ['pending']
            })
            .selectTournament('created', tournamentB.title, 'conclude');

        browser.page.concludeModal()
            .validate(tournamentB.title)
            .validate(tournamentB.date)
            .concludeManual({
                players_number: tournamentB.players,
                top_number: tournamentB.top
            });

        // verify on tournament view
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentB.title,
                ttype: tournamentB.type,
                creator: regularLogin.username,
                description: tournamentB.description,
                date: tournamentB.date,
                time: tournamentB.time,
                cardpool: tournamentB.cardpool,
                city: tournamentB.city,
                contact: tournamentB.contact,
                map: true,
                decklist: false,
                approvalNeed: true,
                editButton: true,
                approveButton: false,
                rejectButton: false,
                deleteButton: true,
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
                suggestLogin: false,
                buttonNRTMimport: true,
                buttonNRTMclear: true,
                buttonConclude: false,
                playerClaim: false,
                createClaimFrom: true,
                submitClaim: true,
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
                registerButton: true,
                storeInfo: false,
                addressInfo: false
            });

        // verify table on organize page
        browser.page.mainMenu().selectMenu('organize');

        browser.page.tournamentTable()
            .assertTable('created', tournamentB.title, {
                texts: [tournamentB.date, tournamentB.cardpool, tournamentB.city],
                labels: ['pending', 'concluded'], texts_missing: []
            })
            .selectTournament('created', tournamentB.title, 'delete');
    },

    after: function(browser) {
        browser.end();
    }
};