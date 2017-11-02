// user with admin rights and available published decklists
var adminLogin = {
    username: 'Necro',
    password: 'EXAMPLE'
};
// regular user with available published decklists
var regularLogin = {
    username: 'Necro2',
    password: 'EXAMPLE'
};
// user with no published decklists
var emptyLogin = {
    username: 'NecroEmpty',
    password: 'EXAMPLE'
};
// single day tournament
var tournamentSingleDay = {
    title: 'Test Single Day - ' + formatDate(new Date()),
    type: 'non-FFG tournament',
    type_id: '6',
    cardpool: 'Business First',
    cardpool_id: 'bf',
    format: 'cache refresh',
    format_id: '2',
    decklist: true,
    contact: '+66 666 666',
    facebook: 'https://www.facebook.com/groups/505519816175373/',
    description: 'description A',
    conclusion: false,
    date: '2999.01.01.',
    time: '12:40',
    date_type: 'single',
    location_input: 'Budapest metagame',
    location: 'Hungary, Budapest',
    country: 'Hungary',
    state: '',
    city: 'Budapest',
    store: 'Metagame Kártya-, és Társasjáték Bolt',
    address: 'Budapest, Kádár u. 10, 1132 Hungary',
    location_place_id: 'ChIJIaFnNgzcQUcRnH7g2gqy2Xk',
    location_lat: 47.511,
    location_long: 19.054
};
// recurring tournament
var tournamentRecurring = {
    title: 'Test Recurring - ' + formatDate(new Date()),
    type: 'non-tournament event',
    type_id: '8',
    format: 'standard',
    format_id: '1',
    decklist: false,
    contact: '+36 1333 333',
    facebook: 'https://www.facebook.com/events/1715147511863213',
    description: 'description recurring',
    time: '18:00',
    recur_weekly: 'Wednesday',
    date_type: 'recurring',
    date_type_id: 'end-date-recur',
    location_input: 'Barcelona',
    location: 'Spain, Barcelona',
    country: 'Spain',
    state: '',
    city: 'Barcelona',
    store: '',
    address: '',
    location_place_id: 'ChIJ5TCOcRaYpBIRCmZHTz37sEQ',
    location_lat: '41.38506389999999',
    location_long: '2.1734034999999494'
};


module.exports = {
    adminLogin: adminLogin,
    regularLogin: regularLogin,
    emptyLogin: emptyLogin,
    tournamentSingleDay: tournamentSingleDay,
    tournamentRecurring: tournamentRecurring
};