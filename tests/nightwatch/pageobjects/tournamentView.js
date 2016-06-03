var tournamentViewCommands = {
    assertView: function(data, client) {

        this.log('*** Verifying tournament view ***');

        var util = require('util');

        this.api.useXpath().waitForElementVisible('//body', 3000);

        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                if (data[property] === true) {
                    this.verify.elementPresent('@'+property);
                } else if (data[property] === false) {
                    this.verify.elementNotPresent('@'+property);
                } else {
                    this.api.useXpath().verify.elementPresent(util.format(this.elements[property].selector, data[property]));
                }
            }
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this.api;
    },

    claim: function(data, client) {

        this.log('*** Creating claim for tournament ***');

        this.api.useXpath().waitForElementVisible(this.elements.createClaimFrom.selector, 3000);

        // set form
        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                this.api.useXpath().click("//select[@id='" + property + "']")
                    .setValue("//select[@id='" + property + "']", data[property])
                    .keys(['\uE006']);
            }
        }
        // save claim
        this.click("@submitClaim");

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this.api;
    },

    assertClaim: function(username, rank, topRank, conflictRank, conflictTop, runnerDeck, corpDeck, client) {

        this.log('*** Verifying claim for tournament ***');

        var util = require('util');

        this.api.useXpath().waitForElementVisible(this.elements.playerClaim.selector, 3000);

        // verify swiss
        var swissClass = conflictRank ? 'danger' : 'info';
        this.api.useXpath().verify.elementPresent(
            util.format(this.elements.verifySwissEntry.selector, swissClass, rank, username, runnerDeck, corpDeck));

        // verify top
        if (topRank > 0) {
            var topClass = conflictTop ? 'danger' : 'info';
            this.api.useXpath().verify.elementPresent(
                util.format(this.elements.verifyTopEntry.selector, topClass, topRank, username, runnerDeck, corpDeck));
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this.api;
    },

    assertClaimRemoveButton: function(topTable, username, present, text, client) {

        this.log('*** Verifying claim remove button for tournament ***');

        var util = require('util'),
            tableId = topTable ? 'entries-top' : 'entries-swiss';

        if (present) {
            this.api.useXpath().verify.elementPresent(util.format(this.elements.entryRemoveButton.selector, tableId, username, text));
        } else {
            this.api.useXpath().verify.elementNotPresent(util.format(this.elements.entryRemoveButton.selector, tableId, username, text));
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
        verifySwissEntry: "//table[@id='entries-swiss']/tbody/tr[@class='%s']/td[contains(.,'%s')]/../td[contains(.,'%s')]/../td[contains(.,'%s')]/../td[contains(.,'%s')]",
        verifyTopEntry: "//table[@id='entries-top']/tbody/tr[@class='%s']/td[contains(.,'%s')]/../td[contains(.,'%s')]/../td[contains(.,'%s')]/../td[contains(.,'%s')]",
        entryRemoveButton: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/form/button[contains(.,'%s')]",
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
        submitClaim: {
            selector: "//button[@id='submit-claim']",
            locateStrategy: 'xpath'
        },
        removeClaim: {
            selector: "//button[@id='remove-claim']",
            locateStrategy: 'xpath'
        },
        claimError: {
            selector: "//div[@id='error-list']",
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
        ownClaimInTable: {
            selector: "//table/tbody/tr[@class='info']",
            locateStrategy: 'xpath'
        },
        conflictInTable: {
            selector: "//table/tbody/tr[@class='danger']",
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
