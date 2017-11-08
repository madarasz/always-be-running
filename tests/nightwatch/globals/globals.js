var accounts = require('./accounts.js');
var tournaments = require('./tournaments.js');

module.exports = {
    adminLogin: accounts.adminLogin,
    regularLogin: accounts.regularLogin,
    emptyLogin: accounts.emptyLogin,
    tournamentSingleDay: tournaments.tournamentSingleDay,
    tournamentRecurring: tournaments.tournamentRecurring,
    tournamentOnlineConcluded: tournaments.tournamentOnlineConcluded
};