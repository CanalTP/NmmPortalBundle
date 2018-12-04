<?php

namespace CanalTP\NmmPortalBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\SamEcoreApplicationManagerBundle\Services\ApplicationFinder;
use CanalTP\NmmPortalBundle\Entity\CustomerApplication;
use CanalTP\SamCoreBundle\Entity\Application;
use CanalTP\NmmPortalBundle\Entity\Customer;

class CustomerManager extends \CanalTP\SamCoreBundle\Services\CustomerManager
{
    protected $om = null;
    protected $repository = null;
    protected $navitiaTokenManager = null;

    public function __construct(
        ObjectManager $om,
        $navitiaTokenManager,
        ApplicationFinder $applicationFinder
    ) {
        parent::__construct($om, $navitiaTokenManager, $applicationFinder);
        $this->repository = $this->om->getRepository('CanalTPNmmPortalBundle:Customer');
    }

    public function findOneBy(array $filters)
    {
        return ($this->repository->findOneBy($filters));
    }

    protected function syncPerimeters($customer)
    {
        $perimeterRepo = $this->om->getRepository('CanalTPNmmPortalBundle:Perimeter');
        $perimeters = $perimeterRepo->findBy(array('navitiaEntity' => $customer->getNavitiaEntity()));
        $officialPerimeterIds = array();

        foreach ($customer->getNavitiaEntity()->getPerimeters() as $perimeter) {
            if ($perimeter->getId() != null) {
                $officialPerimeterIds[] = $perimeter->getId();
            }
        }

        foreach ($perimeters as $perimeter) {
            if (!in_array($perimeter->getId(), $officialPerimeterIds)) {
                $this->om->remove($perimeter);
            }
        }
    }

    public function save($customer)
    {
        if ($customer->getId() != null) {
            $this->syncPerimeters($customer);
        }
        $customer->getNavitiaEntity()->refreshPerimeters();
        // TODO: UniqueEntity not work in perimeter entity.
        $customer->getNavitiaEntity()->setPerimeters(array_unique($customer->getNavitiaEntity()->getPerimeters()->toArray()));
        $this->om->persist($customer);
        $customer->upload();
        $this->om->flush();
    }

    protected function createCustomerApplicationRelation($customer, Application $application)
    {
        $customerApplication = new CustomerApplication();

        $customerApplication->setCustomer($customer);
        $customerApplication->setApplication($application);
        $customerApplication->setToken(
            $this->navitiaTokenManager->generateToken($application->getCanonicalName())
        );
        $customerApplication->setIsActive(true);

        $this->om->persist($customerApplication);
        $this->om->flush($customerApplication);

        return $customerApplication;
    }

    /**
     * Return customer's perimeters in an array
     *
     * @param \CanalTP\SamCoreBundle\Entity\Customer $customer
     * @return DoctrineCollection
     */
    public function findPerimeterByCustomer($customer)
    {
        $perimeterRepo = $this->om->getRepository('CanalTPNmmPortalBundle:Perimeter');

        return $perimeterRepo->findPerimeterByCustomer($customer);
    }

    /**
     * Return active token to the couple customer/application
     *
     * @param type $customer
     * @param type $applicationName
     * @return null
     */
    public function getActiveNavitiaToken($customerId, $applicationName)
    {
        $custoAppRepo = $this->om->getRepository('CanalTPNmmPortalBundle:CustomerApplication');
        if (null != $customerApp = $custoAppRepo->getActiveNavitiaToken($customerId, $applicationName)) {
            return $customerApp->getToken();
        }
        return null;
    }
}
