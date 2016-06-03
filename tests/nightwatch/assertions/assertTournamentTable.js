exports.assertion = function(table_id, title, data) {

    var client = this;

    this.message = 'Tournament table validation failed.';
    this.expected = true;
    this.pass = function(value) {
        return value == this.expected;
    };
    this.value = function(result) {
        return result;
    };

    this.command = function(callback) {
        this.api.useXpath()
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//table[@id='"+table_id+"']/tbody/tr/td[contains(.,'"+title+"')]", 1000);

        if (data.hasOwnProperty('texts')) {
            data.texts.forEach(function(item) {
                client.api.waitForElementVisible("//table[@id='"+table_id+"']/tbody/tr/td[contains(.,'"+title+"')]/../td[contains(.,'"+item+"')]", 1000);
            });
        }

        if (data.hasOwnProperty('labels')) {
            data.labels.forEach(function(item) {
                client.api.waitForElementVisible("//table[@id='"+table_id+"']/tbody/tr/td[contains(.,'"+title+"')]/../td/span[contains(.,'"+item+"')]", 1000);
            });
        }

        if (data.hasOwnProperty('texts_missing')) {
            data.texts_missing.forEach(function(item) {
                client.api.assert.elementNotPresent("//table[@id='"+table_id+"']/tbody/tr/td[contains(.,'"+title+"')]/../td[contains(.,'"+item+"')]");
            });
        }

        return true;
    };

};