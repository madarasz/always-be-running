exports.assertion = function(data) {

    this.message = 'Tournament view validation failed.';
    this.expected = true;
    this.pass = function(value) {
        return value == this.expected;
    };
    this.value = function(result) {
        return result;
    };

    this.command = function(callback) {
        this.api.waitForElementVisible('//body', 3000);

        if (data.hasOwnProperty('title')) {
            this.api.waitForElementVisible("//h3[contains(., '" + data.title + "')]", 1000);
        }
        if (data.hasOwnProperty('ttype')) {
            this.api.waitForElementVisible("//h3/small[contains(., '" + data.ttype + "')]", 1000);
        }
        if (data.hasOwnProperty('description')) {
            this.api.waitForElementVisible("//div[contains(@class, 'panel-body') and contains(., '" + data.description + "')]", 1000);
        }
        if (data.hasOwnProperty('date')) {
            this.api.waitForElementVisible("//h4[contains(., '" + data.date + "')]", 1000);
        }
        if (data.hasOwnProperty('time')) {
            this.api.waitForElementVisible("//p[contains(., '" + data.time + "')]", 1000);
        }
        if (data.hasOwnProperty('country')) {
            this.api.waitForElementVisible("//h4[contains(., '" + data.country + "')]", 1000);
        }
        if (data.hasOwnProperty('state')) {
            this.api.waitForElementVisible("//h4[contains(., '" + data.state + "')]", 1000);
        }
        if (data.hasOwnProperty('city')) {
            this.api.waitForElementVisible("//h4[contains(., '" + data.city + "')]", 1000);
        }
        if (data.hasOwnProperty('store')) {
            this.api.waitForElementVisible("//p[contains(., '" + data.store + "')]", 1000);
        }
        if (data.hasOwnProperty('address')) {
            this.api.waitForElementVisible("//p[contains(., '" + data.address + "')]", 1000);
        }
        if (data.hasOwnProperty('map')) {
            if (data.map) {
                this.api.waitForElementVisible("//iframe[@id='map']", 1000);
            } else {
                this.api.verify.elementNotPresent("//iframe[@id='map']");
            }
        }
        return true;
    };

};