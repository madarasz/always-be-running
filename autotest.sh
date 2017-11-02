#!/usr/bin/env bash

# download selenium standalone jar if needed
if [ ! -f "./tests/nightwatch/bin/selenium-server-standalone-2.53.1.jar" ]; then
    echo "Downloading Selenium server"
    curl "http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar" > "./tests/nightwatch/bin/selenium-server-standalone-2.53.1.jar"
fi

nightwatch -c tests/nightwatch/nightwatch.json --env phantomjs