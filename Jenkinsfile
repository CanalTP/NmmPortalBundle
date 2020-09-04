pipeline {
    agent any
    options {
        buildDiscarder(logRotator(numToKeepStr:'10'))
        disableConcurrentBuilds()
    }
    parameters {
        string(name: 'sha1', defaultValue: 'master', description: '')
        text(name: 'ghprbPullId', defaultValue: 'nopr', description: 'Pull REquest Id')
    }
    stages {
        stage('Build nmm portal test image & create composer directory') {
            steps {
                sshagent (credentials: ['jenkins-kisio-bot']) {
                    sh '''
                    mkdir -p ${HOME}/.config/composer
                    _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml build --no-cache --force-rm --pull nmm-portal-app
                    '''
                }
            }
        }
        stage('Install dependencies') {
            steps {
                sshagent (credentials: ['jenkins-kisio-bot']) {
                    sh '''
                    rm -f composer.lock
                    _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm --no-deps nmm-portal-app composer install --no-interaction --prefer-dist
                    '''
                }
            }
        }
        stage('Checkstyle') {
            steps {
                sshagent (credentials: ['jenkins-kisio-bot']) {
                    sh '''
                    _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm --no-deps nmm-portal-app \
                    ./vendor/bin/phpcs -n --standard=PSR2 --encoding=utf-8 --extensions=php --ignore=vendor/* --ignore=nmm_portal_functional_test/* --report=checkstyle --report-file=checkstyle-result.xml . \
                    && true
                    '''
                }
            }
            post {
                always {
                    recordIssues enabledForFailure: true, tools: [checkStyle(pattern: 'checkstyle-result.xml')]
                }
            }
        }
        stage('Phpunit tests') {
            steps {
                sshagent (credentials: ['jenkins-kisio-bot']) {
                    sh '''
                    rm -rf docs
                    _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm --no-deps nmm-portal-app \
                    ./vendor/bin/phpunit --testsuite=NmmPortal --log-junit=docs/unit/logs/junit.xml --coverage-html=docs/unit/CodeCoverage --coverage-clover=docs/unit/CodeCoverage/coverage.xml
                    '''
                }
            }
            post {
                always {
                    step([
                          $class:'CloverPublisher',
                          cloverReportDir: 'docs/unit/CodeCoverage',
                          cloverReportFileName: 'coverage.xml',
                          healthyTarget: [methodCoverage: 70, conditionalCoverage: 70, statementCoverage: 70],
                          unhealthyTarget: [methodCoverage: 50, conditionalCoverage: 50, statementCoverage: 50],
                          failingTarget: [methodCoverage: 0, conditionalCoverage: 0, statementCoverage: 0]
                      ])
                    junit testResults: 'docs/unit/logs/junit.xml'
                }
            }
        }
        stage('Functional test') {
             environment {
                 ghprbPullId = "${params.ghprbPullId}"
                 sha1 = "${params.sha1}"
             }
            steps {
                sshagent (credentials: ['jenkins-kisio-bot']) {
                    sh '''
                    rm -rf nmm_portal_functional_test
                    git clone -b task-bot-2046-add-jenkinsfile git@github.com:CanalTP/NMM.git nmm_portal_functional_test
                    _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run -e ghprbPullId=${ghprbPullId} -e sha1=${sha1} nmm-portal-app
                    '''
                }
            }
            post {
                always {
                    junit testResults: 'nmm_portal_functional_test/behat/*.xml'
                    archiveArtifacts artifacts: 'nmm_portal_functional_test/web/uploads/*.png', allowEmptyArchive: true
                }
            }
        }
    }
    post {
        always {
            echo 'Clean environment'
            sshagent (credentials: ['jenkins-kisio-bot']) {
                sh '''
                _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml down --remove-orphans
                '''
            }
        }
    }
}
