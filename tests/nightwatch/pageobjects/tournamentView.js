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

        return this;
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

        return this;
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

        return this;
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

        return this;
    },

    removeClaimOfUser: function(username, client) {

        this.log('*** Removing claim of user: '+username+' ***');

        var util = require('util');

        this.api.useXpath().click(util.format(this.elements.entryRemoveButton.selector, 'entries-swiss', username, 'Remove'))
            .pause(1000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    }
};


module.exports = {
    commands: [tournamentViewCommands],
    elements: {
        title: "//span[@id='tournament-title' and contains(., '%s')]",
        ttype: "//span[@id='tournament-type' and contains(., '%s')]",
        creator: "//span[@id='tournament-creator' and contains(., '%s')]",
        description: "//div[@id='tournament-description' and contains(., '%s')]",
        cardpool: "//span[@id='cardpool' and contains(., '%s')]",
        date: "//span[@id='tournament-date' and contains(., '%s')]",
        time: "//span[@id='start-time' and contains(., '%s')]",
        city: "//span[@id='tournament-city' and contains(., '%s')]",
        store: "//span[@id='store' and contains(., '%s')]",
        address: "//span[@id='address' and contains(., '%s')]",
        contact: "//span[@id='contact' and contains(., '%s')]",
        decklist: "//span[@id='decklist-mandatory']",
        registeredPlayer: "//ul[@id='registered-players']/li[contains(., '%s')]",
        verifySwissEntry: "//table[@id='entries-swiss']/tbody/tr[@class='%s']/td[contains(.,'%s')]/../td[contains(.,'%s')]/../td/a[contains(.,'%s')]/../../td/a[contains(.,'%s')]",
        verifyTopEntry: "//table[@id='entries-top']/tbody/tr[@class='%s']/td[contains(.,'%s')]/../td[contains(.,'%s')]/../td/a[contains(.,'%s')]/../../td/a[contains(.,'%s')]",
        entryRemoveButton: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/form/button[contains(.,'%s')]",
        map: {
            selector: "//div[@id='map']/div",
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
        },
        storeInfo: {
            selector: "//span[@id='store']",
            locateStrategy: 'xpath'
        },
        addressInfo: {
            selector: "//span[@id='address']",
            locateStrategy: 'xpath'
        }
    }
};
