#!/bin/bash -xeu

apt update
apt install systemctl git wget curl -y
apt install apache2 -y
apt install php php-mysqli -y
apt install php-curl php-mbstring -y
wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet
mv composer.phar /usr/local/bin/composer
git clone https://github.com/Hoo-dkwozD/Boomerang-EC2.git
cd Boomerang-EC2
mv * /var/www/html/
cd /var/www/html/
rm -rf ~/Boomerang-EC2
/usr/local/bin/composer update
systemctl start apache2
