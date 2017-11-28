var concludeCommands = {

    validate: function(title, client) {

        this.log('*** Validating conclude tournament modal ***');

        var util = require('util');
        this.api.useXpath().waitForElementVisible(util.format(this.elements.validator.selector, title), 3000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    concludeNrtmJson: function (filename, client) {

        var filepath = require('path').resolve(__dirname + '/../files/' + filename);

        this.log('*** Concluding via NRTM.json file: ' + filepath + ' *** ');

        this
            .api.useXpath()
            .clearValue(this.elements.inputNRTMFile.selector)
            .setValue(this.elements.inputNRTMFile.selector, filepath)
            .click(this.elements.submitImport.selector);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },

    concludeManual: function (data, client) {

        this.log('*** Concluding tournament manually ***');

        this
            .api.useXpath()
            .clearValue(this.elements.inputPlayersNumber.selector)
            .setValue(this.elements.inputPlayersNumber.selector, data['players_number'])
            .setValue(this.elements.inputTopNumber.selector, data['top_number'])
            .click(this.elements.submitManual.selector);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};

module.exports = {
    commands: [concludeCommands],
    elements: {
        validator: "//h4[@class='modal-title' and contains(.,'Conclude tournament')]/div[@class='modal-subtitle' and contains(.,'%s')]",
        submitManual: {
            selector: "//input[@type='submit' and @value='Conclude manually']",
            locateStrategy: 'xpath'
        },
        submitImport: {
            selector: "//input[@type='submit' and @value='Conclude via import']",
            locateStrategy: 'xpath'
        },
        inputPlayersNumber: {
            selector: "//input[@id='players_number']",
            locateStrategy: 'xpath'
        },
        inputTopNumber: {
            selector: "//select[@id='top_number']",
            locateStrategy: 'xpath'
        },
        inputNRTMFile: {
            selector: "//input[@id='jsonresults']",
            locateStrategy: 'xpath'
        }
    }
};
