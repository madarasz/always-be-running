// creates tournament via SQL call
exports.command = function (tournamentData, sqlDB, creatorId, callback) {
    var client = this,
        mysql = require('mysql'),
        connection = mysql.createConnection(sqlDB),
        fieldList = ['title', 'date', 'start_time', 'location_country', 'location_state', 'contact', 'location_city',
            'location_store', 'location_address', 'location_place_id', 'players_number', 'top_number', 'description',
            'concluded', 'decklist', 'conflict', 'creator', 'approved', 'cardpool_id', 'tournament_type_id',
            'created_at', 'updated_at', 'deleted_at', 'import', 'location_lat', 'location_long', 'recur_weekly',
            'charity', 'incomplete', 'link_facebook', 'featured', 'tournament_format_id', 'end_date',
            'concluded_by', 'concluded_at'],
        queryFields = "creator, ",
        queryData = creatorId + ", ";

    client.log("*** SQL: Deleting tournament: " + tournamentData.title + " ***");

    connection.connect();

    // build query
    for (var i = 0; i < fieldList.length; i++) {
        if (tournamentData.hasOwnProperty(fieldList[i])) {

            queryFields = queryFields + fieldList[i] + ", ";

            if (typeof(tournamentData[fieldList[i]]) === "boolean") {
                queryData = queryData + (tournamentData[fieldList[i]] ? "1" : "0") + ", ";
            } else if (typeof(tournamentData[fieldList[i]]) === "string") {
                queryData = queryData + "'" + tournamentData[fieldList[i]] + "', ";
            } else {
                queryData = queryData + tournamentData[fieldList[i]] + ", ";
            }

        }
    }

    // mandatory created at
    if (!tournamentData.hasOwnProperty('created_at')) {
        queryFields = queryFields + 'created_at, ';
        queryData = queryData + "'" + tournamentData.title.slice(34) +  ":00', ";
    }

    // concluded tournament
    if (!tournamentData.hasOwnProperty('concluded_by') && !tournamentData.hasOwnProperty('concluded_at') &&
        tournamentData.hasOwnProperty('concluded') && tournamentData.concluded == true) {

        queryFields = queryFields + 'concluded_by, concluded_at, ';
        queryData = queryData + creatorId + ", '" + tournamentData.title.slice(34) +  ":00', ";
    }

    // cut ", " from the end
    queryFields = queryFields.slice(0, -2);
    queryData = queryData.slice(0, -2);

    client.log('--- fields: ' + queryFields);
    client.log('--- data: ' + queryData);

    // create tournament
    connection.query("INSERT INTO tournaments (" + queryFields + ") VALUES (" + queryData + ")",
        function (error, results, fields) {
            if (error) throw error;
        }
    );

    connection.end();

    if (typeof callback === "function"){
        callback.call(client);
    }

    return this;
};