module.exports = {

    beforeEach: function(browser) {
        browser.deleteCookies().windowMaximize('current');
    },

    after: function(browser) {
        browser.end();
    },

    /**
     * - Navigate to Organize page
     * - Login with NRDB (regular user)
     * - Validate login, click Create Tournament
     * - Validate tournament form, fill out form with recurring tournament data
     * - Validate that location is found and correct
     * - Save tournament, validate tournament details page
     * - Navigate to Organize page, validate entry on table of created tournaments
     * - Navigate to Upcoming page, check recurring tournaments table
     * - Logout
     * - Login as admin, hard delete tournament
     */
    'Create recurring tournament': function (browser) {

        var regularLogin = browser.globals.accounts.regularLogin,
            adminLogin = browser.globals.accounts.adminLogin,
            tournamentRecurring = JSON.parse(JSON.stringify(browser.globals.tournaments.tournamentRecurring)); // clone

        tournamentRecurring.title = browser.currentTest.module.substring(0,3) + "|" +
            browser.currentTest.name.substring(0,29) + "|" + tournamentRecurring.title.substring(0, 16);

        // open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // navigate to Organize page
        browser.log('* Navigate to Organize page *');
        browser.page.mainMenu()
            .selectMenu('organize');

        // login with NRDB (regular user)
        browser.log('* Login with NRDB (regular user) *');
        browser.login(regularLogin.username, regularLogin.password);

        // validate login, click Create Tournament
        browser.log('* Validate login, click Create Tournament *');
        browser.page.organizePage()
            .validate(true)
            .clickCommand('createTournament');

        // validate tournament form, fill out form with recurring tournament data
        browser.log('* Validate tournament form, fill out form with recurring tournament data *');
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                inputs: {
                    title: tournamentRecurring.title,
                    link_facebook: tournamentRecurring.link_facebook,
                    start_time: tournamentRecurring.start_time,
                    contact: tournamentRecurring.contact
                },
                textareas: {description: tournamentRecurring.description},
                selects: {
                    tournament_type_id: tournamentRecurring.type,
                    tournament_format_id: tournamentRecurring.format
                },
                radios: {
                    date_type_id: tournamentRecurring.date_type_id
                },
                checkboxes: {
                    decklist: tournamentRecurring.decklist
                }
            })
            .assertForm({
                visible: ['overlay_cardpool', 'overlay_conclusion'],
                not_visible: ['overlay_location', 'overlay_recurring']
            })
            .fillForm({
                selects: {
                    recur_weekly: tournamentRecurring.recur_weekly_text
                }
            })
            .fillForm({
                location: tournamentRecurring.location_input
            });

        // validate that location is found and correct
        browser.log('* Validate that location is found and correct *');
        browser.page.tournamentForm()
            .assertForm({
                visible: ['location_country', 'location_city'],
                not_present: ['location_state', 'location_store', 'location_address']
            })
            .validateLocation({
                location_country: tournamentRecurring.location_country,
                location_city: tournamentRecurring.location_city,
                location_place_id: tournamentRecurring.location_place_id,
                location_lat: tournamentRecurring.location_lat,
                location_long: tournamentRecurring.location_long
            });

        // save tournament, validate tournament details page
        browser.log('* Save tournament, validate tournament details page *');
        browser.page.mainMenu().acceptCookies(); // cookies info is in the way
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentRecurring.title,
                ttype: tournamentRecurring.type,
                creator: regularLogin.username,
                description: tournamentRecurring.description,
                facebookEvent: tournamentRecurring.link_facebook,
                date: 'recurring: ' + tournamentRecurring.recur_weekly_text,
                time: tournamentRecurring.start_time,
                location: tournamentRecurring.location,
                contact: tournamentRecurring.contact,
                map: true,
                approvalNeed: true,
                editButton: true,
                approveButton: false,
                rejectButton: false,
                deleteButton: true,
                transferButton: true,
                featureButton: false,
                conflictWarning: false,
                playerNumbers: false,
                topPlayerNumbers: false,
                suggestLogin: false,
                buttonNRTMimport: false,
                buttonNRTMclear: false,
                buttonConclude: false,
                playerClaim: false,
                buttonClaim: false,
                removeClaim: false,
                claimError: false,
                topEntriesTable: false,
                swissEntriesTable: false,
                ownClaimInTable: false,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: false,
                noRegisteredPlayers: true,
                unregisterButton: false,
                registerButton: true,
                showMatches: false,
                showPoints: false,
                chartRunnerIds: false,
                chartCorpIds: false
            });

        // click Update button, verify tournament form, click Cancel
        browser.log('* Click Update button, verify tournament form, click Cancel *');
        browser.page.tournamentView().click('@editButton');
        browser.page.tournamentForm()
            .validate()
            .assertForm({
                visible: ['players_number_disabled', 'map_loaded', 'overlay_conclusion', 'overlay_cardpool',
                    'location_country', 'location_city' ],
                not_present: ['location_state', 'location_store', 'location_address'],
                not_visible: ['overlay_location', 'overlay_recurring'],
                inputs: {
                    title: tournamentRecurring.title,
                    link_facebook: tournamentRecurring.link_facebook,
                    start_time: tournamentRecurring.start_time,
                    contact: tournamentRecurring.contact
                },
                textareas: { description: tournamentRecurring.description },
                selects: {
                    tournament_type_id: tournamentRecurring.tournament_type_id,
                    tournament_format_id: tournamentRecurring.tournament_format_id,
                    recur_weekly: tournamentRecurring.recur_weekly
                },
                radios: {
                    date_type_id: tournamentRecurring.date_type_id
                },
                checkboxes: {
                    decklist: tournamentRecurring.decklist
                }
            })
            .validateLocation({
                location_country: tournamentRecurring.location_country,
                location_city: tournamentRecurring.location_city,
                location_place_id: tournamentRecurring.location_place_id,
                location_lat: tournamentRecurring.location_lat,
                location_long: tournamentRecurring.location_long
            })
            .click('@cancel_button');

        // navigate to Organize page, validate entry on table of created tournaments
        browser.log('* Navigate to Organize page, validate entry on table of created tournaments *');
        browser.page.mainMenu()
            .selectMenu('organize');
        browser.page.tournamentTable().assertTable('created', tournamentRecurring.title, {
            texts: [tournamentRecurring.recur_weekly_text, tournamentRecurring.location],
            labels: ['pending']
        });

        // navigate to Upcoming page, check recurring tournaments table
        browser.log('* Navigate to Upcoming page, check recurring tournaments table *');
        browser.page.mainMenu()
            .selectMenu('upcoming');
        browser.page.tournamentTable().assertTable('recur-table', tournamentRecurring.title, {
            texts: [tournamentRecurring.recur_weekly_text, tournamentRecurring.location]
        });

        // logout
        browser.log('* Logout *');
        browser.page.mainMenu().selectMenu('logout');

        // login as admin, hard delete tournament
        browser.log('* Login with NRDB (admin user), hard delete tournament *');
        browser.login(adminLogin.username, adminLogin.password);
        browser.page.mainMenu().selectMenu('admin');
        browser.page.tournamentTable()
            .assertTable('pending', tournamentRecurring.title, {
                texts: [tournamentRecurring.recur_weekly_text, tournamentRecurring.location],
                labels: ['pending']
            })
            .selectTournamentAction('pending', tournamentRecurring.title, 'delete');

        browser.page.tournamentTable()
            .assertTable('deleted', tournamentRecurring.title, {
                texts: [tournamentRecurring.recur_weekly_text, regularLogin.username],
                labels: ['pending']
            })
            .selectTournamentAction('deleted', tournamentRecurring.title, 'remove');

    },
};
