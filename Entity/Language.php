<?php

namespace CanalTP\NmmPortalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Language extends \CanalTP\SamCoreBundle\Entity\AbstractEntity
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $label;


    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Language
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return Language
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function __toString()
    {
        return $this->code;
    }
}
