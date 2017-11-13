<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Evidentainscrieri
 *
 * @ORM\Table(name="evidentainscrieri", uniqueConstraints={@ORM\UniqueConstraint(name="user_curs", columns={"Username", "CursId"})}, indexes={@ORM\Index(name="evidenta_curs_idx", columns={"CursId"}), @ORM\Index(name="IDX_509A8491286421", columns={"Username"})})
 * @ORM\Entity
 */
class Evidentainscrieri
{
    /**
     * @var integer
     *
     * @ORM\Column(name="idevidentainscrieri", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idevidentainscrieri;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Username", referencedColumnName="Username")
     * })
     */
    private $username;

    /**
     * @var \AppBundle\Entity\Curs
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Curs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="CursId", referencedColumnName="CursId")
     * })
     */
    private $cursid;


}

