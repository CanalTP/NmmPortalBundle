<?php

namespace CanalTP\NmmPortalBundle\Features\Context;

use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext
{
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
