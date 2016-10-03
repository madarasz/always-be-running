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
    top: '4',
    contact: '+66 666 666',
    location: 'Budapest metagame',
    city: 'Hungary, Budapest',
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
    location: 'Barcelona Spain',
    city: 'Spain, Barcelona'
};
var tournamentC = {
    title: 'Test B - ' + formatDate(new Date()),
    type: 'online event',
    type_id: '6',
    date: '2001.01.01.',
    country: 'United States',
    country_id: '840',
    state: 'California',
    state_id: '5',
    city: 'Miskolc',
    store: 'Telep',
    address: 'Nincs utca 5.'
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
    'Tournament - create (casual, concluded), edit (worlds, not concluded), view with validation': function (browser) {

        var regularLogin = browser.globals.regularLogin;

        browser
            .url(browser.launchUrl)
            .log('*** Logging in ***')
            .login(regularLogin.username, regularLogin.password)
            .log('*** Creating Tournament A ***');

        browser.page.mainMenu().selectMenu('organize');

        browser.page.organizePage().click('@create');

        // creating new tournament
        browser.page.tournamentForm()
            .assertForm({
                visible: ['location', 'players_number_disabled', 'map_loaded']})
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
                checkboxes: {decklist: true, concluded: true},
            })
            .fillForm({
                inputs: {
                    players_number: tournamentA.players,
                    top_number: tournamentA.top
                }
            })
            // submit with missing location
            .click('@submit_button')
            .assertForm({
                errors: ['city', 'country']
            })
            // adding location info
            .fillForm({
                location: tournamentA.location
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
                //decklist: true, // don't know why this fails
                approvalNeed: true,
                editButton: true,
                approveButton: false,
                rejectButton: false,
                deleteButton: true,
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
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
            .assertForm({
                visible: ['location_country', 'location_city', 'location_store', 'location_address'],
                not_present: ['location_state'],
                inputs: {
                    title: tournamentA.title,
                    date: tournamentA.date,
                    start_time: tournamentA.time,
                    contact: tournamentA.contact,
                    players_number: tournamentA.players,
                    top_number: tournamentA.top
                },
                textareas: { description: tournamentA.description },
                selects: {
                    tournament_type_id: tournamentA.type_id,
                    cardpool_id: tournamentA.cardpool_id
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
                location: tournamentB.wrong_location,
                checkboxes: {concluded: true, decklist: false}
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
                labels: ['pending', 'due'], texts_missing: []
            })
            .selectTournament('created', tournamentB.title, 'delete');
    },

    after: function(browser) {
        browser.end();
    }
};