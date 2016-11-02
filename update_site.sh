#!/usr/bin/env bash
php artisan down
git pull
php artisan migrate
gulp --production
php artisan up