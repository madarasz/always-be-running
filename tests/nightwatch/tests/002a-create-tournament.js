module.exports = {

    beforeEach: function(browser) {
        browser.deleteCookies().windowMaximize('current');
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
     */
    'Create single day tournament (future date)': function (browser) {

        var regularLogin = browser.globals.accounts.regularLogin,
            adminLogin = browser.globals.accounts.adminLogin,
            tournamentSingleDay = JSON.parse(JSON.stringify(browser.globals.tournaments.tournamentSingleDay)); // clone

        tournamentSingleDay.title = browser.currentTest.module.substring(0,3) + "|" +
            browser.currentTest.name.substring(0,29) + "|" + tournamentSingleDay.title.substring(0, 16);

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
                    link_facebook: tournamentSingleDay.link_facebook,
                    start_time: tournamentSingleDay.start_time,
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
                    concluded: tournamentSingleDay.concluded
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
                location_country: tournamentSingleDay.location_country,
                location_city: tournamentSingleDay.location_city,
                location_store: tournamentSingleDay.location_store,
                location_address: tournamentSingleDay.location_address,
                location_place_id: tournamentSingleDay.location_place_id,
                location_lat: tournamentSingleDay.location_lat,
                location_long: tournamentSingleDay.location_long
            });

        // save tournament, validate tournament details page
        browser.log('* Save tournament, validate tournament details page *');
        browser.page.mainMenu().acceptCookies(); // cookies info is in the way
        browser.page.tournamentForm().getLocationInView('@submit_button').click('@submit_button');
        browser.page.tournamentView()
            .validate()
            .removeJson()
            .assertView({
                title: tournamentSingleDay.title,
                ttype: tournamentSingleDay.type,
                tformat: tournamentSingleDay.format,
                creator: regularLogin.username,
                description: tournamentSingleDay.description,
                facebookGroup: tournamentSingleDay.link_facebook,
                date: tournamentSingleDay.date,
                time: tournamentSingleDay.start_time,
                cardpool: tournamentSingleDay.cardpool,
                location: tournamentSingleDay.location,
                store: tournamentSingleDay.location_store,
                address: tournamentSingleDay.location_address,
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
                registerButton: true,
                chartRunnerIds: false,
                chartCorpIds: false
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
                    link_facebook: tournamentSingleDay.link_facebook,
                    start_time: tournamentSingleDay.start_time,
                    contact: tournamentSingleDay.contact
                },
                textareas: {description: tournamentSingleDay.description},
                selects: {
                    tournament_type_id: tournamentSingleDay.tournament_type_id,
                    tournament_format_id: tournamentSingleDay.tournament_format_id,
                    cardpool_id: tournamentSingleDay.cardpool_id
                },
                radios: {
                    date_type_id: tournamentSingleDay.date_type_id
                },
                checkboxes: {
                    decklist: tournamentSingleDay.decklist,
                    concluded: tournamentSingleDay.concluded
                }
            })
            .validateLocation({
                location_country: tournamentSingleDay.location_country,
                location_city: tournamentSingleDay.location_city,
                location_store: tournamentSingleDay.location_store,
                location_address: tournamentSingleDay.location_address,
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

        // data cleanup, delete tournament
        browser.sqlDeleteTournament(tournamentSingleDay.title, browser.globals.database.connection);

    },
};
