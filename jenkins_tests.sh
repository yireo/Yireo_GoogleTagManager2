#!/bin/bash
test -d magento2-vagrant && rm -rf magento2-vagrant
git clone https://github.com/yireo-training/magento2-vagrant
cd magento2-vagrant

cp Vagrantfile.sample Vagrantfile
# @todo: Customize this path to reflect your own composer-auth.json file
cp /var/tmp/composer-auth.json vagrant_files/composer-auth.json

vagrant up

BOX_IP=$(vagrant ssh -- ip route | awk 'END{print $NF}')
echo "Registered ip: $BOX_IP"

# Run actual tests
vagrant ssh -c 'cd /vagrant/source; 
chmod 755 bin/magento;
composer require yireo/magento2-googletagmanager2; 
bin/magento module:enable Yireo_GoogleTagManager2;
bin/magento setup:upgrade
cd vendor/yireo/magento2-googletagmanager2;
npm install;
npm test;
phpunit;'

vagrant destroy -f
cd ..
