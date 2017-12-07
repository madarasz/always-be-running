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
        this.api.useXpath().click(this.elements.inputRank.selector)
            .setValue(this.elements.inputRank.selector, data.rank);

        // set top rank
        if (data.rank_top > 0) {
            this.api.useXpath().click(this.elements.inputTopRank.selector)
                .setValue(this.elements.inputTopRank.selector, data.rank_top);
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

    }

};

module.exports = {
    commands: [claimCommands],
    elements: {
        validator: "//h4[@class='modal-title' and contains(.,'Claim spot on tournament')]/div[@class='modal-subtitle' and contains(.,'%s')]",
        swissOption: "//select[@id='rank']/option[%s]",
        topOption: "//select[@id='rank_top']/option[%s]",
        submit: {
            selector: "//button[@type='submit' and contains(.,'Claim spot')]",
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
        inputCorpDeck: {
            selector: "//select[@id='corp_deck']",
            locateStrategy: 'xpath'
        },
        inputRunnerDeck: {
            selector: "//select[@id='runner_deck']",
            locateStrategy: 'xpath'
        },
        loadingFinished: {
            selector: "//select[@id='runner_deck']//option",
            locateStrategy: 'xpath'
        }
    }
};
