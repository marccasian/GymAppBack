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

