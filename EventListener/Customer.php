<?php

namespace CanalTP\NmmPortalBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use CanalTP\NmmPortalBundle\Entity\Customer as CustomerEntity;

class Customer
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof CustomerEntity && $entity->getLanguage() == null) {
            $em = $args->getEntityManager();
            $default = $em->getRepository("CanalTPNmmPortalBundle:Language")->findByCode('fr');
            $entity->setLanguage($default[0]);
        }
    }
}
