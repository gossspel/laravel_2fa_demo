## Laravel 2FA Demo with MySQL, Redis & Google Authenticator

This example illustrates how Two-factor authentication can be integrated on a freshly baked Laravel application.
As you may know already, Two-factor authentication greatly improves the security of any web/mobile application.
We will go through account registration, login, and Two-factor authentication processes in this example.

Author: [Sunny Chan](https://www.linkedin.com/pub/sunny-chan/23/a26/1a1)
(Founder of [Crypteon.Net](https://www.crypteon.net))  
Credits: Laravel Team for [Laravel](http://laravel.com/),
Abdulrhman Alkhodiry for [OTPass](https://github.com/zeroows/Laravel-OTPass-Bundle)

## Requirement

Make sure you have the following:

1.) OSX >= 10.8.5 or Ubuntu >= 12.04  
2.) PHP >= 5.4 | OSX: MAMP | Ubuntu: $ sudo apt-get install php5-cli  
3.) MCrypt PHP Extension | OSX: MAMP | Ubuntu: $ sudo apt-get install php5-mcrypt  
4.) Apache | OSX: MAMP | Ubuntu: $ sudo apt-get install apache2  
5.) MySQL | OSX: MAMP | Ubuntu: $ sudo apt-get install mysql-server  
6.) Redis - http://redis.io/topics/quickstart  
7.) Git - http://git-scm.com/book/en/Getting-Started-Installing-Git  

## Clone the repository

$ cd {your_web_root_path}  
$ git clone https://github.com/gossspel/laravel_2fa_demo.git  

## Create Database in MySQL

$ mysqladmin -uroot -p create laravel_2fa_demo

## Edit Laravel configuration files

In app/config/local/app.php, change the url if necessary.  
In app/config/local/database.php, change the redis and mysql configuration to match with your redis and mysql configuration.  
In bootstrap/start.php, change the 'sunnymab.local' to your machine name.  
You can find your machine name by: $ hostname  

## Change owner and group for Laravel storage

Some of you might run into file permission problem with app/storage:

$ cd {your_webroot_path}  
$ sudo chown -R www-data:www-data app/storage/  

## Configure Apache

Set AllowOverride Directive to 'All' in your sites-enabled configuration  
Enable mod_rewrite:  
$ sudo a2enmod rewrite  

## Start Apache, MySQL and Redis

OSX:  
Start the MAMP Application  
$ redis-server  

Ubuntu:  
$ sudo service apache2 restart  
$ sudo service mysql restart  
$ redis-server  

## Generate Laravel app secret and perform db migrations

$ cd {your_web_root_path}/laravel_2fa_demo  
$ php artisan key:generate  
$ php artisan migrate  

## Test it out in your favorite browser

Open up the browser, and go to:  
http://localhost/laravel_2fa_demo/public/  

You should see the front page of Laravel 2FA Demo! Try it out now!  
