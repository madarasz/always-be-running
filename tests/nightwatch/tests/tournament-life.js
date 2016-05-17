var title = 'Tournament A',
    type = 'casual',
    type_id = '5',
    description = 'description A',
    players = '20',
    top = '4',
    date = '2999.01.01.',
    time = '12:40',
    country = 'United States',
    country_id = '840',
    state = 'California',
    state_id = '5',
    city = 'Budapest',
    store = 'Superstore',
    address = 'Sehol utca 4.',
    title2 = 'Tournament B',
    type2 = 'online event',
    type_id2 = '6',
    description2 = 'description B',
    date2 = '2003.01.01.',
    time2 = '10:00',
    country2 = 'Hungary',
    country_id2 = '348',
    city2 = 'Miskolc',
    store2 = 'Telep',
    address2 = 'Nincs utca 5.',
    wrongdate = '444';
module.exports = {
    'Tournament A create' : function (browser) {
        browser
            .url(browser.launchUrl)
            .useXpath()
            .waitForElementVisible('//body', 3000)
            .windowMaximize('current')
            // create tournament
            .click("//a[contains(text(),'Create')]")
            .waitForElementVisible('//body', 3000)
            .waitForElementNotVisible("//select[@id='location_us_state']", 1000)
            .waitForElementNotVisible("//input[@id='players_number']", 1000)
            .fillTournament({inputs: {title: title, date: date, start_time: time}, textareas:{description: description},
                selects:{location_country: country, tournament_type_id: type}, checkboxes:{decklist: true, concluded: true}})
            .waitForElementVisible("//input[@id='players_number']", 1000)
            .fillTournament({inputs: {players_number: players, top_number: top}, checkboxes:{concluded: false}})
            .waitForElementNotVisible("//input[@id='players_number']", 1000)
            .waitForElementVisible("//select[@id='location_us_state']", 1000)
            .fillTournament({selects: {location_us_state: state},
                    inputs: {location_city: city, location_store: store, location_address: address}})
            .click("//input[@type='submit']")
            // verify on My tournaments
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//td[contains(text(), '" + title + "')]", 1000)
            .waitForElementVisible("//td[contains(text(), '" + date + "')]", 1000)
            .waitForElementVisible("//span[contains(text(), 'pending')]", 1000)
            .waitForElementVisible("//span[contains(text(), 'not yet')]", 1000)
            .assert.elementNotPresent("//td[contains(text(), '" + players + "')]")
            .assert.elementNotPresent("//td[contains(text(), '" + top + "')]")
            // verify on View tournament
            .click("//a[contains(text(), 'view')]")
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//h3[contains(text(), '" + title + "')]", 1000)
            // TODO other stuff
            .end();
    },
    'Tournament A edit' : function (browser) {
        browser
            .url(browser.launchUrl)
            .useXpath()
            .waitForElementVisible('//body', 3000)
            .windowMaximize('current')
            .click("//a[contains(text(),'My Tournaments')]")
            .waitForElementVisible('//body', 3000)
            .click("//a[contains(text(),'edit')]")
            // edit tournament - verify values
            .assert.value("//input[@id='title']", title)
            .assert.value("//select[@id='tournament_type_id']", type_id)
            .assert.value("//textarea[@id='description']", description)
            .useCss()
            .assert.elementPresent("input:checked[name='decklist']")
            .assert.elementNotPresent("input:checked[name='concluded']")
            .useXpath()
            .waitForElementNotVisible("//input[@id='players_number']", 1000)
            .assert.value("//input[@id='date']", date)
            .assert.value("//input[@id='start_time']", time)
            .assert.value("//select[@id='location_country']", country_id)
            .waitForElementVisible("//select[@id='location_us_state']", 1000)
            .assert.value("//select[@id='location_us_state']", state_id)
            .assert.value("//input[@id='location_city']", city)
            .assert.value("//input[@id='location_store']", store)
            .assert.value("//input[@id='location_address']", address)
            // modify values
            .fillTournament({inputs: {title: title2, date: date2, start_time: time2, location_city: city2, location_store: store2, location_address: address2}, textareas:{description: description2},
                selects:{location_country: country2, tournament_type_id: type2}, checkboxes:{decklist: false, concluded: true}})
            .waitForElementVisible("//input[@id='players_number']", 1000)
            .assert.value("//input[@id='players_number']", '')
            .assert.value("//input[@id='top_number']", '')
            .fillTournament({inputs: {players_number: players, top_number: top}})
            .waitForElementNotVisible("//select[@id='location_us_state']", 1000)
            .click("//input[@type='submit']")
            // verify on My tournaments
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//td[contains(text(), '" + title2 + "')]", 1000)
            .waitForElementVisible("//td[contains(text(), '" + date2 + "')]", 1000)
            .waitForElementVisible("//span[contains(text(), 'pending')]", 1000)
            .waitForElementVisible("//span[contains(text(), 'concluded')]", 1000)
            .waitForElementVisible("//td[contains(text(), '" + players + "')]", 1000)
            // verify on View tournament
            .click("//a[contains(text(), 'view')]")
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//h3[contains(text(), '" + title2 + "')]", 1000)
            // TODO other stuff
            .end();
    },
    'Tournament A delete' : function (browser) {
        browser
            .deleteTournament(title2)
            .end();
    },
    'Tournament B create' : function (browser) {
        browser
            .url(browser.launchUrl)
            .useXpath()
            .waitForElementVisible('//body', 3000)
            .windowMaximize('current')
            // create tournament
            .click("//a[contains(text(),'Create')]")
            .waitForElementVisible('//body', 3000)
            .fillTournament({inputs: {title: title2, date: date2, start_time: time2, location_city: city2, location_store: store2, location_address: address2}, textareas:{description: description2},
                selects:{location_country: country2, tournament_type_id: type2}, checkboxes:{concluded: true}})
            .fillTournament({inputs: {players_number: players, top_number: top}})
            .waitForElementNotVisible("//select[@id='location_us_state']", 1000)
            .click("//input[@type='submit']")
            // verify on My tournaments
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//td[contains(text(), '" + title2 + "')]", 1000)
            .waitForElementVisible("//td[contains(text(), '" + date2 + "')]", 1000)
            .waitForElementVisible("//span[contains(text(), 'pending')]", 1000)
            .waitForElementVisible("//span[contains(text(), 'concluded')]", 1000)
            .waitForElementVisible("//td[contains(text(), '" + players + "')]", 1000)
            // verify on View tournament
            .click("//a[contains(text(), 'view')]")
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//h3[contains(text(), '" + title2 + "')]", 1000)
            // TODO other stuff
            .end();
    },
    'Tournament B edit' : function (browser) {
        browser
            .url(browser.launchUrl)
            .useXpath()
            .waitForElementVisible('//body', 3000)
            .windowMaximize('current')
            .click("//a[contains(text(),'My Tournaments')]")
            .waitForElementVisible('//body', 3000)
            .click("//a[contains(text(),'edit')]")
            // edit tournament - verify values
            .assert.value("//input[@id='title']", title2)
            .assert.value("//select[@id='tournament_type_id']", type_id2)
            .assert.value("//textarea[@id='description']", description2)
            .useCss()
            .assert.elementPresent("input:checked[name='concluded']")
            .assert.elementNotPresent("input:checked[name='decklist']")
            .useXpath()
            .waitForElementVisible("//input[@id='players_number']", 1000)
            .waitForElementVisible("//input[@id='top_number']", 1000)
            .assert.value("//input[@id='players_number']", players)
            .assert.value("//input[@id='top_number']", top)
            .assert.value("//input[@id='date']", date2)
            .assert.value("//input[@id='start_time']", time2)
            .assert.value("//select[@id='location_country']", country_id2)
            .waitForElementNotVisible("//select[@id='location_us_state']", 1000)
            .assert.value("//input[@id='location_city']", city2)
            .assert.value("//input[@id='location_store']", store2)
            .assert.value("//input[@id='location_address']", address2)
            // modify values
            .fillTournament({inputs: {title: title, date: date, start_time: time, location_city: city, location_store: store, location_address: address}, textareas:{description: description},
                selects:{location_country: country, location_us_state: state, tournament_type_id: type}, checkboxes:{decklist: true, concluded: false}})
            .waitForElementNotVisible("//input[@id='players_number']", 1000)
            .waitForElementVisible("//select[@id='location_us_state']", 1000)
            .click("//input[@type='submit']")
            // verify on My tournaments
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//td[contains(text(), '" + title + "')]", 1000)
            .waitForElementVisible("//td[contains(text(), '" + date + "')]", 1000)
            .waitForElementVisible("//span[contains(text(), 'pending')]", 1000)
            .waitForElementVisible("//span[contains(text(), 'not yet')]", 1000)
            .assert.elementNotPresent("//td[contains(text(), '" + players + "')]")
            // verify on View tournament
            .click("//a[contains(text(), 'view')]")
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//h3[contains(text(), '" + title + "')]", 1000)
            // TODO other stuff
            .end();
    },
    'Tournament B delete' : function (browser) {
        browser
            .deleteTournament(title)
            .end();
    }
};