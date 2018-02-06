var tournamentViewCommands = {
    assertView: function(data, client) {

        this.log('*** Verifying tournament view ***');

        var util = require('util');

        this.api.useXpath().waitForElementVisible('//body', 3000);

        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                if (data[property] === true) {
                    this.waitForElementPresent('@'+property, 3000);
                    //this.verify.elementPresent('@'+property);
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

    assertImport: function (data, client) {

        this.log('*** Verifying imported results ***');

        var util = require('util');

        // validate swiss
        for (var i = 0; i < data.swiss.length; i++) {
            this.api.useXpath().verify.elementPresent(
                util.format(this.elements.verifyImportedEntry.selector, 'entries-swiss',
                    data.swiss[i].rank, data.swiss[i].player, data.swiss[i].corp_title, data.swiss[i].runner_title));
        }

        // validate top cut
        if (data.hasOwnProperty('topcut')) {
            for (var i = 0; i < data.topcut.length; i++) {
                this.api.useXpath().verify.elementPresent(
                    util.format(this.elements.verifyImportedEntry.selector, 'entries-top',
                        data.topcut[i].rank, data.topcut[i].player, data.topcut[i].corp_title, data.topcut[i].runner_title));
            }
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },

    validateMatches: function (data, client) {

        this.log('*** Verifying imported match data ***');

        var util = require('util');

        // swiss round number
        this.api.useXpath().waitForElementPresent(util.format(this.elements.matchSwissRounds.selector, data.swiss_rounds), 3000);

        // swiss round 1 entries
        for (var i = 0; i < data.swiss.length; i++) {
            this.api.useXpath().verify.elementPresent(
                util.format(this.elements.matchEntry.selector, 'tbody-matches-swiss-1',
                    data.swiss[i].player, data.swiss[i].corp_title, data.swiss[i].runner_title));
        }

        // swiss round 1 bye
        if (data.bye) {
            this.api.useXpath().verify.elementPresent(util.format(this.elements.matchEntryBye.selector, 'tbody-matches-swiss-1'));
        } else {
            this.api.useXpath().verify.elementNotPresent(util.format(this.elements.matchEntryBye.selector, 'tbody-matches-swiss-1'));
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    validatePoints: function (data, client) {

        this.log('*** Verifying imported points data ***');

        var util = require('util');

        for (var i = 0; i < data.points.length; i++) {
            this.api.useXpath().verify.elementPresent(
                util.format(this.elements.pointEntry.selector,
                    data.points[i].player, data.points[i].points, data.points[i].sos, data.points[i].esos));
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    //claim: function(data, client) {
    //
    //    this.log('*** Creating claim for tournament ***');
    //
    //    this.api.useXpath().waitForElementVisible(this.elements.createClaimFrom.selector, 3000);
    //
    //    // set form
    //    for (var property in data) {
    //        if (data.hasOwnProperty(property)) {
    //            this.api.useXpath().click("//select[@id='" + property + "']")
    //                .setValue("//select[@id='" + property + "']", data[property])
    //                .keys(['\uE006']);
    //        }
    //    }
    //    // save claim
    //    this.click("@submitClaim");
    //
    //    if (typeof callback === "function"){
    //        callback.call(client);
    //    }
    //
    //    return this;
    //},

    assertClaim: function(username, rank, topRank, conflictRank, conflictTop, runnerDeck, corpDeck, client) {

        this.log('*** Verifying claim for tournament ***');

        var util = require('util');

        this.api.useXpath().waitForElementVisible(this.elements.playerClaim.selector, 3000);

        // verify swiss
        var swissClass = conflictRank ? 'danger' : 'info';
        this.api.useXpath().verify.elementPresent(
            util.format(this.elements.verifyEntry.selector, 'entries-swiss', swissClass, rank, username, runnerDeck, corpDeck));

        // verify top
        if (topRank > 0) {
            var topClass = conflictTop ? 'danger' : 'info';
            this.api.useXpath().verify.elementPresent(
                util.format(this.elements.verifyEntry.selector, 'entries-top', topClass, topRank, username, runnerDeck, corpDeck));
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },

    assertIDClaim: function(username, rank, topRank, conflictRank, conflictTop, runnerID, corpID, client) {

        this.log('*** Verifying ID claim for tournament ***');

        var util = require('util');

        this.api.useXpath().waitForElementVisible(this.elements.playerClaim.selector, 3000);

        // verify swiss
        var swissClass = conflictRank ? 'danger' : 'info';
        this.api.useXpath().verify.elementPresent(
            util.format(this.elements.verifyIDEntry.selector, 'entries-swiss', swissClass, rank, username, runnerID, corpID));

        // verify top
        if (topRank > 0) {
            var topClass = conflictTop ? 'danger' : 'info';
            this.api.useXpath().verify.elementPresent(
                util.format(this.elements.verifyIDEntry.selector, 'entries-top', topClass, topRank, username, runnerID, corpID));
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

    removeAnonym: function(table, rank, playerName, client) {

        this.log('*** Removing anonym claim: #'+rank+' ***');

        var util = require('util');

        this.api.useXpath().click(util.format(this.elements.deleteAnonym.selector, table, rank, playerName));

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
    },

    // removes pNRTM / Cobr.is JSON - improving test stability
    removeJson: function(client) {
        var fs = require('fs');

        this.api.useXpath().getValue(this.elements.tournamentID.selector, (function(result) {
            tournamentID = result.value;
            filepath = __dirname + '/../../../public/tjsons/' + tournamentID + '.json';

            if (fs.existsSync(filepath)) {
                this.log("*** File: Deleting tournament JSON: " + tournamentID + " ***");
                fs.unlink(filepath);
            } else {
                this.log("*** File: previous tournament JSON was not found: " + tournamentID + " ***");
            }
        }).bind(this));

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },

    validate: function(client) {

        this.log('*** Validating tournament details page ***');

        this.waitForElementVisible('@validator', 10000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
}, tournamentID;


module.exports = {
    commands: [tournamentViewCommands],
    elements: {
        title: "//span[@id='tournament-title' and contains(., '%s')]",
        ttype: "//span[@id='tournament-type' and contains(., '%s')]",
        tformat: "//span[@id='tournament-format' and contains(., '%s')]",
        creator: "//span[@id='tournament-creator' and contains(., '%s')]",
        description: "//div[@id='tournament-description' and contains(., '%s')]",
        cardpool: "//span[@id='cardpool' and contains(., '%s')]",
        facebookGroup: "//a[contains(., 'Facebook group') and @href='%s']",
        facebookEvent: "//a[contains(., 'Facebook event') and @href='%s']",
        date: "//span[@id='tournament-date' and contains(., '%s')]",
        time: "//span[@id='start-time' and contains(., '%s')]",
        location: "//span[@id='tournament-location' and contains(., '%s')]",
        store: "//span[@id='store' and contains(., '%s')]",
        address: "//span[@id='address' and contains(., '%s')]",
        contact: "//span[@id='contact' and contains(., '%s')]",
        registeredPlayer: "//ul[@id='registered-players']/li[contains(., '%s')]",
        verifyEntry: "//table[@id='%s']/tbody/tr[contains(@class,'%s')]/td[contains(.,'#%s')]/../td[contains(.,'%s')]/../td/a[contains(.,'%s')]/../../td/a[contains(.,'%s')]",
        verifyIDEntry: "//table[@id='%s']/tbody/tr[contains(@class,'%s')]/td[contains(.,'#%s')]/../td[contains(.,'%s')]/../td[contains(.,'%s')]/../td[contains(.,'%s')]",
        verifyImportedEntry: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td[contains(.,'%s')]/../td[contains(.,'%s')]/../td[contains(.,'%s')]",
        entryRemoveButton: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/form/button[contains(.,'%s')]",
        concludedBy: "//div[@id='concluded-by' and contains(.,'%s')]",
        matchSwissRounds: "//table[@id='table-matches-swiss']/thead/th[contains(.,'Round %d')]",
        matchEntry: "//tbody[@id='%s']/tr/td[contains(.,'%s')]/../../tr/td[contains(.,'%s')]/../../tr/td[contains(.,'%s')]",
        matchEntryBye: "//tbody[@id='%s']/tr/td[contains(.,'BYE')]",
        pointEntry: "//table[@id='entries-swiss']/tbody/tr/td[contains(.,'%s')]/../td[@class='cell-points' and contains(.,'%s') and contains(.,'%s') and contains(.,'%s')]",
        deleteAnonym: "//table[@id='%s']//td[contains(.,'#%s')]/../td[contains(.,'%s')]/../td/form/button[contains(@class,'delete-anonym')]",
        tournamentID: "//form[@id='form-photos']/input[@name='tournament_id']",
        decklist: {
            selector: "//span[@id='decklist-mandatory']",
            locateStrategy: 'xpath'
        },
        validator: {
            selector: "//h4[contains(.,'created by')]",
            locateStrategy: 'xpath'
        },
        descriptionSection: {
            selector: "//div[@id='tournament-description']",
            locateStrategy: 'xpath'
        },
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
        transferButton: {
            selector: "//button[@id='button-transfer']",
            locateStrategy: 'xpath'
        },
        revertButton: {
            selector: "//button[@id='button-revert']",
            locateStrategy: 'xpath'
        },
        featureButton: {
            selector: "//button[@id='feature-button']",
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
        suggestLogin: {
            selector: "//div[@id='suggest-login']",
            locateStrategy: 'xpath'
        },
        buttonNRTMimport: {
            selector: "//button[@id='button-import-nrtm']",
            locateStrategy: 'xpath'
        },
        buttonNRTMclear: {
            selector: "//button[@id='button-clear-nrtm']",
            locateStrategy: 'xpath'
        },
        buttonConclude: {
            selector: "//button[@id='button-conclude']",
            locateStrategy: 'xpath'
        },
        playerClaim: {
            selector: "//ul[@id='player-claim']",
            locateStrategy: 'xpath'
        },
        buttonClaim: {
            selector: "//button[@id='button-claim']",
            locateStrategy: 'xpath'
        },
        removeClaim: {
            selector: "//button[@id='remove-claim']",
            locateStrategy: 'xpath'
        },
        showMatches: {
            selector: "//button[@id='button-showmatches']",
            locateStrategy: 'xpath'
        },
        showPoints: {
            selector: "//button[@id='button-showpoints']",
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
            selector: "//table/tbody/tr[contains(@class, 'own-claim')]",
            locateStrategy: 'xpath'
        },
        conflictInTable: {
            selector: "//table/tbody/tr[contains(@class,'danger')]",
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
        },
        chartRunnerIds: {
            selector: "//div[@id='stat-chart-runner']/div",
            locateStrategy: 'xpath'
        },
        chartCorpIds: {
            selector: "//div[@id='stat-chart-corp']/div",
            locateStrategy: 'xpath'
        }
    }
};
