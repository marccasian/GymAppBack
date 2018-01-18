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
     * @var string
     *
     * @ORM\Column(name="Description", type="string", length=1000, nullable=true)
     */
    private $description;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

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

    /**
     * @return \DateTime
     */
    public function getStartdate()
    {
        return $this->startdate;
    }

    /**
     * @param \DateTime $startdate
     */
    public function setStartdate($startdate)
    {
        $this->startdate = $startdate;
    }

    /**
     * @return \DateTime
     */
    public function getEnddate()
    {
        return $this->enddate;
    }

    /**
     * @param \DateTime $enddate
     */
    public function setEnddate($enddate)
    {
        $this->enddate = $enddate;
    }

    /**
     * @return int
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * @param int $places
     */
    public function setPlaces($places)
    {
        $this->places = $places;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getCursid()
    {
        return $this->cursid;
    }

    /**
     * @param int $cursid
     */
    public function setCursid($cursid)
    {
        $this->cursid = $cursid;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdabonament()
    {
        return $this->idabonament;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $idabonament
     */
    public function setIdabonament($idabonament)
    {
        $this->idabonament = $idabonament;
    }


}

