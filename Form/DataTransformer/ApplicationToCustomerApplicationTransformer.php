<?php

namespace CanalTP\NmmPortalBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use CanalTP\NmmPortalBundle\Entity\CustomerApplication;
use CanalTP\NmmPortalBundle\Entity\Customer;
use CanalTP\SamCoreBundle\Entity\Application;
use CanalTP\NmmPortalBundle\Services\NavitiaTokenManager;

class ApplicationToCustomerApplicationTransformer implements DataTransformerInterface
{
    private $om;
    private $navitiaTokenManager;
    private $oldCustomerApplications;

    public function __construct(ObjectManager $om, NavitiaTokenManager $navitiaTokenManager)
    {
        $this->om = $om;
        $this->navitiaTokenManager = $navitiaTokenManager;
        $this->oldCustomerApplications = array();
    }

    public function transform($customer)
    {
        if ($customer === null) {
            return ($customer);
        }
        $applications = new ArrayCollection();

        foreach ($customer->getActiveCustomerApplications() as $customerApplication) {
            $customerApplication->setIsActive(false);
            $applications->add($customerApplication->getApplication());
            $this->oldCustomerApplications[$customerApplication->getApplication()->getId()] = $customerApplication;
        }
        $customer->setApplications($applications);
        return $customer;
    }

    private function createCustomerApplicationRelation($customer, Application $application)
    {
        $customerApplication = new CustomerApplication();

        $customerApplication->setCustomer($customer);
        $customerApplication->setApplication($application);
        $customerApplication->setToken(
            $this->navitiaTokenManager->generateToken($application->getCanonicalName())
        );
        $customerApplication->setIsActive(true);
        return ($customerApplication);
    }

    private function clearUnusableTokens()
    {
        foreach ($this->oldCustomerApplications as $oldCustomerApplication) {
            $this->navitiaTokenManager->deleteToken($oldCustomerApplication->getToken());
        }
    }

    public function reverseTransform($customer)
    {
        if (!$customer) {
            return $customer;
        }
        $customerApplications = new ArrayCollection();
        try {
            $this->navitiaTokenManager->initUser($customer->getNavitiaEntity()->getNameCanonical(), $customer->getEmailCanonical());
            $this->navitiaTokenManager->initInstanceAndAuthorizations($customer->getNavitiaEntity()->getPerimeters());
        } catch (\Exception $exception) {
            throw new TransformationFailedException('Tyr error: ' . $exception->getMessage());
        }

        foreach ($customer->getApplications() as $application) {
            if (array_key_exists($application->getId(), $this->oldCustomerApplications)) {
                $customerApplication = $this->oldCustomerApplications[$application->getId()];
                $customerApplication->setIsActive(true);
                $customerApplications->add($customerApplication);
                unset($this->oldCustomerApplications[$application->getId()]);
            } else {
                $customerApplications->add(
                    $this->createCustomerApplicationRelation(
                        $customer,
                        $application
                    )
                );
            }
        }
        $this->clearUnusableTokens();
        $customer->setApplications($customerApplications);

        return $customer;
    }
}
