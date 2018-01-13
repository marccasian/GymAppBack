<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Profile
 *
 * @ORM\Table(name="profile", indexes={@ORM\Index(name="profile_user_idx", columns={"Username"})})
 * @ORM\Entity
 */
class Profile
{
    /**
     * @var string
     *
     * @ORM\Column(name="Sex", type="string", length=255, nullable=true)
     */
    private $sex;

    /**
     * @var integer
     *
     * @ORM\Column(name="Varsta", type="integer", nullable=true)
     */
    private $varsta;

    /**
     * @var string
     *
     * @ORM\Column(name="Fullname", type="string", length=255, nullable=true)
     */
    private $fullname;

    /**
     * @var integer
     *
     * @ORM\Column(name="ProfileId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $profileid;

    /**
     * @return string
     */
    public function getSex(): string
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     */
    public function setSex(string $sex)
    {
        $this->sex = $sex;
    }

    /**
     * @return int
     */
    public function getVarsta(): int
    {
        return $this->varsta;
    }

    /**
     * @param int $varsta
     */
    public function setVarsta(int $varsta)
    {
        $this->varsta = $varsta;
    }

    /**
     * @return string
     */
    public function getFullname(): string
    {
        return $this->fullname;
    }

    /**
     * @param string $fullname
     */
    public function setFullname(string $fullname)
    {
        $this->fullname = $fullname;
    }

    /**
     * @return int
     */
    public function getProfileid(): int
    {
        return $this->profileid;
    }

    /**
     * @param int $profileid
     */
    public function setProfileid(int $profileid)
    {
        $this->profileid = $profileid;
    }

    /**
     * @return User
     */
    public function getUsername(): User
    {
        return $this->username;
    }

    /**
     * @param User $username
     */
    public function setUsername(User $username)
    {
        $this->username = $username;
    }

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Username", referencedColumnName="Username")
     * })
     */
    private $username;


}

