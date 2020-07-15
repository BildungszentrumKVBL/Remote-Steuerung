# Summary

This is the base for all upcoming symfony 4+ projects.

The webapp is written in PHP and uses the [Symfony](http://symfony.com) framework. 
It uses an MariaDB database for data storage. 
Simple deployments are utilizing [deployer](https://deployer.org) on shared hosting and advanced deployments use [Docker](https://www.docker.com) and [Kubernetes](https://kubernetes.io) on our Google Infrastructure.

Dev: ![coverage](https://gitlab.com/JKwebGmbH/symfony4-skeleton/badges/dev/coverage.svg?style=flat-square)

Master: ![coverage](https://gitlab.com/JKwebGmbH/symfony4-skeleton/badges/master/coverage.svg?style=flat-square)


# Table of Contents

- [General Information](#general-information)
- [Requirements](#requirements)
- [Install Locally](#install-locally)
    - [Create a new Project](#create-a-new-project)
        - [Initializing](#initializing)
        - [Installing Dependencies](#installing-dependencies)
        - [Project Structure](#project-structure)
        - [Webserver](#webserver)
        - [CSS and JS](#css-and-js)
        - [Database](#database)
            - [Migrations](#migrations)
    - [Upgrade from Skeleton]()
    - [Common Tasks after creating a new Project](#common-tasks-after-creating-a-new-project)
    - [Before committing](#before-committing)
- [Using DoctrineBehaviors](#using-doctrinebehaviors)
- [Writing Tests](#writing-tests)
- [Running Tests](#running-tests)
- [Deployment](#deployment)
    - [Deployer](#deployer)
        - [Deploy Project on new Server](#deploy-project-on-new-server)
            - [Firstly, connect to the server via SSH](#firstly-connect-to-the-server-via-ssh)
            - [Secondly, display the SSH key](#secondly-display-the-ssh-key)
            - [Lastly, configure the Deployment](#lastly-configure-the-deployment)
            - [Deploy](#deploy)
        - [Deploy on existing Server](#deploy-on-existing-server)
    - [Docker / Kubernetes](#docker--kubernetes)
- [Developers](#developers)


# General Information

This webapp is build with [Symfony](https://symfony.com). 
As dependency managers it uses:

 - [Composer](https://getcomposer.org) for PHP.
 - [Yarn](https://yarnpkg.com) for CSS and JavaScript libraries.
 - [Webpack](https://webpack.js.org) to build the CSS and JavaScript files.
 
The codebase is managed on [GitLab](https://gitlab.com/JKwebGmbH/symfony4-skeleton). The following branches are used
 
 - `master`:   Contains the codebase that is currently in production.
 - `dev`:      Contains the development version of the codebase. 
 - `feature/`: Contains the regular changes of each issue.
 - `hotfix/`:  Contains emergency changes for the `master`-branch.


# Requirements

- [Git](https://git-scm.com)
- PHP >= 7.3
- php-iconv
- php-posix
- php-intl
- php-mysql
- php-mbstring
- php-zip
- [Yarn](https://yarnpkg.com)
- [Webpack](https://webpack.js.org)
- [composer](https://getcomposer.org)


# Install Locally

In this section we show how to install the project locally for development.

Please ensure your development machine matches the [requirements](#requirements).

## Create a new Project

### Initializing

```bash
cd jkweb # Go into your jkweb folder.
``` 

```bash
git clone git@gitlab.com:JKwebGmbH/symfony4-skeleton.git PROJECT_NAME # Clone repository with a new name.
```

```bash
cd PROJECT_NAME # Enter project.
```

```bash
git remote rename origin skeleton # Remove connection to the current repository.
```

Go to GitLab and create the project, if the project hasn't been created.

```bash
git remote add origin git@gitlab.com:JKwebGmbH/PROJECT_NAME.git 
```

NOTE: You need to be Maintainer to do this. Please ask a Maintainer for guidance. 
```bash
git push -u origin dev
```

### Installing Dependencies

```bash
yarn # Installs npm packages.
```

```bash
yarn run encore dev         # Compiles the CSS and JavaScript files.
# OR
yarn run encore dev --watch # Compiles every time there are changes in the defined files. 
```

```bash
composer install # Installs the PHP dependencies.
```

```bash
make init-dev # Add pre-commit hooks.
```

```bash
php bin/console doctrine:database:create # Creates the base database.
```

```bash
php bin/console doctrine:schema:update --force # Applies the database structure defined in the migration files.
```

```bash
php bin/console doctrine:fixtures:load # Loads the base data into the database.
```

### Project Structure

- `assets/`:       Custom SCSS and JavaScript files.
    - `bootstrap/`:         Modifications to the bootstrap CSS framework.
    - `images/`:            Images used in the project.
    - `scss/`:              SCSS files that will be compiled into CSS.
    - `js/`:                JavaScript files that will be bundled and merged into a single file.
- `bin/`:          Project binaries, such as the Symfony Console, PHPUnit etc.
- `config/`:       Contains application configs for each environment.
    - `packages/`:          Configuration files for each package or bundle.
    - `routes/`:            Configuration for the routing of URLs.
    - `bundles.php`:        Includes the corresponding bundles to the given environment.
- `doc/`:          Documentation files for the technical documentation of this project.
- `node_modules/`: The libraries that yarn calculated and downloaded.
- `public/`:       The public folder. Better known as the document root.
    - `index.php`:          The entry point of the web application.
    - `robots.txt`:         Defines all URLs a search engine is allowed to index.
- `src/`:          Contains the project's source code. Note: All folders inside here, have a subfolder, depending on it's usage.
    - `Command/`:           Defines custom console commands.
    - `Controller/`:         Defines all controllers used in this project.
    - `DataFixtures/`:      Classes that are used to generate fixtures for the database.
    - `Entity/`:            All entities used in Doctrine.
    - `Form/`:              All custom form types used in the frontend and backend.
    - `Helper/`:            Helper classes that do not need the DependencyInjectionContainer from Symfony.
    - `Listener/`:          Classes that listen on events. When the event happens, the defined function is being executed.
    - `Migrations/`:        Contains the migration scripts.
    - `Repository/`:        The repository classes for the entities that interface with the database.
    - `Service/`:           Classes that handle complex operations that do not belong inside the controller.
    - `Validator/`:         Validators that add custom validations to forms.
    - `Kernel.php`:         Kernel-file provided by Symfony.
- `templates/`:    Templates for pages and emails.
- `tests/`:        Contains unit and functional tests of the code inside the `src`-folder.
    - Same structure as in `src`
- `translations/`: Translations of strings that are used in the front and backend.
    - `emails.de.yaml`:     German translations for emails.
    - `errors.de.yaml`:     German translations for symfony errors. Used to override the default messages by Symfony.
    - `flash.de.yaml`:      German translations for flashes. Used to notify the user.
    - `messages.de.yaml`:   German translations for regular texts.
    - `security.de.yaml`:   German translations for security errors. Used to override the default messages by Symfony.
    - `validators.de.yaml`: German translations for validation errors.
- `var/`:          Application cache and log.
    - `cache/`:             Application cache for each environment.
    - `log/`:               Application log for each environment.
- `vendor/`:       The libraries that composer calculated and downloaded.

### Webserver

As a webserver you can use any server of your choice. 
It is recommended to point the document root of the webserver to the `/public` folder. 
Otherwise, you can access the `public` folder in the URL by accessing `http://localhost:8000/path/to/root/public/`.

Symfony ships with a built in webserver that serves the `public` folder at port 8000. To start the webserver use

```bash
php bin/console server:run
```

### CSS and JS

As a CSS / JS compiler we use [Yarn](https://yarnpkg.com) and [Webpack](https://webpack.js.org). 
The file `webpack.config.js` configures the building of the CSS and JS files. 
In our project setup, yarn/webpack processes all defined files and bundles them into one css file and one js file.

To add more resources to the bundeled files (for example libraries managed with yarn) we add the paths to the variable `javascripts` and `stylesheets`.

To automatically run yarn at changes of the defined files, run:

```bash
yarn run encore dev --watch
```

### Database

As a database abstraction we use [Doctrine](http://www.doctrine-project.org). 
We use MariaDB as the default database. 
The database related settings are in the file `.env` and `config/packages/doctrine.yaml`.

Doctrine is an ORM (Object Relational Mapper) and a DBAL (Database Abstraction Layer) that also introduces a separate query language DQL (Doctrine Query Language). 
Doctrine persists objects and their connections in a relational database. 
Objects are called "entities" and are retrieved by "repositories".

Doctrine comes with some handy console tools such as:

```bash
php bin/console doctrine:mapping:import --force AppBundle annotation  # import existing schema and create entities accordingly
php bin/console doctrine:generate:entities App\Entity\Entity          # add getters and setters
php bin/console generate:doctrine:entity                              # add new entity
php bin/console doctrine:fixtures:load                                # load fixtures (resets database)
php bin/console doctrine:database:drop --force                        # drop the schema
php bin/console doctrine:database:create                              # create the schema
php bin/console doctrine:schema:update --force                        # update database schema to match the objects
```

#### Migrations

If the code of the entities change, the command `doctrine:schema:update` updates the schema.

However, if we use the code in a team or in production, it is better to use so called migration scripts that alter the database and can be applied separately in both up and down ways. 
The migration scripts are in `src/Migrations` and extend the class `AbstractMigration`. 
Migration scripts can be generated and applied automatically using those commands:

```bash
php bin/console doctrine:migrations:diff     # generate the changescript sql
php bin/console doctrine:migrations:migrate  # migrate db to the newest version
```

If the migration was unsuccessful we can remove the migration script or try to fix it (for example by migrating the data). 
More information about migrations can be found on the official [DoctrineMigrationsBundle website](https://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html).


## Upgrade from Skeleton

Add the remote repository if you did not already add it: 
```bash
git remote add skeleton git@gitlab.com:JKwebGmbH/symfony4-skeleton.git
```

Merge as you desire. Example:
```bash
git checkout dev
git merge skeleton/dev
```

## Common Tasks after creating a new Project
- Change this `README.md`.
- Update the `.env.local`-file.
- Change `app.notifications.email_sender` in `config/services.yml`.

## Before committing

- Fix PHP-Code format: `./vendor/bin/php-cs-fixer fix`


# Using DoctrineBehaviors

The behaviors are configured in [here](config/packages/doctrine.yaml).

Using the DoctrineBehaviors removes the need for `@HasLifetimeCycles`, because they use EventSubscribers.

## Tree

This component gives an easy interface to multiple self-referencing relations.

Just add the `ORMBehaviors\Tree\Node` to the Entity and the `ORMBehaviors\Tree\Tree` to the Repository.

## Translatable

Allows translations on entities. The base entity, is only allowed to have fields that are not translatable.
The base entity need the `ORMBehaviors\Translatable\Translatable` trait.

Then you create an entity with it's translatable fields. This new entity need the `ORMBehaviors\Translatable\Translation` trait.

The naming scheme should be `EntityName` and `EntityNameTranslation`. 
If otherwise:
- override the `public static function getTranslationEntityClass`-method. 
- [override](https://github.com/KnpLabs/DoctrineBehaviors#override) the trait for a different naming scheme.

### Usage

```php
<?php
    $category = new Category;
    $category->translate('fr')->setName('Chaussures');
    $category->translate('en')->setName('Shoes');
```

## Soft-Deletable

Just use `ORMBehaviors\SoftDeletable\SoftDeletable` trait on the desired Entity.

`!IMPORTANT`: 
- This extension does not filter the entities when retrieving from the database.
- And also keep in mind, adding a soft-delete function afterwards will most likely break the application.

## Timestamplable

Add the `ORMBehaviors\Timestampable\Timestampable` trait to the entity and it will 

## Blameable

Associates a User, defined at `paramter:knp.doctrine_behaviors.blameable_subscriber.user_entity:` to database changes.

## Geocodable

Geo feature for PostgreSQL.

## Loggable

Enables log messages based on entity actions. By default it uses Monolog.

## Sluggable (Do not use)

(Use our Sluggable-Trait instead, as it ensures unique slugs.)

The sluggable bahvior adds automatically generated slugs to the entity.
You must define the properties used to create the slug.

`!IMPORTANT:` The generated slugs are not unique by default.

## Filterable

Advanced filter add-on at repository level.

eg. Adds the `LIKE` comparison operator for MySQL.

This behavior is just a trait `Filterable\FilterableRepository`, that you can use inside the EntityRepository.


# Writing Tests

For simple tests, you should extend from `PHPUnit\Framework\TestCase`.

If your test requires some Symfony components like the WebCrawler, extend from the `Symfony\Bundle\FrameworkBundle\Test\WebTestCase`.

If you need the DependencyInjectionContainer or Fixtures too, extend from the `AppTestCase` that I wrote.


# Running Tests

```bash
# Method 1
./vendor/bin/simple-phpunit

# Method 2
php bin/phpunit

# Method 3
php path/to/phpunit.phar -c phpunit.xml.dist
```


# Deployment

## Deployer

Simple deployments utilize [deployer](https://deployer.org).
Install using 
```bash
curl -LO https://deployer.org/deployer.phar
mv deployer.phar /usr/local/bin/dep
chmod +x /usr/local/bin/dep
``` 
The file `servers.yml` contains all the necessary information for deployment such as the `hostname`, `user`, `http_user` and the `deploy_path`.  
Deployer creates the following directory structure in the deploy path:

- `%deploy_path%`
	- `releases`: This directory contains all the releases.
		- `20170222153028`
		- ...
	- `shared`:   The files that are shared over all releases (such as maybe a SQLite database).
	- `old`:      A symlink to the previous release.
	- `current`:  A symlink to the most current release.

The file `deploy.php` defines the constants necessary for deployment. 
Important are the so called shared files and shared folders. 
Those are files or folders that are shared between releases and carried from one release to another, such as the database or user uploads. 
The shared files and folders are stored in the folder `shared` and symlinked at the correct locations.

### Deploy Project on new Server

We first need to read out our public SSH key and add it to the GitLab repository.

#### Firstly, connect to the server via SSH:

```bash
ssh USER@SERVER.com -p PORT
```

If you don't want to use your password, you can trigger a key exchange via:

```bash
ssh-copy-id USER@SERVER.com -p PORT
```

From now on, you do not need to provide the password when connecting.
 
#### Secondly, display the SSH key:

To display the SSH-key, we run:

```bash
cat ~/.ssh/id_rsa.pub
```
 
If no SSH key exists we execute the following commands to generate and display one.

```bash
ssh-keygen -t rsa -b 4096 -C "info@jkweb.ch" # Generate new key. Passphrase can be blank.
```

```bash
cat ~/.ssh/id_rsa.pub                        # Display the new key.
```

#### Lastly, configure the Deployment:

We now add the public key to the GitLab project by accessing the URL https://gitlab.com/projectpath/deploy_keys. 

#### Deploy

Now you can deploy with:

```bash
dep deploy ENVIRONMENT
```
Where `ENVIRONMENT` is replaced by ether `dev`, `testing` or `production` (defaults to `dev`).

After a first deploy we may want to prepare the database data with this command `on the server`:

```bash
php bin/console doctrine:fixtures:load -e prod # Loads production fixtures.
```

Executed from the active release root (the folder `current`).

### Deploy on existing Server

Deployment works by executing:

```bash
php deployer.phar deploy ENVIRONMENT        # Deploys the webapp.
```
where `ENVIRONMENT` is replaced by ether `dev`, `testing` or `production` (defaults to `dev`).

`!ATTENTION:` The first deployment will fail!

You may get en error like: 
```
Fatal error: Uncaught Symfony\Component\Debug\Exception\ClassNotFoundException: Attempted to load class "WebProfilerBundle" from namespace "Symfony\Bundle\WebProfilerBundle"
```

This is due to missing APP_ENV configuration. By default the APP_ENV is set to `dev`, but composer did not install development dependencies.

We now SSH to the server and fix a couple of things. 

Change into the shared folder (e.g. `prod/shared`) and update the `.env`-file with your favorite text-editor:

Make sure you already created the database for your application.

```bash
vim .env.local
nano .env.local
```

You can now deploy again, and there should be no errors.

Change to the active release root (folder `current`) and check with:

```bash
php bin/console doctrine:migrations:status  # Check if there is any database migration that needs to be done.
php bin/console doctrine:migrations:migrate # Do the database migration.
```

If there are any database migrations to be applied.

When not using Doctrine Migrations, use

```bash
php bin/console doctrine:schema:update --dump-sql # Check if changes make sense.
php bin/console doctrine:schema:update --force    # Apply changes.
```

## Docker / Kubernetes

Please contact pascal.liniger@infix.ch to setup the deployment.
Information about our containers and Dockerfiles are available [here](./deploy).


# Compliances

## Cookies (JavaScript)

In order to comply with the GDPR/DSGVO there are several catches to take care of.
The `cookie.js` implements a interface that populates the `window.settings.cookie.allow`-property.

**! ALWAYS CHECK THIS PROPERTY BEFORE SETTINGS COOKIES.**

# Developers

- [Ashura](https://github.com/CoalaJoe)
- tzemp
