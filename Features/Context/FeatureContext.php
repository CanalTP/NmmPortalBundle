<?php

namespace CanalTP\NmmPortalBundle\Features\Context;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext
{
    /**
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep(AfterStepScope $event)
    {
        if ($event->getTestResult()->getResultCode() == TestResults::FAILED) {
            if ($this->getSession()->getDriver() instanceof
                \Behat\Mink\Driver\Selenium2Driver
            ) {
                $stepText = $event->getStep()->getText();
                $fileTitle = preg_replace("#[^a-zA-Z0-9\._-]#", '', $stepText);
                $baseDirectory = explode(DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR, __DIR__);
                $fileName = $baseDirectory[0] . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR .
                    'uploads' . DIRECTORY_SEPARATOR .
                    $fileTitle . '.png';
                $screenshot = $this->getSession()->getDriver()->getScreenshot();
                print "Screenshot for '{$stepText}' placed in {$fileName}\n";
                file_put_contents($fileName, $screenshot);
            }
        }
    }

    /**
     * @Given /^(?:|I )am logged in as Administrator$/
     */
    public function iAmLoggedInAsAdministrator()
    {
        $this->visit('/admin/login');
        $this->fillField('username', 'admin');
        $this->fillField('password', 'admin');
        $this->pressButton('Connexion');
    }

    /**
     * @Given /^I wait (\d+) seconds$/
     */
    public function wait($seconds)
    {
        sleep($seconds);
    }
}
