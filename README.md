# Remote-Steuerung [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/BildungszentrumKVBL/Remote-Steuerung/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/BildungszentrumKVBL/Remote-Steuerung/?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/a58b4a5a-12f5-444f-bc9c-98d399191502/mini.png)](https://insight.sensiolabs.com/projects/a58b4a5a-12f5-444f-bc9c-98d399191502)

Classrooms and computer controlling software by the Bildungszentrum kvBL in Liestal Switzerland. 


## Features

### Observation in Real-Time

![Observing](./doc/gif/demo.gif)

### Easy switching between views

![Switching Views](./doc/gif/switch-view.gif)


## Installation

1. Prepare a webserver for the application.
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
    You can change the driver in `app/config/config.yml` under doctrine > dbal > driver
4. Go to a desired location for the website.
5. Clone this repository. `git clone https://github.com/BildungszentrumKVBL/Remote-Steuerung.git`
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
    # Example for apache2
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
16. Install JavaScripts and CSS files. `bower install -F`
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
21. Import your infrastructure. `php app/console app:import:infrastructure `


## Creating an infrastructure-file

You can use either YAML, CSV or XML.

### YAML

YAML is a tree-like structure, but very picky about spacing. So please keep them consistent.

```yaml
'Main':
    'Room1':
        'Zulu': 'IP.Of.The.MicroController'
        'PC':
            'name': 'Reachable.Hostname.Or.IP'
            
    'AnotherRoom':
        'Zulu': 'IP.Of.This.MicroController'
        'PC':
            'name': 'Reachable.Hostname.Or.Ip'
            
'Another':
...
```

### CSV

Please make sure to label the parts in the CSV. And also keep the right order!
The CSV has to be separated with semicolons (;).

```csv
{{Building}}
Main
Another

{{Rooms}}
Main;Room1
Main;AnotherRoom
Another;...

{{PC}}
Room1;Reachable.Hostname.Or.IP
AnotherRoom;Reachable.Hostname.Or.Ip

{{Zulu}}
Room1;IP.Of.This.MicroController
AnotherARoom;IP.Of.This.MicroController
```

### XML

Keep in mind that a header is required for the XML data. Also, there is a limitation that does not allow rooms and buildings to contain spaces.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<root>
    <Main>
        <Room1>
            <Zulu>IP.Of.This.MicroController</Zulu>
            <PC>
                <name>Reachable.Hostname.Or.Ip</name>
            </PC>
        </Room1>
        <AnotherRoom>
            <Zulu>IP.Of.This.MicroController</Zulu>
            <PC>
                <name>Reachable.Hostname.Or.Ip</name>
            </PC>
        </AnotherRoom>
    </Main>
    <Another>
        ...
    </Another>
</root>
```


## Backup/Restore

Backing up the application is important, once you get it running like you want it.
Be aware, that a restore of the data will erase the data, that is not backed up.

### Backup

To backup your configuration and database, run the: `php backup`-command.
It will backup your `application.yml`, `parameters.yml` and your database into the `.bu`-folder.
The backup also remembers what version you where using.


### Restore

If you every have to restore one of your backup, your can use the: `php restore`-command.
It is recommended, to just look for the missing information in the `.bu`-folder, instead of running this command.


## Update

To keep up-to-date, first create a backup using the [backup](#backup)-command.
1. Update the code. `git pull`
2. Update new dependencies. `composer install --optimize-autoloader`
3. Check for database-updates. `php app/console doctrine:schema:update --dump-sql`
   If there are updates, you can apply them with using the `--force`-flag. `php app/console doctrine:schema:update --force`
4. Check if your settings are alright. `cat app/config/application.yml`
5. Generate new CSS-files. `./generate_css.sh`
6. Dump website assets. `php app/console assetic:dump web -e=prod`
7. Clear the cache. `php app/console cache:cl -e=prod`
8. Check if all works fine. If not, feel free to contact us.


## License

CC-BY-NC-3.0

[Attribution-NonCommercial 3.0 Unported](https://creativecommons.org/licenses/by-nc/3.0/legalcode)


## FAQ & Support

Q: I found a bug.

A: Please create an issue on Github and describe the problem.

---

Q: We have a special wish/idea for this application.

A: For custom features or installations, please contact [ashura@protonmail.ch](ashura@protonmail.ch).
