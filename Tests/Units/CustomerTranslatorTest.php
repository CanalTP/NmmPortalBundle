<?php

namespace CanalTP\NmmPortalBundle\Tests\Units;

use Symfony\Bundle\FrameworkBundle\Tests\Translation\TranslatorTest;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

class CustomerTranslatorTest extends TranslatorTest
{
    private $securityContext;

    protected function setUp()
    {
        parent::setUp();

        $this->mockSecurityContext();
    }

    private function mockSecurityContext()
    {
        $customerStub = $this->getMockBuilder('CanalTP\SamCoreBundle\Entity\CustomerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $customerStub
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('canaltp'));

        $userStub = $this->getMockBuilder('CanalTP\SamEcoreUserManagerBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $userStub
            ->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customerStub));

        $tokenStub = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $tokenStub
            ->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($userStub));

        $this->securityContext = $this
            ->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->securityContext
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($tokenStub));
    }

    protected function getContainer($loader)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($arg) use ($loader) {
                if ($arg == 'security.context') {
                    return ($this->securityContext);
                }
                return ($loader);
            }))
        ;

        return $container;
    }

    public function getTranslator($loader, $options = array(), $translatorClass = '\CanalTP\NmmPortalBundle\Services\CustomerTranslator')
    {
        $translator = new $translatorClass(
            $this->getContainer($loader),
            new MessageSelector(),
            array('loader' => array('loader')),
            $options
        );

        $translator->addResource('loader', 'foo', 'fr');
        $translator->addResource('loader', 'foo', 'en');
        $translator->addResource('loader', 'foo', 'es');
        $translator->addResource('loader', 'foo', 'pt-PT'); // European Portuguese
        $translator->addResource('loader', 'foo', 'pt_BR'); // Brazilian Portuguese
        $translator->addResource('loader', 'foo', 'fr.UTF-8');
        $translator->addResource('loader', 'foo', 'sr@latin'); // Latin Serbian

        return $translator;
    }

    public function testTransWithCustomCustomer()
    {
        $translator = $this->getTranslator($this->getLoader());
        $translator->addLoader('array', new ArrayLoader());
        $translator->setLocale('fr');
        $translator->setFallbackLocales(array('en', 'es', 'pt-PT', 'pt_BR', 'fr.UTF-8', 'sr@latin'));
        $translator->addResource('array', array('body.title' => 'product'), 'fr');
        $translator->addResource('array', array('body.title' => 'canaltp'), 'fr', 'canaltp');
        $translator->addResource('array', array('body.title' => 'transilien'), 'fr', 'transilien');

        $this->assertEquals('canaltp', $translator->trans('body.title', array()));
        $this->assertEquals('canaltp', $translator->trans('body.title', array(), 'canaltp'));
        $this->assertEquals('transilien', $translator->trans('body.title', array(), 'transilien'));
    }
}
