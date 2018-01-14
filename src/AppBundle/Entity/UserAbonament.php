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

    /**
     * @return \DateTime
     */
    public function getAbonamentstartdate(): \DateTime
    {
        return $this->abonamentstartdate;
    }

    /**
     * @param \DateTime $abonamentstartdate
     */
    public function setAbonamentstartdate(\DateTime $abonamentstartdate)
    {
        $this->abonamentstartdate = $abonamentstartdate;
    }

    /**
     * @return \DateTime
     */
    public function getAbonamentenddate(): \DateTime
    {
        return $this->abonamentenddate;
    }

    /**
     * @param \DateTime $abonamentenddate
     */
    public function setAbonamentenddate(\DateTime $abonamentenddate)
    {
        $this->abonamentenddate = $abonamentenddate;
    }

    /**
     * @return int
     */
    public function getActiv(): int
    {
        return $this->activ;
    }

    /**
     * @param int $activ
     */
    public function setActiv(int $activ)
    {
        $this->activ = $activ;
    }

    /**
     * @return int
     */
    public function getPlatit(): int
    {
        return $this->platit;
    }

    /**
     * @param int $platit
     */
    public function setPlatit(int $platit)
    {
        $this->platit = $platit;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return Abonament
     */
    public function getIdabonament(): Abonament
    {
        return $this->idabonament;
    }

    /**
     * @param Abonament $idabonament
     */
    public function setIdabonament(Abonament $idabonament)
    {
        $this->idabonament = $idabonament;
    }

    /**
     * @return Profile
     */
    public function getIduser(): Profile
    {
        return $this->iduser;
    }

    /**
     * @param Profile $iduser
     */
    public function setIduser(Profile $iduser)
    {
        $this->iduser = $iduser;
    }

}

