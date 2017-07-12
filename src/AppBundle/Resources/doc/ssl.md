SSL
===

When you want to use SSL for your website, you also have to use SSL for you websocket connection.

One easy way to achieve this, is by using STunnel. This is a very nifty piece of programm. I basically opens a SSL encrypted port and redirects it locally to an unencrypted port.


# STunnel

1. Install stunnel: `sudo apt-get install stunnel`
2. Create a configfile in `/etc/stunnel/`. Preferably named `stunnel.conf`.
   `sudo nano /etc/stunnel/stunnel.conf`
3. Add and modify this content to your needs (Your domainname, and certificates):
   ```ini
   # Certificate
   cert = /my/way/to/ssl.crt
   key = /my/way/to/not_crypted.key
   
   # Remove TCP delay for local and remote.
   socket = l:TCP_NODELAY=1
   socket = r:TCP_NODELAY=1
   
   chroot = /var/run/stunnel4/
   pid = /stunnel.pid
   
   # Only use this options if for making it more secure after you get it to work.
   # User id
   # setuid = nobody
   # Group id
   # setgid = nobody
   
   # IMPORTANT: If the websocketserver is on the same server as the webserver use this:
   # local = my.domainname.com # Insert here your domain that is secured with https.
   
   [websockets]
   accept = 8443
   connect = 8888
   # IMPORTANT: If you use the local variable above, you have to add the domainname here aswell.
   # connect = my.domainname.com:8888 
   # ALSO *: When starting your websocket server, you have to use the -a parameter to specify the domainname
   ```
4. Save the file and start stunnel. `/etc/init.d/stunnel4 start`
5. To run stunnel on startup, edit the `/etc/default/stunnel4`-file. 
   Add this line to the code: `ENABLED=1`

The value `accept`, that you saw in your created `stunnel.conf`-file, is the port that a client has to connect to.
In this case, it is 8443.

When stunnel receives a connection, it redirects the request locally to the `connect`-port. In this case 8888.

More information is available [here](https://github.com/GeniusesOfSymfony/WebSocketBundle/blob/master/Resources/docs/Ssl.md).
