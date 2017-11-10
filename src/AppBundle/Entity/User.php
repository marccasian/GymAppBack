<?php
/**
 *
 */
namespace AppBundle\Entity;

/**
 * User
 *
 * @ORM\Table(name="user", uniqueConstraints = {@ORM\UniqueConstraint(name="username_password", columns={"Username", "Password"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 *
 */
class User
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(name="Username", type="string", length=255)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="Password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="Email", type="string", length=255, unique=true)
     */
    private $email;
    

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername(string $username) : User
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername() : string
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password) : User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword() : string
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }
}

