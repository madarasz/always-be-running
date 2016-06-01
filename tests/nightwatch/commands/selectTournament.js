exports.command = function (table_id, title, action, callback) {

    if (action === 'delete') {
        this.click("//table[@id='"+table_id+"']/tbody/tr/td[contains(.,'"+title+"')]/../td/form/button[contains(.,'"+action+"')]")
            .assert.elementNotPresent("//table[@id='" + table_id + "']/tbody/tr/td[contains(.,'" + title + "')]");
    } else {
        this.click("//table[@id='"+table_id+"']/tbody/tr/td[contains(.,'"+title+"')]/../td/a[contains(.,'"+action+"')]");
    }

    if (typeof callback === "function"){
        callback.call(client);
    }

    return this;
};