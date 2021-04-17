#!/bin/bash
# requires mysqldump (Ubuntu: apt-get install mysql-client)

mysqldump -h 127.0.0.1 -u root -p --port=3306 netrunner -t badges -t countries -t tournament_formats -t tournament_types --single-transaction --no-create-info > seed.sql