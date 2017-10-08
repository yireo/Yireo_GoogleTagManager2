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
# http://magento2.local/ | 192.168.70.70

#TEST_URL=$BOX_IP nosetests testcase.py

# @todo Run actual tests

vagrant destroy -f
cd ..
