// deletes tournament via SQL call
exports.command = function (tournamentTitle, sqlDB, callback) {
    var client = this,
        mysql = require('mysql'),
        connection = mysql.createConnection(sqlDB);

    client.log("*** SQL: Deleting tournament: " + tournamentTitle + " ***");

    connection.connect();

    // delete tournament entries
    connection.query("DELETE FROM entries WHERE tournament_id IN (SELECT id FROM tournaments WHERE title='" + tournamentTitle + "')",
        function (error, results, fields) {
            if (error) throw error;
        }
    );

    // delete tournament videos
    connection.query("DELETE FROM videos WHERE tournament_id IN (SELECT id FROM tournaments WHERE title='" + tournamentTitle + "')",
        function (error, results, fields) {
            if (error) throw error;
        }
    );

    // delete tournament
    connection.query("DELETE FROM tournaments WHERE title='" + tournamentTitle + "'", function (error, results, fields) {
        if (error) throw error;
    });

    connection.end();

    if (typeof callback === "function"){
        callback.call(client);
    }

    return this;
};