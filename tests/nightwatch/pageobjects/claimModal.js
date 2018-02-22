var claimCommands = {

    validate: function(title, players_number, top_number, client) {
        this.log('* Validating conclude tournament modal *');

        var util = require('util');

        // subtitle
        this.api.useXpath().waitForElementVisible(util.format(this.elements.validator.selector, title), 3000);

        // swiss selector
        this.api.useXpath().verify.elementPresent(util.format(this.elements.swissOption.selector, players_number));

        // top selector
        if (parseInt(top_number) > 0) {
            this.api.useXpath().verify.elementPresent(util.format(this.elements.topOption.selector, top_number));
        } else {
            this.api.useXpath().verify.elementNotPresent(util.format(this.elements.topOption.selector, 1));
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    claim: function (data, client) {

        this.log('*** Claiming tournament spot ***');

        var util = require('util');
        this
            .api.useXpath()
            .waitForElementVisible(this.elements.loadingFinished.selector, 20000);

        // set swiss rank
        this.api.useXpath()
            .click(this.elements.inputRank.selector)
            .click(util.format(this.elements.optionSelected.selector, 'rank', data.rank));

        // set top rank
        if (data.rank_top > 0) {
            this.api.useXpath()
                .click(this.elements.inputTopRank.selector)
                .click(util.format(this.elements.optionSelected.selector, 'rank_top', data.rank_top));
        }

        // set decks
        this.api.useXpath().click(this.elements.inputCorpDeck.selector)
            .setValue(this.elements.inputCorpDeck.selector, data.corp_deck);
        this.api.useXpath().click(this.elements.inputRunnerDeck.selector)
            .setValue(this.elements.inputRunnerDeck.selector, data.runner_deck);

        // submit claim
        this.click(this.elements.submit.selector);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    claimWithID: function (data, validateAuto, client) {

        this.log('*** Claiming tournament spot with IDs ***');

        var util = require('util');

        this.click('@menuIds');

        // set swiss rank
        this.api.useXpath()
            .click(this.elements.inputRankID.selector)
            .click(util.format(this.elements.optionSelected.selector, 'rank_nodeck', data.rank));

        // set top rank
        if (data.rank_top > 0) {
            this.api.useXpath()
                .click(this.elements.inputTopRankID.selector)
                .click(util.format(this.elements.optionSelected.selector, 'rank_top_nodeck', data.rank_top));
        }

        if (validateAuto) {
            // validate automatically selected IDs based on rank
            this.api.useXpath().assert.value(this.elements.inputCorpID.selector, data.corp_id_id);
            this.api.useXpath().assert.value(this.elements.inputRunnerID.selector, data.runner_id_id);
        } else {
            // set IDs manually
            this.api.useXpath().click(this.elements.inputCorpID.selector)
                .setValue(this.elements.inputCorpID.selector, data.corp_id);
            this.api.useXpath().click(this.elements.inputRunnerID.selector)
                .setValue(this.elements.inputRunnerID.selector, data.runner_id);
        }

        // submit claim
        this.click(this.elements.submitID.selector);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    claimWithDeckID: function (data, client) {

        this.log('*** Claiming tournament spot with deck IDs ***');

        var util = require('util');

        this
            .click('@menuDecks')
            .click('@moreOptions');

        // set swiss rank
        this.api.useXpath()
            .click(this.elements.inputRank.selector)
            .click(util.format(this.elements.optionSelected.selector, 'rank', data.rank));

        // set top rank
        if (data.rank_top > 0) {
            this.api.useXpath()
                .click(this.elements.inputTopRank.selector)
                .click(util.format(this.elements.optionSelected.selector, 'rank_top', data.rank_top));
        }

        // set decks IDs
        this.api.useXpath()
            .clearValue(this.elements.inputCorpDeckID.selector)
            .setValue(this.elements.inputCorpDeckID.selector, data.corp_deck_id);
        this.api.useXpath()
            .clearValue(this.elements.inputRunnerDeckID.selector)
            .setValue(this.elements.inputRunnerDeckID.selector, data.runner_deck_id);

        // submit claim
        this.click(this.elements.submit.selector);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    assertModal: function (data, client) {

        this.log('*** Verifying claim modal ***');

        var util = require('util');

        this.api.useXpath().waitForElementVisible('//body', 1000);

        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                if (data[property] === true) {
                    this.waitForElementVisible('@'+property, 1000);
                } else if (data[property] === false) {
                    this.waitForElementNotVisible('@'+property, 1000);
                } else if (data[property] === 'not found') {
                    this.verify.elementNotPresent('@'+property);
                }
            }
        }


        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    }

};

module.exports = {
    commands: [claimCommands],
    elements: {
        validator: "//h4[@class='modal-title' and contains(.,'Claim spot on tournament')]/div[@class='modal-subtitle' and contains(.,'%s')]",
        swissOption: "//select[@id='rank']/option[%s]",
        topOption: "//select[@id='rank_top']/option[%s]",
        optionSelected: "//select[@id='%s']/option[@value='%s']",
        submit: {
            selector: "//button[@id='submit-claim' and not(@disabled)]",
            locateStrategy: 'xpath'
        },
        submitID: {
            selector: "//button[@id='submit-id-claim']",
            locateStrategy: 'xpath'
        },
        inputRank: {
            selector: "//select[@id='rank']",
            locateStrategy: 'xpath'
        },
        inputTopRank: {
            selector: "//select[@id='rank_top']",
            locateStrategy: 'xpath'
        },
        inputRankID: {
            selector: "//select[@id='rank_nodeck']",
            locateStrategy: 'xpath'
        },
        inputTopRankID: {
            selector: "//select[@id='rank_top_nodeck']",
            locateStrategy: 'xpath'
        },
        inputCorpDeck: {
            selector: "//select[@id='corp_deck']",
            locateStrategy: 'xpath'
        },
        inputRunnerDeck: {
            selector: "//select[@id='runner_deck']",
            locateStrategy: 'xpath'
        },
        inputCorpDeckID: {
            selector: "//input[@id='other_corp_deck']",
            locateStrategy: 'xpath'
        },
        inputRunnerDeckID: {
            selector: "//input[@id='other_runner_deck']",
            locateStrategy: 'xpath'
        },
        inputCorpID: {
            selector: "//select[@id='corp_deck_identity']",
            locateStrategy: 'xpath'
        },
        inputRunnerID: {
            selector: "//select[@id='runner_deck_identity']",
            locateStrategy: 'xpath'
        },
        loadingFinished: {
            selector: "//select[@id='runner_deck']//option",
            locateStrategy: 'xpath'
        },
        menuIds: {
            selector: "//a[@id='menu-ids']",
            locateStrategy: 'xpath'
        },
        menuDecks: {
            selector: "//a[@id='menu-decks']",
            locateStrategy: 'xpath'
        },
        moreOptions: {
            selector: "//a[@id='collapser-options']",
            locateStrategy: 'xpath'
        },
        warningNoRunnerDecks: {
            selector: "//div[@id='no-runner-deck']",
            locateStrategy: 'xpath'
        },
        warningNoCorpDecks: {
            selector: "//div[@id='no-corp-deck']",
            locateStrategy: 'xpath'
        },
        warningUsingOtherRunner: {
            selector: "//div[@id='warn_runner_deck_other']",
            locateStrategy: 'xpath'
        },
        warningUsingOtherCorp: {
            selector: "//div[@id='warn_corp_deck_other']",
            locateStrategy: 'xpath'
        },
        warningNetrunnerDB: {
            selector: "//div[@id='claim-user-login']",
            locateStrategy: 'xpath'
        },
        warningPublishing: {
            selector: "//div[@id='alert-private']",
            locateStrategy: 'xpath'
        }
    }
};
