var accounts = require('./accounts.js');
var tournaments = require('./tournaments.js');
var claims = require('./claims.js');

module.exports = {
    // TODO: do 'login' and 'tournament' object only
    adminLogin: accounts.adminLogin,
    regularLogin: accounts.regularLogin,
    emptyLogin: accounts.emptyLogin,
    tournamentSingleDay: tournaments.tournamentSingleDay,
    tournamentRecurring: tournaments.tournamentRecurring,
    tournamentOnlineConcluded: tournaments.tournamentOnlineConcluded,
    tournamentNrtmJsonWithoutTopCut: tournaments.tournamentNrtmJsonWithoutTopCut,
    claims: claims
};