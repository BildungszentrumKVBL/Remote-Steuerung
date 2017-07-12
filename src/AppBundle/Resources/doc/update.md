Update
======

To keep up-to-date, first create a backup using the [backup](backup.md)-command.
1. Update the code. `git pull`
2. Update new dependencies. `composer install --optimize-autoloader`
3. Check for database-updates. `php app/console doctrine:schema:update --dump-sql`
   If there are updates, you can apply them with using the `--force`-flag. `php app/console doctrine:schema:update --force`
4. Check if your settings are alright. `cat app/config/application.yml`
5. Generate new CSS-files. `./generate_css.sh`
6. Dump website assets. `php app/console assetic:dump web -e=prod`
7. Clear the cache. `php app/console cache:cl -e=prod`
8. Check if all works fine. If not, feel free to contact us.
