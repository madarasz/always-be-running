var tournament = {
    title: 'Test - ' + formatDate(new Date()),
    type: 'online event',
    type_id: '6',
    date: '2001.01.01.'
};

// TODO: put in module
function formatDate(date) {
    var year = date.getFullYear(),
        month = date.getMonth() + 1, // months are zero indexed
        day = date.getDate(),
        hour = date.getHours(),
        minute = date.getMinutes(),
        minuteFormatted = minute < 10 ? "0" + minute : minute;

    return year + "." + month + "." + day + " " + hour + ":" + minuteFormatted;
}

module.exports = {
    'Tournament claims - create' : function (browser) {

        var regularLogin = browser.globals.regularLogin;

        browser
            .url(browser.launchUrl)
            .login(regularLogin.username, regularLogin.password)

            // create tournament
            .log('*** Creating Tournament ***')
            .page.mainMenu().selectMenu("create")
            .page.tournamentForm().fillForm({
                inputs: {title: tournament.title, date: tournament.date},
                selects: {tournament_type_id: tournament.type}
            })
            //
            .log('*** Saving ***')
            .page.tournamentForm().click("@submit").api

            // verify on My tournaments
            .log('*** Verifying on my tournaments table ***')
            .page.mainMenu().selectMenu("my")
            .assert.assertTournamentTable('created', tournament.title, {
                texts: [tournament.date],
                labels: ['pending', 'due']
            })

            // verify tournament details view
            .log('*** Verifying on tournament details view ***')
            .selectTournament('created', tournament.title, 'view')

            .page.tournamentView().assertView({
                title: tournament.title, ttype: tournament.type, date: tournament.date,
                conflictWarning: false, playerNumbers: false, topPlayerNumbers: false, playerClaim: false,
                createClaimFrom: false, topEntriesTable: false, swissEntriesTable: false, dueWarning: true,
                registeredPlayers: false, noRegisteredPlayers: true, unregisterButtonDisabled: false,
                unregisterButton: false, registerButton: true
            })
            .page.tournamentView().click('@registerButton').api
            .page.tournamentView().assertView({
                conflictWarning: false, playerNumbers: false, topPlayerNumbers: false, playerClaim: false,
                createClaimFrom: false, topEntriesTable: false, swissEntriesTable: false, dueWarning: true,
                registeredPlayers: true, noRegisteredPlayers: false, unregisterButtonDisabled: false,
                unregisterButton: true, registerButton: false, registeredPlayer: regularLogin.username
            })
            .page.tournamentView().click('@editButton').api

            .end();
    },
    //
    //'Tournament approval - admin rejects' : function (browser) {
    //
    //    var adminLogin = browser.globals.adminLogin;
    //
    //    browser
    //        .url(browser.launchUrl)
    //        .log('*** Logging in with admin ***')
    //        .login(adminLogin.username, adminLogin.password)
    //
    //        // verify on admin table
    //        .click("//a[contains(text(),'Admin')]")
    //        .assert.assertTournamentTable('pending', tournament.title, {
    //            texts: [tournament.date],
    //            labels: ['pending', 'due']
    //        })
    //
    //        // verify tournament details view
    //        .log('*** Verifying on tournament details view ***')
    //        .selectTournament('pending', tournament.title, 'view')
    //        .assert.assertTournamentView({
    //            title: tournament.title, ttype: tournament.type, date: tournament.date,
    //            map: false, approvalNeed: true, approvalRejected: false,
    //            editButton: true, deleteButton: true, approveButton: true, rejectButton: true
    //        })
    //
    //        // rejecting
    //        .log('*** Reject ***')
    //        .click("//div[@id='control-buttons']/form/a[contains(., 'Reject')]")
    //        .assert.assertTournamentView({
    //            approvalNeed: false, approvalRejected: true
    //        })
    //
    //        .end();
    //},
    //
    //// TODO: can not be seen by other user
    //
    //'Tournament approval - creator rechecks' : function (browser) {
    //
    //    var regularLogin = browser.globals.regularLogin;
    //
    //    browser
    //        .url(browser.launchUrl)
    //        .log('*** Logging in with creator ***')
    //        .login(regularLogin.username, regularLogin.password)
    //
    //        // verify on my tournaments table
    //        .click("//a[contains(text(),'My Tournaments')]")
    //        .assert.assertTournamentTable('created', tournament.title, {
    //            texts: [tournament.date],
    //            labels: ['rejected', 'due']
    //        })
    //
    //        // verify tournament details view
    //        .log('*** Verifying on tournament details view ***')
    //        .selectTournament('created', tournament.title, 'view')
    //        .assert.assertTournamentView({
    //            approvalNeed: false, approvalRejected: true,
    //            editButton: true, deleteButton: true, approveButton: false, rejectButton: false
    //        })
    //
    //        .end();
    //},
    //
    //'Tournament approval - admin approves' : function (browser) {
    //
    //    var adminLogin = browser.globals.adminLogin;
    //
    //    browser
    //        .url(browser.launchUrl)
    //        .log('*** Logging in with admin ***')
    //        .login(adminLogin.username, adminLogin.password)
    //
    //        // verify on admin table
    //        .click("//a[contains(text(),'Admin')]")
    //        .assert.assertTournamentTable('pending', tournament.title, {
    //            texts: [tournament.date],
    //            labels: ['rejected', 'due']
    //        })
    //
    //        // approval
    //        .log('*** Approve ***')
    //        .selectTournament('pending', tournament.title, 'approve')
    //
    //        .end();
    //},
    //
    //'Tournament approval - creator rechecks approved' : function (browser) {
    //
    //    var regularLogin = browser.globals.regularLogin;
    //
    //    browser
    //        .url(browser.launchUrl)
    //        .log('*** Logging in with creator ***')
    //        .login(regularLogin.username, regularLogin.password)
    //
    //        // verify on my tournaments table
    //        .click("//a[contains(text(),'My Tournaments')]")
    //        .assert.assertTournamentTable('created', tournament.title, {
    //            texts: [tournament.date],
    //            labels: ['approved', 'due']
    //        })
    //
    //        // verify tournament details view
    //        .log('*** Verifying on tournament details view ***')
    //        .selectTournament('created', tournament.title, 'view')
    //        .assert.assertTournamentView({
    //            approvalNeed: false, approvalRejected: false,
    //            editButton: true, deleteButton: true, approveButton: false, rejectButton: false
    //        })
    //
    //        // delete
    //        .log('*** Deleting ***')
    //        .click("//a[contains(text(),'My Tournaments')]")
    //        .selectTournament('created', tournament.title, 'delete')
    //
    //        .end();
    //}

    // TODO: can be seen by other user

};