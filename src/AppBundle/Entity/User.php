<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user", uniqueConstraints={@ORM\UniqueConstraint(name="username_password", columns={"Username", "Password"}), @ORM\UniqueConstraint(name="UNIQ_8D93D64926535370", columns={"Email"})}, indexes={@ORM\Index(name="user_rol_idx", columns={"RolId"})})
 * @ORM\Entity
 */
class User
{
    /**
     * @var string
     *
     * @ORM\Column(name="Password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="Email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="Username", type="string", length=255)
     * @ORM\Id
     */
    private $username;

    /**
     * @var \AppBundle\Entity\Rol
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Rol")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="RolId", referencedColumnName="RolId")
     * })
     */
    private $rolid;

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return Rol
     */
    public function getRolid()
    {
        return $this->rolid;
    }

    /**
     * @param Rol $rolid
     */
    public function setRolid($rolid)
    {
        $this->rolid = $rolid;
    }

    public function __toString()
    {
        return "User: ".$this->username." Password: ".$this->password. " Email: ".$this->email." RolId: ".$this->rolid->getRolid();
    }


}

