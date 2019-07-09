<?php
/**
 * Entities tests Base
 */
namespace CanalTP\NmmPortalBundle\Tests\Units;

class EntityBase
{
    /**
     * Assert that a class has setters and getters
     *
     * @param \stdClass $entity
     * @param array $props Array with property names
     */
    protected function assertClassHasMethods($entity, array $props = [])
    {
        foreach ($props as $prop) {
            $setter = 'set'.ucfirst($prop);
            $getter = 'get'.ucfirst($prop);

            $this->assertTrue(method_exists($entity, $setter));
            $this->assertTrue(method_exists($entity, $getter));
        }
    }
}
