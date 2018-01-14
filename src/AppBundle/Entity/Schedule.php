<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Schedule
 *
 * @ORM\Table(name="schedule", indexes={@ORM\Index(name="schedule_curs_fk_idx", columns={"IdCurs"}), @ORM\Index(name="schedule_trainer_fk_idx", columns={"IdTrainer"})})
 * @ORM\Entity
 */
class Schedule
{

    /**
     * @return int
     */
    public function getWeekday(): int
    {
        return $this->weekday;
    }

    /**
     * @param int $weekday
     */
    public function setWeekday(int $weekday)
    {
        $this->weekday = $weekday;
    }

    /**
     * @return \DateTime
     */
    public function getStarttime(): \DateTime
    {
        return $this->starttime;
    }

    /**
     * @param \DateTime $starttime
     */
    public function setStarttime(\DateTime $starttime)
    {
        $this->starttime = $starttime;
    }

    /**
     * @return \DateTime
     */
    public function getEndtime(): \DateTime
    {
        return $this->endtime;
    }

    /**
     * @param \DateTime $endtime
     */
    public function setEndtime(\DateTime $endtime)
    {
        $this->endtime = $endtime;
    }

    /**
     * @return \DateTime
     */
    public function getPeriodstartdate(): \DateTime
    {
        return $this->periodstartdate;
    }

    /**
     * @param \DateTime $periodstartdate
     */
    public function setPeriodstartdate(\DateTime $periodstartdate)
    {
        $this->periodstartdate = $periodstartdate;
    }

    /**
     * @return \DateTime
     */
    public function getPeriodenddate(): \DateTime
    {
        return $this->periodenddate;
    }

    /**
     * @param \DateTime $periodenddate
     */
    public function setPeriodenddate(\DateTime $periodenddate)
    {
        $this->periodenddate = $periodenddate;
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
     * @return Curs
     */
    public function getIdcurs(): Curs
    {
        return $this->idcurs;
    }

    /**
     * @param Curs $idcurs
     */
    public function setIdcurs(Curs $idcurs)
    {
        $this->idcurs = $idcurs;
    }

    /**
     * @return Profile
     */
    public function getIdtrainer(): Profile
    {
        return $this->idtrainer;
    }

    /**
     * @param Profile $idtrainer
     */
    public function setIdtrainer(Profile $idtrainer)
    {
        $this->idtrainer = $idtrainer;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="WeekDay", type="integer", nullable=true)
     */
    private $weekday;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="StartTime", type="datetime", nullable=true)
     */
    private $starttime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="EndTime", type="datetime", nullable=true)
     */
    private $endtime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="PeriodStartDate", type="datetime", nullable=true)
     */
    private $periodstartdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="PeriodEndDate", type="datetime", nullable=true)
     */
    private $periodenddate;

    /**
     * @var integer
     *
     * @ORM\Column(name="Id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Curs
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Curs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdCurs", referencedColumnName="CursId")
     * })
     */
    private $idcurs;

    /**
     * @var \AppBundle\Entity\Profile
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdTrainer", referencedColumnName="ProfileId")
     * })
     */
    private $idtrainer;


}

