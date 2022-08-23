# Plugin magento 2 Chazki

# How to install this extension?

Under your root folder, run the following command lines:

composer require alternative-chazki/pluginchazkimagento
composer update
bin/magento module:status

bin/magento module:enable Chazki_ChazkiArg --clear-static-content
bin/magento module:status
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento module:status Chazki_ChazkiArg
sudo chmod -R 777 .
