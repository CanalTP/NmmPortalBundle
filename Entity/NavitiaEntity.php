<?php

namespace CanalTP\NmmPortalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use CanalTP\SamCoreBundle\Slugify;


/**
 * NavitiaEntity
 */
class NavitiaEntity extends \CanalTP\SamCoreBundle\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $nameCanonical;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $emailCanonical;

    private $perimeters;
    protected $customer;
    protected $tokens;

    public function __construct()
    {
        $this->perimeters = new ArrayCollection();
    }

    public function addPerimeter($perimeter)
    {
        $this->perimeters->add($perimeter);
        $perimeter->setNavitiaEntity($this);

        return $this;
    }

    public function addPerimeters($perimeter)
    {
        $this->perimeters->add($perimeter);
        $perimeter->setNavitiaEntity($this);

        return $this;
    }

    public function removePerimeter($perimeter)
    {
        $this->perimeters->removeElement($perimeter);

        return $this;
    }

    public function setPerimeters($perimeters)
    {
        $this->perimeters = $perimeters;
        foreach ($perimeters as $perimeter) {
            $perimeter->setNavitiaEntity($this);
        }

        return $this;
    }

    public function refreshPerimeters()
    {
        foreach ($this->perimeters as $perimeter) {
            $perimeter->setNavitiaEntity($this);
        }

        return $this;
    }

    public function getPerimeters()
    {
        return $this->perimeters;
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return NavitiaEntity
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setNameCanonical($name);

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set nameCanonical
     *
     * @param string $nameCanonical
     * @return NavitiaEntity
     */
    protected function setNameCanonical($name)
    {
        $this->nameCanonical = Slugify::format($name);

        return $this;
    }

    /**
     * Get nameCanonical
     *
     * @return string
     */
    public function getNameCanonical()
    {
        return $this->nameCanonical;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return NavitiaEntity
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->setEmailCanonical($email);

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set emailCanonical
     *
     * @param string $emailCanonical
     * @return NavitiaEntity
     */
    protected function setEmailCanonical($email)
    {
        $this->emailCanonical = strtolower($email);

        return $this;
    }

    /**
     * Get emailCanonical
     *
     * @return string
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function setCustomer($cust)
    {
        $cust->setNavitiaEntity($this);
        $this->customer = $cust;

        return $this;
    }

    public function getTokens()
    {
        return $this->tokens;
    }
}
