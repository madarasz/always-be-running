var menuCommands = {
    selectMenu: function(item, client) {
        this.click("@" + item);

        if (typeof callback === "function") {
            callback.call(client);
        }

        return this;
    },

    validateMenu: function(item, client) {
        this.log('*** Validating existence of menu item: ' + item + ' ***');

        this.waitForElementVisible('@' + item, 10000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },

    acceptCookies: function(client) {
        this.log('*** Accepting cookies ***');

        this
            .waitForElementVisible('@acceptCookies', 10000)
            .click('@acceptCookies');

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    }
};

module.exports = {
    commands: [menuCommands],
    elements: {
        organize: {
            selector: "//a[contains(text(),'Organize')]",
            locateStrategy: 'xpath'
        },
        results: {
            selector: "//a[contains(text(),'Results')]",
            locateStrategy: 'xpath'
        },
        upcoming: {
            selector: "//a[contains(text(),'Upcoming')]",
            locateStrategy: 'xpath'
        },
        admin: {
            selector: "//a[contains(text(),'Admin')]",
            locateStrategy: 'xpath'
        },
        videos: {
            selector: "//a[contains(text(),'Videos')]",
            locateStrategy: 'xpath'
        },
        personal: {
            selector: "//a[contains(text(),'Personal')]",
            locateStrategy: 'xpath'
        },
        profile: {
            selector: "//a[contains(text(),'Profile')]",
            locateStrategy: 'xpath'
        },
        login: {
            selector: "//a[contains(text(),'Login via NetrunnerDB')]",
            locateStrategy: 'xpath'
        },
        logout: {
            selector: "//a[@id='button-logout']",
            locateStrategy: 'xpath'
        },
        acceptCookies: {
            selector: "//a[@class='cc_btn cc_btn_accept_all']",
            locateStrategy: 'xpath'
        }
    }
};
