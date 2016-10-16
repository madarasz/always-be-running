var claimCommands = {

    validate: function(title, client) {
        this.log('* Validating conclude tournament modal *');

        var util = require('util');
        this.api.useXpath().waitForElementVisible(util.format(this.elements.validator.selector, title), 3000);

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
        this
            .selectOption(this.elements.inputRank.selector, data['rank'])
            .selectOption(this.elements.inputTopRank.selector, data['rank_top'])
            .selectOption(this.elements.inputCorpDeck.selector, data['corp_deck'])
            .selectOption(this.elements.inputRunnerDeck.selector, data['runner_deck'])
            //.setValue(this.elements.inputRank.selector, data['rank'])
            //.setValue(this.elements.inputTopRank.selector, data['rank_top'])
            //.setValue(this.elements.inputCorpDeck.selector, data['corp_deck'])
            //.setValue(this.elements.inputRunnerDeck.selector, data['runner_deck'])
            .click(this.elements.submit.selector);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    // TODO: move this to somewhere global
    selectOption: function (xpathSelector, value) {
        this.api.useXpath()
            .click(xpathSelector)
            .setValue(xpathSelector, value)
            .keys(['\uE006']);

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
