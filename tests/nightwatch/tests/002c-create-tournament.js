module.exports = {

    beforeEach: function(browser) {
        browser.deleteCookies().windowMaximize('current');
    },

    after: function(browser) {
        browser.end();
    },

    /**
     * Navigate to Organize page
     * Login with NRDB (regular user)
     * Validate login, click Create Tournament
     * Validate tournament form, fill out form with multi-day, concluded, online tournament data
     * Save tournament, validate tournament details page
     * Navigate to Organize page, validate entry on table of created tournaments
     * Navigate to Results page, check results table
     */
    'Create multi-day, online tournament (concluded)': function (browser) {

        var regularLogin = browser.globals.accounts.regularLogin,
            adminLogin = browser.globals.accounts.adminLogin,
            tournamentOnlineConcluded = JSON.parse(JSON.stringify(browser.globals.tournaments.tournamentOnlineConcluded)); // clone

        tournamentOnlineConcluded.title = browser.currentTest.module.substring(0,3) + "|" +
            browser.currentTest.name.substring(0,29) + "|" + tournamentOnlineConcluded.title.substring(0, 16);

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

        // validate tournament form, fill out form with multi-day, concluded, online tournament data
        browser.log('* Validate tournament form, fill out form with multi-day, concluded, online tournament data *');
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                inputs: {
                    title: tournamentOnlineConcluded.title,
                    date: tournamentOnlineConcluded.date,
                    start_time: tournamentOnlineConcluded.start_time,
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
                    decklist: tournamentOnlineConcluded.decklist
                }
            });
        browser.page.mainMenu().acceptCookies(); // cookies info is in the way
        browser.page.tournamentForm()
            .fillForm({
                checkboxes: {
                    decklist: tournamentOnlineConcluded.decklist,
                    concluded: tournamentOnlineConcluded.concluded
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
                time: tournamentOnlineConcluded.start_time,
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
                registerButton: false,
                showMatches: false,
                showPoints: false,
                chartRunnerIds: true,
                chartCorpIds: true
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
                    start_time: tournamentOnlineConcluded.start_time,
                    contact: tournamentOnlineConcluded.contact,
                    players_number: tournamentOnlineConcluded.players_number,
                    end_date: tournamentOnlineConcluded.end_date
                },
                textareas: { description: tournamentOnlineConcluded.description },
                selects: {
                    tournament_type_id: tournamentOnlineConcluded.tournament_type_id,
                    tournament_format_id: tournamentOnlineConcluded.tournament_format_id,
                    cardpool_id: tournamentOnlineConcluded.cardpool_id,
                    top_number: tournamentOnlineConcluded.top_number
                },
                radios: {
                    date_type_id: tournamentOnlineConcluded.date_type_id
                },
                checkboxes: {
                    decklist: tournamentOnlineConcluded.decklist,
                    concluded: tournamentOnlineConcluded.concluded
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

        // data cleanup, delete tournament
        browser.sqlDeleteTournament(tournamentOnlineConcluded.title, browser.globals.database.connection);

    },

    /**
     * Navigate to Organize pag
     * Login with NRDB (regular user)
     * Validate login, click Create Tournament
     * Fill date, end date (earlier than start date), submit, check for validation errors
     * Fix end date > date, set conclusion, submit, check for validation errors
     * Fix end date > date, set conclusion, submit, wrong player number, check for validation errors
     * @param browser
     */
    'Tournament form validation': function (browser) {

        var regularLogin = browser.globals.accounts.regularLogin,
            tournamentOnlineConcluded = browser.globals.tournaments.tournamentOnlineConcluded;

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

        // Fill date, end date (earlier than start date), submit, check for validation errors
        browser.log('* Fill date, end date (earlier than start date), submit, check for validation errors *');
        browser.page.tournamentForm()
            .validate()
            .fillForm({
                radios: {
                    date_type_id: tournamentOnlineConcluded.date_type_id
                }
            })
            .fillForm({
                inputs: {
                    date: tournamentOnlineConcluded.end_date,
                    end_date: tournamentOnlineConcluded.date
                }
            });
        browser.page.mainMenu().acceptCookies(); // cookies info is in the way
        browser.page.tournamentForm()
            .getLocationInView('@submit_button').click('@submit_button')
            .assertForm({
                errors: [
                    'The location city field is required.',
                    'The location country field is required.',
                    'End date should be later than (start) date.'
                ]
            });

        // Fix end date > date, set conclusion, submit, wrong player number, check for validation errors
        browser.log('* Fix end date > date, set conclusion, wrong player number, submit, check for validation errors *');
        browser.page.tournamentForm()
            .fillForm({
                inputs: {
                    date: tournamentOnlineConcluded.date,
                    end_date: tournamentOnlineConcluded.end_date
                },
                checkboxes: {
                    concluded: tournamentOnlineConcluded.conclusion
                }
            })
            .getLocationInView('@submit_button').click('@submit_button')
            .assertForm({
                errors: [
                    'The location city field is required.',
                    'The location country field is required.',
                    'The players number field is required.'
                ]
            });

        // Input less players than top cut, check for validation errors
        browser.log('* Input less players than top cut, check for validation errors *');
        browser.page.tournamentForm()
            .fillForm({
                inputs: {
                    players_number: tournamentOnlineConcluded.players_number_wrong
                },
                selects: {
                    top_number: tournamentOnlineConcluded.top
                }
            })
            .getLocationInView('@submit_button').click('@submit_button')
            .assertForm({
                errors: [
                    'The location city field is required.',
                    'The location country field is required.',
                    'Players in top cut should be less than the total number of players.'
                ]
            });
    }
};
