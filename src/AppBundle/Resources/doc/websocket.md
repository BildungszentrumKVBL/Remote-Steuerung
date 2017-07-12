Websocket server
================

The websocket server is used, to enable the observation-interface for the administrators.

To test the websocket server, you can run this command: `php app/console gos:websocket:server -a YOUR.DOMAIN.NAME -e=prod`

If everything works as expected, and the admin-clients can connect to the server, create a script.
```bash
#!/bin/bash
cd THE_DIRECTORY_OF_THE_APPLICATION
php app/console gos:websocket:server -a YOUR.DOMAIN.NAME -e=prod -n > /dev/null
```

Add this script to your startup-scripts. This depends on your operating system.
In Ubuntu 16.04 for instance, you can add this line of code in `/etc/rc.local`.
```bash
runuser -l YOUR_WEBUSER -c /var/www/myWebsocketScript.sh

exit 0
```
