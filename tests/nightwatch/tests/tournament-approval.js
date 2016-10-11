var tournamentPast = {
        title: 'Test - past - ' + formatDate(new Date()),
        type: 'online event',
        cardpool: 'Business First',
        type_id: '7',
        date: '2001.01.01.',
        players: '9'
    },
    tournamentFuture = {
        title: 'Test - future - ' + formatDate(new Date()),
        type: 'online event',
        cardpool: 'True Colors',
        type_id: '7',
        date: '2022.01.01.'
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

    // TODO: with different users

    /***
     * - create concluded tournament in the past (unapproved)
     * - verify on tournament detail page
     * - verify that unapproved tournament is not seen on the Results page
     * - approve tournament with admin on Admin page
     * - verify on Results page
     * - verify on tournament detail page
     * - reject on tournament detail page
     * - verify on tournament detail page
     * - verify that rejected tournament is not seen on the Results page
     * - delete tournament with admin on Admin page
     */
    'Tournament approval, rejection - past tournament' : function (browser) {

        var adminLogin = browser.globals.adminLogin;

        browser
            .url(browser.launchUrl)
            .log('*** Logging in ***')
            .login(adminLogin.username, adminLogin.password)
            .log('*** Creating Tournament in past ***');

        browser.page.mainMenu().selectMenu('organize');

        browser.page.organizePage().clickCommand('create');

        // create concluded tournament in the past (unapproved)
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                inputs: {
                    title: tournamentPast.title,
                    date: tournamentPast.date
                },
                selects: {
                    tournament_type_id: tournamentPast.type,
                    cardpool_id: tournamentPast.cardpool
                },
                checkboxes: {concluded: true}
            })
            .fillForm({
                inputs: { players_number: tournamentPast.players }
            })
            .click('@submit_button');

        // verify on tournament detail page
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentPast.title,
                ttype: tournamentPast.type,
                cardpool: tournamentPast.cardpool,
                creator: adminLogin.username,
                date: tournamentPast.date,
                approvalNeed: true,
                approvalRejected: false,
                editButton: true,
                approveButton: true,
                rejectButton: true,
                deleteButton: true
            });

        // verify that unapproved tournament is not seen on the Results page
        browser.page.mainMenu().selectMenu('results');

        browser.page.tournamentTable()
            .assertMissingRow('results', tournamentPast.title);

        // approve tournament with admin on Admin page
        browser.page.mainMenu().selectMenu('admin');

        browser.page.tournamentTable()
            .assertTable('pending', tournamentPast.title, {
                texts: [tournamentPast.date, tournamentPast.cardpool, tournamentPast.players],
                labels: ['pending', 'concluded']
            })
            .selectTournamentAction('pending', tournamentPast.title, 'approve');

        browser.page.messages().assertMessage('Tournament approved.');

        browser.page.tournamentTable()
            .assertMissingRow('pending', tournamentPast.title);

        // verify on Results page
        browser.page.mainMenu().selectMenu('results');

        browser.page.tournamentTable()
            .assertTable('results', tournamentPast.title, {
                texts: [tournamentPast.date, tournamentPast.cardpool, tournamentPast.players, 'online']
            })
            .selectTournament('results', tournamentPast.title);

        // verify on tournament details page
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentPast.title,
                ttype: tournamentPast.type,
                cardpool: tournamentPast.cardpool,
                creator: adminLogin.username,
                date: tournamentPast.date,
                approvalNeed: false,
                approvalRejected: false,
                editButton: true,
                approveButton: true,
                rejectButton: true,
                deleteButton: true
            })
            // reject on tournament detail page
            .click('@rejectButton')
            .assertView({
                title: tournamentPast.title,
                ttype: tournamentPast.type,
                cardpool: tournamentPast.cardpool,
                creator: adminLogin.username,
                date: tournamentPast.date,
                approvalNeed: false,
                approvalRejected: true,
                editButton: true,
                approveButton: true,
                rejectButton: true,
                deleteButton: true
            });

        // verify that rejected tournament is not seen on the Results page
        browser.page.mainMenu().selectMenu('results');

        browser.page.tournamentTable()
            .assertMissingRow('results', tournamentPast.title);

        // delete tournament with admin on Admin page
        browser.page.mainMenu().selectMenu('admin');

        browser.page.tournamentTable()
            .assertTable('pending', tournamentPast.title, {
                texts: [tournamentPast.date, tournamentPast.cardpool, tournamentPast.players],
                labels: ['rejected', 'concluded']
            })
            .selectTournamentAction('pending', tournamentPast.title, 'delete');
    },

    /***
     * - create concluded tournament in the future (unapproved)
     * - verify on tournament detail page
     * - verify that unapproved tournament is not seen on the Upcoming page
     * - approve tournament with admin on Admin page
     * - verify on Upcoming page
     * - verify on tournament detail page
     * - reject on tournament detail page
     * - verify on tournament detail page
     * - verify that rejected tournament is not seen on the Upcoming page
     * - delete tournament with admin on Admin page
     */
    'Tournament approval, rejection - upcoming tournament' : function (browser) {

        var adminLogin = browser.globals.adminLogin;

        browser
            .url(browser.launchUrl)
            .log('*** Logging in ***')
            .login(adminLogin.username, adminLogin.password)
            .log('*** Creating Tournament in past ***');

        browser.page.mainMenu().selectMenu('organize');

        browser.page.organizePage().clickCommand('create');

        // create concluded tournament in the future (unapproved)
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                inputs: {
                    title: tournamentFuture.title,
                    date: tournamentFuture.date
                },
                selects: {
                    tournament_type_id: tournamentFuture.type,
                    cardpool_id: tournamentFuture.cardpool
                },
                checkboxes: {concluded: false}
            })
            .click('@submit_button');

        // verify on tournament detail page
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentFuture.title,
                ttype: tournamentFuture.type,
                cardpool: tournamentFuture.cardpool,
                creator: adminLogin.username,
                date: tournamentFuture.date,
                approvalNeed: true,
                approvalRejected: false,
                editButton: true,
                approveButton: true,
                rejectButton: true,
                deleteButton: true
            });

        // verify that unapproved tournament is not seen on the Upcoming page
        browser.page.mainMenu().selectMenu('upcoming');

        browser.page.tournamentTable()
            .assertMissingRow('discover-table', tournamentFuture.title);

        // approve tournament with admin on Admin page
        browser.page.mainMenu().selectMenu('admin');

        browser.page.tournamentTable()
            .assertTable('pending', tournamentFuture.title, {
                texts: [tournamentFuture.date, tournamentFuture.cardpool],
                labels: ['pending', 'not yet']
            })
            .selectTournamentAction('pending', tournamentFuture.title, 'approve');

        browser.page.messages().assertMessage('Tournament approved.');

        browser.page.tournamentTable()
            .assertMissingRow('pending', tournamentFuture.title);

        // verify on Upcoming page
        browser.page.mainMenu().selectMenu('upcoming');

        browser.page.tournamentTable()
            .assertTable('discover-table', tournamentFuture.title, {
                texts: [tournamentFuture.date, tournamentFuture.cardpool, 'online']
            })
            .selectTournament('discover-table', tournamentFuture.title);

        // verify on tournament details page
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentFuture.title,
                ttype: tournamentFuture.type,
                cardpool: tournamentFuture.cardpool,
                creator: adminLogin.username,
                date: tournamentFuture.date,
                approvalNeed: false,
                approvalRejected: false,
                editButton: true,
                approveButton: true,
                rejectButton: true,
                deleteButton: true
            })
            // reject on tournament detail page
            .click('@rejectButton')
            .assertView({
                title: tournamentFuture.title,
                ttype: tournamentFuture.type,
                cardpool: tournamentFuture.cardpool,
                creator: adminLogin.username,
                date: tournamentFuture.date,
                approvalNeed: false,
                approvalRejected: true,
                editButton: true,
                approveButton: true,
                rejectButton: true,
                deleteButton: true
            });

        // verify that rejected tournament is not seen on the Upcoming page
        browser.page.mainMenu().selectMenu('upcoming');

        browser.page.tournamentTable()
            .assertMissingRow('discover-table', tournamentFuture.title);

        // delete tournament with admin on Admin page
        browser.page.mainMenu().selectMenu('admin');

        browser.page.tournamentTable()
            .assertTable('pending', tournamentFuture.title, {
                texts: [tournamentFuture.date, tournamentFuture.cardpool],
                labels: ['rejected', 'not yet']
            })
            .selectTournamentAction('pending', tournamentFuture.title, 'delete');
    },

    after: function(browser) {
        browser.end();
    }

};