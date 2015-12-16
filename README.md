# reservation
Mediawiki extension to reserve resources e.g. cores on a computer

Requires at least: Mediawiki 1.25

License: MIT

License URI: https://opensource.org/licenses/MIT 

Code is at:https://github.com/owen-kellie-smith/reservation/

## Description 

This extension provides a special page which renders forms that enable the booking of homogeneous resources (e.g. cores on a computer).

## Installation

1. Download, unzip and upload to your extensions directory.  
1. Add  wfLoadExtension( 'Reservation' );   to your LocalSettings.php.
1. Go to the new Special page (check in the special pages for "Reservation").

## How to run the unit tests

The unit tests overwrite newly created tables in the database, so first backup your reservation tables in the MediaWiki database.
	mysqldump -u <MySQL username> -p <MediaWiki database name>  > dumpRes.sql

Next run the unit test.  Go to your MediaWiki core folder, and run
    php tests/phpunit/phpunit.php extensions/Reservation/tests/phpunit/

Finally, restore your bookings by running 
    mysql -u <MySQL username> -p <MediaWiki database name> < dumpRes.sql


