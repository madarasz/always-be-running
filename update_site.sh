#!/usr/bin/env bash
php artisan down
git pull
php artisan migrate
npm install
npm run build
php artisan up
