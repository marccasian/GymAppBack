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
     * @ORM\Column(name="Active", type="integer")
     */
    private $active;



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

    /**
     * @return \DateTime
     */
    public function getStartdate(): \DateTime
    {
        return $this->startdate;
    }

    /**
     * @param \DateTime $startdate
     */
    public function setStartdate(\DateTime $startdate)
    {
        $this->startdate = $startdate;
    }

    /**
     * @return \DateTime
     */
    public function getEnddate(): \DateTime
    {
        return $this->enddate;
    }

    /**
     * @param \DateTime $enddate
     */
    public function setEnddate(\DateTime $enddate)
    {
        $this->enddate = $enddate;
    }

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
     * @return Abonamenttype
     */
    public function getAbonamenttypeid(): Abonamenttype
    {
        return $this->abonamenttypeid;
    }

    /**
     * @param Abonamenttype $abonamenttypeid
     */
    public function setAbonamenttypeid(Abonamenttype $abonamenttypeid)
    {
        $this->abonamenttypeid = $abonamenttypeid;
    }

    /**
     * @return User
     */
    public function getUsername(): User
    {
        return $this->username;
    }

    /**
     * @param User $username
     */
    public function setUsername(User $username)
    {
        $this->username = $username;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function setActive(int $active)
    {
        $this->active = $active;
    }





}

