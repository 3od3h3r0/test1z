http://18.222.248.71


cd ~/apps/magento/htdocs/app/code

git init .
git remote add -t \* -f origin https://github.com/3od3h3r0/test1z.git
git checkout master

php bin/magento setup:upgrade

php bin/magento setup:di:compile

php bin/magento setup:static-content:deploy -f

php bin/magento indexer:reindex

php bin/magento cache:clean

php bin/magento cache:flush
