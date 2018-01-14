<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserAbonament
 *
 * @ORM\Table(name="user_abonament", indexes={@ORM\Index(name="user_abonament__abonament_fk_idx", columns={"IdAbonament"}), @ORM\Index(name="user_abonament__user_idx", columns={"IdUser"})})
 * @ORM\Entity
 */
class UserAbonament
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="AbonamentStartDate", type="datetime", nullable=true)
     */
    private $abonamentstartdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="AbonamentEndDate", type="datetime", nullable=true)
     */
    private $abonamentenddate;

    /**
     * @var integer
     *
     * @ORM\Column(name="Activ", type="integer", nullable=true)
     */
    private $activ;

    /**
     * @var integer
     *
     * @ORM\Column(name="Platit", type="integer", nullable=true)
     */
    private $platit;

    /**
     * @var integer
     *
     * @ORM\Column(name="Id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Abonament
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Abonament")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdAbonament", referencedColumnName="AbonamentId")
     * })
     */
    private $idabonament;

    /**
     * @var \AppBundle\Entity\Profile
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdUser", referencedColumnName="ProfileId")
     * })
     */
    private $iduser;


}

