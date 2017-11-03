module.exports = {

    beforeEach: function(browser) {
        browser.deleteCookies();
    },

    after: function(browser) {
        browser.end();
    },

    /***
     * - Navigate to Organize page
     * - Login with NRDB (regular user)
     * - Validate login, click Create Tournament
     * - Validate tournament form, fill out form with single day tournament data
     * - Validate that location is found and correct
     * - Save tournament, validate tournament details page
     * - Click Update button, verify tournament form, click Cancel
     * - Navigate to Organize page, validate entry on table of created tournaments
     * - Navigate to Upcoming page, check upcoming tournaments table
     * - Logout
     * - Login as admin, hard delete tournament
     */
    'Create single day tournament (future date)': function (browser) {

        var regularLogin = browser.globals.regularLogin,
            adminLogin = browser.globals.adminLogin,
            tournamentSingleDay = browser.globals.tournamentSingleDay;

        // open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // navigate to Organize page
        browser.log('* Navigate to Organize page *');
        browser.page.mainMenu()
            .selectMenu('organize');

        browser.page.organizePage().validate(false);

        // login with NRDB (regular user)
        browser.log('* Login with NRDB (regular user) *');
        browser.login(regularLogin.username, regularLogin.password);

        // validate login, click Create Tournament
        browser.log('* Validate login, click Create Tournament *');
        browser.page.organizePage()
            .validate(true)
            .clickCommand('createTournament');

        // validate tournament form, fill out form with single day tournament data
        browser.log('* Validate tournament form, fill out form with single day tournament data *');
        browser.page.tournamentForm()
            .validate()
            .assertForm({
                visible: ['players_number_disabled', 'map_loaded', 'overlay_recurring'],
                not_visible: ['overlay_conclusion', 'overlay_location', 'overlay_cardpool']
            })
            .fillForm({
                inputs: {
                    title: tournamentSingleDay.title,
                    date: tournamentSingleDay.date,
                    link_facebook: tournamentSingleDay.facebook,
                    start_time: tournamentSingleDay.time,
                    contact: tournamentSingleDay.contact
                },
                textareas: {description: tournamentSingleDay.description},
                selects: {
                    tournament_type_id: tournamentSingleDay.type,
                    tournament_format_id: tournamentSingleDay.format,
                    cardpool_id: tournamentSingleDay.cardpool
                },
                radios: {
                    date_type_id: tournamentSingleDay.date_type_id
                },
                checkboxes: {
                    decklist: tournamentSingleDay.decklist,
                    concluded: tournamentSingleDay.conclusion
                }
            })
            .fillForm({
                location: tournamentSingleDay.location_input
            });

        // validate that location is found and correct
        browser.log('* Validate that location is found and correct *');
        browser.page.tournamentForm()
            .assertForm({
                visible: ['location_country', 'location_city', 'location_store', 'location_address'],
                not_present: ['location_state']
            })
            .validateLocation({
                location_country: tournamentSingleDay.country,
                location_city: tournamentSingleDay.city,
                location_store: tournamentSingleDay.store,
                location_address: tournamentSingleDay.address,
                location_place_id: tournamentSingleDay.location_place_id,
                location_lat: tournamentSingleDay.location_lat,
                location_long: tournamentSingleDay.location_long
            });

        // save tournament, validate tournament details page
        browser.log('* Save tournament, validate tournament details page *');
        browser.page.mainMenu().click('@acceptCookies'); // cookies info is in the way
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentSingleDay.title,
                ttype: tournamentSingleDay.type,
                tformat: tournamentSingleDay.format,
                creator: regularLogin.username,
                description: tournamentSingleDay.description,
                facebookGroup: tournamentSingleDay.facebook,
                date: tournamentSingleDay.date,
                time: tournamentSingleDay.time,
                cardpool: tournamentSingleDay.cardpool,
                location: tournamentSingleDay.location,
                store: tournamentSingleDay.store,
                address: tournamentSingleDay.address,
                contact: tournamentSingleDay.contact,
                map: true,
                decklist: true,
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
                registerButton: true
            });

        // click Update button, verify tournament form, click Cancel
        browser.log('* Click Update button, verify tournament form, click Cancel *');
        browser.page.tournamentView().click('@editButton');
        browser.page.tournamentForm()
            .validate()
            .assertForm({
                visible: ['players_number_disabled', 'map_loaded', 'overlay_recurring',
                    'location_country', 'location_city', 'location_store', 'location_address'],
                not_present: ['location_state'],
                not_visible: ['overlay_location', 'overlay_cardpool', 'overlay_conclusion'],
                inputs: {
                    title: tournamentSingleDay.title,
                    date: tournamentSingleDay.date,
                    link_facebook: tournamentSingleDay.facebook,
                    start_time: tournamentSingleDay.time,
                    contact: tournamentSingleDay.contact
                },
                textareas: {description: tournamentSingleDay.description},
                selects: {
                    tournament_type_id: tournamentSingleDay.type_id,
                    tournament_format_id: tournamentSingleDay.format_id,
                    cardpool_id: tournamentSingleDay.cardpool_id
                },
                radios: {
                    date_type_id: tournamentSingleDay.date_type_id
                },
                checkboxes: {
                    decklist: tournamentSingleDay.decklist,
                    concluded: tournamentSingleDay.conclusion
                }
            })
            .validateLocation({
                location_country: tournamentSingleDay.country,
                location_city: tournamentSingleDay.city,
                location_store: tournamentSingleDay.store,
                location_address: tournamentSingleDay.address,
                location_place_id: tournamentSingleDay.location_place_id,
                location_lat: tournamentSingleDay.location_lat,
                location_long: tournamentSingleDay.location_long
            })
            .click('@cancel_button');

        // navigate to Organize page, validate entry on table of created tournaments
        browser.log('* Navigate to Organize page, validate entry on table of created tournaments *');
        browser.page.mainMenu()
            .selectMenu('organize');
        browser.page.tournamentTable().assertTable('created', tournamentSingleDay.title, {
            texts: [tournamentSingleDay.date, tournamentSingleDay.cardpool, tournamentSingleDay.location],
            labels: ['pending', 'not yet']
        });

        // navigate to Upcoming page, check upcoming tournaments table
        browser.log('* Navigate to Upcoming page, check upcoming tournaments table *');
        browser.page.mainMenu()
            .selectMenu('upcoming');
        browser.page.tournamentTable().assertTable('discover-table', tournamentSingleDay.title, {
            texts: [tournamentSingleDay.date, tournamentSingleDay.cardpool, tournamentSingleDay.location, tournamentSingleDay.type],
        });

        // logout
        browser.log('* Logout *');
        browser.page.mainMenu().selectMenu('logout');

        // login as admin, hard delete tournament
        browser.log('* Login with NRDB (admin user), hard delete tournament *');
        browser.login(adminLogin.username, adminLogin.password);
        browser.page.mainMenu().selectMenu('admin');
        browser.page.tournamentTable()
            .assertTable('pending', tournamentSingleDay.title, {
                texts: [tournamentSingleDay.date, tournamentSingleDay.cardpool, tournamentSingleDay.location],
                labels: ['pending']
            })
            .selectTournamentAction('pending', tournamentSingleDay.title, 'delete');
        browser.page.tournamentTable()
            .assertTable('deleted', tournamentSingleDay.title, {
                texts: [tournamentSingleDay.date, regularLogin.username],
                labels: ['pending']
            })
            .selectTournamentAction('deleted', tournamentSingleDay.title, 'remove');

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

        var regularLogin = browser.globals.regularLogin,
            adminLogin = browser.globals.adminLogin,
            tournamentRecurring = browser.globals.tournamentRecurring;

        // open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // navigate to Organize page
        browser.log('* Navigate to Organize page *');
        browser.page.mainMenu()
            .selectMenu('organize');

        browser.page.organizePage().validate(false);

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
                    link_facebook: tournamentRecurring.facebook,
                    start_time: tournamentRecurring.time,
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
                    recur_weekly: tournamentRecurring.recur_weekly
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
                location_country: tournamentRecurring.country,
                location_city: tournamentRecurring.city,
                location_place_id: tournamentRecurring.location_place_id,
                location_lat: tournamentRecurring.location_lat,
                location_long: tournamentRecurring.location_long
            });

        // save tournament, validate tournament details page
        browser.log('* Save tournament, validate tournament details page *');
        browser.page.mainMenu().click('@acceptCookies'); // cookies info is in the way
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentRecurring.title,
                ttype: tournamentRecurring.type,
                creator: regularLogin.username,
                description: tournamentRecurring.description,
                facebookEvent: tournamentRecurring.facebook,
                date: 'recurring: ' + tournamentRecurring.recur_weekly,
                time: tournamentRecurring.time,
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
                registerButton: true
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
                    link_facebook: tournamentRecurring.facebook,
                    start_time: tournamentRecurring.time,
                    contact: tournamentRecurring.contact
                },
                textareas: { description: tournamentRecurring.description },
                selects: {
                    tournament_type_id: tournamentRecurring.type_id,
                    tournament_format_id: tournamentRecurring.format_id,
                    recur_weekly: tournamentRecurring.recur_weekly_id
                },
                radios: {
                    date_type_id: tournamentRecurring.date_type_id
                },
                checkboxes: {
                    decklist: tournamentRecurring.decklist
                }
            })
            .validateLocation({
                location_country: tournamentRecurring.country,
                location_city: tournamentRecurring.city,
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
            texts: [tournamentRecurring.recur_weekly, tournamentRecurring.location],
            labels: ['pending', 'not yet']
        });

        // navigate to Upcoming page, check recurring tournaments table
        browser.log('* Navigate to Upcoming page, check recurring tournaments table *');
        browser.page.mainMenu()
            .selectMenu('upcoming');
        browser.page.tournamentTable().assertTable('recur-table', tournamentRecurring.title, {
            texts: [tournamentRecurring.recur_weekly, tournamentRecurring.location]
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
                texts: [tournamentRecurring.recur_weekly, tournamentRecurring.location],
                labels: ['pending']
            })
            .selectTournamentAction('pending', tournamentRecurring.title, 'delete');

        browser.page.tournamentTable()
            .assertTable('deleted', tournamentRecurring.title, {
                texts: [tournamentRecurring.recur_weekly, regularLogin.username],
                labels: ['pending']
            })
            .selectTournamentAction('deleted', tournamentRecurring.title, 'remove');

    },

    /**
     * Navigate to Organize page
     * Login with NRDB (regular user)
     * Validate login, click Create Tournament
     * Validate tournament form, fill out form with multi-day, concluded, online tournament data
     * Save tournament, validate tournament details page
     * Navigate to Organize page, validate entry on table of created tournaments
     * Navigate to Results page, check results table
     * Logout
     * Login with NRDB (admin user), hard delete tournament
     */
    'Create multi-day, online tournament (concluded)': function (browser) {

        var regularLogin = browser.globals.regularLogin,
            adminLogin = browser.globals.adminLogin,
            tournamentOnlineConcluded = browser.globals.tournamentOnlineConcluded;

        // open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // navigate to Organize page
        browser.log('* Navigate to Organize page *');
        browser.page.mainMenu()
            .selectMenu('organize');

        browser.page.organizePage().validate(false);

        // login with NRDB (regular user)
        browser.log('* Login with NRDB (regular user) *');
        browser.login(regularLogin.username, regularLogin.password);

        // validate login, click Create Tournament
        browser.log('* Validate login, click Create Tournament *');
        browser.page.organizePage()
            .validate(true)
            .clickCommand('createTournament');

        // validate tournament form, fill out form with multi-day, concluded, online tournament data
        browser.log('* Validate tournament form, fill out form with multi-day, concluded, online tournament data *');
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                inputs: {
                    title: tournamentOnlineConcluded.title,
                    date: tournamentOnlineConcluded.date,
                    start_time: tournamentOnlineConcluded.time,
                    contact: tournamentOnlineConcluded.contact
                },
                textareas: {description: tournamentOnlineConcluded.description},
                selects: {
                    tournament_type_id: tournamentOnlineConcluded.type,
                    tournament_format_id: tournamentOnlineConcluded.format,
                    cardpool_id: tournamentOnlineConcluded.cardpool
                },
                radios: {
                    date_type_id: tournamentOnlineConcluded.date_type_id
                },
                checkboxes: {
                    decklist: tournamentOnlineConcluded.decklist,
                    concluded: tournamentOnlineConcluded.conclusion
                }
            })
            .assertForm({
                visible: ['overlay_location', 'overlay_recurring'],
                not_visible: ['overlay_cardpool', 'overlay_conclusion']
            })
            .fillForm({
                inputs: {
                    players_number: tournamentOnlineConcluded.players_number,
                    end_date: tournamentOnlineConcluded.end_date
                },
                selects: {
                    top_number: tournamentOnlineConcluded.top
                }
            });

        // save tournament, validate tournament details page
        browser.log('* Save tournament, validate tournament details page *');
        browser.page.mainMenu().click('@acceptCookies'); // cookies info is in the way
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');
        browser.page.tournamentView()
            .validate()
            .assertView({
                title: tournamentOnlineConcluded.title,
                ttype: tournamentOnlineConcluded.type,
                tformat: tournamentOnlineConcluded.format,
                creator: regularLogin.username,
                description: tournamentOnlineConcluded.description,
                date: tournamentOnlineConcluded.date.slice(0, -1) + ' - ' + tournamentOnlineConcluded.end_date,
                time: tournamentOnlineConcluded.time,
                cardpool: tournamentOnlineConcluded.cardpool,
                contact: tournamentOnlineConcluded.contact,
                map: false,
                decklist: true,
                approvalNeed: true,
                editButton: true,
                approveButton: false,
                rejectButton: false,
                deleteButton: true,
                transferButton: true,
                featureButton: false,
                conflictWarning: false,
                playerNumbers: true,
                topPlayerNumbers: true,
                suggestLogin: false,
                buttonNRTMimport: true,
                buttonNRTMclear: false,
                buttonConclude: false,
                playerClaim: false,
                buttonClaim: true,
                removeClaim: false,
                claimError: false,
                topEntriesTable: true,
                swissEntriesTable: true,
                ownClaimInTable: false,
                conflictInTable: false,
                dueWarning: false,
                registeredPlayers: false,
                noRegisteredPlayers: true,
                unregisterButton: false,
                registerButton: false
            });

        // click Update button, verify tournament form, click Cancel
        browser.log('* Click Update button, verify tournament form, click Cancel *');
        browser.page.tournamentView().click('@editButton');
        browser.page.tournamentForm()
            .validate()
            .assertForm({
                visible: ['map_loaded', 'overlay_recurring', 'overlay_location'],
                not_present: ['players_number_disabled',
                    'location_state', 'location_country', 'location_city', 'location_store', 'location_address'],
                not_visible: ['overlay_cardpool', 'overlay_conclusion'],
                inputs: {
                    title: tournamentOnlineConcluded.title,
                    date: tournamentOnlineConcluded.date,
                    start_time: tournamentOnlineConcluded.time,
                    contact: tournamentOnlineConcluded.contact,
                    players_number: tournamentOnlineConcluded.players_number,
                    end_date: tournamentOnlineConcluded.end_date
                },
                textareas: { description: tournamentOnlineConcluded.description },
                selects: {
                    tournament_type_id: tournamentOnlineConcluded.type_id,
                    tournament_format_id: tournamentOnlineConcluded.format_id,
                    cardpool_id: tournamentOnlineConcluded.cardpool_id,
                    top_number: tournamentOnlineConcluded.top_number
                },
                radios: {
                    date_type_id: tournamentOnlineConcluded.date_type_id
                },
                checkboxes: {
                    decklist: tournamentOnlineConcluded.decklist,
                    concluded: tournamentOnlineConcluded.conclusion
                }
            })
            .click('@cancel_button');

        // navigate to Organize page, validate entry on table of created tournaments
        browser.log('* Navigate to Organize page, validate entry on table of created tournaments *');
        browser.page.mainMenu()
            .selectMenu('organize');
        browser.page.tournamentTable().assertTable('created', tournamentOnlineConcluded.title, {
            texts: [tournamentOnlineConcluded.date, tournamentOnlineConcluded.cardpool, 'online'],
            labels: ['pending', 'concluded'],
            multi_day: true
        });

        // navigate to Results page, check results table
        browser.log('* Navigate to Results page, check results table *');
        browser.page.mainMenu()
            .selectMenu('results');
        browser.page.tournamentTable().assertTable('results', tournamentOnlineConcluded.title, {
            texts: [tournamentOnlineConcluded.date, tournamentOnlineConcluded.cardpool, 'online',
                tournamentOnlineConcluded.cardpool, tournamentOnlineConcluded.players_number]
        });

        // logout
        browser.log('* Logout *');
        browser.page.mainMenu().selectMenu('logout');

        // login as admin, hard delete tournament
        browser.log('* Login with NRDB (admin user), hard delete tournament *');
        browser.login(adminLogin.username, adminLogin.password);
        browser.page.mainMenu().selectMenu('admin');
        browser.page.tournamentTable()
            .assertTable('pending', tournamentOnlineConcluded.title, {
                texts: [tournamentOnlineConcluded.date, tournamentOnlineConcluded.cardpool, 'online'],
                labels: ['pending'],
                multi_day: true
            })
            .selectTournamentAction('pending', tournamentOnlineConcluded.title, 'delete');
        browser.page.tournamentTable()
            .assertTable('deleted', tournamentOnlineConcluded.title, {
                texts: [tournamentOnlineConcluded.date, regularLogin.username],
                labels: ['concluded'],
                multi_day: true
            })
            .selectTournamentAction('deleted', tournamentOnlineConcluded.title, 'remove');

    }
};
