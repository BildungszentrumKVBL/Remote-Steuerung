# Remote-Steuerung [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/BildungszentrumKVBL/Remote-Steuerung/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/BildungszentrumKVBL/Remote-Steuerung/?branch=master)

Classrooms and computer controlling software by the Bildungszentrum kvBL in Liestal Switzerland. 


## Features

### Observation in Real-Time

![Observing](./doc/gif/demo.gif)

### Easy switching between views

![Switching Views](./doc/gif/switch-view.gif)


## Installation

1. Prepare a webserver for the application.
2. Depending on your network infrastructure, it also can be multi-homed. (Make sure to add routes if needed.)
3. Go to a desired location for the website.
4. Clone this repository. `git clone https://github.com/BildungszentrumKVBL/Remote-Steuerung.git`
5. Create a virtual-host and set the `DocumentRoot` to the `/web`-Directory inside the cloned folder.
6. Create a rewrite rule for this virtual-host to redirect all requests to `app.php`.
   ```apacheconf
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^(.*)$ app_dev.php [QSA,L]
   </IfModule>
   ```
   You might have to do some research, depending on your webserver.
7. Check your PHP installation. `php app/SymfonyRequirements.php`
   If there are missing libraries or plugins, install them accordingly.
8. Install dependencies and configure variables. `composer install --optimize-autoloader`
   The variables asked during the installation can be changed later in `/app/config/parameters.yml`.
9. Create the database. `php app/console doctrine:database:create`
10. Create the schema. `php app/console doctrine:schame:create`
11. Install fixtures. `php app/console doctrine:fixtures:load -n`
[//]: # (uglifyjs2 and uglifycss maybe do not need to be install, because they are in the git repository. We need to check that.)
12. Install `nodejs`, `npm`, `bower`, `uglifyjs2` and `uglifycss`.
    ```bash
    sudo apt-get install nodejs
    sudo apt-get install npm
    ```
13. Install JavaScripts and CSS files. `bower install -F`
14. Dump website assets. `php app/console assetic:dump web -e=prod`
15. Create an admin-account. `php app/console app:create:admin`
    To reset the password, use this command. `php app/console app:create:admin --change-password`
16. Import your infrastructure. `php app/console app:import:infrastructure `


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



## License

CC-BY-NC-3.0

[Attribution-NonCommercial 3.0 Unported](https://creativecommons.org/licenses/by-nc/3.0/legalcode)


## FAQ & Support

Q: I found a bug.

A: Please create an issue on Github and describe the problem.

---

Q: We have a special wish/idea for this application.

A: For custom features or installations, please contact [ashura@protonmail.ch](ashura@protonmail.ch).
