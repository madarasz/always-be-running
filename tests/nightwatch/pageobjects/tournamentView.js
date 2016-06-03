var tournamentViewCommands = {
    assertView: function(data, client) {
        var util = require('util');

        this.api.useXpath().waitForElementVisible('//body', 3000);

        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                if (data[property] === true) {
                    this.waitForElementVisible('@'+property, 1000);
                } else if (data[property] === false) {
                    this.verify.elementNotPresent('@'+property);
                } else {
                    this.api.useXpath().waitForElementVisible(util.format(this.elements[property].selector, data[property]), 1000);
                }
            }
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this.api;
    }
};


module.exports = {
    commands: [tournamentViewCommands],
    elements: {
        title: "//h3[contains(., '%s')]",
        ttype: "//h3/small[contains(., '%s')]",
        description: "//div[contains(@class, 'panel-body') and contains(., '%s')]",
        date: "//h4[contains(., '%s')]",
        time: "//p[contains(., '%s')]",
        country: "//h4[contains(., '%s')]",
        state: "//h4[contains(., '%s')]",
        city: "//h4[contains(., '%s')]",
        store: "//p[contains(., '%s')]",
        address: "//p[contains(., '%s')]",
        registeredPlayer: "//ul[@id='registered-players']/li[contains(., '%s')]",
        map: {
            selector: "//iframe[@id='map']",
            locateStrategy: 'xpath'
        },
        approvalNeed: {
            selector: "//div[@id='approval-needed']",
            locateStrategy: 'xpath'
        },
        approvalRejected: {
            selector: "//div[@id='approval-rejected']",
            locateStrategy: 'xpath'
        },
        editButton: {
            selector: "//a[@id='edit-button']",
            locateStrategy: 'xpath'
        },
        approveButton: {
            selector: "//a[@id='approve-button']",
            locateStrategy: 'xpath'
        },
        rejectButton: {
            selector: "//a[@id='reject-button']",
            locateStrategy: 'xpath'
        },
        deleteButton: {
            selector: "//button[@id='delete-button']",
            locateStrategy: 'xpath'
        },
        conflictWarning: {
            selector: "//div[@id='conflict-warning']",
            locateStrategy: 'xpath'
        },
        playerNumbers: {
            selector: "//div[@id='player-numbers']",
            locateStrategy: 'xpath'
        },
        topPlayerNumbers: {
            selector: "//span[@id='top-player-numbers']",
            locateStrategy: 'xpath'
        },
        playerClaim: {
            selector: "//ul[@id='player-claim']",
            locateStrategy: 'xpath'
        },
        createClaimFrom: {
            selector: "//form[@id='create-claim']",
            locateStrategy: 'xpath'
        },
        topEntriesTable: {
            selector: "//table[@id='entries-top']",
            locateStrategy: 'xpath'
        },
        swissEntriesTable: {
            selector: "//table[@id='entries-swiss']",
            locateStrategy: 'xpath'
        },
        dueWarning: {
            selector: "//div[@id='due-warning']",
            locateStrategy: 'xpath'
        },
        registeredPlayers: {
            selector: "//ul[@id='registered-players']",
            locateStrategy: 'xpath'
        },
        noRegisteredPlayers: {
            selector: "//em[@id='no-registered-players']",
            locateStrategy: 'xpath'
        },
        unregisterButtonDisabled: {
            selector: "//span[@id='unregister-disabled']",
            locateStrategy: 'xpath'
        },
        unregisterButton: {
            selector: "//a[@id='unregister']",
            locateStrategy: 'xpath'
        },
        registerButton: {
            selector: "//a[@id='register']",
            locateStrategy: 'xpath'
        }
    }
};
