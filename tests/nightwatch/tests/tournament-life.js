var tournamentA = {
    title: 'Test A - ' + formatDate(new Date()),
    type: 'casual',
    type_id: '5',
    cardpool: 'Business First',
    cardpool_id: 'bf',
    description: 'description A',
    date: '2999.01.01.',
    time: '12:40',
    country: 'United States',
    country_id: '840',
    state: 'California',
    state_id: '5',
    city: 'Budapest',
    store: 'Superstore',
    address: 'Sehol utca 4.',
    wrongdate: '444'
};
var tournamentB = {
    title: 'Test B - ' + formatDate(new Date()),
    type: 'worlds championship',
    type_id: '4',
    cardpool: 'True Colors',
    cardpool_id: 'tc',
    description: 'description B',
    players: '222',
    top: '44',
    date: '2003.01.01.',
    time: '10:00',
    country: 'Hungary',
    country_id: '348',
    city: 'Miskolc',
    store: 'Telep',
    address: 'Nincs utca 5.'
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
    'Tournament A - create, edit, view with validation' : function (browser) {

        var regularLogin = browser.globals.regularLogin;

        browser
            .url(browser.launchUrl)
            .log('*** Logging in ***')
            .login(regularLogin.username, regularLogin.password)

            // create tournament
            .log('*** Creating Tournament A ***')
            .click("//a[contains(text(),'Create')]")
            .assert.assertTournamentForm({not_visible: ['location_us_state', 'players_number', 'map']})
            .fillTournament({ // with wroing date
                inputs: {title: tournamentA.title, date: tournamentA.wrongdate, start_time: tournamentA.time},
                textareas: {description: tournamentA.description},
                selects: {
                    location_country: tournamentA.country,
                    tournament_type_id: tournamentA.type,
                    cardpool_id: tournamentA.cardpool
                },
                checkboxes: {decklist: true, concluded: true, display_map: true}
            })
            .assert.assertTournamentForm({visible: ['players_number', 'map']})
            .fillTournament({
                inputs: {players_number: tournamentB.players, top_number: tournamentB.top},
                checkboxes: {concluded: false}
            })
            .assert.assertTournamentForm({not_visible: ['players_number'], visible: ['location_us_state']})
            .fillTournament({
                selects: {location_us_state: tournamentA.state},
                inputs: {
                    location_city: tournamentA.city,
                    location_store: tournamentA.store,
                    location_address: tournamentA.address
                }
            })
            .click("//input[@type='submit']")

            // data check after validation
            .log('*** Data check after form validation fails ***')
            .waitForElementVisible("//li[contains(text(), 'YYYY.MM.DD.')]", 1000)
            .assert.assertTournamentForm({
                inputs: {
                    title: tournamentA.title,
                    date: tournamentA.wrongdate,
                    start_time: tournamentA.time,
                    location_city: tournamentA.city,
                    location_store: tournamentA.store,
                    location_address: tournamentA.address
                },
                textareas: {description: tournamentA.description},
                selects: {
                    location_country: tournamentA.country_id, tournament_type_id: tournamentA.type_id,
                    cardpool_id: tournamentA.cardpool_id, location_us_state: tournamentA.state_id
                },
                checkboxes: {decklist: true, concluded: false, display_map: true},
                not_visible: ['players_number'], visible: ['location_us_state', 'map']
            })
            .fillTournament({inputs: {date: tournamentA.date}})
            .log('*** Saving ***')
            .click("//input[@type='submit']")

            // verify on My tournaments
            .log('*** Verifying on my tournaments table ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .assert.assertTournamentTable('created', tournamentA.title, {
                texts: [tournamentA.date, tournamentA.cardpool],
                labels: ['pending', 'not yet'], texts_missing: [tournamentB.players]
            })

            // verify tournament details view
            .log('*** Verifying on tournament details view ***')
            .selectTournament('created', tournamentA.title, 'view')
            .assert.assertTournamentView({
                title: tournamentA.title, ttype: tournamentA.type,
                description: tournamentA.description, date: tournamentA.date, time: tournamentA.time,
                country: tournamentA.country, state: tournamentA.state, city: tournamentA.city,
                store: tournamentA.store, address: tournamentA.address, map: true
            })

            // edit tournament
            .log('*** Verifying data on edit form ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .selectTournament('created', tournamentA.title, 'edit')
            .assert.assertTournamentForm({
                inputs: {
                    title: tournamentA.title,
                    date: tournamentA.date,
                    start_time: tournamentA.time,
                    location_city: tournamentA.city,
                    location_store: tournamentA.store,
                    location_address: tournamentA.address
                },
                textareas: {description: tournamentA.description},
                selects: {
                    location_country: tournamentA.country_id, tournament_type_id: tournamentA.type_id,
                    cardpool_id: tournamentA.cardpool_id, location_us_state: tournamentA.state_id
                },
                checkboxes: {decklist: true, concluded: false, display_map: true},
                not_visible: ['players_number'], visible: ['location_us_state', 'map']
            })

            // modify values
            .log('*** Editing Tournament A to B ***')
            .fillTournament({
                inputs: {
                    title: tournamentB.title,
                    date: tournamentA.wrongdate,    // wrong date
                    start_time: tournamentB.time,
                    location_city: tournamentB.city,
                    location_store: tournamentB.store,
                    location_address: tournamentB.address
                },
                textareas: {description: tournamentB.description},
                selects: {
                    location_country: tournamentB.country,
                    tournament_type_id: tournamentB.type,
                    cardpool_id: tournamentB.cardpool
                },
                checkboxes: {decklist: false, concluded: true, display_map: false}
            })
            .assert.assertTournamentForm({
                inputs: {players_number: '', top_number: ''},
                not_visible: ['location_us_state', 'map']
            })
            .fillTournament({inputs: {players_number: tournamentB.players, top_number: tournamentB.top}})
            .click("//input[@type='submit']")

            // data check after validation
            .log('*** Data check after form validation fails ***')
            .waitForElementVisible("//li[contains(text(), 'YYYY.MM.DD.')]", 1000)
            .assert.assertTournamentForm({
                inputs: {
                    title: tournamentB.title,
                    date: tournamentA.wrongdate,
                    start_time: tournamentB.time,
                    location_city: tournamentB.city,
                    location_store: tournamentB.store,
                    location_address: tournamentB.address,
                    players_number: tournamentB.players,
                    top_number: tournamentB.top
                },
                textareas: {description: tournamentB.description},
                selects: {
                    location_country: tournamentB.country_id,
                    tournament_type_id: tournamentB.type_id,
                    cardpool_id: tournamentB.cardpool_id
                },
                checkboxes: {decklist: false, concluded: true, display_map: false},
                not_visible: ['location_us_state', 'map']
            })
            .fillTournament({inputs: {date: tournamentB.date}})
            .log('*** Saving ***')
            .click("//input[@type='submit']")

            // verify on My tournaments
            .log('*** Verifying on my tournaments table ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .assert.assertTournamentTable('created', tournamentB.title, {
                texts: [tournamentB.date, tournamentB.cardpool, tournamentB.players],
                labels: ['pending', 'concluded']
            })

            // verify tournament details view
            .log('*** Verifying on tournament details view ***')
            .selectTournament('created', tournamentB.title, 'view')
            .assert.assertTournamentView({
                title: tournamentB.title, ttype: tournamentB.type,
                description: tournamentB.description, date: tournamentB.date, time: tournamentB.time,
                country: tournamentB.country, city: tournamentB.city,
                store: tournamentB.store, address: tournamentB.address, map: false
            })

            // delete
            .log('*** Deleting ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .selectTournament('created', tournamentB.title, 'delete')

            .end();
    },

    'Tournament B - create, edit, view with validation' : function (browser) {

        var regularLogin = browser.globals.regularLogin;

        browser
            .url(browser.launchUrl)
            .log('*** Logging in ***')
            .login(regularLogin.username, regularLogin.password)

            // create tournament
            .log('*** Creating Tournament B ***')
            .click("//a[contains(text(),'Create')]")
            .waitForElementVisible('//body', 3000)
            .fillTournament({
                inputs: {
                    title: tournamentB.title, date: tournamentA.wrongdate, start_time: tournamentB.time, // wrong date
                    location_city: tournamentB.city, location_store: tournamentB.store,
                    location_address: tournamentB.address
                },
                textareas: {description: tournamentB.description},
                selects: {
                    location_country: tournamentB.country,
                    tournament_type_id: tournamentB.type,
                    cardpool_id: tournamentB.cardpool
                },
                checkboxes: {concluded: true}
            })
            .fillTournament({inputs: {players_number: tournamentB.players, top_number: tournamentB.top}})
            .assert.assertTournamentForm({not_visible: ['location_us_state', 'map']})
            .click("//input[@type='submit']")

            // data check after validation
            .log('*** Data check after form validation fails ***')
            .waitForElementVisible("//li[contains(text(), 'YYYY.MM.DD.')]", 1000)
            .assert.assertTournamentForm({
                inputs: {
                    title: tournamentB.title,
                    date: tournamentA.wrongdate,
                    start_time: tournamentB.time,
                    location_city: tournamentB.city,
                    location_store: tournamentB.store,
                    location_address: tournamentB.address,
                    players_number: tournamentB.players,
                    top_number: tournamentB.top
                },
                textareas: {description: tournamentB.description},
                selects: {
                    location_country: tournamentB.country_id,
                    tournament_type_id: tournamentB.type_id,
                    cardpool_id: tournamentB.cardpool_id
                },
                checkboxes: {decklist: false, concluded: true, display_map: false},
                not_visible: ['location_us_state', 'map']
            })
            .fillTournament({inputs: {date: tournamentB.date}})
            .log('*** Saving ***')
            .click("//input[@type='submit']")

            // verify on My tournaments
            .log('*** Verifying on my tournaments table ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .assert.assertTournamentTable('created', tournamentB.title, {
                texts: [tournamentB.date, tournamentB.cardpool, tournamentB.players],
                labels: ['pending', 'concluded']
            })

            // verify tournament details view
            .log('*** Verifying on tournament details view ***')
            .selectTournament('created', tournamentB.title, 'view')
            .assert.assertTournamentView({
                title: tournamentB.title, ttype: tournamentB.type,
                description: tournamentB.description, date: tournamentB.date, time: tournamentB.time,
                country: tournamentB.country, city: tournamentB.city,
                store: tournamentB.store, address: tournamentB.address, map: false
            })

            // edit tournament
            .log('*** Verifying data on edit form ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .selectTournament('created', tournamentB.title, 'edit')
            .assert.assertTournamentForm({
                inputs: {
                    title: tournamentB.title,
                    date: tournamentB.date,
                    start_time: tournamentB.time,
                    location_city: tournamentB.city,
                    location_store: tournamentB.store,
                    location_address: tournamentB.address,
                    players_number: tournamentB.players,
                    top_number: tournamentB.top
                },
                textareas: {description: tournamentB.description},
                selects: {
                    location_country: tournamentB.country_id,
                    tournament_type_id: tournamentB.type_id,
                    cardpool_id: tournamentB.cardpool_id
                },
                checkboxes: {decklist: false, concluded: true, display_map: false},
                not_visible: ['location_us_state', 'map']
            })

            .log('*** Editing Tournament B to A ***')
            .fillTournament({ // with wrong date
                inputs: {
                    title: tournamentA.title,
                    date: tournamentA.wrongdate,
                    start_time: tournamentA.time,
                    location_city: tournamentA.city,
                    location_store: tournamentA.store,
                    location_address: tournamentA.address
                },
                textareas: {description: tournamentA.description},
                selects: {
                    location_country: tournamentA.country,
                    tournament_type_id: tournamentA.type,
                    cardpool_id: tournamentA.cardpool
                },
                checkboxes: {decklist: true, concluded: false, display_map: true}
            })
            .assert.assertTournamentForm({not_visible: ['players_number'], visible: ['location_us_state', 'map']})
            .fillTournament({selects: {location_us_state: tournamentA.state}})
            .pause(1000)// gotta have this
            .click("//input[@type='submit']")

            // data check after validation
            .log('*** Data check after form validation fails ***')
            .waitForElementVisible("//li[contains(text(), 'YYYY.MM.DD.')]", 1000)
            .assert.assertTournamentForm({
                inputs: {
                    title: tournamentA.title,
                    date: tournamentA.wrongdate,
                    start_time: tournamentA.time,
                    location_city: tournamentA.city,
                    location_store: tournamentA.store,
                    location_address: tournamentA.address
                },
                textareas: {description: tournamentA.description},
                selects: {
                    location_country: tournamentA.country_id, tournament_type_id: tournamentA.type_id,
                    cardpool_id: tournamentA.cardpool_id, location_us_state: tournamentA.state_id
                },
                checkboxes: {decklist: true, concluded: false, display_map: true},
                not_visible: ['players_number'], visible: ['location_us_state', 'map']
            })
            .fillTournament({inputs: {date: tournamentA.date}})
            .log('*** Saving ***')
            .click("//input[@type='submit']")

            // verify on My tournaments
            .log('*** Verifying on tournament details view ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .assert.assertTournamentTable('created', tournamentA.title, {
                texts: [tournamentA.date, tournamentA.cardpool],
                labels: ['pending', 'not yet'], texts_missing: [tournamentB.players]
            })

            // verify tournament details view
            .log('*** Verifying on tournament details view ***')
            .selectTournament('created', tournamentA.title, 'view')
            .assert.assertTournamentView({
                title: tournamentA.title, ttype: tournamentA.type,
                description: tournamentA.description, date: tournamentA.date, time: tournamentA.time,
                country: tournamentA.country, state: tournamentA.state, city: tournamentA.city,
                store: tournamentA.store, address: tournamentA.address, map: true
            })

            // delete
            .log('*** Deleting ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .selectTournament('created', tournamentA.title, 'delete')
    },
    'Tournament C online - create, edit, view with validation' : function (browser) {

        var regularLogin = browser.globals.regularLogin;

        browser
            .url(browser.launchUrl)
            .log('*** Logging in ***')
            .login(regularLogin.username, regularLogin.password)

            // create tournament
            .log('*** Creating Tournament C ***')
            .click("//a[contains(text(),'Create')]")
            .waitForElementVisible('//body', 3000)
            .fillTournament({
                inputs: {
                    title: tournamentC.title, date: tournamentA.wrongdate, // wrong date
                    location_city: tournamentC.city, location_store: tournamentC.store,
                    location_address: tournamentC.address
                },
                selects: {
                    location_country: tournamentC.country,
                    location_us_state: tournamentC.state
                },
                checkboxes: {display_map: true}
            })
            .assert.assertTournamentForm({visible: ['location','location_us_state', 'map']})
            .fillTournament({selects: {tournament_type_id: tournamentC.type}})
            .assert.assertTournamentForm({not_visible: ['location']})
            .click("//input[@type='submit']")

            // data check after validation
            .log('*** Data check after form validation fails ***')
            .waitForElementVisible("//li[contains(text(), 'YYYY.MM.DD.')]", 1000)
            .assert.assertTournamentForm({
                inputs: {
                    title: tournamentC.title,
                    date: tournamentA.wrongdate
                },
                selects: {
                    tournament_type_id: tournamentC.type_id
                },
                not_visible: ['location', 'map']
            })
            .fillTournament({inputs: {date: tournamentC.date}})
            .log('*** Saving ***')
            .click("//input[@type='submit']")

            // verify on My tournaments
            .log('*** Verifying on my tournaments table ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .assert.assertTournamentTable('created', tournamentC.title, {
                texts: [tournamentC.date],
                labels: ['pending', 'due']
            })

            // verify tournament details view
            .log('*** Verifying on tournament details view ***')
            .selectTournament('created', tournamentB.title, 'view')
            .assert.assertTournamentView({
                title: tournamentC.title, ttype: tournamentC.type, date: tournamentC.date, map: false
            })

            // edit tournament
            .log('*** Verifying data on edit form ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .selectTournament('created', tournamentB.title, 'edit')
            .assert.assertTournamentForm({
                inputs: {
                    title: tournamentC.title,
                    date: tournamentC.date
                },
                selects: {
                    tournament_type_id: tournamentC.type_id
                },
                not_visible: ['location', 'map']
            })

            .log('*** Editing Tournament C to A ***')
            .fillTournament({ // with wrong date
                inputs: {
                    title: tournamentA.title,
                    date: tournamentA.wrongdate,
                    start_time: tournamentA.time
                },
                textareas: {description: tournamentA.description},
                selects: {
                    tournament_type_id: tournamentA.type,
                    cardpool_id: tournamentA.cardpool
                },
                checkboxes: {display_map: true}
            })
            .fillTournament({
                inputs: {
                    location_city: tournamentA.city,
                    location_store: tournamentA.store,
                    location_address: tournamentA.address
                },
                selects: {
                    location_country: tournamentA.country,
                }
            })
            .assert.assertTournamentForm({visible: ['location_us_state', 'map']})
            .fillTournament({selects: {location_us_state: tournamentA.state}})
            .pause(1000)// gotta have this
            .click("//input[@type='submit']")

            // data check after validation
            .log('*** Data check after form validation fails ***')
            .waitForElementVisible("//li[contains(text(), 'YYYY.MM.DD.')]", 1000)
            .assert.assertTournamentForm({
                inputs: {
                    title: tournamentA.title,
                    date: tournamentA.wrongdate,
                    start_time: tournamentA.time,
                    location_city: tournamentA.city,
                    location_store: tournamentA.store,
                    location_address: tournamentA.address
                },
                textareas: {description: tournamentA.description},
                selects: {
                    location_country: tournamentA.country_id, tournament_type_id: tournamentA.type_id,
                    cardpool_id: tournamentA.cardpool_id, location_us_state: tournamentA.state_id
                },
                checkboxes: {display_map: true},
                not_visible: ['players_number'], visible: ['location_us_state', 'map']
            })
            .fillTournament({inputs: {date: tournamentA.date}})
            .log('*** Saving ***')
            .click("//input[@type='submit']")

            // verify on My tournaments
            .log('*** Verifying on tournament details view ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .assert.assertTournamentTable('created', tournamentA.title, {
                texts: [tournamentA.date, tournamentA.cardpool],
                labels: ['pending', 'not yet'], texts_missing: [tournamentB.players]
            })

            // verify tournament details view
            .log('*** Verifying on tournament details view ***')
            .selectTournament('created', tournamentA.title, 'view')
            .assert.assertTournamentView({
                title: tournamentA.title, ttype: tournamentA.type,
                description: tournamentA.description, date: tournamentA.date, time: tournamentA.time,
                country: tournamentA.country, state: tournamentA.state, city: tournamentA.city,
                store: tournamentA.store, address: tournamentA.address, map: true
            })

            // delete
            .log('*** Deleting ***')
            .click("//a[contains(text(),'My Tournaments')]")
            .selectTournament('created', tournamentA.title, 'delete')
    }
};