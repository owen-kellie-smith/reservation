#! /bin/bash
# copied from https://github.com/wikimedia/mediawiki-extensions-Wikibase/edit/master/build/travis/install.sh

originalDirectory=$(pwd)

cd ..

wget https://github.com/wikimedia/mediawiki/archive/$MW.tar.gz
tar -zxf $MW.tar.gz
mv mediawiki-$MW phase3

cd phase3
composer self-update
composer install

# Try composer install again... this tends to fail from time to time
if [ $? -gt 0 ]; then
	composer install
fi

mysql -e 'create database its_a_mw;'
php maintenance/install.php --dbtype $DBTYPE --dbuser root --dbname its_a_mw --dbpath $(pwd) --pass nyan TravisWiki admin

cd extensions

cp -r $originalDirectory Wikibase

cd Wikibase

composer install --prefer-source

# Try composer install again... this tends to fail from time to time
if [ $? -gt 0 ]; then
	composer install --prefer-source
fi

