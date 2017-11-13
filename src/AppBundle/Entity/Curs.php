<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Curs
 *
 * @ORM\Table(name="curs", indexes={@ORM\Index(name="curs_user_idx", columns={"Trainer"}), @ORM\Index(name="curs_cursType_idx", columns={"CursTypeId"})})
 * @ORM\Entity
 */
class Curs
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
     * @var integer
     *
     * @ORM\Column(name="Places", type="integer", nullable=true)
     */
    private $places;

    /**
     * @var integer
     *
     * @ORM\Column(name="Level", type="integer", nullable=true)
     */
    private $level;

    /**
     * @var integer
     *
     * @ORM\Column(name="CursId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cursid;

    /**
     * @var \AppBundle\Entity\Curstype
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Curstype")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="CursTypeId", referencedColumnName="CursTypeId")
     * })
     */
    private $curstypeid;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Trainer", referencedColumnName="Username")
     * })
     */
    private $trainer;


}

