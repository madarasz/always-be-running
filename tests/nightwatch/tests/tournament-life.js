var tournamentA = {
    title: 'Tournament A - ' + formatDate(new Date()),
    type: 'casual',
    type_id: '5',
    cardpool: 'Business First',
    cardpool_id: 'bf',
    description: 'description A',
    players: '222',
    top: '44',
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
    title: 'Tournament B - ' + formatDate(new Date()),
    type: 'worlds championship',
    type_id: '4',
    cardpool: 'True Colors',
    cardpool_id: 'tc',
    description: 'description B',
    date: '2003.01.01.',
    time: '10:00',
    country: 'Hungary',
    country_id: '348',
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
    'Tournament A create' : function (browser) {

        var regularLogin = browser.globals.regularLogin;

        browser
            .url(browser.launchUrl)
            .login(regularLogin.username, regularLogin.password)
            .waitForElementVisible('//body', 3000)
            .windowMaximize('current')

            // create tournament
            .click("//a[contains(text(),'Create')]")
            .assert.assertTournamentForm({not_visible:['location_us_state', 'players_number', 'map']})
            .fillTournament({
                inputs: {title: tournamentA.title, date: tournamentA.date, start_time: tournamentA.time},
                textareas:{description: tournamentA.description},
                selects:{location_country: tournamentA.country, tournament_type_id: tournamentA.type, cardpool_id: tournamentA.cardpool},
                checkboxes:{decklist: true, concluded: true, display_map: true}})
            .assert.assertTournamentForm({visible:['players_number', 'map']})
            .fillTournament({inputs: {players_number: tournamentA.players, top_number: tournamentA.top}, checkboxes:{concluded: false}})
            .assert.assertTournamentForm({not_visible:['players_number'], visible:['location_us_state']})
            .fillTournament({
                selects: {location_us_state: tournamentA.state},
                inputs: {location_city: tournamentA.city, location_store: tournamentA.store, location_address: tournamentA.address}})
            .click("//input[@type='submit']")

            // verify on My tournaments
            .assert.assertTournamentTable('created', tournamentA.title, {texts: [tournamentA.date, tournamentA.cardpool],
                labels: ['pending', 'not yet'], texts_missing: [tournamentA.players]})

            // verify tournament details view
            .selectTournament('created', tournamentA.title, 'view')
            .assert.assertTournamentView({
                title: tournamentA.title, ttype: tournamentA.type,
                description: tournamentA.description, date: tournamentA.date, time: tournamentA.time,
                country: tournamentA.country, state: tournamentA.state, city: tournamentA.city,
                store: tournamentA.store, address: tournamentA.address, map: true})

            // edit tournament
            .selectTournament('created', tournamentA.title, 'edit')
            .assert.assertTournamentForm({
                inputs: {title: tournamentA.title, date: tournamentA.date, start_time: tournamentA.time,
                    location_city: tournamentA.city, location_store: tournamentA.store, location_address: tournamentA.address},
                textareas: {description: tournamentA.description},
                selects: {location_country: tournamentA.country_id, tournament_type_id: tournamentA.type_id,
                    cardpool_id: tournamentA.cardpool_id, location_us_state: tournamentA.state_id},
                checkboxes: {decklist: true, concluded: false, display_map: true},
                not_visible:['players_number'], visible:['location_us_state', 'map']})

            // modify values
            .fillTournament({
                inputs: {title: tournamentB.title, date: tournamentB.date, start_time: tournamentB.time,
                    location_city: tournamentB.city, location_store: tournamentB.store, location_address: tournamentB.address},
                textareas: {description: tournamentB.description},
                selects: {location_country: tournamentB.country, tournament_type_id: tournamentB.type, cardpool_id: tournamentB.cardpool},
                checkboxes: {decklist: false, concluded: true, display_map: false}})
            .assert.assertTournamentForm({
                inputs: {players_number: '', top_number: ''},
                not_visible:['location_us_state', 'map']
            })
            .fillTournament({inputs: {players_number: tournamentA.players, top_number: tournamentA.top}})
            .click("//input[@type='submit']")

            // verify on My tournaments
            .assert.assertTournamentTable('created', tournamentB.title, {
                texts: [tournamentB.date, tournamentB.cardpool, tournamentA.players],
                labels: ['pending', 'concluded']})

            // verify tournament details view
            .selectTournament('created', tournamentB.title, 'view')
            .assert.assertTournamentView({
                title: tournamentB.title, ttype: tournamentB.type,
                description: tournamentB.description, date: tournamentB.date, time: tournamentB.time,
                country: tournamentB.country, city: tournamentB.city,
                store: tournamentB.store, address: tournamentB.address, map: false})

            // delete
            .selectTournament('created', tournamentB.title, 'delete')

            .end();
    },
    //'Tournament A edit' : function (browser) {
    //    browser
    //        .url(browser.launchUrl)
    //        .useXpath()
    //        .waitForElementVisible('//body', 3000)
    //        .windowMaximize('current')
    //        .click("//a[contains(text(),'My Tournaments')]")
    //        .waitForElementVisible('//body', 3000)
    //        .click("//a[contains(text(),'edit')]")
    //        // edit tournament - verify values
    //        .assert.value("//input[@id='title']", title)
    //        .assert.value("//select[@id='tournament_type_id']", type_id)
    //        .assert.value("//textarea[@id='description']", description)
    //        .useCss()
    //        .assert.elementPresent("input:checked[name='decklist']")
    //        .assert.elementNotPresent("input:checked[name='concluded']")
    //        .useXpath()
    //        .waitForElementNotVisible("//input[@id='players_number']", 1000)
    //        .assert.value("//input[@id='date']", date)
    //        .assert.value("//input[@id='start_time']", time)
    //        .assert.value("//select[@id='location_country']", country_id)
    //        .waitForElementVisible("//select[@id='location_us_state']", 1000)
    //        .assert.value("//select[@id='location_us_state']", state_id)
    //        .assert.value("//input[@id='location_city']", city)
    //        .assert.value("//input[@id='location_store']", store)
    //        .assert.value("//input[@id='location_address']", address)
    //        // modify values
    //        .fillTournament({inputs: {title: title2, date: date2, start_time: time2, location_city: city2, location_store: store2, location_address: address2}, textareas:{description: description2},
    //            selects:{location_country: country2, tournament_type_id: type2}, checkboxes:{decklist: false, concluded: true}})
    //        .waitForElementVisible("//input[@id='players_number']", 1000)
    //        .assert.value("//input[@id='players_number']", '')
    //        .assert.value("//input[@id='top_number']", '')
    //        .fillTournament({inputs: {players_number: players, top_number: top}})
    //        .waitForElementNotVisible("//select[@id='location_us_state']", 1000)
    //        .click("//input[@type='submit']")
    //        // verify on My tournaments
    //        .waitForElementVisible('//body', 3000)
    //        .waitForElementVisible("//td[contains(text(), '" + title2 + "')]", 1000)
    //        .waitForElementVisible("//td[contains(text(), '" + date2 + "')]", 1000)
    //        .waitForElementVisible("//span[contains(text(), 'pending')]", 1000)
    //        .waitForElementVisible("//span[contains(text(), 'concluded')]", 1000)
    //        .waitForElementVisible("//td[contains(text(), '" + players + "')]", 1000)
    //        // verify on View tournament
    //        .click("//a[contains(text(), 'view')]")
    //        .waitForElementVisible('//body', 3000)
    //        .waitForElementVisible("//h3[contains(text(), '" + title2 + "')]", 1000)
    //        // TODO other stuff
    //        .end();
    //},
    //'Tournament A delete' : function (browser) {
    //    browser
    //        .deleteTournament(title2)
    //        .end();
    //},
    //'Tournament B create' : function (browser) {
    //    browser
    //        .url(browser.launchUrl)
    //        .useXpath()
    //        .waitForElementVisible('//body', 3000)
    //        .windowMaximize('current')
    //        // create tournament
    //        .click("//a[contains(text(),'Create')]")
    //        .waitForElementVisible('//body', 3000)
    //        .fillTournament({inputs: {title: title2, date: date2, start_time: time2, location_city: city2, location_store: store2, location_address: address2}, textareas:{description: description2},
    //            selects:{location_country: country2, tournament_type_id: type2}, checkboxes:{concluded: true}})
    //        .fillTournament({inputs: {players_number: players, top_number: top}})
    //        .waitForElementNotVisible("//select[@id='location_us_state']", 1000)
    //        .click("//input[@type='submit']")
    //        // verify on My tournaments
    //        .waitForElementVisible('//body', 3000)
    //        .waitForElementVisible("//td[contains(text(), '" + title2 + "')]", 1000)
    //        .waitForElementVisible("//td[contains(text(), '" + date2 + "')]", 1000)
    //        .waitForElementVisible("//span[contains(text(), 'pending')]", 1000)
    //        .waitForElementVisible("//span[contains(text(), 'concluded')]", 1000)
    //        .waitForElementVisible("//td[contains(text(), '" + players + "')]", 1000)
    //        // verify on View tournament
    //        .click("//a[contains(text(), 'view')]")
    //        .waitForElementVisible('//body', 3000)
    //        .waitForElementVisible("//h3[contains(text(), '" + title2 + "')]", 1000)
    //        // TODO other stuff
    //        .end();
    //},
    //'Tournament B edit' : function (browser) {
    //    browser
    //        .url(browser.launchUrl)
    //        .useXpath()
    //        .waitForElementVisible('//body', 3000)
    //        .windowMaximize('current')
    //        .click("//a[contains(text(),'My Tournaments')]")
    //        .waitForElementVisible('//body', 3000)
    //        .click("//a[contains(text(),'edit')]")
    //        // edit tournament - verify values
    //        .assert.value("//input[@id='title']", title2)
    //        .assert.value("//select[@id='tournament_type_id']", type_id2)
    //        .assert.value("//textarea[@id='description']", description2)
    //        .useCss()
    //        .assert.elementPresent("input:checked[name='concluded']")
    //        .assert.elementNotPresent("input:checked[name='decklist']")
    //        .useXpath()
    //        .waitForElementVisible("//input[@id='players_number']", 1000)
    //        .waitForElementVisible("//input[@id='top_number']", 1000)
    //        .assert.value("//input[@id='players_number']", players)
    //        .assert.value("//input[@id='top_number']", top)
    //        .assert.value("//input[@id='date']", date2)
    //        .assert.value("//input[@id='start_time']", time2)
    //        .assert.value("//select[@id='location_country']", country_id2)
    //        .waitForElementNotVisible("//select[@id='location_us_state']", 1000)
    //        .assert.value("//input[@id='location_city']", city2)
    //        .assert.value("//input[@id='location_store']", store2)
    //        .assert.value("//input[@id='location_address']", address2)
    //        // modify values
    //        .fillTournament({inputs: {title: title, date: date, start_time: time, location_city: city, location_store: store, location_address: address}, textareas:{description: description},
    //            selects:{location_country: country, location_us_state: state, tournament_type_id: type}, checkboxes:{decklist: true, concluded: false}})
    //        .waitForElementNotVisible("//input[@id='players_number']", 1000)
    //        .waitForElementVisible("//select[@id='location_us_state']", 1000)
    //        .click("//input[@type='submit']")
    //        // verify on My tournaments
    //        .waitForElementVisible('//body', 3000)
    //        .waitForElementVisible("//td[contains(text(), '" + title + "')]", 1000)
    //        .waitForElementVisible("//td[contains(text(), '" + date + "')]", 1000)
    //        .waitForElementVisible("//span[contains(text(), 'pending')]", 1000)
    //        .waitForElementVisible("//span[contains(text(), 'not yet')]", 1000)
    //        .assert.elementNotPresent("//td[contains(text(), '" + players + "')]")
    //        // verify on View tournament
    //        .click("//a[contains(text(), 'view')]")
    //        .waitForElementVisible('//body', 3000)
    //        .waitForElementVisible("//h3[contains(text(), '" + title + "')]", 1000)
    //        // TODO other stuff
    //        .end();
    //},
    //'Tournament B delete' : function (browser) {
    //    browser
    //        .deleteTournament(title)
    //        .end();
    //}
};