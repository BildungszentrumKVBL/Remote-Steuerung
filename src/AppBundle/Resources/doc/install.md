Base installation
=================

1. Prepare a webserver for the application. Apache2, Nginx, lighttpd or IIS should work fine.
2. Depending on your network infrastructure, it also can be multi-homed. (Make sure to add routes if needed.)
3. Make sure you have the following packages installed:
    ```bash
    apt-get install git
    apt-get install mysql # *
    apt-get install php-ldap
    apt-get install php-dom
    apt-get install php-mbstring
    apt-get install php-zip
    apt-get install composer
    ```
    \* Or similar. (Postgres, mariadb, and even mssql should work fine. Infos are [here](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html))
    You can change the driver in `app/config/config.yml` under doctrine > dbal > driver.
    For MongoDB, the application needs some changes in the ORM mapping.
4. Go to a desired location for the website.
6. Set owners and rights. 
    ```bash
    # Example for apache2
    chown -R www-data:www-data YOUR_DOCUMENT_ROOT
    chmod -R 775 YOUR_DOCUMENT_ROOT
    usermod -a -G www-data YOUR_WEB_USER
    ```
7. Create a virtual-host and set the `DocumentRoot` to the `/web`-Directory inside the cloned folder.
8. Create a rewrite rule for this virtual-host to redirect all requests to `app.php`. [Infos here](https://symfony.com/doc/current/setup/web_server_configuration.html).
    ```apacheconf
    # Simplyfied example for apache2
    # Make sure to activate mod_rewrite: a2enmod rewrite
    <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^(.*)$ /app.php [QSA,L]
    </IfModule>
    ```
   You might have to do some research, depending on your webserver.
9. Check your PHP installation. `php app/check.php`
  If there are missing libraries or plugins, install them accordingly.
10. Install dependencies and configure variables. `composer install --optimize-autoloader`
   The variables asked during the installation can be changed later in `/app/config/parameters.yml`.
   KEEP IN MIND: to set a variable to `null` in YAML, you have to use the tilde-character `~`.
11. Create the database. `php app/console doctrine:database:create`
12. Create the schema. `php app/console doctrine:schema:create`
13. Install fixtures. `php app/console doctrine:fixtures:load -n`
14. Install `nodejs`, `node-sass`, `npm` and `bower`.
    ```bash
    sudo apt-get install nodejs
    sudo apt-get install npm
    sudo npm install -g node-sass # Eventually you have to use: npm install --unsafe-perm -g node-sass
    sudo npm install -g bower # Eventually you have to use: npm install --unsafe-perm -g bower
    ```
15. Install `uglify-js` and `uglifycss` inside the project.
    ```bash
    cd bin
    npm install uglify-js
    npm install uglifycss
    cd ..
    ```
16. Install JavaScripts and CSS dependencies. `bower install -F`
17. Generate CSS files. `./generate_css.sh`
18. Dump website assets. `php app/console assetic:dump web -e=prod`
19. Reset permissions.
    ```bash
    # Example for apache2
    chown -R www-data:www-data YOUR_DOCUMENT_ROOT
    chmod -R 775 YOUR_DOCUMENT_ROOT
    ```
20. Create an admin-account. `php app/console app:create:admin`
    To reset the password, use this command. `php app/console app:create:admin --change-password`
21. [Create your infrastructure](infrastructure.md) and it. `php app/console app:import:infrastructure`
22. Start your websocket server.