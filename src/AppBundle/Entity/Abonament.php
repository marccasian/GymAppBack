<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Abonament
 *
 * @ORM\Table(name="abonament", indexes={@ORM\Index(name="abonament_user_idx", columns={"Username"}), @ORM\Index(name="abonament_abonamentType_idx", columns={"AbonamentTypeId"})})
 * @ORM\Entity
 */
class Abonament
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="StartDate", type="datetime", nullable=true)
     */
    private $startdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="EndDate", type="datetime", nullable=true)
     */
    private $enddate;

    /**
     * @var float
     *
     * @ORM\Column(name="Price", type="float", precision=10, scale=0, nullable=true)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="Level", type="integer", nullable=true)
     */
    private $level;

    /**
     * @var integer
     *
     * @ORM\Column(name="AbonamentId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $abonamentid;

    /**
     * @var \AppBundle\Entity\Abonamenttype
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Abonamenttype")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="AbonamentTypeId", referencedColumnName="AbonamentTypeId")
     * })
     */
    private $abonamenttypeid;

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

