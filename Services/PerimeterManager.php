<?php

namespace CanalTP\NmmPortalBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Description of PerimeterManager
 */
class PerimeterManager
{

    private $repository = null;
    private $om = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPNmmPortalBundle:Perimeter');
    }

    private function getPerimeterByExtenalNetworkId($perimeters, $externalNetworkId)
    {
        foreach ($perimeters as $perimeter) {
            if ($perimeter->getExternalNetworkId() == $externalNetworkId) {
                return ($perimeter);
            }
        }
        throw new AccessDeniedException();
    }

    public function findOneByExternalNetworkId($customer, $externalNetworkId)
    {
        $perimeters = $customer->getPerimeters();

        return ($this->getPerimeterByExtenalNetworkId($perimeters, $externalNetworkId));
    }

    public function findOneByCustomerIdAndExternalNetworkId($customerId, $externalNetworkId)
    {
        $customer = $this->om->getRepository('CanalTPNmmPortalBundle:Customer')->find($customerId);

        return ($this->getPerimeterByExtenalNetworkId($customer->getPerimeters(), $externalNetworkId));
    }

    public function find($networkId)
    {
        return $this->repository->find($networkId);
    }
}
