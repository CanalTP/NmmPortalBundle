<?php

namespace CanalTP\NmmPortalBundle\Tests\Units\Entity;

use CanalTP\NmmPortalBundle\Tests\Units\EntityBase as EntityBaseTest;
use CanalTP\NmmPortalBundle\Entity\Customer as CustomerEntity;

class CustomerTest extends EntityBaseTest
{
    /**
     * @var CustomerEntity
     */
    private $entity;

    protected function setUp()
    {
        $this->entity = new CustomerEntity();
    }

    public function testMethodsExists()
    {
        $this->assertClassHasMethods($this->entity, [
            'identifier',
            'name',
            'file',
            'logoPath',
            'nameCanonical',
            'locked',
            'applications',
            'users',
            'perimeters',
            'navitiaEntity',
            'language']);
    }

    public function testGetContributor()
    {
        $this->entity->setIdentifier('canaltp');
        $this->assertEquals('shortterm.tr_canaltp', $this->entity->getContributor());
    }
}
