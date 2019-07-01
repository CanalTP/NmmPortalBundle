<?php

namespace CanalTP\NmmPortalBundle\Tests\Units\Entity;

use CanalTP\NmmPortalBundle\Tests\Units\EntityBase as EntityBaseTest;
use CanalTP\RealTimeBundle\Entity\Language as LanguageEntity;

class LanguageTest extends EntityBaseTest
{
    /**
     * @var LanguageEntity
     */
    private $entity;

    protected function setUp()
    {
        $this->entity = new LanguageEntity();
    }

    public function testMethodsExists()
    {
        $this->assertClassHasMethods($this->entity, ['code', 'label']);
    }

    /**
     * Test Language entity methods
     *
     * @param string $code
     * @param string $label
     *
     * @dataProvider languageDataProvider
     */
    public function testLanguageMethods($code, $label)
    {
        $this->entity->setCode($code);
        $this->entity->setLabel($label);

        $this->assertEquals($code, $this->entity->getCode());
        $this->assertEquals($label, $this->entity->getLabel());
    }

    /**
     * DataProvider for testLanguageMethods
     * @return array
     */
    public function languageDataProvider()
    {
        return [
            ['fr', 'français'],
            ['en', 'english'],
        ];
    }
}
