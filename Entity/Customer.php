<?php

namespace CanalTP\NmmPortalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use CanalTP\SamCoreBundle\Entity\CustomerInterface;
use CanalTP\SamCoreBundle\Slugify;

class Customer extends \CanalTP\SamCoreBundle\Entity\AbstractEntity implements CustomerInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var file
     */
    protected $file;

    /**
     * @var string
     */
    protected $logoPath;

    /**
     * @var string
     */
    protected $nameCanonical;

    /**
     * @var boolean
     */
    private $locked;

    /**
     * @var Application
     */
    protected $applications;

    protected $users;

    protected $perimeters;

    private $navitiaEntity;

    private $language;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->perimeters = new ArrayCollection();
        $this->locked = false;
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

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Set logoPath
     *
     * @param string $logoPath
     * @return Customer
     */
    public function setLogoPath($logoPath)
    {
        $this->logoPath = $logoPath;

        return $this;
    }

    /**
     * Get logoPath
     *
     * @return string
     */
    public function getLogoPath()
    {
        return $this->logoPath;
    }

    public function setNavitiaEntity($navitiaEntity)
    {
        $this->navitiaEntity = $navitiaEntity;
    }

    public function getNavitiaEntity()
    {
        return $this->navitiaEntity;
    }

    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getEmail()
    {
        return $this->getNavitiaEntity()->getEmail();
    }

    public function setEmail($email)
    {
        $this->getNavitiaEntity()->setEmail($email);

        return $this;
    }

    public function getPerimeters()
    {
        return $this->getNavitiaEntity()->getPerimeters();
    }

    public function setPerimeters($perimeters)
    {
        $this->getNavitiaEntity()->setPerimeters($perimeters);

        return $this;
    }

    public function getNameCanonical()
    {
        return $this->nameCanonical;
    }

    public function getEmailCanonical()
    {
        return $this->getNavitiaEntity()->getEmailCanonical();
    }

    public function setName($name)
    {
        $this->name = $name;
        $this->setNameCanonical($name);

        return $this;
    }

    protected function setNameCanonical($name)
    {
        $this->nameCanonical = Slugify::format($name, '_');

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     * @return Customer
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    public function setApplications($applications)
    {
        $this->applications = $applications;

        return $this;
    }

    public function addApplication($application)
    {
        $this->applications->add($application);

        return $this;
    }

    public function removeApplication($application)
    {
        $this->applications->removeElement($application);

        return $this;
    }

    public function getApplications()
    {
        return $this->applications;
    }

    public function getActiveCustomerApplications()
    {
        return (
            $this->getApplications()->filter(
                function ($customerApplication) {
                    return ($customerApplication->getIsActive());
                }
            )
        );
    }

    public function addUser($user)
    {
        $this->users->add($user);

        return $this;
    }

    public function removeUser($user)
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set File
     *
     * @return LayoutConfig
     */
    public function getFile()
    {
        return ($this->file);
    }

    /**
     * Set file
     *
     * @return LayoutConfig
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return ($this);
    }

    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }
        $file = $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );
        $fileName = $this->getId() . '.' . $file->getExtension();
        $file->move(
            $this->getUploadRootDir(),
            $fileName
        );

        $this->logoPath = $fileName;
        $this->file = null;
    }

    public function getAbsoluteLogoPath()
    {
        return null === $this->logoPath
            ? null
            : $this->getUploadRootDir().'/'.$this->logoPath;
    }

    public function getWebLogoPath()
    {
        return null === $this->logoPath
            ? null
            : $this->getUploadDir().'/'.$this->logoPath;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web' . $this->getUploadDir();
    }

    private function getUploadDir()
    {
        return '/uploads/customers/logos/';
    }
}
