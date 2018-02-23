var netrunnerDBCommands = {
    validate: function(client) {

        this.log('*** Validating NetrunnerDB page ***');
        this.waitForElementVisible('@validator', 3000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },
    loginNetrunnerDB: function(login, client) {
        var browser = this;

        browser.log('*** Logging into NetunnerDB.com with user: '+login.username+' ***');

        browser.click('@menuUser');

        // logout if needed
        browser.api.element('Xpath', this.elements.menuLogout.selector, function(result) {
            if (result.value && result.value.ELEMENT) {
                browser.click('@menuLogout');
                browser.waitForElementVisible('@validator', 3000);
                browser.click('@menuUser');
            }
        });

        // click login
        browser
            .click('@menuLogin')
            .waitForElementVisible('@validator', 3000);

        // clear cache if user is remembered
        //browser.api.element('Xpath', this.elements.authorization.selector, function(result) {
        //    if (result.value && result.value.ELEMENT) {
        //        browser.deleteCookies().refresh();
        //    }
        //});

        // provide login details, login
        browser
            .clearValue("@fieldUsername")
            .setValue("@fieldUsername", login.username)
            .clearValue("@fieldPassword")
            .setValue("@fieldPassword", login.password)
            .click("@buttonSubmit")
            .waitForElementVisible('@validator', 3000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },
    goToMyDeckLists:function(client) {
        this.log('--- Going to My Decklists ---');
        this
            .click('@menuDecklists')
            .waitForElementVisible('@validator', 3000)
            .click('@submenuMyDecklists');

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },
    isDecklistExists:function(decklistTitle, callback) {
        var browser = this,
            util = require('util');
        browser.goToMyDeckLists();
        browser.api.element('Xpath', util.format(this.elements.deckListLink.selector, decklistTitle), function(result) {
            if (result.value && result.value.ELEMENT) {
                // if deck exists execute callback
                browser.log('--- Decklist found: '+decklistTitle+' ---');
                if (typeof callback === "function"){
                    callback.call();
                    // recursively call itself to delete multiple instances
                    browser.isDecklistExists(decklistTitle, callback);
                }
            } else {
                browser.log('--- Decklist not found: '+decklistTitle+' ---');
            }
        });
    },
    assertDeckExist:function(decklistTitle, client) {
        var util = require('util');

        this.log('*** Verifying existence of published decklist: '+decklistTitle+' ***');
        this.goToMyDeckLists();
        this.api.useXpath().waitForElementVisible(util.format(this.elements.deckListLink.selector, decklistTitle) ,3000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },
    deleteDecklist:function(decklistTitle) {
        this.log('*** Deleting all decklists named: '+decklistTitle+' ***');

        var browser = this,
            util = require('util');

        browser.isDecklistExists(decklistTitle, function(){
            browser.log('--- Deleting decklist ---');
            browser
                .api.useXpath()
                .click(util.format(browser.elements.deckListLink.selector, decklistTitle));
            browser
                .waitForElementVisible('@validator', 3000)
                .click('@buttonDeleteDecklist')
                .waitForElementVisible('@buttonSubmitDelete', 3000)
                .click('@buttonSubmitDelete');
        });

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    }
};

module.exports = {
    commands: [netrunnerDBCommands],
    elements: {
        deckListLink: "//div[contains(@class,'col-sm-9')]/a[contains(.,'%s')]",
        validator: {
            selector: "//a[@class='navbar-brand' and contains(.,'NetrunnerDB')]",
            locateStrategy: 'xpath'
        },
        menuUser : {
            selector: "//li[@id='login']/a",
            locateStrategy: 'xpath'
        },
        menuLogout: {
            selector: "//a[contains(.,'Jack out')]",
            locateStrategy: 'xpath'
        },
        menuLogin: {
            selector: "//a[contains(.,'Login or Register')]",
            locateStrategy: 'xpath'
        },
        menuDecklists: {
            selector: "//ul[contains(@class,'nav')]/li/a[contains(.,'Decklists')]",
            locateStrategy: 'xpath'
        },
        submenuMyDecklists: {
            selector: "//a[contains(.,'My decklists')]",
            locateStrategy: 'xpath'
        },
        authorization: {
            selector: "//h3[contains(., 'NetrunnerDB Authorization')]",
            locateStrategy: 'xpath'
        },
        fieldUsername: {
            selector: "//input[@id='username']",
            locateStrategy: 'xpath'
        },
        fieldPassword: {
            selector: "//input[@id='password']",
            locateStrategy: 'xpath'
        },
        buttonSubmit: {
            selector: "//input[@type='submit']",
            locateStrategy: 'xpath'
        },
        buttonAccept: {
            selector: "//input[@name='accepted']",
            locateStrategy: 'xpath'
        },
        buttonDeleteDecklist: {
            selector: "//a[@id='decklist-delete']/span",
            locateStrategy: 'xpath'
        },
        buttonSubmitDelete: {
            selector: "//button[@id='btn-delete-submit']",
            locateStrategy: 'xpath'
        }
    }
};
