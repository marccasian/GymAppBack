<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivationCode
 *
 * @ORM\Table(name="activation_code")
 * @ORM\Entity
 */
class ActivationCode
{
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    private $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="used", type="integer", nullable=false)
     */
    private $used;

    /**
     * @var integer
     *
     * @ORM\Column(name="idactivation_code", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idactivationCode;

    /**
     * ActivationCode constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getIdactivationCode(): int
    {
        return $this->idactivationCode;
    }

    /**
     * @param int $idactivationCode
     */
    public function setIdactivationCode(int $idactivationCode)
    {
        $this->idactivationCode = $idactivationCode;
    }

    /**
     * @return int
     */
    public function getUsed(): int
    {
        return $this->used;
    }

    /**
     * @param int $used
     */
    public function setUsed(int $used)
    {
        $this->used = $used;
    }





}

