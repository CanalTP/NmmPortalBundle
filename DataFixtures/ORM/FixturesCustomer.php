<?php

namespace CanalTP\NmmPortalBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CanalTP\SamCoreBundle\DataFixtures\ORM\CustomerTrait;

class FixturesCustomer extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use CustomerTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $om)
    {
        $navitiaToken = $this->container->getParameter('nmm.navitia.token');
        $this->createCustomer($om, 'CanalTP', 'nmm-ihm@canaltp.fr', 'canaltp');
        $this->addPerimeterToCustomer($om, 'fr-bou', 'network:CGD', 'customer-canaltp');

        $this->createCustomer($om, 'Realtime Deactivate', 'fahmi.laajimi@canaltp.fr', 'realtime_deactivate');
        $this->addPerimeterToCustomer($om, 'jdr', 'network:DUA014', 'customer-realtime_deactivate');

        $this->addCustomerToApplication($om, 'app-samcore', 'customer-realtime_deactivate', $navitiaToken);
        $this->addCustomerToApplication($om, 'app-samcore', 'customer-canaltp', $navitiaToken);

        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 2;
    }
}
