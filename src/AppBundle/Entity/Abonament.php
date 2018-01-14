<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Abonament
 *
 * @ORM\Table(name="abonament")
 * @ORM\Entity
 */
class Abonament
{
    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price)
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getAbonamentid(): int
    {
        return $this->abonamentid;
    }

    /**
     * @param int $abonamentid
     */
    public function setAbonamentid(int $abonamentid)
    {
        $this->abonamentid = $abonamentid;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdcurs(): \Doctrine\Common\Collections\Collection
    {
        return $this->idcurs;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $idcurs
     */
    public function setIdcurs(\Doctrine\Common\Collections\Collection $idcurs)
    {
        $this->idcurs = $idcurs;
    }
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
     * @var string
     *
     * @ORM\Column(name="Type", type="string", length=200, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="string", length=2000, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="AbonamentId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $abonamentid;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Curs", inversedBy="idabonament")
     * @ORM\JoinTable(name="curs_abonament",
     *   joinColumns={
     *     @ORM\JoinColumn(name="IdAbonament", referencedColumnName="AbonamentId")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="IdCurs", referencedColumnName="CursId")
     *   }
     * )
     */
    private $idcurs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idcurs = new \Doctrine\Common\Collections\ArrayCollection();
    }

}

