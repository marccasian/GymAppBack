<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Curs
 *
 * @ORM\Table(name="curs")
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
     * @var string
     *
     * @ORM\Column(name="Type", type="string", length=100, nullable=true)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="CursId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cursid;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Abonament", mappedBy="idcurs")
     */
    private $idabonament;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idabonament = new \Doctrine\Common\Collections\ArrayCollection();
    }

}

