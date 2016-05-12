var title = 'Tournament A',
    type = 'casual',
    type_id = '5',
    description = 'description A',
    players = 20,
    top = 4,
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
            .setValue("//input[@id='title']", title)
            .click("//select[@id='tournament_type_id']")
            .setValue("//select[@id='tournament_type_id']", type)
            .keys(['\uE006'])
            .click("//input[@name='decklist']")
            .setValue("//textarea[@id='description']", description)
            .waitForElementNotVisible("//input[@id='players_number']", 1000)
            .click("//input[@name='concluded']")
            .waitForElementVisible("//input[@id='players_number']", 1000)
            .setValue("//input[@id='players_number']", players)
            .setValue("//input[@id='top_number']", top)
            .click("//input[@name='concluded']")
            .waitForElementNotVisible("//input[@id='players_number']", 1000)
            .setValue("//input[@id='date']", date)
            .setValue("//input[@id='start_time']", time)
            .waitForElementNotVisible("//select[@id='location_us_state']", 1000)
            .click("//select[@id='location_country']")
            .setValue("//select[@id='location_country']", country)
            .keys(['\uE006'])
            .waitForElementVisible("//select[@id='location_us_state']", 1000)
            .click("//select[@id='location_us_state']")
            .setValue("//select[@id='location_us_state']", state)
            .keys(['\uE006'])
            .setValue("//input[@id='location_city']", city)
            .setValue("//input[@id='location_store']", store)
            .setValue("//input[@id='location_address']", address)
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
            .clearValue("//input[@id='title']")
            .setValue("//input[@id='title']", title2)
            .click("//select[@id='tournament_type_id']")
            .setValue("//select[@id='tournament_type_id']", type2)
            .keys(['\uE006'])
            .click("//input[@name='decklist']")
            .clearValue("//textarea[@id='description']")
            .setValue("//textarea[@id='description']", description2)
            .click("//input[@name='concluded']")
            .waitForElementVisible("//input[@id='players_number']", 1000)
            .assert.value("//input[@id='players_number']", '')
            .assert.value("//input[@id='top_number']", '')
            .setValue("//input[@id='players_number']", players)
            .setValue("//input[@id='top_number']", top)
            .clearValue("//input[@id='date']")
            .setValue("//input[@id='date']", date2)
            .clearValue("//input[@id='start_time']")
            .setValue("//input[@id='start_time']", time2)
            .click("//select[@id='location_country']")
            .setValue("//select[@id='location_country']", country2)
            .keys(['\uE006'])
            .waitForElementNotVisible("//select[@id='location_us_state']", 1000)
            .clearValue("//input[@id='location_city']")
            .setValue("//input[@id='location_city']", city2)
            .clearValue("//input[@id='location_store']")
            .setValue("//input[@id='location_store']", store2)
            .clearValue("//input[@id='location_address']")
            .setValue("//input[@id='location_address']", address2)
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
            .url(browser.launchUrl)
            .useXpath()
            .waitForElementVisible('//body', 3000)
            .windowMaximize('current')
            .click("//a[contains(text(),'My Tournaments')]")
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//td[contains(text(), '" + title2 + "')]", 1000)
            .click("//input[@value='delete']")
            .assert.elementNotPresent("//td[contains(text(), '" + title2 + "')]")
            .end();
    }
};