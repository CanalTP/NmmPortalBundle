# NmmPortalBundle

This bundle is part of [Navitia Mobility Manager](https://github.com/CanalTP/navitia-mobility-manager).
It inherits [SamCoreBundle](https://github.com/CanalTP/SamCoreBundle) and contains:
- Some entities more (Perimeter, NavitiaEntity, NavitiaToken)
- Customer management

## How to launch tests

### Requirements

- Docker
- Build image and dependency with the command :
```
mkdir -p ${HOME}/.config/composer
_UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml build --no-cache --force-rm --pull nmm-portal-app
rm -f composer.lock
_UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm --no-deps nmm-portal-app composer install --no-interaction --prefer-dist
```

### Checkstyle

Launch with :
```
_UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm --no-deps nmm-portal-app ./vendor/bin/phpcs -n --standard=PSR2 --encoding=utf-8 --extensions=php --ignore=vendor/* --ignore=nmm_portal_functional_test/* --report=checkstyle --report-file=checkstyle-result.xml .
```

You could check the result file :
```
cat checkstyle-result.xml
```

### PhpUnit

Launch with :
```
rm -rf docs
_UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm --no-deps nmm-portal-app ./vendor/bin/phpunit --testsuite=NmmPortal --log-junit=docs/unit/logs/junit.xml --coverage-html=docs/unit/CodeCoverage --coverage-clover=docs/unit/CodeCoverage/coverage.xml
```

You could check the result file :
```
cat docs/unit/logs/junit.xml
```

And also the coverage file :
- Html : `docs/unit/CodeCoverage/index.html`
- Xml : `docs/unit/CodeCoverage/coverage.xml`

### Behat

For behat test, you need to have access to the repository [NMM](https://github.com/CanalTP/NMM). If not don't do this tests.

Launch with :
```
rm -rf nmm_portal_functional_test
git clone -b task-bot-2046-add-jenkinsfile git@github.com:CanalTP/NMM.git nmm_portal_functional_test
_UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run -e ghprbPullId=${ghprbPullId} -e sha1=$(git rev-parse HEAD) nmm-portal-app
```

The result file should be :
`nmm_portal_functional_test/behat/nmm_portal.xml`

If some error occured, you should be able to get some screenshot in :
`nmm_portal_functional_test/web/uploads`

## License

This bundle is released under the [GPL-3.0 License](LICENSE)
