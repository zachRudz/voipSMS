# VoipSMS

An SMS client for voip.ms, written in PHP.

voip.ms offers a great API to compliment their VOIP service. In addition, they also offer an SMS service, which also fully interfaces with their API.

voipSMS is an interface to the voip.ms API. You can send and recieve SMS (text) messages using voipSMS, with a cleaner, more functional interface than voip.ms' built-in SMS center. Some of the benefits of this site include...

* Support for a contacts list.
* Easy switching between DIDs when messaging.
* Searching for conversations.
* FOSS!


## Getting Started

These instructions will get you a copy of the project up and running.

### Prerequisites

This guide assumes you already have a LAMP/LEMP stack setup. Below are the requirements.

Previous/later versions might work, although you'll probably want to install up to date software.
* PHP (min version: 5.5.0, tested on 5.6.30)
* mysql server (up to date, tested on 14.14)
* Apache or Nginx (up to date, tested on nginx 1.6.2)

### Installing

Clone this repo into your web directory (eg: /var/www/voipSMS)

```
cd /var/www/
git clone git@github.com:zachRudz/voipSMS.git
```

Modify the database configuration for the site. This is how the application will access your database. You'll want to modify $dbHost to reflect the address of your database. Also, create a new password for this application to access your database, and store it under $dbPass. 

```
cd voipSMS
vim sql/dbinfo.php

** Before **
$dbHost = 'INSERT THE IP ADDRESS OF YOUR DATABASE HERE';
$dbPass = 'INSERT YOUR PASSWORD HERE';

** After **
$dbHost = 'localhost';
$dbPass = 'my_secure_password';
```

Now, we'll need to create the database, tables, and users for this application. There's a setup script that will do that for you, but you have to edit it to to reference the password you just created, and the address of your webserver. 

``` 
vim sql/setup.sql

** Before **
CREATE USER `voipSMS_user`@`INSERT_DATABASE_ADDRESS_HERE` IDENTIFIED BY 'INSERT_PASSWORD_HERE';
GRANT DELETE,INSERT,INSERT,SELECT,UPDATE ON `voipSMS` . * TO `voipSMS_user`@`INSERT_DATABASE_ADDRESS_HERE';

** After **
CREATE USER `voipSMS_user`@`localhost` IDENTIFIED BY 'my_secure_password';
GRANT DELETE,INSERT,INSERT,SELECT,UPDATE ON `voipSMS` . * TO `voipSMS_user`@`localhost';
```

Now that you have your database configuration prepared, you can go ahead and run the setup.sql script. This will create the database, tables, and mysql user for the application. 

``` 
mysql -u root -p
Enter password: 
mysql> source ./sql/setup.sql
```
You're all set! At this point, you can delete sql/setup.sql if you want. At this point, you can configure your webserver to point to /var/www/voipSMS for this site.

## Built With

* [The voip.ms API](http://voip.ms/) - The API used
* [mysql](https://www.mysql.com/) - The database used
* [nginx](https://www.nginx.com/) - The webserver used during development
* [php](http://www.php.net/) - For the backend, and for keeping my blood pressure up
* [jquery](https://jquery.com/) - For datatables
* [Datatables](https://datatables.net/) - For cleanly displaying contacts, and searched conversations
* [Bootstrap](https://getbootstrap.com/) - For a clean frontend design

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Thanks to voip.ms for the fantastic API!
* Thanks to [PurpleBooth](https://gist.github.com/PurpleBooth) for the [readme template](https://gist.github.com/PurpleBooth/109311bb0361f32d87a2)!
