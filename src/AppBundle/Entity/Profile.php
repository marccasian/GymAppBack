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
     * @var integer
     *
     * @ORM\Column(name="ProfileId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $profileid;

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

