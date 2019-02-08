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

    /**
     * @Then /^(?:|I )should see (?P<num>\d+) "(?P<selector>[^"]*)" selector elements?$/
     */
    public function iShouldSeeSelectorElements($num, $selector)
    {
        if (intval($num, 10) > 0) {
            $this->iWaitForAnElementToExistNTimes($selector, $num);
        } else {
            $result = $this->getSession()->evaluateScript("$('" . $selector . "').length;");
            if (intval($result, 10) !== intval($num, 10)) {
                throw new \Exception(
                    sprintf(
                        '%s elements matching $(\'%s\') found on the page, but should be %s.',
                        $result,
                        $selector,
                        $num
                    )
                );
            }
        }
    }

    /**
     * @Given /^I wait for an element "(?P<selector>[^"]*)" to exist (?P<num>\d+) times$/
     */
    public function iWaitForAnElementToExistNTimes($selector, $num)
    {
        $session = $this->getSession();
        $evaluateScript = sprintf("($('%s').length == %u)", $selector, $num);
        $this->spin(function () use ($evaluateScript, $session, $selector, $num) {
            $session->wait(2000, $evaluateScript);
            if (!$session->evaluateScript(sprintf("return %s;", $evaluateScript))) {
                $result = $session->evaluateScript("$('" . $selector . "').length;");
                throw new \Exception(
                    sprintf(
                        '%s elements matching $(\'%s\') found on the page, but should be %s.',
                        $result,
                        $selector,
                        $num
                    )
                );
            }
            return true;
        });
    }

    /**
     * @param Callable $lambda
     * @param integer $retry
     * @throws \Exception
     */
    public function spin($lambda, $retry = 30)
    {
        $lastException = '';
        for ($i = 0; $i < $retry; $i++) {
            try {
                $result = $lambda($this);
                if ($result) {
                    return $result;
                }
            } catch (\Exception $e) {
                $lastException = $e->getMessage();
            }
            sleep(1);
        }
        $backtrace = debug_backtrace();
        throw new \Exception(
            'Timeout thrown by ' . $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . ' (' . implode(',', $backtrace[1]['args']) . ') \n' .
            (isset($backtrace[1]['file']) ? $backtrace[1]['file'] : '<unknown>') . ', line ' .
            (isset($backtrace[1]['line']) ? $backtrace[1]['line'] : '<unknown>') . '\n' .
            $lastException
        );
    }
}
