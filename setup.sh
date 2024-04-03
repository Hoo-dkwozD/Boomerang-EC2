#!/bin/bash -xeu

apt install systemctl wget curl -y
apt install apache2 -y
apt install php php-mysqli -y
apt install php-curl php-mbstring php-xml -y
wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet
mv composer.phar /usr/local/bin/composer
mv * /var/www/html/
cd /var/www/html/
rm -rf ~/Boomerang-EC2
/usr/local/bin/composer update
systemctl start apache2
