#!/bin/sh

./docker/wait-for-it.sh -h nmm-portal-db -p 5432 -t 0 && \
echo "Database service is UP !"

cd nmm_portal_functional_test

export ASSETS_VERSION=`date +"%s"`

rm -rf app/cache/* app/bootstrap.php.cache app/logs/*.log
rm -rf vendor/canaltp/nmm-portal-bundle
rm -f web/uploads/*.png

composer install -o -n --ansi

if [ "${ghprbPullId}" != "nopr" ] && [ "${ghprbPullId}" != "test" ]; then
  # Quickfix if test on branch with new adding on composer json (to disable when merge and tag -> add an update on NMM)
  composer require -vvv --update-with-dependencies --no-interaction canaltp/nmm-portal-bundle:dev-${ghprbSourceBranch}
  cd vendor/canaltp/nmm-portal-bundle && git fetch origin +refs/pull/*:refs/remotes/origin/pr/* && git reset --hard HEAD && git checkout ${sha1} && cd ../../..;
else
  # Quickfix if test on branch with new adding on composer json (to disable when merge and tag -> add an update on NMM)
  composer require -vvv --update-with-dependencies --no-interaction canaltp/nmm-portal-bundle:dev-${sha1}
  cd vendor/canaltp/nmm-portal-bundle && git fetch origin && git reset --hard HEAD && git checkout -B ${sha1} origin/${sha1} && cd ../../..;
fi;
composer update canaltp/sam-core-bundle
bin/install-assets

setfacl -R -m u:www-data:rwX -m u:`whoami`:rwX app/cache app/logs
setfacl -dR -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs

php app/console doctrine:database:drop -e test_sam --force
php app/console doctrine:database:create -e test_sam

php app/console claroline:migration:upgrade CanalTPSamCoreBundle --target=farthest -e test_sam
php app/console claroline:migration:upgrade CanalTPNmmPortalBundle --target=farthest -e test_sam

php app/console  doctrine:fixtures:load --fixtures=vendor/canaltp/nmm-portal-bundle/DataFixtures/ORM --fixtures=vendor/canaltp/sam-ecore-user-manager-bundle/DataFixtures/ORM -n -e test_sam --append -vv

if [ "${ghprbPullId}" = "nopr" ]; then
  vendor/behat/behat/bin/behat @CanalTPNmmPortalBundle -p nmm_portal --colors
else
  vendor/behat/behat/bin/behat @CanalTPNmmPortalBundle -p nmm_portal --colors --stop-on-failure
fi

cd -
