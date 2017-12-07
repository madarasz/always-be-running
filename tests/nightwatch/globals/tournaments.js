// single day tournament (future date)
var tournamentSingleDay = {
    title: formatDate(new Date()) + ' Test Single Day',
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
    date_type_id: 'end-date-single',
    location_input: 'Budapest metagame',
    location: 'Hungary, Budapest',
    country: 'Hungary',
    state: '',
    city: 'Budapest',
    store: 'Metagame Kártya-, és Társasjáték Bolt',
    address: 'Budapest, Kádár u. 10, 1132 Hungary',
    location_place_id: 'ChIJIaFnNgzcQUcRnH7g2gqy2Xk',
    location_lat: '47.511667',
    location_long: '19.054372000000058'
};
// recurring tournament
var tournamentRecurring = {
    title: formatDate(new Date()) + ' Test Recurring',
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
    recur_weekly_id: '3',
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
// online, multi-day concluded tournament
var tournamentOnlineConcluded = {
    title: formatDate(new Date()) + ' - Test Multi-Day',
    type: 'online event',
    type_id: '7',
    cardpool: 'Terminal Directive',
    cardpool_id: 'td',
    format: '1.1.1.1',
    format_id: '3',
    decklist: true,
    contact: 'alwaysberunning@gmail.com',
    description: 'description online',
    conclusion: true,
    players_number: '22',
    players_number_wrong: '2',
    top: 'top 4',
    top_number: '4',
    date: '2017.01.01.',
    end_date: '2017.01.05.',
    time: '11:40',
    date_type: 'multiple',
    date_type_id: 'end-date-multiple'
};
//  nrtm-without-topcut.json values
var tournamentNrtmJsonWithoutTopCut = {
    old_title: 'Netrunner-Turnier',
    title: formatDate(new Date()) + ' - Test NRTM.json without top-cut',
    type: 'online event',
    type_id: '7',
    cardpool: 'Terminal Directive',
    cardpool_id: 'td',
    players_number: '9',
    top: '- no elimination rounds -',
    top_number: '0',
    date: '2017.01.01.',
    conclusion: true,
    imported_results: {
        swiss: [
            { rank: 1, player: 'Jörg', corp_title: 'Skorpios Defense Systems', runner_title: 'Hayley Kaplan' },
            { rank: 2, player: 'Dominik', corp_title: 'Controlling the Message', runner_title: 'Armand "Geist" Walker' },
            { rank: 3, player: 'Jan', corp_title: 'Gagarin Deep Space', runner_title: 'Armand "Geist" Walker' },
            { rank: 4, player: 'René', corp_title: 'Controlling the Message', runner_title: 'Hayley Kaplan' },
            { rank: 5, player: 'Volker', corp_title: 'Personal Evolution', runner_title: 'Steve Cambridge' },
            { rank: 6, player: 'Luis', corp_title: 'Near-Earth Hub', runner_title: 'Hayley Kaplan' },
            { rank: 7, player: 'Fabian', corp_title: 'Cerebral Imaging', runner_title: 'Sunny Lebeau' },
            { rank: 8, player: 'Tim', corp_title: 'AgInfusion', runner_title: 'Rielle "Kit" Peddler' },
            { rank: 9, player: 'Gereon', corp_title: 'Spark Agency', runner_title: 'Ele "Smoke" Scovak' }
        ],
        swiss_rounds: 3,
        bye: true,
        points: [
            { player: 'Jörg', points: '18', sos: '3.250', esos: '3.479' }
        ]
    }
};

function formatDate(date) {
    var year = date.getFullYear(),
        month = date.getMonth() + 1, // months are zero indexed
        day = date.getDate(),
        hour = date.getHours(),
        minute = date.getMinutes(),
        dayFormatted = day < 10 ? "0" + day : day,
        monthFormatted = month < 10 ? "0" + month : month,
        hourFormatted = hour < 10 ? "0" + hour : hour,
        minuteFormatted = minute < 10 ? "0" + minute : minute;

    return year + "." + monthFormatted + "." + dayFormatted + " " + hourFormatted + ":" + minuteFormatted;
}

module.exports = {
    tournamentSingleDay: tournamentSingleDay,
    tournamentRecurring: tournamentRecurring,
    tournamentOnlineConcluded: tournamentOnlineConcluded,
    tournamentNrtmJsonWithoutTopCut: tournamentNrtmJsonWithoutTopCut
};