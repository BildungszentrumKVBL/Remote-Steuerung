Backup/Restore
==============

Backing up the application is important, once you get it running like you want it.
Be aware, that a restore of the data will erase the data, that is not backed up.


# Backup 

To backup your configuration and database, run the: `php backup`-command.
It will backup your `application.yml`, `parameters.yml` and your database into the `.bu`-folder.
The backup also remembers what version you where using.


# Restore

If you every have to restore one of your backup, your can use the: `php restore`-command.
It is recommended, to just look for the missing information in the `.bu`-folder, instead of running this command.
