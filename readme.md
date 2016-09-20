# AlwaysBeRunning.net (ABR)

### Installation (Linux/Mac)

You will need the following in order to run ABR locally:
- MySQL (preferably)
- PHP
- PHP Composer
- Node, NPM
- JQ - download via apt-get (Debian) or homebrew (Mac), this is NOT an npm module
- imagemagick

1. Clone ABR from GitHub
2. Install npm dependencies, install npm gulp globally

        npm install
        npm install -g gulp

3. Install PHP dependencies

        php composer.phar install

4. Configure the settings of your local environment. Rename the **.example.env** file to **.env**. Edit the DB settings to connect to your locally running DB. *Ask the main dev (madarasz / Necro) for NetrunnerDB and Google API keys*. Everything else should be fine.
5. Prepare ID icons by running this script (downloads from NetrunnerDB, run it regularly)

        ./get_id_images.sh

6. Prepare DB tables

        php artisan migrate
        php artisan db:seed

7. Run the webapp. It should be available at [http://localhost:8000](http://localhost:8000) afterwards.

        php artisan serve

8. Make yourself an admin. Go to the webapp in your browser. Login via NetrunnerDB to enter your user in the DB. Check your DB (use phpMyAdmin), in table **users** set the **admin** value of your user to 1. If you reload the webapp you should see the **Admin** section in the top menu.

9. Download all the data required from NetrunnerDB. Go to [Admin section](http://localhost:8000/admin) and click the **Update Card cycles**, **Update Card packs** and **Update Identities** buttons to get the data.

10. You are done :)